<?php

namespace App\Http\Controllers\Studios;

use App\Events\CreateStudio;
use App\Http\Controllers\Controller;
use App\Models\News\News;
use App\Models\News\NewsRoles;
use App\Models\News\NewsStudio;
use App\Models\News\NewsUsers;
use App\Models\Settings\SettingRole;
use App\Models\Studios\StudioLastLogin;
use App\Models\Studios\Studios;
use App\Models\Studios\TenantHasTenant;
use App\Models\Studios\UserHasTenant;
use App\Models\Tenancy\Tenant;
use App\Models\Training\Training;
use App\Models\Training\Trainingroles;
use App\Models\Training\TrainingStudios;
use App\Models\Training\TrainingUsers;
use App\Models\Wiki\Wiki;
use App\Models\Wiki\WikiRole;
use App\Models\Wiki\WikiStudios;
use App\Models\Wiki\WikiUser;
use App\User;
use Carbon\Carbon;
use Database\Seeds\tenant\TenantDatabaseSeeder;
use Faker\Generator;
use GlobalCountriesSeeder2;
use Illuminate\Http\Request;
use App\Traits\TraitGlobal;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LocationTableSeeder;
use mysqli;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use SeedTenancy\DatabaseSeeder;
use Stancl\Tenancy\Features\UserImpersonation;

class StudioController extends Controller
{
    use TraitGlobal;

    public function __construct()
    {
        $this->middleware('auth');

        // Access to only certain methods
        $this->middleware('permission:studio')->only('list');
        $this->middleware('permission:studio-assign-user')->only('assignUsers');

        $tables_to_copy[] = [
            'ast_tipo',
            'locacion',
            'otraslocaciones',
            'locacion_has_otraslocaciones',
            'paginas',
            'productos_bloqueo_max',
            'rh_valor_extras',
            'role',
            'sec_show_options',
            'opciones',
            'role_has_tareas',
            'role_has_tareas1',
            'tareas',
            'tarea_p',
            'trabajo_rol_crea',
            'cafeteria_tipo',
            'cafeteria_des_category',
            'cafeteria_des_variety',
            'modulo',
            'modulo_has_study',
            'studies_child',
            'rh_alarmas',
            'rh_label_imagen',
            'view_information',
            'sec_estado',
            'x_subestudios',
            'x_substudio_email_info',
            'configuracion',
        ];
    }

    public function list(Request $request)
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $roles = SettingRole::orderBy('name')->get();

