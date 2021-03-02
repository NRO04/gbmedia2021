<?php

namespace App\Http\Controllers\Contracts;

use App\Http\Controllers\Controller;
use App\Models\Contracts\Contract;
use App\Models\Contracts\ContractModuleInfo;
use App\Models\Contracts\RoleHasContract;
use App\Models\Contracts\TenantHasContract;
use App\Models\Globals\Document;
use App\Models\Settings\SettingLocation;
use App\Models\Settings\SettingRole;
use App\Models\Tenancy\Tenant;
use App\Traits\TraitGlobal;
use App\User;
use Carbon\Carbon;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;

class ContractController extends Controller
{
    use TraitGlobal;

    public function __construct()
    {
        $this->middleware('auth');

        // Access to only certain methods
        $this->middleware('permission:contract-main')->only('index');
        $this->middleware('permission:contract-assign-studios')->only('assignStudios');
        $this->middleware('permission:contract-edit-module-info')->only('editingInfo');
    }

    public function index(Request $request)
    {
        $roles = SettingRole::orderBy('name', 'asc')->get();
        $global_documents = Document::where('is_listed', 1)->orderBy('name')->get();
        $locations = SettingLocation::where('id', '!=', 1)->orderBy('name')->get();

        return view('adminModules.contracts.index')->with(
            compact(
                'roles',
                'global_documents',
                'locations'
            )
        );
    }

    public function info(Request $request)
    {
        $info = ContractModuleInfo::first();
        return view('adminModules.contracts.info')->with(compact('info'));
    }

    public function assignStudios(Request $request)
    {
        return view('adminModules.contracts.assign-studios');
    }

    public function editingInfo(Request $request)
    {
        $info = ContractModuleInfo::first();
        return view('adminModules.contracts.edit-info')->with(compact('info'));
    }

    public function getModuleInfo(Request $request)
    {
        $info = ContractModuleInfo::first();
        return view('adminModules.contracts.edit-info')->with(compact('info'));
    }

    public function getContracts()
    {
        $all_contracts = Contract::with('roles')->get();
        $contracts = [];

        foreach ($all_contracts AS $contract) {
            $contract_roles = [];

            foreach ($contract->roles AS $contract_role) {
                $contract_roles[] = $contract_role->setting_role_id;
            }

            $contracts[] = [
                'id' => $contract->id,
                'title' => $contract->title,
                'description' => $contract->description,
                'url' => $contract->url,
                'image' => $contract->image,
                'active' => $contract->active,
                'actions' => '',
                'roles' => $contract_roles,
            ];
        }

        return response()->json($contracts);
    }

    public function getRole(Request $request)
    {
        $setting_role = SettingRole::find($request->role_id);

        return response()->json($setting_role);
    }

    public function getStudios(Request $request)
    {
        $data = [];
        $tenants = Tenant::with('contracts')->orderBy('data->studio_name')->get();

        foreach ($tenants AS $tenant) {
            $active = !is_null($tenant->contracts) && $tenant->contracts->active ? 1 : 0;
            $data[] = [
                'id' => $tenant->id,
                'studio' => $tenant->studio_name,
                'active' => $active,
            ];
        }

        return response()->json($data);
    }

