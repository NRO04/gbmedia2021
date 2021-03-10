<?php

namespace App\Http\Controllers\Users;
use App\Exports\Cafeteria\Orders;
use App\Http\Controllers\Payrolls\PayrollController;
use App\Http\Controllers\Tasks\TaskController;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceComment;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Bookings\Booking;
use App\Models\Bookings\BookingType;
use App\Models\Cafeteria\CafeteriaOrder;
use App\Models\Contracts\Contract;
use App\Models\Contracts\RoleHasContract;
use App\Events\Satellite\Payment\PaymentAccount;
use App\Models\Contracts\TenantHasContract;
use App\Models\HumanResources\RHVacationRequest;
use App\Models\HumanResources\RHVacationUser;
use App\Models\monitoring\Monitoring;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Satellite\SatelliteAccountLog;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Satellite\SatelliteOwnerCommissionRelation;
use App\Models\Satellite\SatelliteOwnerPaymentInfo;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Models\Satellite\SatellitePaymentFile;
use App\Models\Schedule\Schedule;
use App\Models\Settings\SettingPage;
use App\Models\Globals\Bank;
use App\Models\Globals\BloodType;
use App\Models\Globals\City;
use App\Models\Globals\Department;
use App\Models\Globals\Document;
use App\Models\Globals\GlobalCountry;
use App\Models\Globals\GlobalEPS;
use App\Models\Globals\GlobalTypeContract;
use App\Models\HumanResources\RHExtraValue;
use App\Models\Payrolls\Payroll;
use App\Models\Payrolls\PayrollMovement;
use App\Models\Statistics\StatisticSummary;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Models\Users\UserDocument;
use App\Models\Users\UserRetirementHistory;
use App\Traits\TraitGlobal;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings\SettingRole;
use App\Models\Settings\SettingLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use stdClass;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    use TraitGlobal;

	public function create(Request $request)
    {
        $this->validate($request,
        [
            'first_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
        ],
        [
            'first_name.required' => 'Este campo es obligatorio',
            'last_name.required' => 'Este campo es obligatorio',
            'second_last_name.required' => 'Este campo es obligatorio',
            'birth_date.required' => 'Este campo es obligatorio',
            'email.required' => 'Este campo es obligatorio',
            'email.unique' => 'El email ya existe',
            'password.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $user = new User();
            $user->first_name               = $request->first_name;
            $user->middle_name              = $request->middle_name;
            $user->last_name                = $request->last_name;
            $user->second_last_name         = $request->second_lastname;
            $user->birth_date               = $request->birth_date;
            $user->email                    = $request->email;
            $user->password                 = bcrypt($request->password);
            $user->nick                     = trim($request->model_nick);
            $user->setting_role_id          = $request->role_id;
            $user->setting_location_id      = $request->location_id;
            $user->blood_type_id            = 1;
            $user->department_id            = $request->department_id;
            $user->city_id                  = $request->city_id;
            $user->bank_account_document_id = 1;
            $user->document_id              = 1;
            $user->theme                    = 'c-app c-dark-theme';
            $user->email_verified_at        = Carbon::now();
            $success = $user->save();
            $user_id = $user->id;

            if($request->role_id == 14) {
                $owner = new SatelliteOwner();
                $owner->owner = trim($request->model_nick);
                $owner->email = $request->email;
                $owner->first_name = $request->first_name;
                $owner->second_name = $request->middle_name;
                $owner->last_name = $request->last_name;
                $owner->second_last_name = $request->second_lastname;
                $owner->statistics_emails = $request->email;
                $owner->department_id = $request->department_id;
                $owner->city_id = $request->city_id;
                $owner->commission_percent = 50;
                $owner->is_user = 1;
                $owner->user_id = $user_id;
                $owner->save();
            }

            $role_name = SettingRole::find($request->role_id)->first();
            $user->assignRole($role_name->name);

            DB::commit();

            return response()->json(['success' => $success, 'user_id' => $user_id]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

	public function edit(Request $request, $user_id)
    {
        //dd($user_id);
        $user = User::where('id', $user_id)->with('documents')->with('roles')->first();
        $roles = SettingRole::orderBy('name', 'asc')->get();
        $locations = SettingLocation::all();
        $user_permissions = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $departments = Department::orderBy('name')->get();
        $countries = GlobalCountry::orderBy('name')->get();
        $blood_types = BloodType::orderBy('name')->get();
        $contract_types = GlobalTypeContract::all();
        $documents_types = Document::where('is_listed', 1)->get();
        $banks = Bank::all();
        $eps = GlobalEPS::orderBy('name')->get();
        $transportation_aid_value = RHExtraValue::select('transportation_aid')->where('id', 1)->first();
        $quarter_transportation_aid_value = $transportation_aid_value->transportation_aid / 2;
        $contracts = Contract::all();
        $data_contracts = RoleHasContract::where('setting_role_id', $user->setting_role_id)->with('contract')->get();
        $user_contracts = [];
        $have_contracts_access = TenantHasContract::where('tenant_id', tenant('id'))->first();
        $have_contracts_access = is_null($have_contracts_access) ? false : true;

        foreach ($data_contracts AS $user_contract) {
            if($user_contract->contract->active == 0) { continue; }

            if($user_contract->contract->id == 10 && $user->contract_id != 2) {
                continue;
            }

            $user_contracts[] = $user_contract;
        }

        return view('adminModules.user.users.edit')->with(
            compact([
                'user',
                'roles',
                'locations',
                'user_permissions',
                'departments',
                'countries',
                'blood_types',
                'contract_types',
                'documents_types',
                'banks',
                'eps',
                'quarter_transportation_aid_value',
                'user_contracts',
                'have_contracts_access',
            ])
        );
    }

    public function listUsersInactives()
    {
        return view('adminModules.user.users.inactive');
    }

    public function getUsers(Request $request)
    {
        $users = User::where('status', $request->user_status)->get();

        $cont = 0;
        $result = [];
        foreach($users as $key => $user){
            $result[$cont]["id"] = $user->id;
            $result[$cont]["avatar"] = is_null($user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
            $result[$cont]["full_name"] = $user->userFullName();
            $result[$cont]["email"] = $user->email;
            $result[$cont]["role"] = $user->role->name;
            $result[$cont]["location"] = $user->location->name;
            $result[$cont]["id_card"] = 1;
            $result[$cont]["id_card_front"] = 1;
            $result[$cont]["rut"] = 1;
            $result[$cont]["work_permision"] = 1;
            $cont++;
        }

        return response()->json($result);
    }

    public function getUsersBirthday($month)
    {
        $users = User::where('status', 1)->whereMonth('birth_date', $month)->orderByRaw('DAY(birth_date)', 'ASC')->get();

        $cont = 0;
        $result = [];
        foreach($users as $key => $user){
            $result[$cont]["id"] = $user->id;
            $result[$cont]["birth_date"] = $user->birth_date;
            $result[$cont]["avatar"] = is_null($user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
            $result[$cont]["full_name"] = ($user->setting_role_id == 14)? $user->nick : $user->userFullName();
            $result[$cont]["role"] = $user->role->name;
            $result[$cont]["location"] = $user->location->name;
            $result[$cont]["birthday"] = (Carbon::now()->day == Carbon::parse($user->birth_date)->day && Carbon::now()->month == Carbon::parse($user->birth_date)->month)? 1 : 0;
            $cont++;
        }

        return response()->json($result);
    }

    public function modelPageAccess()
    {
        $accounts = SatelliteAccount::select('id', 'nick', 'page_id', 'access', 'password')->where('user_id', Auth::user()->id)->get();

        $cont = 0;
        $result = [];

        foreach ($accounts as $key => $account) {
            $result[$cont]["account_id"] = $account->id;
            $result[$cont]["nick"] = $account->nick;
            $result[$cont]["page_id"] = $account->page_id;
            $result[$cont]["page"] = $account->page_account->name;
            $result[$cont]["login"] = $account->page_account->login;
            $result[$cont]["access"] = $account->access;
            $result[$cont]["password"] = $account->password;
            $cont++;
        }

        $location = SettingLocation::find(Auth::user()->setting_location_id);

        return view('adminModules.user.users.account-access')->with(['access' => $result, 'location' => $location]);
    }

    public function getAllModels(Request $request)
    {
        $users = User::where('status', $request->user_status)->where('setting_role_id', 14)->get();
        $pages = SettingPage::select('id','name')->get();
        $cont = 0;
        $result = [];
        foreach($users as $key => $user){
            $result[$cont]["id"] = $user->id;
            $result[$cont]["avatar"] = is_null($user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
            $result[$cont]["nick"] = $user->nick;
            $result[$cont]["full_name"] = $user->userFullName();
            $result[$cont]["email"] = $user->email;
            $result[$cont]["hangout_password"] = $user->hangouts_password;
            $result[$cont]["location"] = $user->location->name;
            $cont++;
        }
        $result['model'] = $result;
        $result['pages'] = $pages;
        return response()->json($result);
    }

    public function getModelAccounts(Request $request)
    {
        $accounts = SatelliteAccount::select('id','nick','page_id','access','password')->where( 'user_id', $request->id)->get();
        $cont = 0;
        $result = [];
        foreach($accounts as $key => $account){
            $result[$cont]["account_id"] = $account->id;
            $result[$cont]["nick"] = $account->nick;
            $result[$cont]["page_id"] = $account->page_id;
            $result[$cont]["page"] = $account->page_account->name;
            $result[$cont]["login"] = $account->page_account->login;
            $result[$cont]["access"] = $account->access;
            $result[$cont]["password"] = $account->password;
            $cont++;
        }
        return response()->json($result);
    }

    public function changeTheme(Request $request)
    {
        $class = $request->clase;
        $user = User::find(auth()->user()->id);
        $user->theme = $class;
        $user->save();
    }

    public function getModels($id)
    {
        $models = User::select('id', 'nick')
            ->where('status', '=', 1)
            ->where('setting_role_id', $id)->orderBy('nick')->get();
        return response()->json(['models' => $models]);
    }

    public function getPhotographers($id)
    {
        $photographers = User::select('id', 'first_name', 'last_name')->where('setting_role_id', $id)->where('status', 1)->get();
        return response()->json(['photographers' => $photographers]);
    }

    public function getFilmakers($id)
    {
        $videographers = User::select('id', 'first_name', 'last_name')->where('setting_role_id', $id)->where('status', 1)->get();
        return response()->json(['videographers' => $videographers]);
    }

    public function storeAccount(Request $request)
    {

        try {
            DB::beginTransaction();

            $this->validate($request,
                [
                    'nick' => ['required',Rule::unique('satellite_accounts')->where(function ($query) use ($request){
                        return $query->where('nick',$request->nick)->where('page_id',$request->page);
                    })],
                    'page' => 'required',
                ],
                [
                    'nick.required' => 'Este campo es obligatorio',
                    'nick.unique' => 'Esta cuenta ya existe',
                    'page.required' => 'Este campo es obligatorio',
                ]);

            $user = User::find($request->user_id);
            $owner = SatelliteOwner::where('user_id', $request->user_id)->first();
            if ($owner == null)
            {
                $owner = new SatelliteOwner();
                $owner->owner = trim($user->nick);
                $owner->email = $user->email;
                $owner->first_name = $user->first_name;
                $owner->second_name = $user->middle_name;
                $owner->last_name = $user->last_name;
                $owner->second_last_name = $user->second_lastname;
                $owner->statistics_emails = $user->email;
                $owner->department_id = $user->department_id;
                $owner->city_id = $user->city_id;
                $owner->commission_percent = 50;
                $owner->is_user = 1;
                $owner->user_id = $request->user_id;
                $owner->save();
            }
            $account = new SatelliteAccount;
            $account->owner_id = $owner->id;
            $account->page_id = $request->page;
            $account->status_id = 2;
            $account->nick = trim($request->nick);
            $account->original_nick = trim($request->nick);
            $account->first_name = trim($user->first_name);
            $account->second_name = trim($user->middle_name);
            $account->last_name = trim($user->last_name);
            $account->second_last_name = trim($user->second_last_name);
            $account->birth_date = $user->birth_date;
            $account->access = trim($request->access);
            $account->password = trim($request->password);
            $account->modified_by = Auth::user()->id;
            $account->from_gb = 1;
            $account->user_id = $request->user_id;
            $account->save();

            $log = new SatelliteAccountLog;
            $log->type = "Cuenta";
            $log->account_id = $account->id;
            $log->action = "creada";
            $log->now = $account->nick;
            $log->created_by = Auth::user()->id;
            $log->save();

            //verificando si la cuenta esta en Resumen de Archivos Subidos
            $file = SatellitePaymentFile::select('payment_date', 'trm')->orderBy('payment_date', 'desc')->first();
            if ($file != null)
            {
                $payment_accounts = SatellitePaymentAccount::where('owner_id', null)->where('page_id', $account->page_id)
                    ->where('nick', $account->nick)->where('payment_date', $file->payment_date)->get();

                foreach ($payment_accounts as $key => $payment_account) {

                    if ($account->status_id == 1 || $account->status_id == 6 || $account->status_id == 8) {
                        $account->status_id = 2;
                        $account->save();
                    }

                    $payment_account->owner_id = $account->owner_id;
                    $payment_account->account_id = $account->id;
                    $payment_account->save();
                    $owner = SatelliteOwner::select('owner')->where('id', $account->owner_id)->get();

                    $payment_account_alert = [
                        "id" => $payment_account->id,
                        "owner_id" => $payment_account->owner_id,
                        "owner_name" => $owner[0]->owner,
                        "account_id" => $payment_account->account_id,
                    ];
                    event(new PaymentAccount($payment_account_alert));
                }
            }

            $result["account_id"] = $account->id;
            $result["nick"] = $account->nick;
            $result["page_id"] = $account->page_id;
            $result["page"] = $account->page->name;
            $result["login"] = $account->page->login;
            $result["access"] = $account->access;
            $result["password"] = $account->password;

            DB::commit();
            return response()->json(['success' => true, 'result' => $result]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateAccount(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->validate($request,
                [
                    'nick' => ['required',Rule::unique('satellite_accounts')->where(function ($query) use ($request){
                        return $query->where('nick',$request->nick)->where('page_id',$request->page_id)->where('id', '!=', $request->account_id);
                    })],
                    'page_id' => 'required',
                ],
                [
                    'nick.required' => 'Este campo es obligatorio',
                    'nick.unique' => 'Esta cuenta ya existe',
                    'page_id.required' => 'Este campo es obligatorio',
                ]);


            $account = SatelliteAccount::find($request->account_id);
            $original = $account->getOriginal();
            $account->nick = $request->nick;
            $account->access = $request->access;
            $account->password = $request->password;
            $account->modified_by = Auth::user()->id;
            $account->save();
            $changes = $account->getChanges();

            //historial de columnas modificadas
            $fields = [
                "nick" => "Nick",
                "access" => "Email",
                "password" => "Clave",
            ];

            foreach ($changes as $key => $change) {
                if ($key != "updated_at" && $key != "modified_by")
                {
                    $log = new SatelliteAccountLog;
                    $log->type = $fields[$key];
                    $log->account_id = $account->id;
                    $log->action = "modificado";

                    $previous = $original[$key];
                    $now = $change;

                    $log->previous = $previous;
                    $log->now = $now;

                    $log->created_by = Auth::user()->id;
                    $log->save();
                }

            }

            //verificando si la cuenta esta en Resumen de Archivos Subidos
            $file = SatellitePaymentFile::select('payment_date', 'trm')->orderBy('payment_date', 'desc')->first();
            if ($file != null)
            {
                $payment_accounts = SatellitePaymentAccount::where('owner_id', null)->where('page_id', $account->page_id)
                    ->where('nick', $account->nick)->where('payment_date', $file->payment_date)->get();

                foreach ($payment_accounts as $key => $payment_account) {

                    if ($account->status_id == 1 || $account->status_id == 6 || $account->status_id == 8) {
                        $account->status_id = 2;
                        $account->save();
                    }

                    $payment_account->owner_id = $account->owner_id;
                    $payment_account->account_id = $account->id;
                    $payment_account->save();
                    $owner = SatelliteOwner::select('owner')->where('id', $account->owner_id)->get();

                    $payment_account_alert = [
                        "id" => $payment_account->id,
                        "owner_id" => $payment_account->owner_id,
                        "owner_name" => $owner[0]->owner,
                        "account_id" => $payment_account->account_id,
                    ];
                    event(new PaymentAccount($payment_account_alert));
                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function viewUsers()
    {
        $roles = SettingRole::orderBy('name', 'asc')->get();
        $locations = SettingLocation::all();
        $user_permissions = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $departments = Department::orderBy('name')->get();

        return view('adminModules.user.users.list')->with(compact(['roles', 'locations', 'user_permissions', 'departments']));
    }

    public function viewModels()
    {
        $roles = SettingRole::orderBy('name', 'asc')->get();
        $locations = SettingLocation::all();
        $user_permissions = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $departments = Department::orderBy('name')->get();

        return view('adminModules.user.users.models')->with(compact(['roles', 'locations', 'user_permissions', 'departments']));
    }

    public function getDepartmentCities(Request $request)
    {
        $cities = City::where('department_id', $request->department_id)->orderBy('name')->get();

        return response()->json($cities);
    }

    public function editPersonalInfo(Request $request)
    {
        $this->validate($request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'birth_date' => 'required',
                'country_id' => 'required',
                'rh_id' => 'required',
                'document_type' => 'required',
                'document_number' => 'required',
            ],
            [
                'first_name.required' => 'Este campo es obligatorio',
                'last_name.required' => 'Este campo es obligatorio',
                'birth_date.required' => 'Este campo es obligatorio',
                'rh_id.required' => 'Este campo es obligatorio',
                'country_id.required' => 'Este campo es obligatorio',
                'document_type.required' => 'Este campo es obligatorio',
                'document_number.required' => 'Este campo es obligatorio',
            ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name != null ? $request->middle_name : '';
            $user->last_name = $request->last_name;
            $user->second_last_name = $request->second_lastname != null ? $request->second_lastname : '';
            $user->birth_date = $request->birth_date;
            $user->blood_type_id = $request->rh_id;
            $user->nationality = isset($request->country_id) && $request->country_id != 'null' ? $request->country_id : null;
            $user->mobile_number = isset($request->mobile_number) && $request->mobile_number != 'null' ? $request->mobile_number : null;
            $original_user_status = $user->status;
            $user->status = $request->status;

            // Documents
            $user->document_id = $request->document_type;
            $user->document_number = $request->document_number != 'null' ? $request->document_number : null;

            if($request->document_has_expiration_date == 'true') {
                $user->expiration_date = $request->document_expiration_date;
            } else {
                $user->expiration_date = null;
            }

            if($request->file('front_document_file')) {
                $front_document_file_name = $this->tenantUploadFile($request->file('front_document_file'), 'documents', tenant('studio_slug'));
                $document = new UserDocument();
                $document->document_id = 5;
                $document->user_id = $request->user_id;
                $document->file_name = $front_document_file_name;
                $document->save();
            }

            if($request->file('back_document_file')) {
                $front_document_file_name = $this->tenantUploadFile($request->file('back_document_file'), 'documents', tenant('studio_slug'));
                $document = new UserDocument();
                $document->document_id = 6;
                $document->user_id = $request->user_id;
                $document->file_name = $front_document_file_name;
                $document->save();
            }

            if($request->file('face_id_document_file')) {
                $front_document_file_name = $this->tenantUploadFile($request->file('face_id_document_file'), 'documents', tenant('studio_slug'));
                $document = new UserDocument();
                $document->document_id = 7;
                $document->user_id = $request->user_id;
                $document->file_name = $front_document_file_name;
                $document->save();
            }

            if($request->file('rut_document_file')) {
                $front_document_file_name = $this->tenantUploadFile($request->file('rut_document_file'), 'documents', tenant('studio_slug'));
                $document = new UserDocument();
                $document->document_id = 8;
                $document->user_id = $request->user_id;
                $document->file_name = $front_document_file_name;
                $document->save();
            }

            // Inactivate User
            if($original_user_status != $user->status) {
                // Activate User
                if($request->status == 1) {
                    if($user->setting_role_id == 14) { // If Modelo
                        $schedule = Schedule::where('user_id', $user->id)->update(['setting_location_id' => $user->setting_location_id]);

                        $owner = SatelliteOwner::firstOrCreate(
                            ['user_id' => $user->id],
                            [
                                'owner' => trim($user->nick),
                                'email' => !is_null($user->personal_email) ? $user->personal_email : $user->email,
                                'first_name' => $user->first_name,
                                'second_name' => $user->middle_name,
                                'last_name' => $user->last_name,
                                'second_last_name' => $user->second_last_name,
                                'document_number' => $user->document_number,
                                'statistics_emails' => !is_null($user->personal_email) ? $user->personal_email : $user->email,
                                'department_id' => $user->department_id,
                                'city_id' => $user->city_id,
                                'address' => $user->address,
                                'neighborhood' => $user->neighborhood,
                                'commission_percent' => 50,
                                'payment_method' => ($user->has_bank_account == 1 ? 2 : 1),
                                'is_user' => 1,
                                'user_id' => $user->id,
                            ]
                        );

                        // Satellite payment info
                        if(Auth::user()->can('user-payment-info-edit') && $user->has_bank_account) {
                            $holder = !empty($user->bank_account_owner)
                                ? $user->bank_account_owner
                                : $user->first_name . (!empty($user->middle_name) ? " $user->middle_name" : '') . " $user->last_name" . (!empty($user->second_last_name) ? " $user->second_last_name" : '');

                            $owner_payment_info = SatelliteOwnerPaymentInfo::firstOrCreate(
                                ['owner' => $owner->id],
                                [
                                    'account_type' => $user->bank_account_type == 'Ahorros' ? 0 : 1,
                                ]
                            );

                            $owner_payment_info->holder = $holder;
                            $owner_payment_info->bank = $user->bank_account_id;
                            $owner_payment_info->document_type = $user->bank_account_document_id;
                            $owner_payment_info->document_number = $user->bank_account_document_number;
                            $owner_payment_info->account_type = 0;
                            $owner_payment_info->account_number = $user->bank_account_number;
                            $owner_payment_info->city_id = isset($user->city_id) && $user->city_id != 'null' ? $user->city_id : 1;
                            $owner_payment_info->address = $user->address;
                            $owner_payment_info->phone = $user->mobile_number;
                            $owner_payment_info->save();
                        }

                        //Create summary
                        $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                        $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                        $now = Carbon::now()->format('Y-m-d');
                        AttendanceSummary::create([
                            'model_id' => $user->id,
                            'range' => $week_start." / ".$week_end,
                            'date' => $now,
                            'worked_days' => 0,
                            'unjustified_days' => 0,
                            'justified_days' => 0,
                            'period' => 0,
                            'total_minutes' => 0,
                            'total_recovery_minutes' => 0,
                            'goal' => 50.00,
                            'created_by' => 594
                        ]);
                    }
                }

                // Inactivate User
                if($request->status == 0) {
                    if($user->setting_role_id == 14) { // If Modelo
                        // Remove all bookings
                        $bookings_types = BookingType::all();

                        foreach ($bookings_types AS $type) {
                            $this->removeUserBookings($request->user_id, $type->id);
                        }

                        // Remove schedule
                        $this->removeUserSchedule($request->user_id);

                        // Remove attendance
                        $this->removeUserAttendances($request->user_id);

                        $user_owner = SatelliteOwner::where('user_id', $request->user_id)->first();
                        $user_owner->status = 3;
                        $user_owner->save();
                    }

                    // For all users
                    $retirement_reason = !empty($request->inactivated_user_reason) ? $request->inactivated_user_reason : '';
                    $retirement_history = new UserRetirementHistory();
                    $retirement_history->user_id = $request->user_id;
                    $retirement_history->created_by_user_id = Auth::user()->id;
                    $retirement_history->description = $retirement_reason;
                    $retirement_history->starting_date = $user->admission_date;
                    $retirement_history->save();

                    $this->removeUserVacations($request->user_id);
                    $this->removeUserCafeteriaOrders($request->user_id);
                    $this->createInactiveUserTask($request->user_id);

                    //if() { // Check here if studio have the Retirement Protocol Task Notification configuration
                        $this->createRetirementProtocolTask($request->user_id, $retirement_reason);
                    //}

                    // Here do the support system things if it is 'Soporte'
                }
            }

            $success = $user->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }

    }

    public function editAccessInfo(Request $request)
    {
        $this->validate($request,
            [
                'email' => "required|unique:users,email,$request->user_id",
                'new_password' => 'required_if:change_password,1|same:confirm_new_password',
            ],
            [
                'email.required' => 'Este campo es obligatorio',
                'email.unique' => 'Ya este correo registrado',
                'new_password.required_if' => 'Debe ingresar la nueva contraseña',
                'new_password.same' => 'Las contraseñas deben coincidir',
            ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $user->email = $request->email;
            $user->hangouts_password = $request->hangouts_password;
            if($request->change_password) {
                $user->password = bcrypt($request->new_password);
            }
            $success = $user->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editPayroll(Request $request)
    {
        $this->validate($request,
            [
                //'social_security_amount' => 'required_if:has_social_security,1|required_if:contract_type,1',
                //'transportation_aid_amount' => 'required_if:has_transportation_aid,1',
                'bonus_amount' => 'required_if:has_bonus,1',
                'mobilization_amount' => 'required_if:has_mobilization,1',
                'contract_type' => 'required',
                'admission_date' => 'required',
                'eps_id' => 'required',
                'current_salary' => 'integer',
                'new_eps' => 'required_if:eps_id,other',
            ],
            [
                //'social_security_amount.required_if' => 'Debe ingresar el monto',
                //'transportation_aid_amount.required_if' => 'Debe ingresar el monto',
                'current_salary.integer' => 'El valor debe ser un número',
                'bonus_amount.integer' => 'El valor debe ser un número',
                'mobilization_amount.integer' => 'El valor debe ser un número',
                'bonus_amount.required_if' => 'Debe ingresar el monto',
                'mobilization_amount.required_if' => 'Debe ingresar el monto',
                'contract_type.required' => 'Debe seleccionar una opción',
                'admission_date.required' => 'Debe ingresar la fecha de inicio',
                'eps_id.required' => 'Debe seleccionar una opción',
                'new_eps.required_if' => 'Debe ingresar el nombre de la EPS',
            ]);

        try {
            DB::beginTransaction();

            $now = Carbon::now()->day;
            $last_day_of_month = Carbon::now()->endOfMonth()->day;

            if(($now >= 1 && $now <= 14) || ($now == $last_day_of_month))
            {
                $quarter = 1;
                $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-07";

                if($now == $last_day_of_month) {
                    $date = Carbon::now()->addDay();
                    $for_date = $date->year . "-" . $date->month . "-07";
                }
            }
            else
            {
                $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-27";
                $quarter = 2;
            }

            $first_for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-07";
            $second_for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-27";

            $user = User::findOrFail($request->user_id);
            $user->contract_id = $request->contract_type;
            $user->admission_date = $request->admission_date;
            $user->contract_date = $request->contract_sign_date;

            if(Auth::user()->can('user-current-salary-edit')) {
                $user->current_salary = $request->current_salary;

                if ($quarter == 1) {
                    // Create or update the first quarter of month
                    $payroll = Payroll::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'month' => Carbon::now()->month,
                            'year' => Carbon::now()->year,
                        ],
                        [
                            'user_id' => $request->user_id,
                            'month' => Carbon::now()->month,
                            'year' => Carbon::now()->year,
                            'salary1' => $request->current_salary,
                            'worked_days1' => 15,
                            'salary2' => $request->current_salary,
                            'worked_days2' => 15,
                        ]
                    );

                    $payroll->salary1 = $request->current_salary;
                    $payroll->salary2 = $request->current_salary;
                    $payroll->save();
                }

                if ($quarter == 2) {
                    // Only create or update the second quarter of month
                    $payroll = Payroll::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'month' => Carbon::now()->month,
                            'year' => Carbon::now()->year,
                        ],
                        [
                            'user_id' => $request->user_id,
                            'month' => Carbon::now()->month,
                            'year' => Carbon::now()->year,
                            'salary1' => 0,
                            'worked_days1' => 1,
                            'salary2' => $request->current_salary,
                            'worked_days2' => 15,
                        ]
                    );

                    $payroll->salary2 = $request->current_salary;
                    $payroll->save();
                }
            }

            if(Auth::user()->can('user-social-security-edit')) {
                // If the user contract type is changed to 'Indefinido' and it have social security payroll, delete that
                if($request->contract_type == 2) {
                    if ($quarter == 1) {
                        // Delete social security payroll movement of month
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $first_for_date)->where('payroll_type_id', 12)->delete();
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 12)->delete();
                    }

                    if ($quarter == 2) {
                        // Only delete social security payroll for the second quarter of month
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 12)->delete();
                    }
                }

                // Social Security
                $user->has_social_security = $request->has_social_security;

                if($request->has_social_security)
                {
                    if($request->eps_id == 'other') {
                        $eps = new GlobalEPS();
                        $eps->name = $request->new_eps;
                        $eps->save();

                        $user->eps_id = $eps->id;
                    } else {
                        $user->eps_id = $request->eps_id;
                    }

                    if($request->contract_type == 1) { // Prestacion de Servicios
                        $user->social_security_amount = $request->social_security_amount;

                        $amount_to_quarter = $request->social_security_amount / 2;

                        if ($quarter == 1) {
                            // Create or update the first quarter of month
                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $first_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $first_for_date,
                                    'amount' => $amount_to_quarter,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Bonificación',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $amount_to_quarter;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Seguridad Social";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();

                            // Create or update the second quarter of month
                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $second_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $second_for_date,
                                    'amount' => $amount_to_quarter,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Seguridad Social',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $amount_to_quarter;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Seguridad Social";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();
                        }

                        if ($quarter == 2) {
                            // Only create or update the second quarter of month
                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $second_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $second_for_date,
                                    'amount' => $amount_to_quarter,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Seguridad Social',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $amount_to_quarter;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Seguridad Social";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();
                        }
                    }
                    else
                    {
                        // If has social security and contract type is 'Indefinido'
                        $user->social_security_amount = null;

                        $payroll = Payroll::firstOrCreate(
                            [
                                'user_id' => $request->user_id,
                                'month' => Carbon::now()->month,
                                'year' => Carbon::now()->year,
                            ],
                            [
                                'user_id' => $request->user_id,
                                'month' => Carbon::now()->month,
                                'year' => Carbon::now()->year,
                                'salary1' => $request->current_salary,
                                'worked_days1' => 15,
                                'salary2' => $request->current_salary,
                                'worked_days2' => 15,
                            ]
                        );

                        $payroll_controller = new PayrollController();

                        if ($quarter == 1) {
                            // First quarter
                            $date_from = Carbon::now()->year . "-" . Carbon::now()->month . "-01";
                            $date_to = Carbon::now()->year . "-" . Carbon::now()->month . "-15";

                            $extra_hours = $payroll_controller->payrollMovements($request->user_id, $date_from, $date_to, 14);
                            $night_surcharge = $payroll_controller->payrollMovements($request->user_id, $date_from, $date_to, 1);
                            $current_salary = $payroll->salary1 / 2;

                            $quarter_salary = round(($current_salary / 15) * $payroll->worked_days1);
                            $social_security_amount = round(($extra_hours + $night_surcharge + $quarter_salary) * 0.08);

                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $first_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $first_for_date,
                                    'amount' => $social_security_amount,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Seguridad Social',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $social_security_amount;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Seguridad Social";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();

                            // Second quarter
                            $last_day_of_month = Carbon::now()->endOfMonth()->day;

                            $date_from = Carbon::now()->year . "-" . Carbon::now()->month . "-16";
                            $date_to = Carbon::now()->year . "-" . Carbon::now()->month . "-" . $last_day_of_month;

                            $extra_hours = $payroll_controller->payrollMovements($request->user_id, $date_from, $date_to, 14);
                            $night_surcharge = $payroll_controller->payrollMovements($request->user_id, $date_from, $date_to, 1);
                            $current_salary = $payroll->salary2 / 2;

                            $quarter_salary = round(($current_salary / 15) * $payroll->worked_days2);
                            $social_security_amount = round(($extra_hours + $night_surcharge + $quarter_salary) * 0.08);

                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $first_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $first_for_date,
                                    'amount' => $social_security_amount,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Seguridad Social',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $social_security_amount;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Seguridad Social";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();
                        }

                        if ($quarter == 2) {
                            // Only update the second quarter of month
                            $last_day_of_month = Carbon::now()->endOfMonth()->day;

                            $date_from = Carbon::now()->year . "-" . Carbon::now()->month . "-16";
                            $date_to = Carbon::now()->year . "-" . Carbon::now()->month . "-" . $last_day_of_month;

                            $extra_hours = $payroll_controller->payrollMovements($request->user_id, $date_from, $date_to, 14);
                            $night_surcharge = $payroll_controller->payrollMovements($request->user_id, $date_from, $date_to, 1);
                            $current_salary = $payroll->salary2 / 2;

                            $quarter_salary = round(($current_salary / 15) * $payroll->worked_days2);
                            $social_security_amount = round(($extra_hours + $night_surcharge + $quarter_salary) * 0.08);

                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $second_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 12,
                                    'for_date' => $second_for_date,
                                    'amount' => $social_security_amount,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Seguridad Social',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $social_security_amount;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Seguridad Social";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();
                        }
                    }
                }
                else
                {
                    $user->eps_id = 1;
                    $user->social_security_amount = null;

                    if ($quarter == 1) {
                        // Delete social security payroll movement of month
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $first_for_date)->where('payroll_type_id', 12)->delete();
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 12)->delete();
                    }

                    if ($quarter == 2) {
                        // Only delete social security payroll for the second quarter of month
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 12)->delete();
                    }
                }
            }

            // Transportation Aid
            $user->has_transportation_aid = $request->has_transportation_aid;

            if($request->has_transportation_aid)
            {
                if($request->contract_type == 2) { // Indefinido
                    if($request->current_salary < 1755606) {
                        $user->transportation_aid_amount = $request->transportation_aid_amount;

                        $transportation_aid_value = RHExtraValue::select('transportation_aid')->where('id', 1)->first();
                        $quarter_transportation_aid_value = $transportation_aid_value->transportation_aid / 2;

                        if ($quarter == 1) {
                            // Create or update the first quarter of month
                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 6,
                                    'for_date' => $first_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 6,
                                    'for_date' => $first_for_date,
                                    'amount' => $quarter_transportation_aid_value,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Auxilio de Transporte',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $quarter_transportation_aid_value;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Auxilio de Transporte";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();

                            // Create or update the second quarter of month
                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 6,
                                    'for_date' => $second_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 6,
                                    'for_date' => $second_for_date,
                                    'amount' => $quarter_transportation_aid_value,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Auxilio de Transporte',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $quarter_transportation_aid_value;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Auxilio de Transporte";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();
                        }

                        if ($quarter == 2) {
                            // Only create or update the second quarter of month
                            $payroll_movement = PayrollMovement::firstOrCreate(
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 6,
                                    'for_date' => $second_for_date
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'payroll_type_id' => 6,
                                    'for_date' => $second_for_date,
                                    'amount' => $quarter_transportation_aid_value,
                                    'created_by' => Auth::user()->id,
                                    'comment' => 'Modificado valor Auxilio de Transporte',
                                    'automatic' => 1,
                                ]
                            );

                            $payroll_movement->amount = $quarter_transportation_aid_value;
                            $payroll_movement->created_by = Auth::user()->id;
                            $payroll_movement->comment = "Modificado valor Auxilio de Transporte";
                            $payroll_movement->automatic = 1;
                            $payroll_movement->save();
                        }
                    }
                }
                else
                {
                    $user->transportation_aid_amount = null;

                    if ($quarter == 1) {
                        // Delete trasportation aid payroll movement of month
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $first_for_date)->where('payroll_type_id', 6)->delete();
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 6)->delete();
                    }

                    if ($quarter == 2) {
                        // Only delete trasportation aid payroll for the second quarter of month
                        PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 6)->delete();
                    }
                }
            }
            else
            {
                $user->transportation_aid_amount = null;

                if ($quarter == 1) {
                    // Delete trasportation aid payroll movement of month
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $first_for_date)->where('payroll_type_id', 6)->delete();
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 6)->delete();
                }

                if ($quarter == 2) {
                    // Only delete trasportation aid payroll for the second quarter of month
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 6)->delete();
                }
            }

            // Bonus
            $user->has_bonus = $request->has_bonus;

            if($request->has_bonus)
            {
                $user->bonus_amount = $request->bonus_amount;
                $amount_to_quarter = $request->bonus_amount / 2;

                if ($quarter == 1) {
                    // Create or update the first quarter of month
                    $payroll_movement = PayrollMovement::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 13,
                            'for_date' => $first_for_date
                        ],
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 13,
                            'for_date' => $first_for_date,
                            'amount' => $amount_to_quarter,
                            'created_by' => Auth::user()->id,
                            'comment' => 'Modificado valor Bonificación',
                            'automatic' => 1,
                        ]
                    );

                    $payroll_movement->amount = $amount_to_quarter;
                    $payroll_movement->created_by = Auth::user()->id;
                    $payroll_movement->comment = "Modificado valor Bonificación";
                    $payroll_movement->automatic = 1;
                    $payroll_movement->save();

                    // Create or update the second quarter of month
                    $payroll_movement = PayrollMovement::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 13,
                            'for_date' => $second_for_date
                        ],
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 13,
                            'for_date' => $second_for_date,
                            'amount' => $amount_to_quarter,
                            'created_by' => Auth::user()->id,
                            'comment' => 'Modificado valor Bonificación',
                            'automatic' => 1,
                        ]
                    );

                    $payroll_movement->amount = $amount_to_quarter;
                    $payroll_movement->created_by = Auth::user()->id;
                    $payroll_movement->comment = "Modificado valor Bonificación";
                    $payroll_movement->automatic = 1;
                    $payroll_movement->save();
                }

                if ($quarter == 2) {
                    // Only create or update the second quarter of month
                    $payroll_movement = PayrollMovement::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 13,
                            'for_date' => $second_for_date
                        ],
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 13,
                            'for_date' => $second_for_date,
                            'amount' => $amount_to_quarter,
                            'created_by' => Auth::user()->id,
                            'comment' => 'Modificado valor Bonificación',
                            'automatic' => 1,
                        ]
                    );

                    $payroll_movement->amount = $amount_to_quarter;
                    $payroll_movement->created_by = Auth::user()->id;
                    $payroll_movement->comment = "Modificado valor Bonificación";
                    $payroll_movement->automatic = 1;
                    $payroll_movement->save();
                }
            }
            else
            {
                $user->bonus_amount = null;

                if ($quarter == 1) {
                    // Delete bonus payroll movement of month
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $first_for_date)->where('payroll_type_id', 13)->delete();
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 13)->delete();
                }

                if ($quarter == 2) {
                    // Only delete bonus payroll for the second quarter of month
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 13)->delete();
                }
            }

            // Mobilization Aid
            $user->has_mobilization = $request->has_mobilization;

            if($request->has_mobilization)
            {
                $user->mobilization_amount = $request->mobilization_amount;
                $amount_to_quarter = $request->mobilization_amount / 2;

                if ($quarter == 1) {
                    // Create or update the first quarter of month
                    $payroll_movement = PayrollMovement::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 3,
                            'for_date' => $first_for_date
                        ],
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 3,
                            'for_date' => $first_for_date,
                            'amount' => $amount_to_quarter,
                            'created_by' => Auth::user()->id,
                            'comment' => 'Modificado valor Auxilio Movilizacion',
                            'automatic' => 1,
                        ]
                    );

                    $payroll_movement->amount = $amount_to_quarter;
                    $payroll_movement->created_by = Auth::user()->id;
                    $payroll_movement->comment = "Modificado valor Auxilio Movilizacion";
                    $payroll_movement->automatic = 1;
                    $payroll_movement->save();

                    // Create or update the second quarter of month
                    $payroll_movement = PayrollMovement::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 3,
                            'for_date' => $second_for_date
                        ],
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 3,
                            'for_date' => $second_for_date,
                            'amount' => $amount_to_quarter,
                            'created_by' => Auth::user()->id,
                            'comment' => 'Modificado valor Auxilio Movilizacion',
                            'automatic' => 1,
                        ]
                    );

                    $payroll_movement->amount = $amount_to_quarter;
                    $payroll_movement->created_by = Auth::user()->id;
                    $payroll_movement->comment = "Modificado valor Auxilio Movilizacion";
                    $payroll_movement->automatic = 1;
                    $payroll_movement->save();
                }

                if ($quarter == 2) {
                    // Only create or update the second quarter of month
                    $payroll_movement = PayrollMovement::firstOrCreate(
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 3,
                            'for_date' => $second_for_date
                        ],
                        [
                            'user_id' => $request->user_id,
                            'payroll_type_id' => 3,
                            'for_date' => $second_for_date,
                            'amount' => $amount_to_quarter,
                            'created_by' => Auth::user()->id,
                            'comment' => 'Modificado valor Auxilio Movilizacion',
                            'automatic' => 1,
                        ]
                    );

                    $payroll_movement->amount = $amount_to_quarter;
                    $payroll_movement->created_by = Auth::user()->id;
                    $payroll_movement->comment = "Modificado valor Auxilio Movilizacion";
                    $payroll_movement->automatic = 1;
                    $payroll_movement->save();
                }

            }
            else
            {
                $user->mobilization_amount = null;

                if ($quarter == 1) {
                    // Delete mobilization aid payroll movement of month
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $first_for_date)->where('payroll_type_id', 3)->delete();
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 3)->delete();
                }

                if ($quarter == 2) {
                    // Only delete mobilization aid payroll for the second quarter of month
                    PayrollMovement::where('user_id', $request->user_id)->whereDate('for_date', $second_for_date)->where('payroll_type_id', 3)->delete();
                }
            }

            $success = $user->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editBankAccountInfo(Request $request)
    {
        if(!Auth::user()->can('user-payment-method')) {
            return response()->json(['success' => false, 'msg' => 'No tienes permisos para realizar esta acción']);
        }

        $this->validate($request,
            [
                'bank_account_id' => 'required_if:has_bank_account,1',
                'bank_account_city' => 'required_if:has_bank_account,1',
                'bank_account_owner' => 'required_if:has_bank_account,1',
                'bank_account_type' => 'required_if:has_bank_account,1',
                'bank_document_type' => 'required_if:has_bank_account,1',
                'bank_account_number' => 'required_if:has_bank_account,1',
                'bank_document_number' => 'required_if:has_bank_account,1',
            ],
            [
                'bank_account_id.required_if' => 'Este campo es obligatorio',
                'bank_account_city.required_if' => 'Este campo es obligatorio',
                'bank_account_owner.required_if' => 'Este campo es obligatorio',
                'bank_account_type.required_if' => 'Este campo es obligatorio',
                'bank_document_type.required_if' => 'Este campo es obligatorio',
                'bank_account_number.required_if' => 'Este campo es obligatorio',
                'bank_document_number.required_if' => 'Este campo es obligatorio',
            ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $user->has_bank_account = $request->has_bank_account;

            if($request->has_bank_account) {
                $user->has_bank_without_retention = $request->bank_without_retention;
                $user->bank_account_id = isset($request->bank_account_id) ? $request->bank_account_id : 1;
                $user->bank_account_owner = $request->bank_account_owner;
                $user->bank_account_document_id = isset($request->bank_document_type) ? $request->bank_document_type : 1;
                $user->bank_account_document_number = $request->bank_document_number;
                $user->bank_account_type = $request->bank_account_type;
                $user->bank_account_number = $request->bank_account_number;
                $user->bank_account_city = $request->bank_account_city;
            }

            $success = $user->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editExtraInfo(Request $request)
    {
        $this->validate($request,
            [
                'setting_role_id' => 'required',
                'setting_location_id' => 'required',
            ],
            [
                'setting_role_id.required' => 'Debe seleccionar un rol principal',
                'setting_location_id.required' => 'Debe seleccionar una locación',
            ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $original_user_role_id = $user->setting_role_id;

            $user->nick = trim($request->model_nick);
            $user->setting_role_id = $request->setting_role_id;
            $user->setting_location_id = $request->setting_location_id;
            $user->personal_email = (isset($request->personal_email) && $request->personal_email != 'null' ? $request->personal_email : null);
            $user->emergency_contact = (isset($request->emergency_contact) && $request->emergency_contact != 'null' ? $request->emergency_contact : null);
            $user->emergency_phone = (isset($request->emergency_phone) && $request->emergency_phone != 'null' ? $request->emergency_phone : null);
            $user->department_id = isset($request->department_id) ? $request->department_id : 1;
            $user->city_id = isset($request->city_id) ? $request->city_id : 1;
            $user->address = $request->address;
            $user->neighborhood = $request->neighborhood;
            $user->has_uniform = $request->has_uniform == "true" ? 1 : 0;
            $user->blouse_size = (isset($request->blouse_size) && $request->blouse_size != 'null' ? $request->blouse_size : null);
            $user->pants_size = (isset($request->pants_size) && $request->pants_size != 'null' ? $request->pants_size : null);
            $user->pants_long = (isset($request->pants_long) && $request->pants_long != 'null' ? $request->pants_long : null);

            $roles = $user->getRoleNames();
            
            // print_r($roles);
        
            foreach ($roles AS $role) {
                $setting_role = SettingRole::where('name', $role)->first();

                if($setting_role->id == $original_user_role_id) { continue; }

                $user->removeRole($setting_role->name); // for revoke permission to user
            }

            if($original_user_role_id != $user->setting_role_id) {
                // Revoke old role from user
                $old_setting_role = SettingRole::where('id', $original_user_role_id)->first();
                $user->removeRole($old_setting_role->name); // revoke permission to user

                // Add new role to user
                $new_setting_role = SettingRole::where('id', $user->setting_role_id)->first();
                $user->assignRole($new_setting_role->name);
            }

            if ($user->setting_role_id === 14){
                // create summary
                $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                $now = Carbon::now()->format('Y-m-d');
                AttendanceSummary::create([
                    'model_id' => $user->id,
                    'range' => $week_start." / ".$week_end,
                    'date' => $now,
                    'worked_days' => 0,
                    'unjustified_days' => 0,
                    'justified_days' => 0,
                    'period' => 0,
                    'total_minutes' => 0,
                    'total_recovery_minutes' => 0,
                    'goal' => 50.00,
                    'created_by' => 594
                ]);
            }

            if($request->has_extended_role == "true") {
                $extended_roles = explode(',', $request->extended_roles);

                foreach ($extended_roles AS $extended_role) {
                    $user->assignRole($extended_role);
                }
            }

            if($request->file('profile_pic_file')) {
                $profile_pic_file_name = $this->tenantUploadFile($request->file('profile_pic_file'), 'avatars', tenant('studio_slug'));
                $user->avatar = $profile_pic_file_name;
            }

            $success = $user->save();

            $changes = $user->getChanges();

            if (SatelliteOwner::where('user_id', $user->id)->first())
            {
                $owner = SatelliteOwner::where('user_id', $user->id)->first();
                $owner->owner = trim($user->nick);
                $owner->email = $user->email;
                $owner->first_name = $user->first_name;
                $owner->second_name = $user->middle_name;
                $owner->last_name = $user->last_name;
                $owner->second_last_name = $user->second_lastname;
                $owner->statistics_emails = $user->email;
                $owner->department_id = $user->department_id;
                $owner->city_id = $user->city_id;
                $owner->commission_percent = 50;
                $owner->is_user = 1;
                $owner->user_id = $user->id;
                $owner->save();
            }
            if (tenant('id') == 1 && $user->setting_role_id == 14 && isset($changes['nick'])) {
                // Update owner with the new model nick

                $new_nick = $changes['nick'];
                SatelliteOwner::where('user_id', $user->id)->update(['owner' => $new_nick]);
            }

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function getRetirementHistory(Request $request)
    {
        $data = [];
        $retirement_history = UserRetirementHistory::where('user_id', $request->id)->get();

        foreach ($retirement_history AS $history) {
            $data[] = [
                'id' => $history->id,
                'starting_date' => Carbon::parse($history->starting_date)->format('d/M/Y'),
                'ending_date' =>  Carbon::parse($history->created_at)->format('d/M/Y'),
                'description' => $history->description,
                'created_by' => $history->createdByUser->roleUserShortName(),
            ];
        }

        return response()->json($data);
    }

    public function viewBirthday()
    {
        return view("adminModules.user.birthday.list");
    }

    public function removeUserVacations($user_id, $date = null)
    {
        try {
            DB::beginTransaction();

            if (is_null($date)) {
                $date = Carbon::now()->toDateString();
            }

            $vacations = RHVacationUser::where('user_id', $user_id)->where('date', '>=', $date)->delete();
            $vacations_requests = RHVacationRequest::where('user_id', $user_id)->where('start_date', '>=', $date)->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function createInactiveUserTask($user_id)
    {
        try {
            DB::beginTransaction();

            $task_controller = new TaskController();

            $user = User::findOrFail($user_id);
            $user_name = $user->roleUserShortName();

            $task = new Task();
            $task->created_by_type = 2; // Role
            $task->created_by = 7; // Recursos Humanos
            $task->title = "Novedad Personal";
            $task->status = 0;
            $task->should_finish = $should_finish = Carbon::now()->addDays(2);
            $task->terminated_by = 0;
            $task->code = $task_controller->generateCode();
            $created = $task->save();

            // All roles
            $all_roles = SettingRole::select(['id', 'name'])->get();

            $receivers = [
                'to_roles' => $all_roles,
                'to_users' => [],
                'to_models' => [],
            ];

            $request_object = new Request(['receivers' => json_encode($receivers), 'task_id' => $task->id, 'from_add_receivers' => 0]);
            $task_controller->addReceivers($request_object);

            if ($created) {
                $task_comment = new TaskComment();
                $task_comment->task_id = $task->id;
                $task_comment->user_id = Auth::user()->id;
                $task_comment->comment = "Se informa que <b>$user_name</b> No continuará prestando sus servicios a la empresa, se recuerda que a partir de este momento esta persona no debe tener acceso a las instalaciones.";
                $task_comment->save();
            }

            DB::commit();

            return response()->json(['success' => $created]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function createRetirementProtocolTask($user_id, $inactivated_user_reason)
    {
        try {
            DB::beginTransaction();

            $task_controller = new TaskController();

            $user = User::findOrFail($user_id);
            $user_name = $user->roleUserShortName();

            $task = new Task();
            $task->created_by_type = 2; // Role
            $task->created_by = 7; // Recursos Humanos
            $task->title = "Protocolo de Retiro ($user_name)";
            $task->status = 0;
            $task->should_finish = $should_finish = Carbon::now()->addDays(2);
            $task->terminated_by = 0;
            $task->code = $task_controller->generateCode();
            $created = $task->save();

            // Gerente, Recursos Humanos, Asistente Administrativa, Administradora, Recursos Humanos Operativo, Auxiliar Nómina
            $receivers = [
                'to_roles' => [
                    ['id' => 1, 'name' => 'Gerente'],
                    ['id' => 7, 'name' => 'Recursos Humanos'],
                    ['id' => 2, 'name' => 'Asistente Administrativo'],
                    ['id' => 3, 'name' => 'Administrador/a'],
                    ['id' => 35, 'name' => 'Recursos Humanos Operativo'],
                    ['id' => 38, 'name' => 'Auxiliar Nómina'],
                ],
                'to_users' => [],
                'to_models' => [],
            ];

            $request_object = new Request(['receivers' => json_encode($receivers), 'task_id' => $task->id, 'from_add_receivers' => 0]);
            $task_controller->addReceivers($request_object);

            if ($created) {
                $task_comment = new TaskComment();
                $task_comment->task_id = $task->id;
                $task_comment->user_id = Auth::user()->id;
                $task_comment->comment =
                    "Dado que <b>$user_name</b> ya no continuará con nosotros confirmar:
                     <br><br>Desvincular de la Seguridad Social, entrega de carnet, llaves de Locker (en caso de que sea Modelo), entrega de uniformes (en caso que haya tenido alguno).
                     <br>Que se encuentre a paz y salvo de Boutique, Cafetería y Nevera, Firma de documento de Paz y Salvo.
                     <br><br>* Recordar que en caso que sea Modelo la entrega de pertenencias debe realizarse solo por la persona encargada para ello.";
                $task_comment->save();

                $task_comment = new TaskComment();
                $task_comment->task_id = $task->id;
                $task_comment->user_id = Auth::user()->id;
                $task_comment->comment = "Razón: $inactivated_user_reason";
                $task_comment->save();
            }

            DB::commit();

            return response()->json(['success' => $created]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function removeUserCafeteriaOrders($user_id, $date = null)
    {
        try {
            DB::beginTransaction();

            if (is_null($date)) {
                $date = Carbon::now()->toDateString();
            }

            $success = CafeteriaOrder::where('user_id', $user_id)->where('date', '>=', $date)->delete();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function removeUserBookings($user_id, $booking_type_id, $date = null)
    {
        try {
            DB::beginTransaction();

            if (is_null($date)) {
                $date = Carbon::now()->toDateString();
            }

            $success = Booking::where('model_id', $user_id)->where('booking_type_id', $booking_type_id)->where('date', '>=', $date)->delete();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function removeUserSchedule($user_id)
    {
        try {
            DB::beginTransaction();

            $success = Schedule::where('user_id', $user_id)->delete();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function removeUserAttendances($model_id, $date = null)
    {
        try {
            DB::beginTransaction();

            if (is_null($date)) {
                $date = Carbon::now()->toDateString();
            }

            $attendances = Attendance::where('model_id', $model_id)->where('date', '>=', $date)->get();

            foreach ($attendances AS $attendance) {
                $attendance_comment = AttendanceComment::where('attendance_id', $attendance->id)->delete();
                $delete_attendance = Attendance::where('id', $attendance->id)->delete();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function executeScript()
    {
        //dd('YOU DID THIS ALREADY!');

        try {
            DB::beginTransaction();

            $min_id = 1;
            $max_id = 1085;

            $users = DB::connection('gbmedia')->table('usuario')->whereBetween('id', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('users', 'old_user_id'))
            {
                Schema::table('users', function (Blueprint $table) {
                    $table->integer('old_user_id')->nullable();
                });
            }

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

            $GB_locations = [
                1 => 1,
                2 => 1,
                4 => 2,
                8 => 3,
                12 => 2,
            ];

            $GB_type_contracts = [
                'PRS' => 1,
                'IND' => 2,
                'MOD' => 1,
            ];

            $GB_blood_types = [
                'A+' => 1,
                'B+' => 2,
                'O+' => 3,
                'AB+' => 4,
                'A-' => 5,
                'B-' => 6,
                'O-' => 7,
                'AB-' => 8,
                'indefinido' => 8,
            ];

            $GB_nationality = [
                'CO' => 49,
                'CU' => 57,
                'VE' => 241,
            ];

            $GB_banks = [
                 'BANCO AV VILLAS' => 2,
                 'BANCO BBVA' => 3,
                 'BANCO CAJA SOCIAL' => 4,
                 'BANCO DAVIPLATA' => 29,
                 'BANCO DAVIVIENDA' => 8,
                 'BANCO DE BOGOTA' => 9,
                 'BANCO POPULAR' => 17,
                 'BANCOLOMBIA' => 21,
                 'ITAÃš' => 34,
                 'NEQUI' => 36,
                 'BANCO COLPATRIA' => 5,
            ];

            foreach ($users AS $user) {
                /*$already_exists = User::where('email', $user->email)->exists();

                if ($already_exists) { continue; }*/

                //dd($user);
                $first_name = $user->nombre;
                $first_name = str_replace('Ã‘', 'ñ', $first_name);
                $first_name = str_replace('Ã±', 'ñ', $first_name);
                $first_name = trim($first_name);
                //$first_name = utf8_decode($first_name);

                $middle_name = $user->segundo_nombre;
                $middle_name = str_replace('Ã‘', 'ñ', $middle_name);
                $middle_name = str_replace('Ã±', 'ñ', $middle_name);
                $middle_name = trim($middle_name);
                //$middle_name = utf8_decode($middle_name);

                $last_name = $user->apellidos;
                $last_name = str_replace('Ã‘', 'ñ', $last_name);
                $last_name = str_replace('Ã±', 'ñ', $last_name);
                $last_name = trim($last_name);
                //$last_name = utf8_decode($last_name);

                //dd($last_name);
                $second_last_name = $user->segundo_apellido;
                $second_last_name = str_replace('Ã‘', 'ñ', $second_last_name);
                $second_last_name = str_replace('Ã±', 'ñ', $second_last_name);
                $second_last_name = trim($second_last_name);
                //$second_last_name = utf8_decode($second_last_name);

                $neighborhood = $user->barrio;
                $neighborhood = str_replace('Ã‘', 'ñ', $neighborhood);
                $neighborhood = str_replace('Ã±', 'ñ', $neighborhood);
                $neighborhood = trim($neighborhood);
                //$neighborhood = utf8_decode($neighborhood);

                $emergency_contact = $user->contacto_emergencia;
                $emergency_contact = str_replace('Ã‘', 'ñ', $emergency_contact);
                $emergency_contact = str_replace('Ã±', 'ñ', $emergency_contact);
                $emergency_contact = trim($emergency_contact);
                //$emergency_contact = utf8_decode($emergency_contact);

                $bank_account_owner = $user->fp_titular;
                $bank_account_owner = str_replace('Ã‘', 'ñ', $bank_account_owner);
                $bank_account_owner = utf8_decode($bank_account_owner);
                $bank_account_owner = trim($bank_account_owner);
                //dd($bank_account_owner);

                //User::where('old_user_id', $user->id)->delete();

                if(!is_null($user->ciudad) || !empty($user->ciudad) || is_numeric($user->ciudad)) {
                    $city = City::find($user->ciudad);
                    if(is_object($city)) {
                        $city_department_id = $city->department_id;
                    } else {
                        $city_department_id = 1;
                    }
                } else {
                    $city_department_id = 1;
                }

                if(!empty($user->fp_banco)) {
                    $bank_account_id = $GB_banks[$user->fp_banco];
                } else {
                    $bank_account_id = 1;
                }

                $user_created = User::firstOrCreate(
                    [
                        'old_user_id' => $user->id,
                    ],
                    [
                    'setting_role_id' => $GB_roles[$user->role_id],
                    'setting_location_id' => !is_null($user->locacion_id) ? $GB_locations[$user->locacion_id] : 2,
                    'contract_id' => $GB_type_contracts[$user->type_contract],
                    'blood_type_id' => (is_null($user->rh) || empty($user->rh) ? 1 : $GB_blood_types[$user->rh]),
                    'eps_id' => (is_null($user->eps_id) || empty($user->eps_id) ? 1 : $user->eps_id),
                    'department_id' => $city_department_id,
                    'city_id' => (is_null($user->ciudad) || empty($user->ciudad) || !is_numeric($user->ciudad) ? 1 : $user->ciudad),
                    'document_id' => 1,
                    'first_name' => ucfirst($first_name),
                    'middle_name' => ucfirst($middle_name),
                    'last_name' => ucfirst($last_name),
                    'second_last_name' => ucfirst($second_last_name),
                    'nick' => $user->usuario_modelo,
                    'birth_date' => (is_null($user->fecha_nacimiento) || empty($user->fecha_nacimiento) || $user->fecha_nacimiento == '0000-00-00' ? null : $user->fecha_nacimiento),
                    'document_number' => $user->cedula,
                    'expiration_date' => (is_null($user->fecha_vencimiento) || empty($user->fecha_vencimiento) || $user->fecha_vencimiento == '0000-00-00' ? null : $user->fecha_vencimiento),
                    'personal_email' => (is_null($user->email_alternativo) || empty($user->email_alternativo) ? null : $user->email_alternativo),
                    'password' => $user->password,
                    'email' => strtolower($user->email),
                    'mobile_number' => $user->telefono_alternativo,
                    'hangouts_password' => $user->clave_hangout,
                    'nationality' => (isset($GB_nationality[$user->nacionalidad]) ? $GB_nationality[$user->nacionalidad] : 49),
                    'address' => $user->direccion_alternativo,
                    'neighborhood' => $neighborhood,
                    'emergency_contact' => $emergency_contact,
                    'emergency_phone' => $user->telefono_emergencia,
                    'has_bank_account' => $user->tiene_cuenta,
                    'has_bank_without_retention' => 0,
                    'bank_account_id' => $bank_account_id,
                    'bank_account_document_id' => 1,
                    'bank_account_owner' => $bank_account_owner,
                    'bank_account_document_number' => $user->fp_cedula,
                    'bank_account_number' => $user->fp_nro_cuenta,
                    'bank_account_type' => $user->fp_tipo_cuenta,
                    'bank_account_city' => $user->fp_ciudad,
                    'current_salary' => (empty($user->salario) ? 0 : $user->salario),
                    'starting_salary' => (empty($user->salario_inicial) ? 0 : $user->salario_inicial),
                    'admission_date' => (is_null($user->fecha_ingreso) || empty($user->fecha_ingreso) || $user->fecha_ingreso == '0000-00-00' ? null : $user->fecha_ingreso),
                    'contract_date' => (is_null($user->fecha_contrato) || empty($user->fecha_contrato) || $user->fecha_contrato == '0000-00-00' ? null : $user->fecha_contrato),
                    'has_social_security' => $user->tiene_seguridad_s,
                    'social_security_amount' => $user->valor_seguridad_s,
                    'status' => $user->inactivo == 'no' ? 1 : 0,
                    'is_admin' => 0,
                    'is_passcode_active' => $user->u_palabra_seg_activa,
                    'user_passcode' => $user->u_palabra_seguridad,
                    'theme' => 'c-app c-dark-theme',
                    'avatar' => $user->imagen,
                    'has_uniform' => is_null($user->uniforme) || $user->uniforme == 0 ? 0 : 1,
                    'blouse_size' => $user->talla_blusa,
                    'pants_size' => $user->talla_pantalon,
                    'pants_long' => $user->largo_pantalon,
                    'has_bonus' => $user->tiene_bono_extra,
                    'bonus_amount' => $user->bono_extra,
                    'has_mobilization' => $user->tiene_aux_movilizacion,
                    'mobilization_amount' => $user->aux_movilizacion,
                    'has_transportation_aid' => $user->aux_transporte > 0 ? 1 : 0,
                    'transportation_aid_amount' => $user->aux_transporte,
                    'last_seen' => now(),
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'old_user_id' => $user->id,
                ]);

                $user_created->setting_role_id = $GB_roles[$user->role_id];
                $user_created->setting_location_id= !is_null($user->locacion_id) ? $GB_locations[$user->locacion_id] : 2;
                $user_created->contract_id = $GB_type_contracts[$user->type_contract];
                $user_created->blood_type_id= (is_null($user->rh) || empty($user->rh) ? 1 : $GB_blood_types[$user->rh]);
                $user_created->eps_id = (is_null($user->eps_id) || empty($user->eps_id) ? 1 : $user->eps_id);
                $user_created->department_id= $city_department_id;
                $user_created->city_id = (is_null($user->ciudad) || empty($user->ciudad) || !is_numeric($user->ciudad) ? 1 : $user->ciudad);
                $user_created->document_id= 1;
                $user_created->first_name = ucfirst($first_name);
                $user_created->middle_name= ucfirst($middle_name);
                $user_created->last_name = ucfirst($last_name);
                $user_created->second_last_name= ucfirst($second_last_name);
                $user_created->nick = $user->usuario_modelo;
                $user_created->birth_date= (is_null($user->fecha_nacimiento) || empty($user->fecha_nacimiento) || $user->fecha_nacimiento == '0000-00-00' ? null : $user->fecha_nacimiento);
                $user_created->document_number = $user->cedula;
                $user_created->expiration_date= (is_null($user->fecha_vencimiento) || empty($user->fecha_vencimiento) || $user->fecha_vencimiento == '0000-00-00' ? null : $user->fecha_vencimiento);
                $user_created->personal_email = (is_null($user->email_alternativo) || empty($user->email_alternativo) ? null : $user->email_alternativo);
                $user_created->password= $user->password;
                $user_created->email = strtolower($user->email);
                $user_created->mobile_number= $user->telefono_alternativo;
                $user_created->hangouts_password = $user->clave_hangout;
                $user_created->nationality= (isset($GB_nationality[$user->nacionalidad]) ? $GB_nationality[$user->nacionalidad] : 49);
                $user_created->address = $user->direccion_alternativo;
                $user_created->neighborhood= $neighborhood;
                $user_created->emergency_contact = $emergency_contact;
                $user_created->emergency_phone= $user->telefono_emergencia;
                $user_created->has_bank_account = $user->tiene_cuenta;
                $user_created->has_bank_without_retention= 0;
                $user_created->bank_account_id = $bank_account_id;
                $user_created->bank_account_document_id= 1;
                $user_created->bank_account_owner = $bank_account_owner;
                $user_created->bank_account_document_number= $user->fp_cedula;
                $user_created->bank_account_number = $user->fp_nro_cuenta;
                $user_created->bank_account_type= $user->fp_tipo_cuenta;
                $user_created->bank_account_city = $user->fp_ciudad;
                $user_created->current_salary= (empty($user->salario) ? 0 : $user->salario);
                $user_created->starting_salary = (empty($user->salario_inicial) ? 0 : $user->salario_inicial);
                $user_created->admission_date= (is_null($user->fecha_ingreso) || empty($user->fecha_ingreso) || $user->fecha_ingreso == '0000-00-00' ? null : $user->fecha_ingreso);
                $user_created->contract_date= (is_null($user->fecha_contrato) || empty($user->fecha_contrato) || $user->fecha_contrato == '0000-00-00' ? null : $user->fecha_contrato);
                $user_created->has_social_security= $user->tiene_seguridad_s;
                $user_created->social_security_amount = $user->valor_seguridad_s;
                $user_created->status= $user->inactivo == 'no' ? 1 : 0;
                $user_created->is_admin = 0;
                $user_created->is_passcode_active= $user->u_palabra_seg_activa;
                $user_created->user_passcode = $user->u_palabra_seguridad;
                $user_created->theme= 'c-app c-dark-theme';
                $user_created->avatar = $user->imagen;
                $user_created->has_uniform= is_null($user->uniforme) || $user->uniforme == 0 ? 0 : 1;
                $user_created->blouse_size = $user->talla_blusa;
                $user_created->pants_size= $user->talla_pantalon;
                $user_created->pants_long = $user->largo_pantalon;
                $user_created->has_bonus= $user->tiene_bono_extra;
                $user_created->bonus_amount = $user->bono_extra;
                $user_created->has_mobilization= $user->tiene_aux_movilizacion;
                $user_created->mobilization_amount = $user->aux_movilizacion;
                $user_created->has_transportation_aid= $user->aux_transporte > 0 ? 1 : 0;
                $user_created->transportation_aid_amount = $user->aux_transporte;
                $user_created->last_seen= now();
                $user_created->email_verified_at = now();
                $user_created->remember_token= Str::random(10);
                $user_created->created_at = now();
                $user_created->updated_at= now();
                $user_created->old_user_id= $user->id;
                $user_created->save();

                /*$user_created = User::create([
                    'setting_role_id' => $GB_roles[$user->role_id],
                    'setting_location_id' => !is_null($user->locacion_id) ? $GB_locations[$user->locacion_id] : 2,
                    'contract_id' => $GB_type_contracts[$user->type_contract],
                    'blood_type_id' => (is_null($user->rh) || empty($user->rh) ? 1 : $GB_blood_types[$user->rh]),
                    'eps_id' => (is_null($user->eps_id) || empty($user->eps_id) ? 1 : $user->eps_id),
                    'department_id' => $city_department_id,
                    'city_id' => (is_null($user->ciudad) || empty($user->ciudad) || !is_numeric($user->ciudad) ? 1 : $user->ciudad),
                    'document_id' => 1,
                    'first_name' => ucfirst($first_name),
                    'middle_name' => ucfirst($middle_name),
                    'last_name' => ucfirst($last_name),
                    'second_last_name' => ucfirst($second_last_name),
                    'nick' => $user->usuario_modelo,
                    'birth_date' => (is_null($user->fecha_nacimiento) || empty($user->fecha_nacimiento) || $user->fecha_nacimiento == '0000-00-00' ? null : $user->fecha_nacimiento),
                    'document_number' => $user->cedula,
                    'expiration_date' => (is_null($user->fecha_vencimiento) || empty($user->fecha_vencimiento) || $user->fecha_vencimiento == '0000-00-00' ? null : $user->fecha_vencimiento),
                    'personal_email' => (is_null($user->email_alternativo) || empty($user->email_alternativo) ? null : $user->email_alternativo),
                    'password' => $user->password,
                    'email' => strtolower($user->email),
                    'mobile_number' => $user->telefono_alternativo,
                    'hangouts_password' => $user->clave_hangout,
                    'nationality' => (isset($GB_nationality[$user->nacionalidad]) ? $GB_nationality[$user->nacionalidad] : 49),
                    'address' => $user->direccion_alternativo,
                    'neighborhood' => $neighborhood,
                    'emergency_contact' => $emergency_contact,
                    'emergency_phone' => $user->telefono_emergencia,
                    'has_bank_account' => $user->tiene_cuenta,
                    'has_bank_without_retention' => 0,
                    'bank_account_id' => $bank_account_id,
                    'bank_account_document_id' => 1,
                    'bank_account_owner' => $bank_account_owner,
                    'bank_account_document_number' => $user->fp_cedula,
                    'bank_account_number' => $user->fp_nro_cuenta,
                    'bank_account_type' => $user->fp_tipo_cuenta,
                    'bank_account_city' => $user->fp_ciudad,
                    'current_salary' => (empty($user->salario) ? 0 : $user->salario),
                    'starting_salary' => (empty($user->salario_inicial) ? 0 : $user->salario_inicial),
                    'admission_date' => (is_null($user->fecha_ingreso) || empty($user->fecha_ingreso) || $user->fecha_ingreso == '0000-00-00' ? null : $user->fecha_ingreso),
                    'contract_date' => (is_null($user->fecha_contrato) || empty($user->fecha_contrato) || $user->fecha_contrato == '0000-00-00' ? null : $user->fecha_contrato),
                    'has_social_security' => $user->tiene_seguridad_s,
                    'social_security_amount' => $user->valor_seguridad_s,
                    'status' => $user->inactivo == 'no' ? 1 : 0,
                    'is_admin' => 0,
                    'is_passcode_active' => $user->u_palabra_seg_activa,
                    'user_passcode' => $user->u_palabra_seguridad,
                    'theme' => 'c-app c-dark-theme',
                    'avatar' => $user->imagen,
                    'has_uniform' => is_null($user->uniforme) || $user->uniforme == 0 ? 0 : 1,
                    'blouse_size' => $user->talla_blusa,
                    'pants_size' => $user->talla_pantalon,
                    'pants_long' => $user->largo_pantalon,
                    'has_bonus' => $user->tiene_bono_extra,
                    'bonus_amount' => $user->bono_extra,
                    'has_mobilization' => $user->tiene_aux_movilizacion,
                    'mobilization_amount' => $user->aux_movilizacion,
                    'has_transportation_aid' => $user->aux_transporte > 0 ? 1 : 0,
                    'transportation_aid_amount' => $user->aux_transporte,
                    'last_seen' => now(),
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'old_user_id' => $user->id,
                ]);*/

                $role = SettingRole::select('name')->where('id', $GB_roles[$user->role_id])->first();
                $user_created->assignRole($role->name);

                /*$user_documents = DB::connection('gbmedia')->table('usuarios_documentos')->where('user_id', $user->id)->get();

                foreach ($user_documents AS $document) {
                    $document_id = 5;

                    $user_document = new UserDocument();

                    switch ($document->type) {
                        case 'frente_documento':
                            $document_id = 5;
                            break;

                        case 'reverso_documento':
                            $document_id = 6;
                            break;

                        case 'rostro_cedula_documento':
                            $document_id = 7;
                            break;

                        case 'rut_documento':
                            $document_id = 8;
                            break;

                        case 'permiso_trabajo':
                            $document_id = 9;
                            break;
                    }

                    $user_document->user_id = $user_created->id;
                    $user_document->document_id = $document_id;
                    $user_document->file_name = $document->path;
                    $user_document->save();
                }*/
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function userDocumentsExecute()
    {
        try {
            if (!Schema::hasColumn('user_documents', 'old_document_id'))
            {
                Schema::table('user_documents', function (Blueprint $table) {
                    $table->integer('old_document_id')->nullable();
                });
            }

            $min_id = 1;
            $max_id = 832; // Migrado hasta el

            $documents = DB::connection('gbmedia')->table('usuarios_documentos')->whereBetween('id', [$min_id, $max_id])->get();

            DB::beginTransaction();

            foreach ($documents AS $document) {
                $user = User::select('id')->where('old_user_id', $document->user_id)->first();

                if(is_null($user)) {
                    continue;
                }

                $user_id = $user->id;
                $document_id = 1;

                switch ($document->type) {
                    case 'frente_documento':
                        $document_id = 5;
                        break;

                    case 'reverso_documento':
                        $document_id = 6;
                        break;

                    case 'rostro_cedula_documento':
                        $document_id = 7;
                        break;

                    case 'rut_documento':
                        $document_id = 8;
                        break;

                    case 'permiso_trabajo':
                        $document_id = 9;
                        break;
                }

                $created_user_document = UserDocument::firstOrCreate(
                    [
                        'old_document_id' => $document->id,
                    ],
                    [
                        'document_id' => $document_id,
                        'user_id' => $user_id,
                        'file_name' => $document->path,
                        'old_document_id' => $document->id,
                    ]
                );

                $created_user_document->document_id = $document_id;
                $created_user_document->user_id = $user_id;
                $created_user_document->file_name = $document->path;
                $created_user_document->old_document_id = $document->id;
                $created_user_document->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

}
