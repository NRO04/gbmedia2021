<?php

namespace App\Http\Controllers\Alarms;

use App\Http\Controllers\Controller;
use App\Models\Alarms\Alarm;
use App\Models\Alarms\AlarmRole;
use App\Models\Alarms\AlarmUser;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Settings\SettingRole;
use App\Models\Users\UserDocument;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;
use Yajra\DataTables\DataTables;

class AlarmController extends Controller
{
    protected $guard = [];

    protected $current_date = '';

    public function __construct()
    {
        $this->middleware('auth');
        //$this->middleware(['permission:alarms']);
        $this->current_date = Carbon::now()->toDateString();
    }

    public function alarms()
    {
        $user = auth()->user();
        $roles = SettingRole::where('name', '!=', 'sin rol')->orderBy('name', 'asc')->get();
        $models = User::where('is_admin', 0)->where('status', 1)->where('setting_role_id', 14)->orderBy('nick', 'asc')->get();

        $current_date = CarbonImmutable::now()->toDateString(); // 1 day
        $search_date = CarbonImmutable::now()->subtract(25, 'weeks')->toDateString(); // 6 months + 1 week
        $admission_date = CarbonImmutable::now()->addMonth()->toDateString();

        // Contracts renewals
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
            ->get();

        foreach ($renewals AS $renewal) {
            $contract_date = Carbon::parse($renewal->contract_date);
            $renewal->contract_singning_date = $contract_date->add(6, 'months')->toDateString();
        }

        // Pending Alarms
        $alarms = Alarm::select('alarms.*')
            ->where('status_id', 1)
            ->where('showing_date', '<=', $this->current_date)
            ->where('user_id', $user->id)
            ->join('alarm_users', 'alarms.id', '=', 'alarm_users.alarm_id')
            ->orderBy('showing_date', 'DESC')
            ->get();

        // Documents expiry
        $expiry_documents = [];

        $users = User::where('status', 1)->orderBy('first_name')->orderBy('last_name')->get();

        foreach ($users AS $user) {
            if(!is_null($user->expiration_date)) {
                $difference = Carbon::parse($user->expiration_date)->diff();

                if($difference->invert && $difference->days > 15) {
                    continue;
                }

                $expiry_class = new stdClass();
                $expiry_class->user_name = $user->first_name . " " . $user->last_name;
                $expiry_class->role_name = $user->role->name;
                $expiry_class->location_name = $user->location->name;
                $expiry_class->expiration_date = Carbon::parse($user->expiration_date)->format('d / M / Y');
                $expiry_class->expiration_in = ucfirst(Carbon::parse($user->expiration_date)->diffForHumans());

                $expiry_documents[] = $expiry_class;
            }
        }

        $expiry_documents = collect($expiry_documents);

        // Boutique products
        $products = [
            'locations_alarms' => [],
            'stocks_alarms' => [],
        ];

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

        $products = collect($products);

        foreach ($alarms AS $alarm) {
            $roles_receivers = [];
            $users_receivers = [];

            $alarm_roles  = $alarm->roles;
            $alarm_users  = $alarm->users;

            foreach ($alarm_roles AS $alarm_role) {
                $role = SettingRole::find($alarm_role->setting_role_id);
                $roles_receivers[] = $role->name;
            }

            foreach ($alarm_users AS $alarm_user) {
                $user = User::find($alarm_user->user_id);
                $users_receivers[] = $user->setting_role_id == 14 ? $user->nick : $user->first_name . " " . $user->last_name;
            }

            $alarm->roles_receivers_count = count($roles_receivers);
            $alarm->roles_receivers = collect($roles_receivers);

            $alarm->users_receivers_count = count($users_receivers);
            $alarm->users_receivers = collect($users_receivers);
        }

        return view('adminModules.alarm.alarms', compact('renewals', 'alarms', 'expiry_documents', 'products', 'roles', 'users', 'models'));
    }

    public function list()
    {
        $roles = SettingRole::where('name', '!=', 'sin rol')->orderBy('id', 'asc')->get();
        $users = User::where('is_admin', 0)->where('status', 1)->where('setting_role_id', '!=', 14)->orderBy('first_name')->orderBy('last_name')->get(); // No 'Modelos'
        $models = User::where('is_admin', 0)->where('status', 1)->where('setting_role_id', 14)->orderBy('nick', 'asc')->get();

        return view('adminModules.alarm.list', compact('roles', 'users', 'models'));
    }

