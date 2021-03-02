<?php

namespace App\Providers;

use App\Http\Controllers\Studios\StudioController;
use App\Models\Alarms\Alarm;
use App\Models\Bookings\Booking;
use App\Models\Bookings\BookingType;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Contracts\TenantHasContract;
use App\Models\HumanResources\RHAlarm;
use App\Models\Maintenance\MaintenanceAlarm;
use App\Models\News\NewsUsers;
use App\Models\Studios\TenantHasTenant;
use App\Models\Studios\UserHasTenant;
use App\Models\Tasks\TaskUserStatus;
use App\Models\Tenancy\Tenant;
use App\Models\Training\TrainingUsers;
use App\Models\Wiki\WikiUser;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use stdClass;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        if($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        Carbon::setLocale('es');

        if (Schema::hasTable('booking_types')){
            view()->share('schedules', BookingType::all());
        }

        view()->composer('*', function ($view)
        {
            if (session('studio_id') && !is_null(Auth::user())) {
                $tenant_id = session('studio_id');

                $central_tenant = Tenant::where('id', 1)->first();
                $data = [];

                $assignments = $central_tenant->run(function () use ($tenant_id) {
                    $user_id = Auth::user()->id;
                    $user_from_tenant = UserHasTenant::where('to_tenant_id', $tenant_id)->where('to_user_id', $user_id)->first();

                    if (is_null($user_from_tenant)) {
                        return [];
                    }

                    $from_user_id = $user_from_tenant->from_user_id;
                    $from_tenant_id = $user_from_tenant->from_tenant_id;

                    return UserHasTenant
                        ::where('from_user_id', $from_user_id)
                        ->where('from_tenant_id', $from_tenant_id)
                        ->with(['toTenant' => function ($query) {
                            $query->orderBy('data->studio_name');
                        }])->get();
                });

                view()->share('assignments', $assignments);

                // Maintenance Alarms
                $maintenance_alarms = 0;

                if (Schema::hasTable('maintenance_alarms')){
                    $maintenance_alarms = MaintenanceAlarm::where('user_id', Auth::user()->id)->where('viewed', 0)->count();
                }

                view()->share('maintenance_alarm', $maintenance_alarms);

                // Studio have access to Contracts
                $have_access = $central_tenant->run(function () use ($tenant_id) {
                    $tenant_access = TenantHasContract::where('tenant_id', $tenant_id)->first();

                    return is_null($tenant_access) ? false : true;
                });

                view()->share('studio_have_access', $have_access);

                // Alarms count
                $current_date = CarbonImmutable::now()->toDateString(); // 1 day

                $alarms_count = 0;

                $pending = Alarm::select('alarms.*')
                    ->where('status_id', 1)
                    ->where('showing_date', '<=', $current_date)
                    ->where('user_id', Auth::user()->id)
                    ->join('alarm_users', 'alarms.id', '=', 'alarm_users.alarm_id')
                    ->orderBy('showing_date', 'DESC')
                    ->count();

                $alarms_count = $alarms_count + $pending;

                if(Auth::user()->can('alarms-document-expiration')) {
                    $expiry_documents = [];

                    $users = User::where('status', 1)->orderBy('first_name')->orderBy('last_name')->get();

                    foreach ($users AS $user) {
                        if(!is_null($user->expiration_date)) {
                            $difference = Carbon::parse($user->expiration_date)->diff();

                            if($difference->invert && $difference->days > 15) {
                                continue;
                            }

                            $expiry_class = new stdClass();
                            $expiry_class->user_name = $user->roleUserShortName();
                            $expiry_class->role_name = $user->role->name;
                            $expiry_class->location_name = $user->location->name;
                            $expiry_class->expiration_date = Carbon::parse($user->expiration_date)->format('d / M / Y');
                            $expiry_class->expiration_in = ucfirst(Carbon::parse($user->expiration_date)->diffForHumans());

                            $expiry_documents[] = $expiry_class;
                        }
                    }

                    $expiry_documents = count($expiry_documents);
                    $alarms_count = $alarms_count + $expiry_documents;
                }

                if(Auth::user()->can('alarms-boutique-products')) {
                    $products = [];
                    $products['locations_alarms'] = [];
                    $products['stocks_alarms'] = [];

                    $boutique_products = BoutiqueProduct::with('boutiqueInventory')->where('active', 1)->where('stock_alarm', '!=', 0)->where('location_alarm', '!=', 0)->get();

                    foreach ($boutique_products AS $product) {
                        $product_total_stock = 0;

                        foreach ($product->boutiqueInventory AS $item) {
                            if($item->quantity <= $product->location_alarm) {
                                $product_location_alarm = new stdClass();
                                $product_location_alarm->product_name = $product->name;
                                $product_location_alarm->location_name = $item->settingLocation->name;
                                $product_location_alarm->quantity = $item->quantity;

                                $products['locations_alarms'][] = $product_location_alarm;
                            }

                            $product_total_stock = $product_total_stock + $item->quantity;
                        }

                        if($product_total_stock <= $product->stock_alarm) {
                            $product_stock_alarm = new stdClass();
                            $product_stock_alarm->product_name = $product->name;
                            $product_stock_alarm->quantity = $product_total_stock;

                            $products['stocks_alarms'][] = $product_stock_alarm;
                        }
                    }

                    $locations_alarms = count($products['locations_alarms']);
                    $alarms_count = $alarms_count + $locations_alarms;

                    $stocks_alarms = count($products['stocks_alarms']);
                    $alarms_count = $alarms_count + $stocks_alarms;
                }

                if(Auth::user()->can('alarms-contract-renovation')) {
                    $current_date = CarbonImmutable::now()->toDateString(); // 1 day
                    $search_date = CarbonImmutable::now()->subtract(25, 'weeks')->toDateString(); // 6 months + 1 week
                    $admission_date = CarbonImmutable::now()->addMonth()->toDateString();

                    $renewals = User::select()
                        ->where(
                            [
                                ['status', '=', 1],
                                ['is_admin', '=', 0],
                                ['contract_id', '=', 1],
                                ['contract_date', '<=', $search_date],
                            ])
                        ->orwhere([
                            ['contract_date', '<=', $admission_date],
                            ['status', '=', 1],
                            ['is_admin', '=', 0],
                            ['contract_id', '=', 1],
                            ['contract_date', '<=', $current_date]
                        ])
                        ->count();

                    $alarms_count = $alarms_count + $renewals;
                }

                view()->share('alarms_count', $alarms_count);

                // Tasks
                $bold_pulsing_tasks = false;
                $pulsing_tasks = TaskUserStatus::with('task')->where('user_id', Auth::user()->id)->where('pulsing', 1)->where('status', 0)->get();
                if (!is_null($pulsing_tasks)) {
                    if ($pulsing_tasks->count() > 0) {
                        foreach ($pulsing_tasks AS $task) {
                            if(is_null($task->task)) { continue; }

                            if ($task->task->status == 0) {
                                $bold_pulsing_tasks = true;
                                break;
                            }
                        }
                    }
                }

                view()->share('bold_pulsing_tasks', $bold_pulsing_tasks);

                //wiki
                $bold_pulsing_wiki = false;
                $pulsing_wiki = WikiUser::where('user_id', Auth::user()->id)->where('status', 0)->where('studio_id', \tenant('id'))->get();
                if (!is_null($pulsing_wiki)){
                    if ($pulsing_wiki->count() > 0){
                        foreach ($pulsing_wiki AS $wiki) {
                            if ($wiki->status == 0) {
                                $bold_pulsing_wiki = true;
                                break;
                            }
                        }
                    }
                }
                view()->share('bold_pulsing_wiki', $bold_pulsing_wiki);


                //news
                $bold_pulsing_news = false;
                $pulsing_news = NewsUsers::where('user_id', Auth::user()->id)->where('status', 0)->where('studio_id', \tenant('id'))->get();
                if (!is_null($pulsing_news)){
                    if ($pulsing_news->count() > 0){
                        foreach ($pulsing_news AS $news) {
                            if ($news->status == 0) {
                                $bold_pulsing_news = true;
                                break;
                            }
                        }
                    }
                }
                view()->share('bold_pulsing_news', $bold_pulsing_news);

                // HR
                $pulsing_rh = RHAlarm::firstOrCreate(
                    [
                        'user_id' => Auth::user()->id
                    ],
                    [
                        'user_id' => Auth::user()->id,
                    ]
                );

                $rh_bolts = [
                    'main' => ($pulsing_rh->rha_interviews || $pulsing_rh->rha_extra_assign || $pulsing_rh->rha_extra_request || $pulsing_rh->rha_sol_vac || $pulsing_rh->rha_annotate_vac),
                    'prospects' => $pulsing_rh->rha_interviews,
                    'extra_hours' => $pulsing_rh->rha_extra_assign,
                    'extra_hours_requests' => $pulsing_rh->rha_extra_request,
                    'vacations_requests' => $pulsing_rh->rha_sol_vac,
                    'vacations' => $pulsing_rh->rha_annotate_vac,
                ];

                view()->share('rh_bolts', $rh_bolts);

                //training
                $bold_pulsing_training = false;
                $pulsing_training = TrainingUsers::where('user_id', Auth::user()->id)->where('status', 0)->where('studio_id', \tenant('id'))->get();
                if (!is_null($pulsing_training)){
                    if ($pulsing_training->count() > 0){
                        foreach ($pulsing_training AS $training) {
                            if ($training->status == 0) {
                                $bold_pulsing_training = true;
                                break;
                            }
                        }
                    }
                }
                view()->share('bold_pulsing_training', $bold_pulsing_training);

                //audiovisuals
                $bold_pulsing_audiovisual = false;
                $pulsing_audiovisual = Booking::where('status', 0)->where('booking_type_id', 1)->get();
                if (!is_null($pulsing_audiovisual)){
                    if ($pulsing_audiovisual->count() > 0){
                        foreach ($pulsing_audiovisual AS $audiovisual) {
                            if ($audiovisual->status == 0) {
                                $bold_pulsing_audiovisual = true;
                                break;
                            }
                        }
                    }
                }
                view()->share('bold_pulsing_audiovisual', $bold_pulsing_audiovisual);

                view()->share('session', session()->all());
            }
        });
    }
}