    public function edit(Request $request)
    {
        $this->validate($request,
            [
                'title' => 'required',
                'description' => 'required',
                'url' => 'required',
            ],
            [
                'title.required' => 'Este campo es obligatorio',
                'description.required' => 'Este campo es obligatorio',
                'url.required' => 'Este campo es obligatorio',
            ]);

        try {
            DB::beginTransaction();

            $contract = Contract::find($request->id);
            $contract->title = $request->title;
            $contract->description = $request->description;
            $contract->url = $request->url;

            $image = $request->file('image');

            if($image) {
                $image_file_name = $this->uploadPublicImage($image, 'contracts');
                $contract->image = $image_file_name;
            }

            $roles = explode(',', $request->assignToRoles);

            $delete_all = RoleHasContract::where('contract_id', $request->id)->delete();

            if($request->assignToRoles) {
                foreach ($roles AS $role) {
                    $role_has_contract = new RoleHasContract();
                    $role_has_contract->contract_id = $request->id;
                    $role_has_contract->setting_role_id = $role;
                    $role_has_contract->save();
                }
            }

            $success = $contract->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editRolesAndFunction(Request $request)
    {
        try {
            DB::beginTransaction();

            $role = SettingRole::find($request->role_id);
            $role->alternative_name = $request->alternative_name;
            $role->tasks = $request->tasks;

            $success = $role->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editModuleInfo(Request $request)
    {
        $this->validate($request,
            [
                'title' => 'required',
            ],
            [
                'title.required' => 'Este campo es obligatorio',
            ]);

        try {
            DB::beginTransaction();

            $module_info = ContractModuleInfo::find(1);
            $module_info->title = $request->title;
            $module_info->description = $request->description;

            $success = $module_info->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function changeContractStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $contract = Contract::find($request->id);
            $contract->active = $request->active;
            $success = $contract->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function changeStudioStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $tenant = TenantHasContract::firstOrCreate(
                ['tenant_id' => $request->id],
                ['tenant_id' => $request->id]
            );

            $tenant->active = $request->active;
            $success = $tenant->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function changeStudioStatusBulk(Request $request)
    {
        try {
            DB::beginTransaction();
            $success = true;

            if($request->select_tenants == 1)
            {
                //Only to selected tenants
                foreach ($request->selected_tenants AS $selected_tenant) {
                    if ($selected_tenant == 1) { continue; }

                    $tenant = TenantHasContract::firstOrCreate(
                        ['tenant_id' => $selected_tenant],
                        ['tenant_id' => $selected_tenant]
                    );

                    $tenant->active = $request->option;
                    $success = $tenant->save();
                }
            } else {
                // All tenants
                $tenants = Tenant::all();

                foreach ($tenants AS $tenant) {
                    if ($tenant->id == 1) { continue; }

                    $tenant_has_contract = TenantHasContract::firstOrCreate(
                        ['tenant_id' => $tenant->id],
                        ['tenant_id' => $tenant->id]
                    );

                    $tenant_has_contract->active = $request->option;
                    $success = $tenant_has_contract->save();
                }
            }

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editConfiguration(Request $request)
    {
        try {
            DB::beginTransaction();

            $current_tenant_id = tenant('id');
            $tenant = Tenant::find($current_tenant_id);

            $contract_info = [
                'company_name' => $request->name,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'city' => $request->city,
                'legal_representative' => $request->legal_representative,
                'legal_representative_document_type' => $request->legal_representative_document_type,
                'legal_representative_document_number' => $request->legal_representative_document_number,
                'dian_code' => $request->dian_code,
                'dian_number_authorization' => $request->dian_number_authorization,
                'daily_rent' => $request->daily_rent,
                'marketing_contract_validity' => $request->marketing_contract_validity,
                'cleaning_participation_accounts' => $request->cleaning_participation_accounts,
                'min_connection_minutes_participation_accounts' => $request->min_connection_minutes_participation_accounts,
                'payment_frequency' => $request->payment_frequency,
                'salary_quantity_confidentiality_agreement_penalty' => $request->salary_quantity_confidentiality_agreement_penalty,
                'pagare_interest_ea' => $request->pagare_interest_ea,
                'witness_name' => $request->witness_name,
                'witness_document_type' => $request->witness_document_type,
                'witness_document_number' => $request->witness_document_number,
                'turns_quantity' => !is_null($request->turns_quantity) ? $request->turns_quantity : 1,
                'turns' => [],
            ];

            $turns = [];

            foreach ($request->turns AS $key => $turn) {
                $turns[$key] = [
                    'name' => $turn['name'],
                    'start_time' => $turn['start_time'],
                    'end_time' => $turn['end_time'],
                ];
            }

            $contract_info['turns'] = $turns;
            $tenant->contract_info = $contract_info;

            $success = $tenant->save();

            $tenant->contract_info = $contract_info;

            foreach ($request->locations AS $key => $location) {
                $setting_location = SettingLocation::find($key);

                if (is_null($setting_location) || $setting_location->id == 1) { continue; }

                $setting_location->address = $location['address'];
                $setting_location->save();
            }

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function getConfiguration(Request $request)
    {
        $locations = [];

        $current_tenant_id = tenant('id');
        $tenant = Tenant::find($current_tenant_id);

        $setting_locations = SettingLocation::where('id', '!=', 1)->get();

        foreach ($setting_locations AS $location) {
            $locations[$location->id] = $location->address;
        }

        $turns = [];
        if(isset($tenant->contract_info['turns'])) {
            foreach ($tenant->contract_info['turns'] AS $key => $turn) {
                $turns[$key] = [
                    'name' => $turn['name'],
                    'start_time' => $turn['start_time'],
                    'end_time' => $turn['end_time'],
                ];
            }
        }

        $data = [
            'contract_company_name' => $tenant->contract_info['company_name'],
            'contract_document_type' => $tenant->contract_info['document_type'],
            'contract_document_number' => $tenant->contract_info['document_number'],
            'contract_address' => $tenant->contract_info['address'],
            'contract_phone' => $tenant->contract_info['phone'],
            'contract_email' => $tenant->contract_info['email'],
            'contract_city' => $tenant->contract_info['city'],
            'contract_legal_representative' => $tenant->contract_info['legal_representative'],
            'contract_legal_representative_document_type' => $tenant->contract_info['legal_representative_document_type'],
            'contract_legal_representative_document_number' => $tenant->contract_info['legal_representative_document_number'],
            'contract_dian_code' => $tenant->contract_info['dian_code'],
            'contract_dian_number_authorization' => $tenant->contract_info['dian_number_authorization'],
            'contract_daily_rent' => $tenant->contract_info['daily_rent'],
            'contract_marketing_contract_validity' => $tenant->contract_info['marketing_contract_validity'],
            'contract_cleaning_participation_accounts' => $tenant->contract_info['cleaning_participation_accounts'],
            'contract_min_connection_minutes_participation_accounts' => $tenant->contract_info['min_connection_minutes_participation_accounts'],
            'contract_payment_frequency' => $tenant->contract_info['payment_frequency'],
            'contract_salary_quantity_confidentiality_agreement_penalty' => $tenant->contract_info['salary_quantity_confidentiality_agreement_penalty'],
            'contract_pagare_interest_ea' => $tenant->contract_info['pagare_interest_ea'],
            'contract_witness_name' => $tenant->contract_info['witness_name'],
            'contract_witness_document_type' => $tenant->contract_info['witness_document_type'],
            'contract_witness_document_number' => $tenant->contract_info['witness_document_number'],
            'contract_turns_quantity' => $tenant->contract_info['turns_quantity'],
            'locations_addresses' => $locations,
            'turns' => $turns,
        ];

        return response()->json($data);
    }

    public function getStudioContractInfo($tenant_id)
    {
        $tenant = Tenant::find($tenant_id);

        $studio_document_type = $tenant->contract_info['document_type'];

        $studio_document_name = '';
        if ($studio_document_type == 1)
        {
            $studio_document_name = "Cédula de Ciudadania";
        }
        else if ($studio_document_type == 2)
        {
            $studio_document_name = "Cédula de Extranjeria";
        }
        else if ($studio_document_type == 3)
        {
            $studio_document_name = "NIT";
        }
        else if ($studio_document_type == 4)
        {
            $studio_document_name = "Pasaporte";
        }

        $studio_legal_representative_document_type = $tenant->contract_info['legal_representative_document_type'];

        $studio_legal_representative_document_name = '';
        if ($studio_legal_representative_document_type == 1)
        {
            $studio_legal_representative_document_name = "Cédula de Ciudadania";
        }
        else if ($studio_legal_representative_document_type == 2)
        {
            $studio_legal_representative_document_name = "Cédula de Extranjeria";
        }
        else if ($studio_legal_representative_document_type == 3)
        {
            $studio_legal_representative_document_name = "NIT";
        }
        else if ($studio_legal_representative_document_type == 4)
        {
            $studio_legal_representative_document_name = "Pasaporte";
        }

        $data = [
            'company_name' => $tenant->contract_info['company_name'],
            'document_type' => $tenant->contract_info['document_type'],
            'document_name' => $studio_document_name,
            'document_number' => $tenant->contract_info['document_number'],
            'address' => $tenant->contract_info['address'],
            'phone' => $tenant->contract_info['phone'],
            'email' => $tenant->contract_info['email'],
            'city' => $tenant->contract_info['city'],
            'legal_representative' => $tenant->contract_info['legal_representative'],
            'legal_representative_document_type' => $tenant->contract_info['legal_representative_document_type'],
            'legal_representative_document_name' => $studio_legal_representative_document_name,
            'legal_representative_document_number' => $tenant->contract_info['legal_representative_document_number'],
            'dian_code' => $tenant->contract_info['dian_code'],
            'dian_number_authorization' => $tenant->contract_info['dian_number_authorization'],
            'daily_rent' => $tenant->contract_info['daily_rent'],
            'marketing_contract_validity' => $tenant->contract_info['marketing_contract_validity'],
            'cleaning_participation_accounts' => $tenant->contract_info['cleaning_participation_accounts'],
            'min_connection_minutes_participation_accounts' => $tenant->contract_info['min_connection_minutes_participation_accounts'],
            'payment_frequency' => $tenant->contract_info['payment_frequency'],
            'salary_quantity_confidentiality_agreement_penalty' => $tenant->contract_info['salary_quantity_confidentiality_agreement_penalty'],
            'pagare_interest_ea' => $tenant->contract_info['pagare_interest_ea'],
            'witness_name' => $tenant->contract_info['witness_name'],
            'witness_document_type' => $tenant->contract_info['witness_document_type'],
            'witness_document_number' => $tenant->contract_info['witness_document_number'],
            'turns_quantity' => $tenant->contract_info['turns_quantity'],
        ];


        /*$locations = [];

        $current_tenant_id = tenant('id');
        $tenant = Tenant::find($current_tenant_id);

        $setting_locations = SettingLocation::where('id', '!=', 1)->get();

        foreach ($setting_locations AS $location) {
            $locations[$location->id] = $location->address;
        }

        $turns = [];
        if(isset($tenant->contract_info['turns'])) {
            foreach ($tenant->contract_info['turns'] AS $key => $turn) {
                $turns[$key] = [
                    'name' => $turn['name'],
                    'start_time' => $turn['start_time'],
                    'end_time' => $turn['end_time'],
                ];
            }
        }

        $data = [
            'contract_company_name' => $tenant->contract_info['company_name'],
            'contract_document_type' => $tenant->contract_info['document_type'],
            'contract_document_number' => $tenant->contract_info['document_number'],
            'contract_address' => $tenant->contract_info['address'],
            'contract_phone' => $tenant->contract_info['phone'],
            'contract_email' => $tenant->contract_info['email'],
            'contract_city' => $tenant->contract_info['city'],
            'contract_legal_representative' => $tenant->contract_info['legal_representative'],
            'contract_legal_representative_document_type' => $tenant->contract_info['legal_representative_document_type'],
            'contract_legal_representative_document_number' => $tenant->contract_info['legal_representative_document_number'],
            'contract_dian_code' => $tenant->contract_info['dian_code'],
            'contract_dian_number_authorization' => $tenant->contract_info['dian_number_authorization'],
            'contract_daily_rent' => $tenant->contract_info['daily_rent'],
            'contract_marketing_contract_validity' => $tenant->contract_info['marketing_contract_validity'],
            'contract_cleaning_participation_accounts' => $tenant->contract_info['cleaning_participation_accounts'],
            'contract_min_connection_minutes_participation_accounts' => $tenant->contract_info['min_connection_minutes_participation_accounts'],
            'contract_payment_frequency' => $tenant->contract_info['payment_frequency'],
            'contract_salary_quantity_confidentiality_agreement_penalty' => $tenant->contract_info['salary_quantity_confidentiality_agreement_penalty'],
            'contract_pagare_interest_ea' => $tenant->contract_info['pagare_interest_ea'],
            'contract_witness_name' => $tenant->contract_info['witness_name'],
            'contract_witness_document_type' => $tenant->contract_info['witness_document_type'],
            'contract_witness_document_number' => $tenant->contract_info['witness_document_number'],
            'contract_turns_quantity' => $tenant->contract_info['turns_quantity'],
            'locations_addresses' => $locations,
            'turns' => $turns,
        ];*/

        return $data;
    }

    // Export Contracts

    public function PDF_ExportContratoAC($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        /*dump($full_name);
        dump($document_name);
        dump($contract_type_name);
        dump($document_number);
        dump($address);
        dump($mobile_number);
        dump($personal_email);
        dump($contract_date_day);
        dump($contract_date_month);
        dump($contract_date_year);*/

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        //dd($studio_contract_info);



        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        TCPDF::SetCreator('El guebo mio');
        TCPDF::SetAuthor('GBMEDIA');
        TCPDF::SetTitle('ACUERDO DE CONFIDENCIALIDAD');
        TCPDF::SetSubject('ACUERDO DE CONFIDENCIALIDAD');
        TCPDF::setPrintHeader(false);
        TCPDF::setPrintFooter(false);
        TCPDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        TCPDF::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);
        TCPDF::SetAutoPageBreak(TRUE, 0);
        TCPDF::setImageScale(PDF_IMAGE_SCALE_RATIO);

        TCPDF::AddPage();
        TCPDF::setJPEGQuality(75);
        $tbl = <<<EOD
                <h4>ACUERDO DE CONFIDENCIALIDAD
                <br><br>Entre <B>nombre_estudio</B>. y su tipo_contrato_nombre</h4>
                <p style="text-align:justify; font-size: 13px;">Entre <B>nombre_estudio</B>, identificada con <B>tipo_estudio. tipo_d_estudio</B>, representada por <B>nombre_rp_legal</B> y <B>nombre_modelo</B>, identificado (a) con tipo_documento_nombre No. documento_modelo, actuando en nombre propio, existe una relación tipo_contrato_nombre, la cual está regida por las normas generales del derecho, además de las cláusulas propias del contrato suscrito por las partes.
                <br><br>En atención a lo anterior, este documento que suscriben las partes en mención se entenderá como el <B>ACUERDO DE CONFIDENCIALIDAD</B>. Este acuerdo contiene las siguientes cláusulas:
                <br><br><B>1. Obligaciones especiales del <B>trabajador/contratista y/o proveedor de bienes o servicios</B>:</B>
                <br><br>a. Guardar absoluta confidencialidad, incluso después de terminado el contrato de respecto a: procedimientos, métodos, características, lista de clientes, claves de seguridad, suministros, software, base de datos de cualquier índole, valores de bienes y servicios, información técnica, fotografías, videos, financiera, económica o comercial del contratante o sus clientes y demás que <B>nombre_estudio</B>. utiliza en el desarrollo de su objeto social frente a clientes o terceros.
                <br><br><B>Parágrafo uno:</B> El incumplimiento de esta obligación no solo es causal de terminación de los vínculos existentes entre las partes, sino que podría conllevar a iniciar acciones judiciales en contra del <B>tipo_contrato_nombre</B> por los perjuicios materiales e inmateriales que cause, además del cobro de la cláusula penal que más adelante se describe.
                <br><br>b. No ejercer actos de competencia desleal frente al <B>nombre_estudio</B>., por lo que el <B>tipo_contrato_nombre</B> se compromete a no utilizar, incluso después de terminado el contrato de trabajo para sí o para beneficio de terceros: la lista de clientes, base de datos de cualquier índole, su software, o procedimientos, claves secretas, métodos, características, estudios, fotografías, videos, estadísticas, proyectos, suministros utilizados por <B>nombre_estudio</B>. interna y externamente frente a sus clientes o terceros, información técnica, financiera, económica o comercial de sus clientes.
                <br><br><B>Parágrafo dos:</B> Es obligación del <B>tipo_contrato_nombre</B>, devolver inmediatamente a la terminación de su contrato, lista de clientes, claves, bases de datos, equipos, información técnica, fórmulas químicas biológicas o similares, videos, fotografías, financiera, económica o comercial y todo lo demás que tenga el <B>tipo_contrato_nombre</B> de <B>nombre_estudio</B>. y que haya recibido para poder ejecutar su labor.
                <br><br><B>Parágrafo tres:</B> El incumplimiento de esta obligación no solo es causal de terminación de los vínculos existentes entre las partes, sino que podría conllevar a iniciar acciones judiciales en contra del <B>tipo_contrato_nombre</B> por los perjuicios materiales e inmateriales que cause, además del cobro de la cláusula penal que más adelante se describe.
                <br><br>c. Adoptar todas las precauciones necesarias y apropiadas para la guarda de la confidencialidad de la información que tenga el <B>tipo_contrato_nombre</B> de la empresa, esto es, lista de clientes, base de datos de cualquier índole, su software, o procedimientos, claves secretas, métodos, características, estudios, estadísticas, proyectos, fotografías, videos o suministros utilizados por <B>nombre_estudio</B>. interna y externamente frente a sus clientes o terceros, información técnica, financiera, económica o comercial de <B>nombre_estudio</B> o sus clientes.
                <br><br><B>Parágrafo cuatro:</B> La omisión del <B>tipo_contrato_nombre</B> en prevenir la fuga de información confidencial o exclusiva de la empresa, esto es, lista de clientes, base de datos de cualquier índole, su software, o procedimientos, claves secretas, métodos, características, estudios, video, fotografías, estadísticas, proyectos, suministros utilizados por <B>nombre_estudio</B>. interna y externamente frente a sus clientes o terceros, información técnica, financiera, económica o comercial de sus clientes, es causal de despido con justa causa, sin perjuicio de las acciones legales en su contra por los perjuicios causados y el cobro de las sanciones por incumplimiento, además de la cláusula penal por incumplimiento.
                <br><br><B>2. Sanciones por incumplimiento:</B> Fuera de ser causal de terminación de la relación contractual por incumplimiento de cualquiera de las obligaciones especiales que tiene el <B>tipo_contrato_nombre</B> mediante este acuerdo de confidencialidad, dará derecho a <B>nombre_estudio</B>. exigir a título de cláusula penal, la suma de nr_salario_p  salarios mínimos mensuales legales vigentes, pena que se podrá exigir vía ejecutiva sin necesidad de previo requerimiento en mora, para lo cual se acepta que la presente cláusula y el contenido de este acuerdo, constituyen una obligación clara, expresa y exigible, que presta mérito ejecutivo en los términos del Código de Procedimiento Civil y del Código General del Proceso, sin perjuicio de todas las acciones judiciales para cobrar los perjuicios ocasionados a <B>nombre_estudio</B>. Como quiera que la cláusula penal pactada es a título de pena o sanción, se podrá exigir tanto la pena como la indemnización de los perjuicios a que haya lugar.
                <br><br><B>3. Aspectos finales:</B> Este acuerdo de confidencialidad se mantendrá en el tiempo, así la relación laboral o de cualquier tipo haya terminado, pues su incumplimiento causará perjuicios a <B>nombre_estudio</B> y le dará derecho a cobrar la cláusula penal establecida por el solo hecho de su incumplimiento y sin perjuicio de las acciones judiciales del caso por los perjuicios causados.
                <br><br>Se suscribe en la ciudad de ciudad_estudio, a los dia días del mes de mes de año.</p>
                <table border="0" style="font-size: 12px;">
                    <tr>
                        <td style="text-align:left;">
                            <p>&nbsp;<br><br><br>____________________________________
                            <br>Firma
                            <br><B>NOMBRE: nombre_modelo</B>
                            <br><B>tipo_documento documento_modelo</B>
                            <br><B>El <B>tipo_contrato_nombre</B></B></p>
                        </td>
                        <td style="text-align:left;">
                        <center>
                            <table border="1">
                                <tr>
                                    <td style="width: 115px; height: 115px;"></td>
                                </tr>
                            </table>
                            <B style="text-align:left;">Huella de Índice Derecho.</B>
                        </center>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left;">
                            <p><br>&nbsp;<br><br>____________________________________
                            <br>Firma
                            <br><B>nombre_rp_legal</B>
                            <br><B>tipo_documento_rp num_documento_rp</B>
                            <br><B>nombre_estudio</B>
                            <br><B>tipo_estudio tipo_d_estudio</B></p>
                        </td>
                        <td></td>
                    </tr>
                </table>
EOD;
        TCPDF::writeHTML($tbl, true, false, false, false, '');

        TCPDF::Output('Invoice', 'I');
    }

    public function PDF_ExportContrato($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContrato";
    }

    public function PDF_ExportContratoAT($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoAT";
    }

    public function PDF_ExportContratoTD($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoTD";
    }

    public function PDF_ExportContratoCP($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoCP";
    }

    public function PDF_ExportContratoCM($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoCM";
    }

    public function PDF_ExportContratoCD($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoCD";
    }

    public function PDF_Pagare($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_Pagare";
    }

    public function PDF_ExportContratoTI($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoTI";
    }

    public function PDF_ExportContratoPS($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoPS";
    }

    public function PDF_ExportContratoMKP($user_id)
    {
        $user = User::find($user_id);

        $first_name = $user->first_name;
        $middle_name = $user->middle_name;
        $last_name = $user->last_name;
        $second_last_name = $user->second_last_name;
        $full_name = "$first_name $middle_name $last_name $second_last_name";
        $full_name = strtoupper($full_name);

        //$full_name = "$first_name" . !empty($middle_name) ? " $middle_name" : "" . " $last_name" . !empty($second_last_name) ? " $second_last_name" : "";

        $document_type = $user->document_id;

        if ($document_type == 1)
        {
            $document_name = "Cédula de Ciudadania";
        }
        else if ($document_type == 2)
        {
            $document_name = "Cédula de Extranjeria";
        }
        else if ($document_type == 3)
        {
            $document_name = "NIT";
        }
        else if ($document_type == 4)
        {
            $document_name = "Pasaporte";
        }

        $document_number = $user->document_number;
        $address = $user->address;
        $mobile_number = $user->mobile_number;
        $personal_email = $user->personal_email;

        $contract_type = $user->contract_id;

        if ($contract_type == 1)
        {
            $contract_type_name = "Contratista";
        }
        elseif ($contract_type == 2)
        {
            $contract_type_name = "Empleado";
        }

        $contract_date_day = Carbon::parse($user->contract_date)->day;
        $contract_date_month = Carbon::parse($user->contract_date)->month;
        $contract_date_year = Carbon::parse($user->contract_date)->year;

        // Studio

        $studio_contract_info = $this->getStudioContractInfo(tenant('id'));
        return "CONTRACT: PDF_ExportContratoMKP";
    }
}