    public function finished()
    {
        return view('adminModules.alarm.finished');
    }

    public function finish(Request $request)
    {
        $new_showing_date = null;

        $id = $request->id;

        $user = auth()->user();

        $alarm = Alarm::find($id);
        $cycle_count = $alarm->cycle_count;
        $cycle = $alarm->cycle;
        $is_fixed_date = $alarm->is_fixed_date;
        $showing_date = $alarm->showing_date;

        switch ($cycle) {
            case 'weekly':
                $new_showing_date = Carbon::parse(($is_fixed_date ? $showing_date : $this->current_date))->addWeeks($cycle_count);
            break;

            case 'monthly':
                $new_showing_date = Carbon::parse(($is_fixed_date ? $showing_date : $this->current_date))->addMonths($cycle_count);
            break;

            case 'yearly':
                $new_showing_date = Carbon::parse(($is_fixed_date ? $showing_date : $this->current_date))->addYears($cycle_count);
            break;
        }

        $alarm->status_id = 2; // finished
        $alarm->finished_by = $user->id;
        $alarm->finished_date = $this->current_date;
        $alarm->showing_date = $new_showing_date;
        $success = $alarm->save();

        if ($success) {
            return response()->json(['success' => $success]);
        } else {
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al finalizar la alarma. Intente mas tarde.', 'code' => 1], 500);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request,
            [
                'name' => 'required|max:255',
                'for' => 'required',
                'cycle' => 'required',
                'date' => 'required',
                'timer' => 'required',
                'cycle_type' => 'required',
            ],
            [
                'name.required' => 'Debe ingresar el nombre de la alarma',
                'for.required' => '',
                'cycle_type.required' => '',
            ]
        );

        $users = [];
        $role_users = null;

        $alarm = new Alarm();
        $alarm->name = $request->name;
        $alarm->status_id = Carbon::parse($request->date)->lt(Carbon::now()) ? 1 : 2;
        $alarm->showing_date = $request->date;
        $alarm->cycle_count = $request->timer;
        $alarm->cycle = $request->cycle;
        $alarm->is_fixed_date = ($request->cycle_type == 'fixed-date' ? 1 : 0);

        $receivers = json_decode($request->receivers);
        $to_roles = $receivers->to_roles;
        $to_users = $receivers->to_users;
        $to_models = $receivers->to_models;

        $roles_ids = [];

        // Roles
        foreach ($to_roles AS $role) {
            $roles_ids[] = $role->id;
        }

        // Users
        foreach ($to_users AS $user) {
            $users[] = $user->id;
        }

        // Models
        foreach ($to_models AS $model) {
            $users[] = $model->id;
        }

        $success = $alarm->save();
        $alarm_id = $alarm->id;

        if($success) {
            $role_users = User::whereIn('setting_role_id', $roles_ids)->get();

            foreach ($role_users AS $user) {
                if(!in_array($user->id, $users)) {
                    $users[] = $user->id;
                }
            }

            foreach ($users AS $user) {
                $alarmUser = new AlarmUser();
                $alarmUser->alarm_id = $alarm_id;
                $alarmUser->user_id = $user;
                $alarmUser->save();
            }

            foreach ($roles_ids AS $role) {
                $alarmUser = new AlarmRole();
                $alarmUser->alarm_id = $alarm_id;
                $alarmUser->setting_role_id = $role;
                $alarmUser->save();
            }
        }

        if ($success) {
            return response()->json(['success' => $success]);
        } else {
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al crear la alarma. Intente mas tarde.', 'code' => 1], 500);
        }
    }