        return view('adminModules.studios.list')->with(compact(['roles', 'user_permission']));
    }

    public function assignUsers(Request $request)
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $users = User::where('is_admin', '!=', 1)->where('status', 1)->where('setting_role_id', '!=', 14)->orderBy('first_name')->orderBy('last_name')->get();

        $content = new Request();
        $content->tenant_id = tenant('id');

        $studio_assignments = $this->getStudioAssignments($content);

        return view('adminModules.studios.assign-users')->with(compact(['users', 'studio_assignments', 'user_permission']));
    }

    public function generateImpersonateToken(Request $request, $to_tenant_id)
    {
        $current_tenant_id = tenant('id');
        $central_tenant = Tenant::find(1);

        $tenant_user_id = null;

        $redirect_url = '/news';

        //$tenant = Tenant::where('studio_id', $request->studio_id)->first();

        $from_user_id = $central_tenant->run(function () use ($current_tenant_id, $to_tenant_id) {
            $auth_user_id = Auth::user()->id;

            $from_user_tenant = UserHasTenant::where('to_user_id', $auth_user_id)->where('to_tenant_id', $current_tenant_id)->first();
            $from_user_id = $from_user_tenant->from_user_id;

            $from_user_tenant = UserHasTenant::where('from_user_id', $from_user_id)->where('to_tenant_id', $to_tenant_id)->first();
            $to_user_id = $from_user_tenant->to_user_id;

            return $to_user_id;
        });

        $to_tenant = Tenant::find($to_tenant_id);

        $to_user_id = $to_tenant->run(function () use ($from_user_id) {
            return User::find($from_user_id);
        });

        $token = tenancy()->impersonate($to_tenant, $from_user_id, $redirect_url);

        //dd("http://$to_tenant->studio_url/impersonate/$token->token");
        return redirect("http://$to_tenant->studio_url/impersonate/$token->token");

        //return UserImpersonation::makeResponse($token->token);

        //return response()->json(['success' => true, 'token' => $token->token]);
    }

    public function impersonate(Request $request, $token)
    {
        return UserImpersonation::makeResponse($token);
    }

    function generateCode($length = 4, $letters = false, $letters_count = 4)
    {
        $code = '';

        if($letters) {
            $random_letters = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $letters_count);
            $code = $random_letters;
        }

        $random_numbers = substr(str_shuffle("0123456789"), 0, $length);

        $code .= $random_numbers;

        return trim($code);
    }

    function roomsControlCodeExists($code)
    {
        return Studios::where('rooms_control_code', $code)->exists();
    }

    public function getStudios(Request $request)
    {
        $data = [];

        $content = new Request();
        $content->tenant_id = tenant('id');

        $tenants = $this->getStudioAssignments($content);

        foreach ($tenants AS $tenant) {
            $last_login_user = isset($tenant->hasTenant->last_login_user_name) ? $tenant->hasTenant->last_login_user_name : null;
            $last_login_diff = isset($tenant->hasTenant->last_login_datetime) ? Carbon::parse($tenant->hasTenant->last_login_datetime)->diffForHumans() : null;
            $last_login_format = isset($tenant->hasTenant->last_login_datetime) ? Carbon::parse($tenant->hasTenant->last_login_datetime)->format('d/M/Y h:i a') : null;

            if($tenant->hasTenant->is_active != $request->status) { continue; }

            $data[] = [
                'tenant_id' => $tenant->hasTenant->id,
                'studio_name' => $tenant->hasTenant->studio_name,
                'studio_slug' => $tenant->hasTenant->studio_slug,
                'studio_url' => $tenant->hasTenant->studio_url,
                'studio_api' => 'API Studio',
                'is_shared' => $tenant->hasTenant->should_share,
                'is_active' => $tenant->hasTenant->is_active,
                'assign' => 'asignar',
                'studio_logo' => asset("storage/" . $tenant->hasTenant->studio_slug . "/logo/" . $tenant->hasTenant->studio_logo),
                'studio_last_login_user' => $last_login_user,
                'studio_last_login_diff' => $last_login_diff,
                'studio_last_login_datetime' => $last_login_format,
                'login_url' => "$tenant->hasTenant->id.gblaravel.test/impersonate",
                'studio_created_date' =>  Carbon::parse($tenant->created_at)->format('d/M/Y'),
            ];
        }

        return response()->json($data);
    }

    public function createStudio(Request $request)
    {

        $studio_name = ucwords($request->name);
        $studio_name_lower = trim(strtolower($this->removeAccents($request->name)));
        $studio_name_snake_case = str_replace(' ', '_', $studio_name_lower);
        $studio_name_kebab_case = str_replace(' ', '-', $studio_name_lower);
        $studio_name_for_domain = str_replace(' ', '', $studio_name_lower);
        $studio_db_name = $studio_name_snake_case . "_studio";

        $studio_url = "https://gbmediagroup.com/admin/admin/login/$studio_name_kebab_case";
        $studio_domain = "$studio_name_for_domain.gblaravel.test";

        $created = Storage::disk('public')->makeDirectory($studio_name_snake_case);

        if(!$created) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al crear la carpeta del estudio. Por favor intente mas tarde.', 'code' => 1]);
        }

        event(new CreateStudio(['step' => 1, 'step_code' => 'directory_created']));

        try {
            DB::beginTransaction();
            $success = true;

            do {
                $rooms_control_code = $this->generateCode(4);
            } while ($this->roomsControlCodeExists($rooms_control_code));

            $logo = $request->file('logo');
            $logo_file_name = 'logo.png';

            if($logo) {
                //$this->tenantFolderExists($studio_name_snake_case, 'logo');
                $logo_file_name = $this->tenantUploadFile($logo, 'logo', $studio_name_snake_case, 'logo');
            }

            $tenant = Tenant::create(
                [
                    'tenancy_db_name' => $studio_db_name,
                    'studio_name' => $studio_name,
                    'studio_slug' => $studio_name_snake_case,
                    'studio_url' => $studio_domain,
                    'studio_logo' => $logo_file_name,
                    'is_active' => 1,
                    'studio_owner_id' => null,
                    'rooms_control_code' => $rooms_control_code,
                    'unique_code' => '',
                    'support_url' => null,
                    'support_db_name' => null,
                    'support_db_user' => null,
                    'support_db_passcode' => null,
                    'should_share' => $request->shared == 'true' ? 1 : 0,
                    'owner_id' => 0,
                ]
            );

            $tenant->domains()->create(
                ['domain' => $studio_domain]
            );

            event(new CreateStudio(['step' => 2, 'step_code' => 'db_created']));

            // Get GB admin users
            $admin_users = User::where('setting_role_id', 11)->orWhere('setting_role_id', 1)->where('status', 1)->get();

            $tenant->run(function () use ($tenant, $request, $admin_users) {
                (new \TenantDatabaseSeeder())->run();

                // Adding admin users
                $this->addAdminUsers($admin_users, $tenant->id);

                $create_user = $request->create_user == 'true' ? true : false;

                if($create_user) {
                    $now = Carbon::now();

                    // Create user
                    DB::table('users')->insert([
                        'setting_role_id' => $request->user_role_id,
                        'setting_location_id' => 1,
                        'contract_id' => 2,
                        'current_salary' => 0,
                        'blood_type_id' => 1,
                        'first_name' => $request->user_name,
                        'middle_name' => '',
                        'last_name' => $request->user_last_name,
                        'second_last_name' => '',
                        'birth_date' => $now,
                        'address' => '',
                        'email' => $request->user_email,
                        'email_verified_at' => now(),
                        'password' => bcrypt($request->user_password),
                        'theme' => 'c-app c-dark-theme',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'avatar' => 'avatar.png',
                        'contract_date' => $now,
                        'department_id' => 1,
                        'city_id' => 1,
                        'document_id' => 1,
                    ]);

                    $user = User::where('email',  $request->user_email)->first();
                    $role_name = SettingRole::find($request->user_role_id)->first();

                    $user->assignRole($role_name->name);
                }

                // Assign admin users to the new studio

                // Do here other actions in the created studio
                $shared = $request->shared == 'true' ? true : false;

                if($shared) {
                    // Share everything with the new studio!

                    // share posts and news and trainings
                    $role = $request->user_role_id;
                    $root = 594;
                    $wikis = Wiki::where('is_shared', 1)->get();
                    if (!empty($wikis)){
                        foreach ($wikis as $wiki){
                            WikiStudios::create([
                                'studio_id' => $tenant->id,
                                'wiki_id' => $wiki->id,
                                'wiki_category_id' => $wiki->wiki_category_id,
                                'created_by' => $root,
                            ]);

                            WikiRole::create([
                                'setting_role_id' => $role,
                                'wiki_id' => $wiki->id,
                                'studio_id' => $tenant->id
                            ]);

                            $users = User::where('setting_role_id', $role)->get();
                            foreach($users as $user){
                                WikiUser::create([
                                    'user_id' => $user->id,
                                    'wiki_id' => $wiki->id,
                                    'studio_id' => $tenant->id,
                                    'status' => 0,
                                ]);
                            }
                        }
                    }

                    $news = News::where('is_shared', 1)->get();
                    if (!empty($news)){
                        foreach ($news as $new){
                            NewsStudio::create([
                                'studio_id' => $tenant->id,
                                'news_id' => $new->id,
                                'created_by' => $root,
                            ]);

                            NewsRoles::create([
                                'news_id' => $new->id,
                                'role_id' => $role,
                                'studio_id' => $tenant->id
                            ]);

                            $users = User::where('setting_role_id', $role)->get();
                            foreach($users as $user){
                                NewsUsers::create([
                                    'news_id' => $new->id,
                                    'user_id' => $user->id,
                                    'studio_id' => $tenant->id,
                                    'status' => 0,
                                ]);
                            }
                        }
                    }

                    $trainings = Training::where('is_shared', 1)->get();
                    if (!empty($trainings)){
                        foreach ($trainings as $training){
                            TrainingStudios::create([
                                'training_id' => $training->id,
                                'studio_id' => $tenant->id,
                                'status' => 0,
                                'created_by' => $root,
                            ]);

                            Trainingroles::create([
                                'training_id' => $training->id,
                                'setting_role_id' => $role,
                                'studio_id' => $tenant->id
                            ]);

                            $users = User::where('setting_role_id', $role)->get();
                            foreach($users as $user){
                                TrainingUsers::create([
                                    'training_id' => $training->id,
                                    'user_id' => $user->id,
                                    'studio_id' => $tenant->id,
                                    'status' => 0,
                                ]);
                            }
                        }
                    }
                }

                event(new CreateStudio(['step' => 3, 'step_code' => 'db_seeded']));
            });

            // Do here other actions in GB studio

            // Assign new studio to GB
            $tenant_has_tenants = new TenantHasTenant();
            $tenant_has_tenants->tenant_id = 1;
            $tenant_has_tenants->has_tenant_id = $tenant->id;
            $tenant_has_tenants->save();

            // Assign new studio to itself
            $tenant_has_tenants = new TenantHasTenant();
            $tenant_has_tenants->tenant_id = $tenant->id;
            $tenant_has_tenants->has_tenant_id = $tenant->id;
            $tenant_has_tenants->save();

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function changeStudioStatus(Request $request)
    {
        $tenant = Tenant::where('id', $request->id)->first();
        $tenant->is_active = $request->status;
        $success = $tenant->save();

        return response()->json(['success' => $success]);
    }

    public function changeStudioLogo(Request $request)
    {
        $success = false;

        $logo = $request->file('logo');
        $tenant = Tenant::where('id', $request->tenant_id)->first();
        $studio_slug = $tenant->studio_slug;

        if($logo) {
            $logo_file_name = $this->tenantUploadFile($logo, 'logo', $studio_slug, 'logo');

            $tenant->studio_logo = $logo_file_name;
            $success = $tenant->save();
        }

        return response()->json(['success' => $success]);
    }

    public function checkIfStudioExists(Request $request)
    {
        $exists = Tenant::where('data->studio_name', $request->studio_name)->exists();
        return response()->json(['exists' => $exists]);
    }

    private function addAdminUsers($admin_users, $tenant_id)
    {
        $central_tenant = Tenant::find(1); // GB

        foreach ($admin_users AS $admin_user) {
            $now = Carbon::now()->format('Y-m-d H:i:s');

            DB::table('users')->insert([
                'setting_role_id' => $admin_user->setting_role_id,
                'setting_location_id' => 1,
                'contract_id' => $admin_user->contract_id,
                'current_salary' => $admin_user->current_salary,
                'blood_type_id' => $admin_user->blood_type_id,
                'first_name' => $admin_user->first_name,
                'middle_name' => $admin_user->middle_name,
                'last_name' => $admin_user->last_name,
                'second_last_name' => $admin_user->second_last_name,
                'birth_date' => $admin_user->birth_date,
                'address' => $admin_user->address,
                'email' => $admin_user->email,
                'email_verified_at' => $admin_user->email_verified_at,
                'password' => $admin_user->password,
                'theme' => $admin_user->theme,
                'created_at' => $now,
                'updated_at' => $now,
                'avatar' => $admin_user->avatar,
                'contract_date' => $admin_user->contract_date,
                'department_id' => $admin_user->department_id,
                'city_id' => $admin_user->city_id,
                'document_id' => $admin_user->document_id,
                'document_number' => $admin_user->document_number,
                'expiration_date' => $admin_user->expiration_date,
                'is_admin' => 1,
            ]);

            $user = User::where('email', $admin_user->email)->first();
            $user_role = SettingRole::where('id', $admin_user->setting_role_id)->first();
            $user->assignRole($user_role->name);

            $central_tenant->run(function () use ($admin_user, $user, $tenant_id) {
                $user_has_tenant = new UserHasTenant();
                $user_has_tenant->from_user_id = $admin_user->id;
                $user_has_tenant->from_tenant_id = 1;
                $user_has_tenant->to_user_id = $user->id;
                $user_has_tenant->to_tenant_id = $tenant_id;
                $user_has_tenant->save();
            });
        }
    }

    public function changeToStudio(Request $request)
    {
        $tenant_user_id = null;

        $redirect_url = '/dashboard';

        $tenant = Tenant::where('id', $request->studio_id)->first();
        //dd($tenant);

        $tenant_user_id = $tenant->run(function () {
            $user = Auth::user();

            $tenant_user = User::where('email', $user->email)->first();
            return $tenant_user->id;
        });

        $token = tenancy()->impersonate($tenant, $tenant_user_id, $redirect_url);

        return response()->json(['success' => true, 'token' => $token->token]);
    }

    public function assignStudio(Request $request)
    {
        $this->resetStudioAssignments($request->tenant_id);

        // Assign itself
        $tenant_has_tenants = new TenantHasTenant();
        $tenant_has_tenants->tenant_id = $request->tenant_id;
        $tenant_has_tenants->has_tenant_id = $request->tenant_id;
        $tenant_has_tenants->save();

        // Assign selected studios
        foreach ($request->assign_to AS $tenant_id) {
            $exists = TenantHasTenant::where('tenant_id', $request->tenant_id)->where('has_tenant_id', $tenant_id)->exists();

            if (!$exists) {
                $tenant_has_tenants = new TenantHasTenant();
                $tenant_has_tenants->tenant_id = $request->tenant_id;
                $tenant_has_tenants->has_tenant_id = $tenant_id;
                $tenant_has_tenants->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function getStudioAssignments(Request $request)
    {
        $tenant = Tenant::find(1);

        $assignments = $tenant->run(function () use ($request) {
            return TenantHasTenant::where('tenant_id', $request->tenant_id)->with('hasTenant')->get();
        });

        return $assignments;
    }

    public function getStudioUsers(Request $request)
    {
        $tenant = Tenant::where('studio_id', $request->studio_id)->first();

        return $tenant->run(function () {
            return User::where('is_admin', '!=', 1)->where('status', 1)->get();
        });
    }

    public function resetStudioAssignments($tenant_id)
    {
        return TenantHasTenant::where('tenant_id', $tenant_id)->delete();
    }

    public function assignUserToTenant(Request $request)
    {
        $current_tenant_id = tenant('id');
        $user = User::findOrFail($request->user_id);

        foreach ($request->tenants AS $tenant_id) {
            $tenant = Tenant::find($tenant_id);

            $user_created = $tenant->run(function () use ($user) {
                $user_create = null;

                $exists = User::where('email', $user->email)->first();

                if(!is_null($exists)) {
                    return $exists;
                }

                $now = Carbon::now()->format('Y-m-d H:i:s');

                DB::table('users')->insert([
                    'setting_role_id' => $user->setting_role_id,
                    'setting_location_id' => 1,
                    'contract_id' => $user->contract_id,
                    'current_salary' => $user->current_salary,
                    'blood_type_id' => $user->blood_type_id,
                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name' => $user->last_name,
                    'second_last_name' => $user->second_last_name,
                    'birth_date' => $user->birth_date,
                    'address' => $user->address,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'password' => $user->password, // password
                    'theme' => $user->theme,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'avatar' => $user->avatar,
                    'contract_date' => $user->contract_date,
                    'department_id' => $user->department_id,
                    'city_id' => $user->city_id,
                    'document_id' => $user->document_id,
                    'document_number' => $user->document_number,
                    'expiration_date' => $user->expiration_date,
                    'is_admin' => 1,
                ]);

                $user_create = User::latest()->first();
                $user_role = SettingRole::where('id', $user_create->setting_role_id)->first();
                $user_create->assignRole($user_role->name);

                return $user_create;
            });

            if(!is_null($user_created)) {

                // Connect to GB
                $tenant_gb = Tenant::find(1);

                $tenant_gb->run(function () use ($user, $user_created, $current_tenant_id, $tenant_id) {
                    $already_exists = UserHasTenant
                        ::where('from_tenant_id', $current_tenant_id)
                        ->where('to_user_id', $user_created->id)
                        ->where('to_tenant_id', $tenant_id)
                        ->first();

                    if(is_null($already_exists)) {
                        $user_has_tenant = new UserHasTenant();
                        $user_has_tenant->from_user_id = $user->id;
                        $user_has_tenant->from_tenant_id = $current_tenant_id;
                        $user_has_tenant->to_user_id = $user_created->id;
                        $user_has_tenant->to_tenant_id = $tenant_id;
                        $user_has_tenant->save();
                    }
                });
            }
        }

        return response()->json(['success' => true]);
    }

    public function getAssignedUsers(Request $request)
    {
        $data = [];
        $users = [];

        $current_tenant_id = tenant('id');

        // Connect to GB
        $tenant = Tenant::find(1);

        $assigned_users = $tenant->run(function () use ($current_tenant_id)  {
            return UserHasTenant::where('from_tenant_id', $current_tenant_id)->with('fromUser')->get();
        });

        foreach ($assigned_users AS $key => $assigned_user) {
            $user = User::find($assigned_user->fromUser->id);
            $user_assignments = $this->getUserTenantsAssignments($current_tenant_id, $user->id);
            $user_assignments_names = collect($user_assignments)->pluck('studio_name')->implode(', ');

            $users[$user->id] = [
                'user_id' => $user->id,
                'user_name' => $user->roleUserShortName(),
                'user_role' => $user->role->name,
                'assigned_to' => $this->getUserTenantsAssignments($current_tenant_id, $user->id),
                'assigned_to_names' => $user_assignments_names,
                'assigned_date' => Carbon::parse($assigned_user->created_at)->format('d / M / Y h:m a'),
            ];
        }

        foreach ($users AS $user_data) {
            $data[] = $user_data;
        }

        return $data;
    }

    public function getUserTenantsAssignments($tenant_id, $user_id)
    {
        // Connect to GB
        $tenant = Tenant::find(1);

        return $tenant->run(function () use ($tenant_id, $user_id)  {
            $tenants = [];
            $user_tenants = UserHasTenant::where('from_tenant_id', $tenant_id)->where('from_user_id', $user_id)->get();

            foreach ($user_tenants AS $user_tenant) {
                $tenant = Tenant::where('id', $user_tenant->to_tenant_id)->select(['id AS studio_id', 'data->studio_name AS studio_name'])->orderBy('studio_name')->first();

                $tenants[] = $tenant;
            }

            return $tenants;
        });
    }

    public function removeAssignments(Request $request)
    {
        $central_tenant = Tenant::find(1); // GB

        foreach ($request->tenants AS $tenant_id) {
            $to_user_id = $central_tenant->run(function () use ($request, $tenant_id) {
                $user_has_tenant = UserHasTenant::select('to_user_id')->where('from_user_id', $request->user_id)->where('to_tenant_id', $tenant_id)->first();
                $to_user_id = $user_has_tenant->to_user_id;

                UserHasTenant::select('to_user_id')->where('from_user_id', $request->user_id)->where('to_tenant_id', $tenant_id)->delete();

                return $to_user_id;
            });

            $tenant = Tenant::find($tenant_id);

            $deleted = $tenant->run(function () use ($to_user_id, $tenant_id) {
                if($tenant_id != 1) {
                    User::find($to_user_id)->delete();
                }
            });
        }

        return response()->json(['success' => true]);
    }
}