    public function getAlarms(Request $request)
    {
        if ($request->ajax()) {
            $alarms = Alarm::orderBy('name', 'asc')->get();

            foreach ($alarms AS $alarm) {
                $roles_receivers = [];
                $users_receivers = [];

                $alarm_roles  = $alarm->roles;
                $alarm_users  = $alarm->users;

                foreach ($alarm_roles AS $alarm_role) {
                    $role = SettingRole::find($alarm_role->setting_role_id);
                    $roles_receivers[] = $role->name;
                }

                foreach ($alarm_users AS $alarm_user) {
                    $user = User::find($alarm_user->user_id);
                    $users_receivers[] = $user->setting_role_id == 14 ? $user->nick : $user->first_name . " " . $user->last_name;
                }

                $alarm->roles_receivers_count = count($roles_receivers);
                $alarm->roles_receivers = collect($roles_receivers);

                $alarm->users_receivers_count = count($users_receivers);
                $alarm->users_receivers = collect($users_receivers);
            }

            return DataTables::of($alarms)
                ->addIndexColumn()
                ->addColumn('for_users', function($row) {
                    return '<span class="badge badge-info" title="" data-toggle="tooltip" data-html="true" data-original-title="' . $row->users_receivers->implode(', ') . '">' . ($row->users_receivers_count > 0 ? $row->users_receivers_count : "") . '</span>';;
                })
                ->addColumn('for_roles', function($row) {
                    return '<span class="badge badge-info" title="" data-toggle="tooltip" data-html="true" data-original-title="' . $row->roles_receivers->implode(', ') . '">' . ($row->roles_receivers_count > 0 ? $row->roles_receivers_count : "") . '</span>';;
                })
                ->addColumn('cycle', function($row) {
                    if($row->cycle == 'weekly') {
                        $cycle_show = "$row->cycle_count semanas";
                    }
                    elseif($row->cycle == 'monthly') {
                        $cycle_show = "$row->cycle_count meses";
                    }
                    elseif($row->cycle == 'yearly') {
                        $cycle_show = "$row->cycle_count años";
                    }

                    return $cycle_show;
                })
                ->addColumn('showing_date', function($row) {
                    return Carbon::parse($row->showing_date)->format('d / M / Y');
                })
                ->addColumn('actions', function($row) {
                    $btn_edit = (Auth::user()->can('alarms-edit')) ? "<button type='button' class='btn btn-sm btn-warning' onclick='Edit($row->id)'><i class='fa fa-edit'></i></button>" : "";
                    $btn_delete = (Auth::user()->can('alarms-delete')) ? "<button type='button' class='btn btn-sm btn-danger' onclick='Delete($row->id)'><i class='fa fa-trash'></i></button>" : "";

                    return "$btn_edit $btn_delete";
                })
                ->rawColumns(['actions', 'for_users', 'for_roles'])
                ->make(true);
        }
    }

    public function getFinishedAlarms(Request $request)
    {
        if ($request->ajax()) {
            $alarms = Alarm::select('alarms.*')
                ->where('status_id', 2)
                ->orderBy('finished_date', 'DESC')
                ->get();

            //dd($alarms);
            return DataTables::of($alarms)
                ->addIndexColumn()
                ->addColumn('cycle', function($row) {
                    if($row->cycle == 'weekly') {
                        $cycle_show = "$row->cycle_count semanas";
                    }
                    elseif($row->cycle == 'monthly') {
                        $cycle_show = "$row->cycle_count meses";
                    }
                    elseif($row->cycle == 'yearly') {
                        $cycle_show = "$row->cycle_count años";
                    }

                    return $cycle_show;
                })
                ->addColumn('finished_by', function($row) {
                    $user = User::find($row->finished_by);
                    if(!is_null($row->finished_by)) {
                        return "$user->first_name $user->last_name";
                    } else {
                        return '';
                    }
                })
                ->addColumn('finished_date', function($row) {
                    if(!is_null($row->finished_date)) {
                        return Carbon::parse($row->finished_date)->locale('es')->format('d / M / Y');
                    } else {
                        return '';
                    }
                })
                ->make(true);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $alarm = Alarm::find($id);
        $success = $alarm->delete();

        if ($success) {
            return response()->json(['success' => $success]);
        } else {
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al eliminar la alarma. Intente mas tarde.', 'code' => 1], 500);
        }
    }

    public function edit(Request $request)
    {
        $this->validate($request,
            [
                'name' => 'required|max:255',
                'cycle' => 'required',
                'date' => 'required',
                'timer' => 'required|gt:0',
                'cycle_type' => 'required',
            ],
            [
                'name.required' => 'Debe ingresar el nombre de la alarma',
                'for.required' => '',
                'cycle_type.required' => '',
                'timer.gt' => 'Mínimo 1',
            ]
        );

        $alarm_id = $request->id;
        $alarm = Alarm::find($alarm_id);
        $alarm->name = $request->name;
        $alarm->is_fixed_date = ($request->cycle_type == 'fixed-date' ? 1 : 0);
        $alarm->showing_date = $request->date;
        $alarm->cycle_count = $request->timer;
        $alarm->cycle = $request->cycle;
        $alarm->updated_at = Carbon::now();

        $receivers = json_decode($request->receivers);
        $to_roles = $receivers->to_roles;
        $to_users = $receivers->to_users;
        $to_models = $receivers->to_models;

        $roles_ids = [];

        // Roles
        foreach ($to_roles AS $role) {
            $roles_ids[] = $role->id;
        }

        // Users
        foreach ($to_users AS $user) {
            $users[] = $user->id;
        }

        // Models
        foreach ($to_models AS $model) {
            $users[] = $model->id;
        }

        $success = $alarm->update();

        if($success) {
            // Reset receivers
            $this->resetAlarmReceivers($alarm_id);

            $role_users = User::whereIn('setting_role_id', $roles_ids)->get();

            foreach ($role_users AS $user) {
                if(!in_array($user->id, $users)) {
                    $users[] = $user->id;
                }
            }

            foreach ($users AS $user) {
                $alarmUser = new AlarmUser();
                $alarmUser->alarm_id = $alarm_id;
                $alarmUser->user_id = $user;
                $alarmUser->save();
            }

            foreach ($roles_ids AS $role) {
                $alarmUser = new AlarmRole();
                $alarmUser->alarm_id = $alarm_id;
                $alarmUser->setting_role_id = $role;
                $alarmUser->save();
            }
        }

        if ($success) {
            return response()->json(['success' => $success]);
        } else {
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al editar la alarma. Intente mas tarde.', 'code' => 1], 500);
        }
    }

    public function getAlarm(Request $request)
    {
        $roles = [];
        $roles_names = [];

        $users = [];
        $users_names = [];

        $models = [];
        $models_names = [];

        $showing_receivers = [];

        $alarm = Alarm::with('roles')->with('users')->where('id', $request->id)->first();

        foreach ($alarm->roles AS $role) {
            $role = SettingRole::findOrFail($role->setting_role_id);
            $roles[] = [
                'id' => $role->id,
                'name' => $role->name,
            ];

            $roles_names[] = $role->name;
            $showing_receivers[] = $role->name;
        }

        foreach ($alarm->users AS $user) {
            $user = User::findOrFail($user->user_id);

            if($user->setting_role_id == 14) {
                $models[] = [
                    'id' => $user->id,
                    'name' => $user->nick,
                ];

                $models_names[] = $user->nick;
                $showing_receivers[] = $user->nick;
            } else {
                $users[] = [
                    'id' => $user->id,
                    'name' => "$user->first_name $user->last_name",
                ];

                $users_names[] = "$user->first_name $user->last_name";
                $showing_receivers[] = "$user->first_name $user->last_name";
            }
        }

        $alarm->models = collect($models);
        $alarm->showing_roles = collect($roles_names)->implode(', ');
        $alarm->showing_users = collect($users_names)->implode(', ');
        $alarm->showing_models = collect($models_names)->implode(', ');
        $alarm->showing_receivers = collect($showing_receivers)->implode(', ');

        return response()->json($alarm);
    }

    public function resetAlarmReceivers($alarm_id)
    {
        AlarmUser::where('alarm_id', $alarm_id)->delete();
        AlarmRole::where('alarm_id', $alarm_id)->delete();
    }

    public function execute()
    {
        try {
            DB::beginTransaction();

            $GB_tasks_roles = [
                1 => 7,
                2 => 10,
                3 => 17,
                4 => 18,
                5 => 1,
            ];

            $GB_cycles = [
                'mes' => 'monthly',
                'meses' => 'monthly',
                'semana' => 'weekly',
                'semanas' => 'weekly',
                'aÃ±o' => 'yearly',
                'aÃ±os' => 'yearly',
                'año' => 'yearly',
                'años' => 'yearly',
            ];

            $min_id = 1; //
            $max_id = 200;

            $alarms = DB::connection('gbmedia')->table('alarma_periodica')->whereBetween('id', [$min_id, $max_id])->get();

            //dd($alarms);

            if (!Schema::hasColumn('alarms', 'old_alarm_id'))
            {
                Schema::table('alarms', function (Blueprint $table) {
                    $table->string('old_alarm_id')->nullable();
                });
            }

            //dd($tasks);
            foreach ($alarms AS $alarm) {
                $name = utf8_decode($alarm->nombre);
                $status = $alarm->estado == 'mostrar' ? 1 : 2;
                $showing_date = $alarm->fecha_mostrar;
                $cycle_count = $alarm->numero;
                $cycle = $GB_cycles[$alarm->tipo_temp];
                $is_fixed_date = $alarm->fecha_fija;
                $finished_by = null;
                $finished_date = $alarm->finalizada_fecha;
                $roles = explode(',', trim($alarm->roles_id));

                $user_name = trim($alarm->finalizada_por);
                $exploded = explode(' ', $user_name);

                if ($status == 2 && !is_null($finished_date)) {
                    if (count($exploded) > 0) {
                        $user = User::where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();

                        if (is_object($user)) {
                            $finished_by = $user->id;
                        } else {
                            $finished_by = 473;
                        }
                    }
                } else {
                    $finished_by = null;
                }

                $created_alarm = Alarm::firstOrCreate(
                    ['old_alarm_id' => $alarm->id],
                    [
                        'name' => $name,
                        'status_id' => $status,
                        'showing_date' => $showing_date,
                        'cycle_count' => $cycle_count,
                        'cycle' => $cycle,
                        'is_fixed_date' => $is_fixed_date,
                        'finished_by' => $finished_by,
                        'finished_date' => $finished_date,
                        'old_alarm_id' => $alarm->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );

                $created_alarm->name = $name;
                $created_alarm->status_id = $status;
                $created_alarm->showing_date = $showing_date;
                $created_alarm->cycle_count = $cycle_count;
                $created_alarm->cycle = $cycle;
                $created_alarm->is_fixed_date = $is_fixed_date;
                $created_alarm->finished_by = $finished_by;
                $created_alarm->finished_date = $finished_date;
                $created_alarm->old_alarm_id = $alarm->id;
                $created_alarm->save();

                $GB_roles = [
                    11 => 4,
                    12 => 6,
                    13 => 9,
                    14 => 1,
                    15 => 2,
                    16 => 10,
                    17 => 7,
                    18 => 5,
                    19 => 14,
                    20 => 8,
                    21 => 13,
                    22 => 11,
                    39 => 15,
                    41 => 16,
                    44 => 3,
                    45 => 12,
                    46 => 17,
                    47 => 18,
                    48 => 19,
                    49 => 20,
                    50 => 21,
                    51 => 22,
                    52 => 23,
                    53 => 24,
                    54 => 38,
                    55 => 26,
                    56 => 27,
                    57 => 28,
                    58 => 29,
                    59 => 30,
                    60 => 31,
                    61 => 32,
                    62 => 33,
                    63 => 34,
                    64 => 35,
                    65 => 36,
                    66 => 39,
                    67 => 37,
                    68 => 40,
                ];

                foreach ($roles AS $role) {
                    $role_id = $GB_roles[intval($role)];

                    $alarm_role = AlarmRole::firstOrCreate(
                        [
                            'alarm_id' => $created_alarm->id,
                            'setting_role_id' => $role_id
                        ],
                        [
                            'alarm_id' => $created_alarm->id,
                            'setting_role_id' => $role_id
                        ]
                    );

                    $alarm_role->alarm_id = $created_alarm->id;
                    $alarm_role->setting_role_id = $role_id;
                    $alarm_role->save();

                    $roles_users = User::select(['id', 'first_name', 'last_name'])->where('setting_role_id', $role_id)->where('status', 1)->get();

                    foreach ($roles_users AS $user) {
                        $user_role = AlarmUser::firstOrCreate(
                            [
                                'alarm_id' => $created_alarm->id,
                                'user_id' => $user->id
                            ],
                            [
                                'alarm_id' => $created_alarm->id,
                                'user_id' => $user->id
                            ]
                        );

                        $user_role->alarm_id = $created_alarm->id;
                        $user_role->user_id = $user->id;
                        $user_role->save();
                    }
                }
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }
}
