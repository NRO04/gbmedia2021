<?php

namespace App\Http\Controllers\HumanResources;
use App\Http\Controllers\Payrolls\PayrollController;
use App\Models\Attendance\AttendanceSummary;
use App\Http\Controllers\Tasks\TaskController;
use App\Models\Globals\City;
use App\Models\Globals\Department;
use App\Models\HumanResources\ReferredModel;
use App\Models\HumanResources\ReferredModelImage;
use App\Models\HumanResources\ReferredModelSeen;
use App\Models\HumanResources\ReferredModelShared;
use App\Models\HumanResources\ReferredModelStudio;
use App\Models\HumanResources\RHAlarm;
use App\Models\HumanResources\RhInterviewHistory;
use App\Models\HumanResources\RHInterviewSon;
use App\Models\HumanResources\RHVacationRequest;
use App\Models\HumanResources\RHInterviewImg;
use App\Models\HumanResources\RHVacationUser;
use App\Models\HumanResources\RHInterviews;
use App\Models\HumanResources\RHExtraValue;
use App\Models\HumanResources\RHExtraHours;
use App\Models\Globals\GlobalTypeContract;
use App\Models\HumanResources\RHWorkingInfo;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Models\Tasks\TaskCommentAttachment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Models\Settings\SettingLocation;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Payrolls\PayrollMovement;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Payrolls\Payroll;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Settings\SettingRole;
use App\Traits\TraitGlobal;
use Carbon\Carbon;
use App\User;
use App\Traits\TraitHolliday;
use DataTables;

class HumanResourceController extends Controller
{
    use TraitGlobal;
    //##INTERVIEWS---------------------------------------------------------------------------------------------------------
    public function storeInterview($id = null)
    {
        $referred_prospect_data = null;

        if (!is_null($id)) {
            $referred_prospect_data = ReferredModel::where('id', $id)->first();
        }

        $date = Carbon::now()->format('Y-m-d');
        $setting_roles = DB::table('setting_roles')->select('id', 'name')->orderBy('name', 'asc')->get();
        $blood_types = DB::table('global_blood_types')->select('id', 'name')->orderBy('name', 'asc')->get();
        $document_types = DB::table('global_documents')->select('id', 'name')->where('is_listed', 1)->orderBy('id', 'asc')->get();
        $department_list = DB::table('global_departments')->select('id', 'name')->orderBy('name', 'asc')->get();

        return view('adminModules.rh.interview.create', compact('setting_roles', 'blood_types', 'date', 'document_types', 'department_list', 'referred_prospect_data'));
    }

    public function createInterview(Request $request)
    {
        $this->validate($request,
            [
                'setting_role_id' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'birth_date' => 'required',
                'document_number' => ['required',Rule::unique('rh_interviews')->where(function ($query) use ($request){
                    return $query->where('document_number',$request->document_id)->where('document_id',$request->document_id);
                })],
                'blood_type_id' => 'required',
                'email' => 'required',
                'mobile_number' => 'required',
                'address' => 'required',
                'neighborhood' => 'required',
                'department_id' => 'required',
                'city_id' => 'required',

                'edu_type_study' => 'required_if:edu_validate,==,1',
                'edu_time_final' => 'required_if:edu_validate,==,1',
                'edu_name_inst_current' => 'required_if:edu_validate,==,1',
                'edu_schedule' => 'required_if:edu_validate,==,1',

                'which_study' => 'required_if:was_model,==,1',
                'how_long' => 'required_if:was_model,==,1',
                'work_pages' => 'required_if:was_model,==,1',
                'how_much' => 'required_if:was_model,==,1',
                'retirement_reason' => 'required_if:was_model,==,1',

            ],
            [
                'setting_role_id.required' => 'Debe seleccionar un rol.',
                'first_name.required' => 'Este campo es obligatorio.',
                'last_name.required' => 'Este campo es obligatorio.',
                'birth_date.required' => 'Este campo es obligatorio.',
                'document_number.required' => 'Este campo es obligatorio.',
                'document_number.unique' => 'El numero de documento ya existe para el tipo de documento seleccionado.',
                'blood_type_id.required' => 'Este campo es obligatorio.',
                'email.unique' => 'El email ya se encuentra registrado.',
                'email.required' => 'Este campo es obligatorio.',
                'mobile_number.required' => 'Este campo es obligatorio.',
                'address.required' => 'Este campo es obligatorio.',
                'neighborhood.required' => 'Este campo es obligatorio.',
                'department.required' => 'Este campo es obligatorio.',
                'city_id.required' => 'Este campo es obligatorio.',
                'count_person.required_if' => 'Este campo es obligatorio.',
                'recommended_name.required_if' => 'Este campo es obligatorio.',
                'not_adapts_reason.required_if' => 'En el caso de no adaptar al perfil debe escribir la razon por la cual no se adapta.',
                'not_adapts_reason.required_if' => 'En el caso de no adaptar al perfil debe escribir la razon por la cual no se adapta.',


                'which_study.required_if' => 'Este campo es obligatorio.',
                'how_long.required_if' => 'Este campo es obligatorio.',
                'work_pages.required_if' => 'Este campo es obligatorio.',
                'how_much.required_if' => 'Este campo es obligatorio.',
            ]
        );

        try {

            DB::beginTransaction();
            $RHInterviews = new RHInterviews();
            //foreign keys
            $RHInterviews->user_interviewer_id = $request->user_interviewer_id;
            $RHInterviews->setting_role_id = $request->setting_role_id;
            $RHInterviews->document_id = $request->document_id;
            $RHInterviews->blood_type_id = $request->blood_type_id;
            $RHInterviews->department_id = $request->department_id;
            $RHInterviews->city_id = $request->city_id;
            //personal information
            $RHInterviews->first_name = ucfirst(strtolower($request->first_name));
            $RHInterviews->middle_name = ucfirst(strtolower($request->middle_name));
            $RHInterviews->last_name = ucfirst(strtolower($request->last_name));
            $RHInterviews->second_last_name = ucfirst(strtolower($request->second_last_name));
            $RHInterviews->birth_date = $request->birth_date;
            $RHInterviews->document_number = $request->document_number;
            $RHInterviews->expiration_date = $request->expiration_date;
            $RHInterviews->email = $request->email;
            $RHInterviews->mobile_number = $request->mobile_number;
            $RHInterviews->address = $request->address;
            $RHInterviews->neighborhood = $request->neighborhood;
            $RHInterviews->lives_with = $request->lives_with;
            $RHInterviews->emergency_contact = $request->emergency_contact;
            $RHInterviews->emergency_phone = $request->emergency_phone;
            $RHInterviews->he_has_children = $request->he_has_children;
            $RHInterviews->availability = $request->availability;
            //model web cam information
            $RHInterviews->was_model = $request->was_model;
            $RHInterviews->which_study = $request->which_study;
            $RHInterviews->how_long = $request->how_long;
            $RHInterviews->work_pages = $request->work_pages;
            $RHInterviews->how_much = $request->how_much;
            $RHInterviews->retirement_reason = $request->retirement_reason;
            $success = $RHInterviews->save();

            $id = $RHInterviews->id;
            $role = $RHInterviews->setting_role_id;

            DB::table('rh_interviewer_img')->insert(
                [
                    'rh_interview_id' => $id
                ]
            );

            if(isset($request->son)){
                $sons = $request->son;
                foreach($sons as $son)
                {
                    if($son != '')
                    DB::table('rh_interviewer_son')->insert(
                        [
                            'rh_interview_id' => $id,
                            'name' => $son
                        ]
                    );
                }
            }

            $url = route('rh.interview.others.edit',[$id]);
            if($role == 14)
                $url = route('rh.interview.model.edit',[$id]);


            // Set RH alarm
            $alarm = RHAlarm::query()->update(['rha_interviews' => 1]);

            DB::commit();
            return response()->json(['success' => true, 'url' => $url]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateInterviewPersonal(Request $request)
    {
        $this->validate($request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'birth_date' => 'required',
                'blood_type_id' => 'required',
                'email' => 'required',
                'mobile_number' => 'required',
                'address' => 'required',
                'neighborhood' => 'required',
                'department_id' => 'required',
                'city_id' => 'required',
                'edu_type_study' => 'required_if:edu_validate,==,1',
                'edu_time_final' => 'required_if:edu_validate,==,1',
                'edu_name_inst_current' => 'required_if:edu_validate,==,1',
                'edu_schedule' => 'required_if:edu_validate,==,1',
                'which_study' => 'required_if:was_model,==,1',
                'how_long' => 'required_if:was_model,==,1',
                'work_pages' => 'required_if:was_model,==,1',
                'how_much' => 'required_if:was_model,==,1',
                'retirement_reason' => 'required_if:was_model,==,1',
            ],
            [
                'first_name.required' => 'Este campo es obligatorio.',
                'last_name.required' => 'Este campo es obligatorio.',
                'birth_date.required' => 'Este campo es obligatorio.',
                'document_number.required' => 'Este campo es obligatorio.',
                'document_number.unique' => 'El numero de documento ya existe para el tipo de documento seleccionado.',
                'blood_type_id.required' => 'Este campo es obligatorio.',
                'email.unique' => 'El email ya se encuentra registrado.',
                'email.required' => 'Este campo es obligatorio.',
                'mobile_number.required' => 'Este campo es obligatorio.',
                'address.required' => 'Este campo es obligatorio.',
                'neighborhood.required' => 'Este campo es obligatorio.',
                'department.required' => 'Este campo es obligatorio.',
                'city_id.required' => 'Este campo es obligatorio.',
                'count_person.required_if' => 'Este campo es obligatorio.',
                'recommended_name.required_if' => 'Este campo es obligatorio.',
                'not_adapts_reason.required_if' => 'En el caso de no adaptar al perfil debe escribir la razon por la cual no se adapta.',
                'which_study.required_if' => 'Este campo es obligatorio.',
                'how_long.required_if' => 'Este campo es obligatorio.',
                'work_pages.required_if' => 'Este campo es obligatorio.',
                'how_much.required_if' => 'Este campo es obligatorio.',
                'retirement_reason.required_if' => 'Este campo es obligatorio.',
            ]
        );

        $id = $request->id;

        try
        {
            DB::beginTransaction();
            $getRhInterviewOld = RHInterviews::find($id);

            $RHInterviews = RHInterviews::find($id);
            $RHInterviews->blood_type_id = $request->blood_type_id;
            $RHInterviews->document_id = $request->document_id;
            $RHInterviews->department_id = $request->department_id;
            $RHInterviews->city_id = $request->city_id;
            //personal information
            $RHInterviews->first_name = ucfirst(strtolower($request->first_name));
            $RHInterviews->middle_name = ucfirst(strtolower($request->middle_name));
            $RHInterviews->last_name = ucfirst(strtolower($request->last_name));
            $RHInterviews->second_last_name = ucfirst(strtolower($request->second_last_name));
            $RHInterviews->birth_date = $request->birth_date;
            $RHInterviews->document_number = $request->document_number;
            $RHInterviews->expiration_date = $request->expiration_date;
            $RHInterviews->email = $request->email;
            $RHInterviews->mobile_number = $request->mobile_number;
            $RHInterviews->address = $request->address;
            $RHInterviews->neighborhood = $request->neighborhood;
            $RHInterviews->lives_with = $request->lives_with;
            $RHInterviews->emergency_contact = $request->emergency_contact;
            $RHInterviews->emergency_phone = $request->emergency_phone;
            $RHInterviews->he_has_children = $request->he_has_children;
            $RHInterviews->availability = $request->availability;

            //model information
            $RHInterviews->was_model = $request->was_model;
            $RHInterviews->which_study = $request->which_study;
            $RHInterviews->how_long = $request->how_long;
            $RHInterviews->work_pages = $request->work_pages;
            $RHInterviews->how_much = $request->how_much;
            $RHInterviews->retirement_reason = $request->retirement_reason;
            $RHInterviews->save();

            $id = $request->id;
            DB::table('rh_interviewer_son')->where('rh_interview_id', $id)->delete();
            if(isset($request->son)){
                $sons = $request->son;
                foreach($sons as $son)
                {
                    if($son != '')
                    DB::table('rh_interviewer_son')->insert(
                        [
                            'rh_interview_id' => $id,
                            'name' => $son
                        ]
                    );
                }
            }

            $getRhInterviewNew = RHInterviews::find($id);

            $this->saveChangeHistory($getRhInterviewOld, $getRhInterviewNew, $id);

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }

    }

    public function updateInterviewEducation(Request $request)
    {
        $this->validate($request,
            [
                'edu_final' => 'required',
                'edu_name_inst' => 'required',
                'edu_city' => 'required',
                'edu_title' => 'required',

                'edu_type_study' => 'required_if:edu_validate,==,1',
                'edu_time_final' => 'required_if:edu_validate,==,1',
                'edu_name_inst_current' => 'required_if:edu_validate,==,1',
                'edu_schedule' => 'required_if:edu_validate,==,1',
                'edu_title' => 'required_if:edu_level,==,"carrera tecnica"|required_if:edu_level,==,"universidad"|required_if:edu_level,==,"postgrado"',
            ],
            [
                'edu_final.required_if' => 'Este campo es obligatorio.',
                'edu_name_inst.required_if' => 'Este campo es obligatorio.',
                'edu_city.required_if' => 'Este campo es obligatorio.',
                'edu_title.required_if' => 'Este campo es obligatorio.',
                'edu_type_study.required_if' => 'Este campo es obligatorio.',
                'edu_time_final.required_if' => 'Este campo es obligatorio.',
                'edu_name_inst_current.required_if' => 'Este campo es obligatorio.',
                'edu_schedule.required_if' => 'Este campo es obligatorio.',
                'edu_title.required_if' => 'Este campo es obligatorio.',
            ]
        );
        $id = $request->id;
        try
        {
            DB::beginTransaction();
            $getRhInterviewOld = RHInterviews::find($id);

            $RHInterviews = RHInterviews::find($id);
            $RHInterviews->edu_level = $request->edu_level;
            $RHInterviews->edu_final = $request->edu_final;
            $RHInterviews->edu_name_inst = $request->edu_name_inst;
            $RHInterviews->edu_city = $request->edu_city;
            $RHInterviews->edu_title = $request->edu_title;
            $RHInterviews->edu_validate = $request->edu_validate;
            $RHInterviews->edu_type_study = $request->edu_type_study;
            $RHInterviews->edu_time_final = $request->edu_time_final;
            $RHInterviews->edu_name_inst_current = $request->edu_name_inst_current;
            $RHInterviews->edu_schedule = $request->edu_schedule;
            $RHInterviews->edu_others = $request->edu_others;

            $RHInterviews->save();

            $getRhInterviewNew = RHInterviews::find($id);

            $this->saveChangeHistory($getRhInterviewOld, $getRhInterviewNew, $id);

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateInterviewWorking(Request $request)
    {

        $id = $request->id;

        $this->validate($request,
            [
                'count_person' => 'required_if:person_charge,==,1',
            ],
            [
                'count_person.required_if' => 'Este campo es obligatorio.',
            ]
        );

        try
        {
            DB::beginTransaction();
            $getRhInterviewOld = RHInterviews::find($id);

            $RHInterviews = RHInterviews::find($id);
            $RHInterviews->person_charge = $request->person_charge;
            if($request->person_charge == 1)
            {
                $RHInterviews->count_person = $request->count_person;
            }
            $RHInterviews->unemployment_time = $request->unemployment_time;
            $RHInterviews->developed_activities = $request->developed_activities;
            $RHInterviews->save();

            $works = $request->works;
            DB::table('rh_working_info')->where('rh_interview_id', $id)->delete();
            foreach($works as $key => $work)
            {
                if(($work['name_bussines'] != '') or ($work['time_worked'] != '') or ($work['position'] != '') or ($work['reason_withdrawal'] != '') )
                {
                    DB::table('rh_working_info')->insert(
                        [
                            'rh_interview_id' => $id,
                            'name_bussines' => $work['name_bussines'] ,
                            'time_worked' => $work['time_worked'],
                            'position' => $work['position'],
                            'reason_withdrawal' => $work['reason_withdrawal'],
                        ]
                    );
                }
            }

            $getRhInterviewNew = RHInterviews::find($id);

            $this->saveChangeHistory($getRhInterviewOld, $getRhInterviewNew, $id);

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateInterviewAdditional(Request $request)
    {
        $id = $request->id;

        $this->validate($request,
            [
                'recommended_name' => 'required_if:meet_us,==,"recomendado"',
                'wage_aspiration' => 'required',
                'not_adapts_reason' => 'required_if:it_adapts,==,0'
            ],
            [
                'recommended_name.required_if' => 'Este campo es obligatorio.',
                'wage_aspiration.required' => 'Este campo es obligatorio.',
                'not_adapts_reason.required_if' => 'Este campo es obligatorio.',
            ]
        );

        try
        {
            DB::beginTransaction();
            $getRhInterviewOld = RHInterviews::find($id);

            $RHInterviews = RHInterviews::find($request->id);
            $RHInterviews->know_business = $request->know_business;
            $RHInterviews->meet_us = $request->meet_us;
            $RHInterviews->recommended_name = $request->recommended_name;
            $RHInterviews->strengths = $request->strengths;
            $RHInterviews->personality = $request->personality;
            $RHInterviews->visualize = $request->visualize;
            $RHInterviews->health_state = $request->health_state;
            $RHInterviews->wage_aspiration = $request->wage_aspiration;
            $RHInterviews->observations = $request->observations;
            $RHInterviews->it_adapts = $request->it_adapts;

            if($request->it_adapts == '0')
            {
                $RHInterviews->not_adapts_reason = $request->not_adapts_reason;
            }
            else
            {
                $RHInterviews->not_adapts_reason = "";
            }

            $RHInterviews->save();

            $getRhInterviewNew = RHInterviews::find($id);

            $this->saveChangeHistory($getRhInterviewOld, $getRhInterviewNew, $id);

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }

    }

    public function deleteInterview(Request $request)
    {
        $row = $request->input();
        $id = $row['id'];
        $role = RHInterviews::where('id',$id)->select('setting_role_id')->first();
        $role = $role['setting_role_id'];

        DB::table('rh_working_info')->where('rh_interview_id',$id)->delete();
        DB::table('rh_interviewer_son')->where('rh_interview_id',$id)->delete();

        $modelImg = DB::table('rh_interviewer_img')->where('rh_interview_id',$id)->get();
        $modelImg = json_decode($modelImg);
        foreach($modelImg as $img)
        {
            if($img->face != '')
                $this->deleteFile($img->face, 'rh/model_img');

            if($img->front != '')
                $this->deleteFile($img->front, 'rh/model_img');

            if($img->side != '')
                $this->deleteFile($img->side, 'rh/model_img');

            if($img->back != '')
                $this->deleteFile($img->back, 'rh/model_img');
        }


        DB::table('rh_interviewer_img')->where('rh_interview_id',$id)->delete();

        $success = RHInterviews::where('id',$id)->delete();

        return response()->json(['success' => $success]);
    }

    //->Other interview
    public function listInterviewOther()
    {
        $SettingLocation = SettingLocation::orderBy('id','asc')->get();
        $GlobalTypeContract = GlobalTypeContract::orderBy('id','asc')->get();

        $alarm = RHAlarm::where('user_id', Auth::user()->id)->update(['rha_interviews' => 0]);

    	return view('adminModules.rh.interview.other.list', compact('SettingLocation','GlobalTypeContract'));
    }

    public function getInterviewsOther_old(Request $request)
    {
        $data = RHInterviews::where('setting_role_id','<>',14)->where('is_user',0)->orderBy('created_at', 'desc')->get();
        $result = [];

        foreach ($data as $dat)
        {
            $full_name = $dat->first_name." ".$dat->last_name;
            $full_name = "<div>
                            <span>" . $this->accents($full_name) . "</span>
                          </div>
                          <div class='small text-muted'>
                            <span>$dat->email</span> | $dat->mobile_number
                          </div>";

            $id = $dat->id;
            $last_name = $dat->last_name;
            $email = $dat->email;
            $mobile_number = $dat->mobile_number;
            $role = $dat->RHInterviewToRole->name;
            $created_at = Carbon::parse($dat->created_at)
                            ->locale('es')->isoFormat('LL');
            $it_adapts = $dat->it_adapts;
            $action = "";
            if(!is_null($dat->cite))
            {
                if($dat->cite == 0)
                {
                    $it_adapts ="<img style='height: 29px;' src='/images/svg/no_cite.svg'>";
                    $action = "<img style='height: 29px; margin-right: 3px;' src='" . asset("images/dislike.png") . "'>";
                }
                else
                {
                    $it_adapts ="<img style='height: 29px;' src='" . asset("images/like.png") . "'>";
                    if (Auth::user()->can('human-resource-prospect-call')) {
                        $action = "<button type='button' class='btn btn-sm btn-success ml-2' style='margin-right: 3px;' onclick='ModalCite($id)'>
<i class='fas fa-bolt'></i></button>";
                    }
                }

            }
            else
            {
                if($dat->it_adapts == 1)
                {
                    $it_adapts ="<img style='height: 29px;' src='" . asset("images/like.png") . "'>";
                    if (Auth::user()->can('human-resource-prospect-call'))
                    {
                        $action = "<button type='button' class='btn btn-sm btn-info ml-2' style='margin-right: 3px;' onclick='ModalCite($id)'>
<i class='fas fa-bolt'></i></button>";
                    }
                }
                else
                {
                    $it_adapts ="<img style='height: 29px;' src='" . asset("images/dislike.png") . "'>";
                    /*$action = "<button type='button' class='btn btn-sm btn-dark ml-2' style='margin-right: 3px;' disabled>
<i class='fas fa-bolt'></i></button>";*/

                }
            }

            if (Auth::user()->can('human-resources-prospect-view'))
                $action = $action."<a type='button' class='btn btn-sm btn-info ml-2' href='".route('rh.interview.others.edit',$id)."'><i class='fas fa-eye'></i></a>";
            if (Auth::user()->can('human-resources-prospect-edit'))
                $action = $action."<button type='button' class='btn btn-sm btn-success ml-2' onclick='convertUserModal($id)'><i class='fas fa-plus'></i></button>";
            if (Auth::user()->can('human-resources-prospect-delete'))
                $action = $action."<button type='button' class='btn btn-sm btn-danger ml-2' onclick='deleteInterview($id)'><i class='fas fa-trash-alt'></i></button>";


            $result[] = [
                "first_name" => $full_name ,
                "role" => $role,
                "created_at" => $created_at,
                "adapts" => $it_adapts,
                "action" => $action,
            ];

        }

        return datatables($result)
        ->rawColumns(['first_name', 'last_name', 'email', 'mobile_number', 'role', 'created_at', 'adapts', 'action'])
        ->toJson();
    }

    public function getInterviewsOther(Request $request)
    {
        $search = $request->search['value'];

        if ($search != null)
        {
            $query = DB
                ::table('rh_interviews AS in')
                ->select(
                    'in.*'
                )
                ->orWhere('in.first_name', 'LIKE', "%$search%")->where('in.setting_role_id','!=', 14)->where('in.is_user', 0)
                ->orWhere('in.last_name', 'LIKE', "%$search%")->where('in.setting_role_id', '!=',14)->where('in.is_user', 0)
                ->orWhere('in.email', 'LIKE', "%$search%")->where('in.setting_role_id', '!=',14)->where('in.is_user', 0)
                ->orWhere('in.mobile_number', 'LIKE', "%$search%")->where('in.setting_role_id', '!=',14)->where('in.is_user', 0)
                ->orWhere(DB::raw("CONCAT_WS(' ', in.first_name, in.last_name)"), 'LIKE', "%$search%")->where('in.setting_role_id', '!=',14)->where('in.is_user', 0);

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }
        else
        {
            $query = DB
                ::table('rh_interviews AS in')
                ->select(
                    'in.*'
                )
                ->where('in.setting_role_id', '!=',14)
                ->where('in.is_user', 0)
                ->orderBy('in.created_at', 'desc');

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }

        $data = [];

        foreach ($query as $key => $row)
        {
            $id = $row->id;
            $last_name = $row->last_name;
            $email = $row->email;
            $mobile_number = $row->mobile_number;
            $role = SettingRole::find($row->setting_role_id)->name;
            $created_at = Carbon::parse($row->created_at)->locale('es')->isoFormat('LL');
            $it_adapts = $row->it_adapts;

            $full_name = "<div>
                              <span>" . $this->accents($row->first_name . " " . $row->last_name) . "</span>
                          </div>
                          <div class='small text-muted'>
                              <span>$row->email</span> | $row->mobile_number
                          </div>";

            $action = "";

            if (!is_null($row->cite)) {
                if ($row->cite == 0) {
                    $it_adapts = "<img style='height: 29px;' src='/images/svg/no_cite.svg'>";
                    $action = "<img style='height: 29px; margin-right: 3px;' src='" . asset("images/dislike.png") . "'>";
                } else {
                    $it_adapts = "<img style='height: 29px;' src='" . asset("images/like.png") . "'>";
                    if (Auth::user()->can('human-resource-prospect-call')) {
                        $action = "<button type='button' class='btn btn-sm btn-success ml-2' style='margin-right: 3px;' onclick='ModalCite($id)'>
                                        <i class='fas fa-bolt'></i>
                                    </button>";
                    }
                }
            } else {
                if ($row->it_adapts == 1) {
                    $it_adapts = "<img style='height: 29px;' src='" . asset("images/like.png") . "'>";

                    if (Auth::user()->can('human-resource-prospect-call')) {
                        $action = "<button type='button' class='btn btn-sm btn-info ml-2' style='margin-right: 3px;' onclick='ModalCite($id)'>
                                        <i class='fas fa-bolt'></i>
                                   </button>";
                    }
                } else {
                    $it_adapts = "<img style='height: 29px;' src='" . asset("images/dislike.png") . "'>";
                }
            }

            if (Auth::user()->can('human-resources-prospect-view')) {
                $action = $action . "<a type='button' class='btn btn-sm btn-info ml-2' href='" . route('rh.interview.others.edit', $id) . "'><i class='fas fa-eye'></i></a>";
            }

            if (Auth::user()->can('human-resources-prospect-edit')) {
                $action = $action . "<button type='button' class='btn btn-sm btn-success ml-2' onclick='convertUserModal($id)'><i class='fas fa-plus'></i></button>";
            }

            if (Auth::user()->can('human-resources-prospect-delete')) {
                $action = $action . "<button type='button' class='btn btn-sm btn-danger ml-2' onclick='deleteInterview($id)'><i class='fas fa-trash-alt'></i></button>";
            }

            $data[] = [
                "first_name" => $full_name,
                "role" => $role,
                "created_at" => $created_at,
                "adapts" => $it_adapts,
                "action" => $action,
            ];
        }

        return DataTables::of($query)
            ->with([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => $data,
            ])
            ->make(true);
    }

    public function editOtherInterview(Request $request)
    {
        $id = $request->id;
        $rh_interview_user = RHInterviews::find($id);
        $blood_types = DB::table('global_blood_types')->select('id', 'name')->orderBy('name', 'asc')->get();
        $document_types = DB::table('global_documents')->select('id', 'name')->orderBy('id', 'asc')->get();
        $department_list = DB::table('global_departments')->select('id', 'name')->orderBy('name', 'asc')->get();
        $city_list = $rh_interview_user->RHInterviewToCity->CityToDepartment->DepartmentToCities;

        return view('adminModules.rh.interview.other.edit',compact('rh_interview_user','blood_types','document_types','department_list','city_list'));
    }

    //->Model interview
    public function listInterviewModel()
    {
        $SettingLocation = SettingLocation::orderBy('id','asc')->get();
        $GlobalTypeContract = GlobalTypeContract::orderBy('id','asc')->get();

        $alarm = RHAlarm::where('user_id', Auth::user()->id)->update(['rha_interviews' => 0]);

    	return view('adminModules.rh.interview.model.list', compact('SettingLocation','GlobalTypeContract'));
    }

    public function getInterviewsModel_old(Request $request)
    {
        $data = RHInterviews::where('setting_role_id',14)->where('is_user',0)->orderBy('created_at', 'desc')->get();
        $result = [];

        foreach ($data as $dat)
        {
            $first_name = $dat->first_name;

            $full_name = $dat->first_name." ".$dat->last_name;
            $full_name = "<div>
                            <span>" . $this->accents($full_name) . "</span>
                          </div>
                          <div class='small text-muted'>
                            <span>$dat->email</span> | $dat->mobile_number
                          </div>";
            $id = $dat->id;
            $created_at = Carbon::parse($dat->created_at)
                            ->locale('es')->isoFormat('LL');
            $action = "";
            if (Auth::user()->can('human-resource-prospect-call')) {
                $action = "<button type='button' class='btn btn-sm btn-info' data-toggle='tooltip' data-placement='top' title='Citar'style='margin-right: 3px;' onclick='ModalCite($id)'><i class='fas fa-bolt'></i></button>";
            }

            if(!is_null($dat->cite)){
                if($dat->cite == 0)
                {
                    $action = "<img style='height: 29px;' src='" . asset("images/dislike.png") . "'>";
                }
                if($dat->cite == 1)
                {
                    if (Auth::user()->can('human-resource-prospect-call')) {
                        $action = "<button type='button' class='btn btn-sm btn-success' data-toggle='tooltip' data-placement='top' title='Citar a la modelo'style='margin-right: 3px;' onclick='ModalCite($id)'><i class='fas fa-bolt'></i></button>";
                    }
                }
            }

            $have_images = false;

            $images = RHInterviewImg::where('rh_interview_id', $id)->first();

            if(!is_null($images) && (!is_null($images->face) || !empty($images->face))) {
                $have_images = true;
            }

            //dump(DB::table('rh_interviewer_img')->where('rh_interview_id',$id)->exists());
            if(!$have_images)
            {
                $action = "<img style='height: 29px;' class='pulsing-active mr-1' title='No tiene fotos registradas' src='" . asset('/images/svg/no-photo.svg') . "'>";
            }

            if (Auth::user()->can('human-resources-model-img'))
                $action = $action."<button type='button' class='btn btn-sm btn-warning ml-2' data-toggle='tooltip' data-placement='top' title='Agrega las imagenes de prospecto modelo.'style='margin-right: 3px;' onclick='ManageIMG($id)'><i class='fas fa-images'></i></button>";
            if (Auth::user()->can('human-resources-prospect-view'))
                $action = $action."<a type='button' class='btn btn-sm btn-info ml-2' href='".route('rh.interview.model.edit',$id)."' data-toggle='tooltip' data-placement='top' title='Visualiza y modifica datos del prospecto.'style='margin-right: 3px;'><i class='fas fa-eye'></i></a>";
            if (Auth::user()->can('human-resources-prospect-edit'))
                $action = $action."<button type='button' class='btn btn-sm btn-success ml-2' data-toggle='tooltip' data-placement='top' title='Convierte el prospecto a usuario.'style='margin-right: 3px;' onclick='convertUserModal($id)'><i class='fas fa-plus'></i></button>";
            if (Auth::user()->can('human-resources-prospect-delete'))
                $action = $action."<button type='button' class='btn btn-sm btn-danger ml-2' data-toggle='tooltip' data-placement='top' title='Elimina el prospecto creado.'style='margin-right: 3px;' onclick='deleteInterview($id)'><i class='fas fa-trash-alt'></i></button>";

            $refer = '';

            if(tenant('id') == 1 && $dat->cite == 0) {
                $refer = "<input type='checkbox' class='referred form-check-input' id='checkbox-" . $id . "' onclick='referModelProspect($id)' " . ($dat->referred ? 'checked' : '') . "><span class='fa fa-pulse fa-spinner' id='loader-" . $id . "' style='display: none'></span>";
            }

            $result[] = [
                "first_name" => $full_name,
                "created_at" => $created_at,
                "action" => $action,
                "refer" => $refer,
            ];

        }

        return datatables($result)
        ->rawColumns(['first_name', 'last_name', 'email', 'mobile_number', 'created_at','action','refer'])
        ->toJson();
    }

    public function getInterviewsModel(Request $request)
    {
        $search = $request->search['value'];

        if ($search != null)
        {
            $query = DB
                ::table('rh_interviews AS in')
                ->select(
                    'in.*'
                )
                ->orWhere('in.first_name', 'LIKE', "%$search%")->where('in.setting_role_id', 14)->where('in.is_user', 0)
                ->orWhere('in.last_name', 'LIKE', "%$search%")->where('in.setting_role_id', 14)->where('in.is_user', 0)
                ->orWhere('in.email', 'LIKE', "%$search%")->where('in.setting_role_id', 14)->where('in.is_user', 0)
                ->orWhere('in.mobile_number', 'LIKE', "%$search%")->where('in.setting_role_id', 14)->where('in.is_user', 0)
                ->orWhere(DB::raw("CONCAT_WS(' ', in.first_name, in.last_name)"), 'LIKE', "%$search%")->where('in.setting_role_id', 14)->where('in.is_user', 0);

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }
        else
        {
            $query = DB
                ::table('rh_interviews AS in')
                ->select(
                    'in.*'
                )
                ->where('in.setting_role_id', 14)
                ->where('in.is_user', 0)
                ->orderBy('in.created_at', 'desc');

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }

        $data = [];

        foreach ($query as $key => $row)
        {
            $id = $row->id;

            $full_name = "<div>
                              <span>" . $this->accents($row->first_name . " " . $row->last_name) . "</span>
                          </div>
                          <div class='small text-muted'>
                              <span>$row->email</span> | $row->mobile_number
                          </div>";

            $created_at = Carbon::parse($row->created_at)->locale('es')->isoFormat('LL');
            $action = "";

            if (Auth::user()->can('human-resource-prospect-call')) {
                $action = "<button type='button' class='btn btn-sm btn-info' data-toggle='tooltip' data-placement='top' title='Citar'style='margin-right: 3px;' onclick='ModalCite($id)'><i class='fas fa-bolt'></i></button>";
            }

            if (!is_null($row->cite)) {
                if ($row->cite == 0) {
                    $action = "<img style='height: 29px;' src='" . asset("images/dislike.png") . "'>";
                }
                if ($row->cite == 1) {
                    if (Auth::user()->can('human-resource-prospect-call')) {
                        $action = "<button type='button' class='btn btn-sm btn-success' data-toggle='tooltip' data-placement='top' title='Citar a la modelo'style='margin-right: 3px;' onclick='ModalCite($id)'><i class='fas fa-bolt'></i></button>";
                    }
                }
            }

            $have_images = false;

            $images = RHInterviewImg::where('rh_interview_id', $id)->first();

            if (!is_null($images) && (!is_null($images->face) || !empty($images->face))) {
                $have_images = true;
            }

            if (!$have_images) {
                $action = "<img style='height: 29px;' class='pulsing-active mr-1' title='No tiene fotos registradas' src='" . asset('/images/svg/no-photo.svg') . "'>";
            }

            if (Auth::user()->can('human-resources-model-img'))
            {
                $action = $action . "<button type='button' class='btn btn-sm btn-warning ml-2' data-toggle='tooltip' data-placement='top' title='Agrega las imagenes de prospecto modelo.'style='margin-right: 3px;' onclick='ManageIMG($id)'><i class='fas fa-images'></i></button>";
            }

            if (Auth::user()->can('human-resources-prospect-view'))
            {
                $action = $action . "<a type='button' class='btn btn-sm btn-info ml-2' href='" . route('rh.interview.model.edit', $id) . "' data-toggle='tooltip' data-placement='top' title='Visualiza y modifica datos del prospecto.'style='margin-right: 3px;'><i class='fas fa-eye'></i></a>";
            }

            if (Auth::user()->can('human-resources-prospect-edit'))
            {
                $action = $action . "<button type='button' class='btn btn-sm btn-success ml-2' data-toggle='tooltip' data-placement='top' title='Convierte el prospecto a usuario.'style='margin-right: 3px;' onclick='convertUserModal($id)'><i class='fas fa-plus'></i></button>";
            }

            if (Auth::user()->can('human-resources-prospect-delete'))
            {
                $action = $action . "<button type='button' class='btn btn-sm btn-danger ml-2' data-toggle='tooltip' data-placement='top' title='Elimina el prospecto creado.'style='margin-right: 3px;' onclick='deleteInterview($id)'><i class='fas fa-trash-alt'></i></button>";
            }

            $refer = '';

            if (tenant('id') == 1 && $row->cite == 0) {
                $refer = "<input type='checkbox' class='referred form-check-input' id='checkbox-" . $id . "' onclick='referModelProspect($id)' " . ($row->referred ? 'checked' : '') . "><span class='fa fa-pulse fa-spinner' id='loader-" . $id . "' style='display: none'></span>";
            }

            $data[] = [
                'first_name' => $full_name,
                'created_at' => $created_at,
                'action'     => $action,
                'refer'      => $refer,
            ];
        }

        return DataTables::of($query)
            ->with([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => $data,
            ])
            ->make(true);
    }

    public function referModelProspect(Request $request)
    {
        $model_prospect = RHInterviews::find($request->id);
        $status = $request->status == 'true' ? true : false;
        $model_prospect->referred = $status;
        $model_prospect->save();
        $data = [];

        if ($status) {
            $request = new Request([
                'prospect_id'       => $request->id,
                'first_name'        => $model_prospect->first_name,
                'middle_name'       => $model_prospect->middle_name,
                'last_name'         => $model_prospect->last_name,
                'second_last_name'  => $model_prospect->second_last_name,
                'phone'             => $model_prospect->mobile_number,
                'email'             => $model_prospect->email,
                'department_id'     => $model_prospect->department_id,
                'city_id'           => $model_prospect->city_id,
                'created_by'        => $model_prospect->created_by,
                'studio_creator_id' => $model_prospect->studio_creator_id,
            ]);

            $created = $this->createReferredModel($request);

            $data = $created->original;

            if($data['success']) {
                $request = new Request([
                    'id'     => $data['id'],
                    'status' => $status,
                ]);

                $this->referModel($request);
            }
        } else {
            $referred_model = ReferredModel::select('id')->where('model_prospect_id', $request->id)->first();

            $request = new Request([
                'id' => $referred_model->id,
            ]);

            $deleted = $this->deleteReferredModel($request);

            $data['success'] = $deleted;
        }

        return response()->json(['success' => $data['success']]);
    }

    public function editModelInterview(Request $request)
    {
        $id = $request->id;
        $rh_interview_model = RHInterviews::find($id);
        $blood_types        = DB::table('global_blood_types')->select('id', 'name')->orderBy('name', 'asc')->get();
        $document_types     = DB::table('global_documents')->select('id', 'name')->orderBy('id', 'asc')->get();
        $department_list    = DB::table('global_departments')->select('id', 'name')->orderBy('name', 'asc')->get();
        $city_list = $rh_interview_model->RHInterviewToCity->CityToDepartment->DepartmentToCities;

        return view('adminModules.rh.interview.model.edit',compact('rh_interview_model','blood_types','document_types','department_list','city_list'));
    }

    //->Model->IMG
    public function getInterviewModelImg(Request $request)
    {
        $id = $request->id;
        $data = RHInterviewImg::where('rh_interview_id',$id)->first();



        $full_name  = $data->ImgToInterview->InterviewUserShortName();
        $face       = $data->face;
        $front      = $data->front;
        $side       = $data->side;
        $back       = $data->back;

        return response()->json(['success' => true,
                                 'id' => $id,
                                 'full_name' => $full_name,
                                 'face' => $face,
                                 'front' => $front,
                                 'side' => $side,
                                 'back' => $back
                                 ]);
    }

    public function getInterviewModelExistImg(Request $request)
    {
        $id = $request->id;

        $exist = false;
        if(DB::table('rh_interviewer_img')->where('rh_interview_id', $id)->exists())
            $exist = true;

        return response()->json(['success' => true, 'exist' => $exist]);
    }

    public function createInterviewModelIMG(Request $request)
    {
        $this->validate($request,
        [
            'face' => 'required',
            'front' => 'required',
            'side' => 'required',
            'back' => 'required',
        ],
        [
            'face.required' => 'Este campo es obligatorio.',
            'front.required' => 'Este campo es obligatorio.',
            'side.required' => 'Este campo es obligatorio.',
            'back.required' => 'Este campo es obligatorio.',
        ]
        );

        $id = $request->id;

        $interviewsIMG = new RHInterviewImg();

        $interviewsIMG->rh_interview_id = $request->id;

        if($request->file('face')){
            $face = $request->file('face');
            $interviewsIMG->face = $this->uploadFile($face, 'rh/model_img');
        }

        if($request->file('front')){
            $front = $request->file('front');
            $interviewsIMG->front = $this->uploadFile($front, 'rh/model_img');
        }

        if($request->file('side')){
            $side = $request->file('side');
            $interviewsIMG->side = $this->uploadFile($side, 'rh/model_img');
        }

        if($request->file('back')){
            $back = $request->file('back');
            $interviewsIMG->back = $this->uploadFile($back, 'rh/model_img');
        }

        $success = $interviewsIMG->save();

        return response()->json(['success' => $success]);
    }

    public function updateInterviewModelIMG(Request $request)
    {
        $id_interview = $request->id_model;

        try
        {
            DB::beginTransaction();
            $interviewsIMG = RHInterviewImg::where('rh_interview_id', $id_interview)->first();

            if($request->file('face')){
                $face = $request->file('face');
                $interviewsIMG->face = $this->uploadFile($face, 'rh/model_img');
            }

            if($request->file('front')){
                $front = $request->file('front');
                $interviewsIMG->front = $this->uploadFile($front, 'rh/model_img');
            }

            if($request->file('side')){
                $side = $request->file('side');
                $interviewsIMG->side = $this->uploadFile($side, 'rh/model_img');
            }

            if($request->file('back')){
                $back = $request->file('back');
                $interviewsIMG->back = $this->uploadFile($back, 'rh/model_img');
            }

            $interviewsIMG->save();

		    DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e)
        {
            DB::rollback();
            return response()->json(['success' => false]);
        }


    }

    public function getInterview(Request $request)
    {
        $data = RHInterviews::select('id',DB::raw('CONCAT(first_name," ",last_name) as full_name'))->firstWhere('id',$request->id);
        $id = $data->id;
        $full_name = $data->full_name;

        return response()->json(['success' => true,
                                 'id' => $id,
                                 'full_name' => $full_name
                                 ]);

    }

    //->Otrher interview->Function
    public function getInterviewHistory(Request $request)
    {
       $id = $request->id;
       $data =  RhInterviewHistory::where('rh_interview_id',$id)->orderBy('created_at', 'desc')->get();
       $result = [];
       foreach ($data as $dat)
       {
           $user = $dat->RhHistoryToUser->userFullName();
           $field = $dat->field;
           $old = $dat->previous_value;
           $new = $dat->new_value;
           $date = $dat->new_value;
           $created_at = Carbon::parse($dat->created_at)
           ->locale('es')->isoFormat('LL');

           $result[] = [
                "user" => $user,
                "field" => $field,
                "old" => $old,
                "new" => $new,
                "date" => $created_at,
            ];

       }

       return datatables($result)
        ->rawColumns(['user', 'field', 'email', 'old', 'new', 'date'])
        ->toJson();
    }

    public function getInterviewID(Request $request)
    {
        $id = $request->id;
        $RhInterview = RHInterviews::select('first_name','middle_name','last_name','second_last_name','setting_role_id')->where('id', $id)->first();
        $full_name = $RhInterview->first_name." ".$RhInterview->last_name;
        $setting_role_id = $RhInterview->setting_role_id;

        return response()->json(['success' => true, 'full_name' => $full_name, 'setting_role_id' => $setting_role_id]);
    }

    public function actionInterviewToUser(Request $request)
    {
        $role = $request->setting_role_id;

        if($role == 14)
        {
            $this->validate($request,
            [
                'setting_location_id' => 'required',
                'contract_id'         => 'required',
                'password'            => 'required',
                'nick'                => 'required|unique:users',
                'email'               => 'required|unique:users|email',
            ],
            [
                'setting_location_id.required'  => 'Debe seleccionar una locación.',
                'contract_id.required'          => 'Debe seleccionar un tipo de contrato.',
                'password.required'             => 'Este campo de obligatorio.',
                'nick.required'                 => 'Este campo de obligatorio.',
                'nick.unique'                   => 'El nick de la modelo ya existe en la base de datos.',
                'email.required'                => 'Este campo de obligatorio.',
                'email.unique'                  => 'El email ya existe en la base de datos.',
                'email.email'                   => 'Este campo debe ser una deirección de correo electronico valida.',
            ]);
        }
        else
        {
            $this->validate($request,
            [
                'setting_location_id' => 'required',
                'contract_id'         => 'required',
                'password'            => 'required',
                'email'               => 'required|unique:users|email',
            ],
            [
                'setting_location_id.required'  => 'Debe seleccionar una locación.',
                'contract_id.required'          => 'Debe seleccionar un tipo de contrato.',
                'password.required'             => 'Este campo de obligatorio.',
                'email.required'                => 'Este campo de obligatorio.',
                'email.unique'                  => 'El email ya existe en la base de datos.',
                'email.email'                   => 'Este campo debe ser una deirección de correo electronico valida.',
            ]);
        }

        $id_interview           = $request->id_interviewer;
        $password               = bcrypt($request->password);
        $nick                   = $request->nick;
        $email                  = $request->email;
        $setting_location_id    = $request->setting_location_id;
        $contract_id            = $request->contract_id;

        try
        {
            DB::beginTransaction();

            $interview = RHInterviews::find($id_interview);

            $user = new User();
            //foreign keys
            $user->setting_location_id      = $setting_location_id ;
            $user->contract_id              = $contract_id ;
            $user->setting_role_id          = $interview->setting_role_id;
            $user->blood_type_id            = $interview->blood_type_id;
            $user->department_id            = $interview->department_id;
            $user->city_id                  = $interview->city_id;
            $user->document_id              = $interview->document_id;
            $user->bank_account_document_id = 1;

            $user->email                = $email;
            $user->password             = $password;
            $user->admission_date       = Carbon::now()->format('Y-m-d');
            $user->contract_date        = Carbon::now()->addMonth()->format('Y-m-d');
            $user->nick                 = $nick ;
            $user->first_name           = $interview->first_name;
            $user->middle_name          = $interview->middle_name;
            $user->last_name            = $interview->last_name;
            $user->second_last_name     = $interview->second_last_name;
            $user->birth_date           = $interview->birth_date;
            $user->personal_email       = $interview->email;
            $user->mobile_number        = $interview->mobile_number;
            $user->address              = $interview->address;
            $user->neighborhood         = $interview->neighborhood;
            $user->document_number      = $interview->document_number;
            $user->expiration_date      = $interview->expiration_date;
            $user->emergency_contact    = $interview->emergency_contact;
            $user->emergency_phone      = $interview->emergency_phone;
            $user->hangouts_password    = $request->password;
            $user->unique_code          = $this->traitSearchCodigoUnicoUser();
            $user->email_verified_at    = Carbon::now();
            //personal information
            $user->status  = true;
            $user->admission_date       = Carbon::now()->format('Y-m-d');
            $ok = $user->save();

            if ($ok) {
                $new_setting_role = SettingRole::where('id', $user->setting_role_id)->first();
                $user->assignRole($new_setting_role->name);
            }

            //update rh_interviews
            $interview->is_user = 1;
            $interview->user_id = $user->id;
            $interview->save();

            if($user->setting_role_id == 14)
            {
                $satellite = new SatelliteOwner();
                $satellite->user_id             = $user->id;
                $satellite->department_id       = $user->department_id;
                $satellite->city_id             = $user->city_id;
                $satellite->payment_method      = 3;
                $satellite->owner               = $user->nick ;
                $satellite->first_name          = $user->first_name ;
                $satellite->second_name         = $user->middle_name;
                $satellite->last_name           = $user->last_name;
                $satellite->second_last_name    = $user->second_last_name;
                $satellite->document_number     = $user->document_number;
                $satellite->email               = $user->email;
                $satellite->others_emails       = $user->personal_email;
                $satellite->phone               = $user->mobile_number;
                $satellite->address             = $user->address;
                $satellite->neighborhood        = $user->neighborhood;
                $satellite->is_user             = 1;
                $satellite->commission_percent  = 50;
                $satellite->save();

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

            DB::commit();

            return response()->json(['success' => true]);
        }
        catch (Exception $e)
        {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateCite(Request $request)
    {
        $row = $request->input();
        $id = $row['id'];
        $cite = $row['cite'];

        $RHInterviews = RHInterviews::find($id);
        $RHInterviews->cite = $cite;
        $success = $RHInterviews->save();

        if($cite == 1)
        {
            $role = $RHInterviews->RHInterviewToRole->name;

            $task_controller = new TaskController();

            $task = new Task();
            $task->created_by_type = 1; // User
            $task->created_by = Auth::user()->id;
            $task->title = "Nuevo/a $role: $RHInterviews->first_name $RHInterviews->last_name";
            $task->status = 0;
            $task->should_finish = Carbon::now()->addDay();
            $task->terminated_by = 0;
            $task->code = $task_controller->generateCode();
            $created = $task->save();

            //Gerente, Asistente Administrativa, Recursos Humanos, Recursos Humanos Operativo, Auxiliar Nómina, Psicologa, Administradora
            $receivers = [
                'to_roles' => [
                    ['id' => 1, 'name' => 'Gerente'],
                    ['id' => 3, 'name' => 'Administrador/a'],
                    ['id' => 2, 'name' => 'Asistente Administrativo'],
                    ['id' => 7, 'name' => 'Recursos Humanos'],
                    ['id' => 35, 'name' => 'Recursos Humanos Operativo'],
                    ['id' => 40, 'name' => 'Auxiliar Nomina'],
                    ['id' => 36, 'name' => 'Psicólogo/a'],
                ],
                'to_users' => [],
                'to_models' => [],
            ];

            $request_object = new Request(['receivers' => json_encode($receivers), 'task_id' => $task->id, 'from_add_receivers' => 0]);
            $task_controller->addReceivers($request_object);

            if ($created) {

                if ($RHInterviews->setting_role_id == 14) // model
                {
                    $task_comment = new TaskComment();
                    $task_comment->task_id = $task->id;
                    $task_comment->user_id = Auth::user()->id;
                    $task_comment->comment = "Citar a la Modelo que asistió a entrevista $RHInterviews->first_name $RHInterviews->last_name para iniciar proceso de documentación y fotografía";
                    $task_comment->save();

                    $file = null;

                    if(!is_null($RHInterviews->RHInterviewToImg->face))
                    {
                        $file = $RHInterviews->RHInterviewToImg->face;
                    }
                    elseif (!is_null($RHInterviews->RHInterviewToImg->front))
                    {
                        $file = $RHInterviews->RHInterviewToImg->front;
                    }
                    elseif (!is_null($RHInterviews->RHInterviewToImg->side))
                    {
                        $file = $RHInterviews->RHInterviewToImg->side;
                    }
                    elseif (!is_null($RHInterviews->RHInterviewToImg->back))
                    {
                        $file = $RHInterviews->RHInterviewToImg->back;
                    }

                    if(!is_null($file)) {
                        $copy = @\File::copy(base_path("storage/app/public/" . tenant('studio_slug') . "/rh//model_img/" . $file), base_path("storage/app/public/" . tenant('studio_slug') . "/task/" . $file));

                        $task_comment_attachment = new TaskCommentAttachment();
                        $task_comment_attachment->task_comments_id = $task_comment->id;
                        $task_comment_attachment->file = $file;
                        $task_comment_attachment->save();
                    }
                }
                else
                {
                    $task_comment = new TaskComment();
                    $task_comment->task_id = $task->id;
                    $task_comment->user_id = Auth::user()->id;
                    $task_comment->comment = "Citar al $role que asistió a entrevista $RHInterviews->first_name $RHInterviews->last_name para iniciar proceso de contratación y labores.";
                    $task_comment->save();
                }
            }

        }

        return response()->json(['success' => $success]);
    }

    //##VACATION REQUEST---------------------------------------------------------------------------------------------------
    public function listVacationRequest()
    {
        $start_date = date("d-m-Y", strtotime("+3 week"));
        $end_date = strtotime("+1 week", strtotime($start_date));
        $end_date = date("d-m-Y", $end_date);

        //Lista todos los usuarios alfabeticamente
        $query = DB::raw("(CASE WHEN setting_role_id <> 14 THEN CONCAT(first_name,' ',last_name) ELSE nick END) as name");

        $users = User::select('id',$query)
                ->where('status',1)
                ->orderBy('name', 'ASC')
                ->get();

        $alarm = RHAlarm::where('user_id', Auth::user()->id)->update(['rha_sol_vac' => 0]);

        return view('adminModules.rh.VacationRequest.list',compact('end_date','start_date','users'));
    }

    public function getVacationRequest(Request $request)
    {
        $user_id = $request->input();
        $user_id = $user_id['user_id'];

        if($user_id == 0):

            $a = RHVacationRequest::where('rh_vacation_status_id',1)
                ->orderBy('start_date','desc')
                ->get();

            $b = RHVacationRequest::where('rh_vacation_status_id','<>',1)
                ->orderBy('start_date','desc')
                ->get();

        else:

            $a = RHVacationRequest::where('rh_vacation_status_id',1)
                ->where('user_id',$user_id)
                ->orderBy('start_date','desc')
                ->get();

            $b = RHVacationRequest::where('rh_vacation_status_id','<>',1)
                ->where('user_id',$user_id)
                ->orderBy('start_date','desc')
                ->get();
        endif;

        $vacation_request = $a->merge($b);
        $result = [];

        foreach ($vacation_request as $vacation)
        {
            $id = $vacation->id;
            $name = $vacation->user_vacation->nick;

            if($vacation->user_vacation->setting_role_id != 14)
                $name = $vacation->user_vacation->first_name.' '.$vacation->user_vacation->last_name;


            $start_date = Carbon::parse($vacation->start_date)
                          ->locale('es')->isoFormat('LL');

            $end_date = Carbon::parse($vacation->end_date)
                        ->locale('es')->isoFormat('LL');

            $days = Carbon::parse($vacation->start_date)
                    ->diffInDays(Carbon::parse($vacation->end_date));

            $date = $start_date.' al '.$end_date;
            $status = $vacation->rh_vacation_status_id;
            $action = "";

            if($status == 1 ):

                $action = "<span class='badge bg-warning'>Proceso</span>";
                if(Auth::user()->can('human-resources-vacation-approve')){
                    $action = "<button type='button' class='btn btn-sm btn-success' onclick='ActionApprove($id)' data-toggle='tooltip' data-placement='top' title='Aprobar vacaciones' style='margin-right: 3px;'><i class='fas fa-check'></i></button>";
                    $action = $action."<button type='button' class='btn btn-sm btn-danger' title='Desaprobar vacaciones' onclick='ActionModalDisapproved($id)'><i class='fas fa-times'></i></button>";

                }

            endif;

            if($status == 2 ):
                $action = "<img style='height: 29px;' src='" . asset("images/like.png") . "'>";
            endif;

            if($status == 3 ):
                $user_confirm = $vacation->user_cofirm;
                $user_confirm = $user_confirm->first_name.' '.$user_confirm->last_name;
                $reason = 'El usuario '.$user_confirm.' ha rechazado la solicitud por la razon de '.$vacation->reason_deny;
                $action = "<img style='height: 29px;' src='" . asset("images/dislike.png") . "' data-toggle='tooltip' data-placement='top' title='$reason'>";
            endif;

            $result[] = [
                "name" => $name,
                "date" => $date,
                "days" => $days.' dias',
                "approve" => $action,
            ];

        }

        return datatables($result)
            ->rawColumns(['name', 'date', 'approve', 'not_approve'])
            ->toJson();
    }

    public function createVacationRequest(Request $request)
    {

        //compruba si el usuario que esta haciendo la peticion tiene por lo menos un proceso pendiente
        $count = RHVacationRequest::where('user_id',$request->input('user_id'))
        ->where('rh_vacation_status_id',1)
        ->count();

        $success = true;

        if ($count == 0):
            //crea y guarda la nueva solicitud de vacaciones
            list($start_date, $end_date) = explode(' - ',$request->input('date'));
            $RHVacationRequest = new RHVacationRequest();
            $RHVacationRequest->user_id = $request->input('user_id');
            $RHVacationRequest->start_date = date('Y-m-d', strtotime($start_date));
            $RHVacationRequest->end_date = date('Y-m-d', strtotime($end_date));
            $RHVacationRequest->rh_vacation_status_id = 1;
            $success = $RHVacationRequest->save();

            $alarm = RHAlarm::query()->update(['rha_sol_vac' => 1]);

            return response()
                ->json(['success' => $success,'message' => true ]);

        elseif ($count > 0):

            return response()
                ->json(['success' => $success,'message' => false ]);

        endif;

    }

    public function updateVacationRequest(Request $request)
    {

        $this->validate($request,
            [
                'reason_deny' => 'required_if:rh_vacation_status_id,==,3',
            ],
            [
                'reason_deny.required_if' => 'Este campo es requerido.',
            ]
        );

        $id               = $request->id;
        $status           = $request->rh_vacation_status_id;
        $user_confirm_id  = $request->user_confirm_id;

        try
        {
            DB::beginTransaction();

            //update vacation Request
            $RHVacationRequest = RHVacationRequest::find($id);
            $RHVacationRequest->user_confirm_id         = $user_confirm_id;
            $RHVacationRequest->rh_vacation_status_id   = $status;

            if ($status == 3)
                $RHVacationRequest->reason_deny = $request->reason_deny;

            $RHVacationRequest->save();

            $user_id = $RHVacationRequest->user_id;
            $user_confirm_id = $RHVacationRequest->user_confirm_id;

            $start_date = $RHVacationRequest->start_date;
            $end_date   = $RHVacationRequest->end_date;

            $role_id = $RHVacationRequest->user_vacation->setting_role_id;

            if($role_id != 14)
            {
                while($start_date <= $end_date)
                {
                    $date = $start_date;
                    $this->insertVactionUser($user_id, $user_confirm_id, $role_id, $date);
                    $date = date("Y-m-d",strtotime($date."+ 1 days"));
                    $start_date = $date;
                }
            }
            else
            {
                while($start_date <= $end_date)
                {
                    $date = $start_date;
                    $number_day = date('N', strtotime($date));
                    $name_day = NAME_TO_DAY_WEEK_OF_NUM[$number_day];

                    $day    = date("d", strtotime($date));
                    $month  = date("m", strtotime($date));
                    $year   = date("Y", strtotime($date));

                    //falta modulo de asistencia-----------------------------------

                    $date = date("Y-m-d",strtotime($date."+ 1 days"));
                    $start_date = $date;
                }

            }

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }

    }

    public function staffVacations(Request $request)
    {
        $months    = array("01"=>'Enero',"02"=>'Febrero',"03"=>'Marzo',"04"=>'Abril',"05"=>'Mayo',"06"=>'Junio',"07"=>'Julio',"08"=>'Agosto',"09"=>'Septiembre',"10"=>'Octubre',"11"=>'Noviembre',"12"=>'Diciembre');
        $inverted  = array("Enero"=>'01',"Febrero"=>'02',"Marzo"=>'03',"Abril"=>'04',"Mayo"=>'05',"Junio"=>'06',"Julio"=>'07',"Agosto"=>'08',"Septiembre"=>'09',"Octubre"=>'10',"Noviembre"=>'11',"Diciembre"=>'12');

        $day    = Carbon::now()->format('d');
        $month  = Carbon::now()->format('m');
        $year   = Carbon::now()->format('Y');
        $today  = Carbon::now()->format('Y-m-d');
        $current_rank = $months[$month]." ".$year;


        if(isset($request->role) && isset($request->rank))
        {
            $role = $request->role;

            $rank = $request->rank;
            $rank_expl = explode(" ",$rank);
            $month_rank = $rank_expl[0];
            $year_rank = $rank_expl[1];
            $month_rank = $inverted[$month_rank];
            $end_month = cal_days_in_month(CAL_GREGORIAN,$month_rank,$year_rank);
        }
        else
        {
            $role = "All";
            $rank = $months[$month]." ".$year;
            $end_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        }

        $vacationListPersonOne = $this->vacationListPersonOne($rank, $role);
        $vacationListPersonTwo = $this->vacationListPersonTwo($rank, $role);

        $diferentUserVacationOne = $this->diferentUserVacationOne($rank, $role);
        $diferentUserVacationTwo = $this->diferentUserVacationTwo($rank, $role);

        $vacation_rank = RHVacationUser::select('rank')->distinct('rank')->orderBy('rank')->get();
        $setting_roles = SettingRole::select('id','name')->where('id','<>',14)->orderBy('name','asc')->get();

        return view('adminModules.rh.VacationRequest.staffVacations',compact(
        'day',
        'today',
        'end_month',
        'current_rank',
        'rank',
        'role',
        'setting_roles',
        'vacation_rank',
        'vacationListPersonOne',
        'vacationListPersonTwo',
        'diferentUserVacationOne',
        'diferentUserVacationTwo'));
    }

    public function vacationListPersonOne($rank, $role)
    {
        if($role == "All")
        {
            $RHVacationUser = RHVacationUser::where('rank',$rank)->where('day','<=',15)->get();
        }
        else
        {
            $RHVacationUser = RHVacationUser::where('rank',$rank)->where('day','<=',15)->where('setting_role_id', $role)->get();
        }

        return $RHVacationUser;
    }

    public function vacationListPersonTwo($rank, $role)
    {
        if($role == "All")
        {
            $RHVacationUser = RHVacationUser::where('rank',$rank)->where('day','>',15)->get();
        }
        else
        {
            $RHVacationUser = RHVacationUser::where('rank',$rank)->where('day','>',15)->where('setting_role_id', $role)->get();
        }

        return $RHVacationUser;
    }

    public function diferentUserVacationOne($rank, $role)
    {
        if($role == "All")
        {
            $RHVacationUser = RHVacationUser::select('user_id')->distinct('user_id')->where('rank',$rank)->where('day','<=',15)->get();
        }
        else
        {
            $RHVacationUser = RHVacationUser::select('user_id')->distinct('user_id')->where('rank',$rank)->where('setting_role_id', $role)->where('day','<=',15)->get();
        }

        return $RHVacationUser;

    }

    public function diferentUserVacationTwo($rank, $role)
    {
        if($role == "All")
        {
            $RHVacationUser = RHVacationUser::select('user_id')->distinct('user_id')->where('rank',$rank)->where('day','>',15)->get();
        }
        else
        {
            $RHVacationUser = RHVacationUser::select('user_id')->distinct('user_id')->where('rank',$rank)->where('setting_role_id', $role)->where('day','>',15)->get();
        }

        return $RHVacationUser;
    }

    //##EXTRA HOURS---------------------------------------------------------------------------------------------------------
    public function listExtraHour()
    {
        $ranks          = RHExtraHours::select('range')->distinct('range')->get();
        $current_date   = Carbon::now()->format('Y-m-d');
        $yesterday_date = Carbon::now()->subDays(1)->format('Y-m-d');
        $id_user        = Auth::user()->id;
        $role           = Auth::user()->setting_role_id;

        /*mienstras que este listo los permisos el gerente el unico que puede listar todos las horas extras y aprobarlas */
        $user_name      = Auth::user()->roleUserFullName();
        $sub_query      = RHExtraHours::select('user_id')->distinct()->get();
        $list_user      = array();

        foreach($sub_query as $i => $query)
        {
          $list_user[$i] = array();
          $list_user[$i]['id'] = $query->user_id;
          $list_user[$i]['full_name'] = $query->RHExtraHoursToUsers->roleUserFullName();
        }
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        list($daytime_hours, $night_hours) = $this->loadUserOvertimeValues($current_date, $id_user);

        $alarm = RHAlarm::where('user_id', Auth::user()->id)->update(['rha_extra_request' => 0]);

        return view('adminModules.rh.ExtraHours.list')->with(['id_user'         => $id_user,
                                                              'user_name'       => $user_name,
                                                              'yesterday_date'  => $yesterday_date,
                                                              'current_date'    => $current_date,
                                                              'daytime_hours'   => $daytime_hours,
                                                              'ranks'           => $ranks,
                                                              'list_user'       => $list_user,
                                                              'night_hours'     => $night_hours,
                                                              'user_permission' => $user_permission]);
    }

    public function getRHExtraHourRange()
    {
        $ranks  = RHExtraHours::select('range')->distinct()->get();

        $range = [];
        foreach($ranks as $rank)
        {
            $range[] = $rank['range'];

        }

        return response()->json(['ranks' => $range]);
    }

    public function listExtraHourProcess()
    {
        return view('adminModules.rh.ExtraHours.listprocess');
    }

    public function createExtraHour(Request $request)
    {

        $this->validate($request,
        [
            'extra_reason' => 'required',
            'total_extras' => 'Integer|min:1',
        ],
        [
            'extra_reason.required'     => 'Este campo de obligatorio.',
            'total_extras.min'          => 'El total minutos debe ser mayor a 0.',
        ]);

        try
        {
            DB::beginTransaction();

            $RHExtraValue = new RHExtraHours();
            $RHExtraValue->user_id          = $request->id_user;
            $RHExtraValue->state_id         = 1;

            $RHExtraValue->extra_reason     = $request->extra_reason;

            $RHExtraValue->start_time       = $request->start_time;
            $RHExtraValue->end_time         = $request->end_time;

            $RHExtraValue->daytime_hours    = $request->daytime_hours;
            $RHExtraValue->daytime_minutes  = $request->daytime_minutes;
            $RHExtraValue->daytime_total    = $request->daytime_total;

            $RHExtraValue->night_hours      = $request->night_hours;
            $RHExtraValue->night_minutes    = $request->night_minutes;
            $RHExtraValue->night_total      = $request->night_total;

            $RHExtraValue->total_extras     = $request->total_extras;
            $RHExtraValue->total            = $request->total;

            $months = ["01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];

            $date   = Carbon::parse($request->date_form)->format('Y-m-d');
            $day    = Carbon::parse($request->date_form)->format('d');
            $month  = Carbon::parse($request->date_form)->format('m');
            $year   = Carbon::parse($request->date_form)->format('Y');

            $RHExtraValue->application_date   = $date;
            $RHExtraValue->day                = $day;
            $RHExtraValue->month              = $month;
            $RHExtraValue->year               = $year;

            if($day <= 15)
            {
                $application_day = "01-15";
            }
            else
            {
                $number = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $application_day = "16-" . $number;
            }

            $month_request = $months[$month];
            $request_range = $application_day." ".$month_request." ".$year;

            $RHExtraValue->range = $request_range;
            $RHExtraValue->save();

            // Set RH alarm
            $alarm = RHAlarm::query()->update(['rha_extra_request' => 1]);

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }


    public function getOvertimeValue(Request $request)
    {
        $date = $request->date;
        $user_id = $request->user_id;
        list($daytime_hours, $night_hours) = $this->loadUserOvertimeValues($date, $user_id);

        return response()->json(['daytime_hours' => $daytime_hours, 'night_hours' => $night_hours]);
    }

    public function loadUserOvertimeValues($current_date, $id_user)
    {
        $daytime_hours = "";
        $night_hours = "";
        //weekday
        $weekday    = Carbon::now()->format('l');
        $day        = Carbon::now()->format('Y-m-d');
        //user info
        $user_info      = User::find($id_user);
        $type_contract  = $user_info->contract_id;
        $salary         = $user_info->current_salary;
        //Extravalue Info
        $extra_value_info = RHExtraValue::find(1);

        $day_value              = $extra_value_info->day_value;
        $night_value            = $extra_value_info->night_value;

        $day_percent            = $extra_value_info->day_percent;
        $night_percent          = $extra_value_info->night_percent;

        $day_sunday_percent     = $extra_value_info->day_sunday_percent;
        $night_sunday_percent   = $extra_value_info->night_sunday_percent;

        if($type_contract == 2)
        {
            $salary = round(($salary/240),2);

            $Holliday   = new TraitHolliday();
            $isHolliday = $Holliday->isHoliday($current_date);

            if(($weekday == 'Sunday')||($isHolliday))
            {
                $daytime_hours  = round((($day_sunday_percent/100)+1),2);
                $night_hours    = round((($night_sunday_percent/100)+1),2);

                $daytime_hours  = floor($salary*$daytime_hours);
                $night_hours    = floor($salary*$night_hours);
            }
            else
            {
                $daytime_hours  = ($day_percent/100)+1;
                $night_hours    = ($night_percent/100)+1;

                $daytime_hours  = floor($salary*$daytime_hours);
                $night_hours    = floor($salary*$night_hours);
            }
        }
        else
        {
            $daytime_hours  = $day_value;
            $night_hours    = $night_value;
        }

        return array($daytime_hours, $night_hours);
    }

    public function editExtraHour()
    {
        $RHExtraValue = RHExtraValue::find(1);
        return view('adminModules.rh.ExtraHours.edit',compact('RHExtraValue'));
    }

    public function getExtraValue()
    {
        $data = RHExtraValue::all()->first();
        return response()->json($data);
    }

    public function UpdateExtraValue(Request $request)
    {
        try {
            DB::beginTransaction();

            $RHExtraValue = RHExtraValue::find(1);
            $RHExtraValue->day_value            = $request->day_value;
            $RHExtraValue->night_value          = $request->night_value;
            $RHExtraValue->day_percent          = $request->day_percent;
            $RHExtraValue->night_percent        = $request->night_percent;
            $RHExtraValue->day_sunday_percent   = $request->day_sunday_percent;
            $RHExtraValue->night_sunday_percent = $request->night_sunday_percent;
            $RHExtraValue->transportation_aid   = $request->transportation_aid;
            $RHExtraValue->save();

            $transportation_aid = ($RHExtraValue->transportation_aid)/2;

            $users = User::select('id')->where('status',1)->where('contract_id',2)->where('current_salary','<=',1755606)->get();

            list ($start_day, $end_day, $month, $year, $fortnight) = $this->getDates();

            foreach($users as $user)
            {
                $payroll_user = Payroll::where('user_id', $user->id)->where('month', $month)->where('year', $year)->first();

                if(!is_null($payroll_user))
                {
                    $worked_days = $payroll_user['worked_days'.$fortnight];
                }

            }

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }

    }

    //##OTHER FUNCTIONS---------------------------------------------------------------------------------------------------------
    public function insertVactionUser($user_id, $user_confirm_id, $role_id, $date)
    {
        $RHVacationUser = RHVacationUser::where('date',$date)->where('user_id', $user_id)->exists();
        $months = array("01" => 'Enero', "02" => 'Febrero', "03" => 'Marzo', "04" => 'Abril', "05" => 'Mayo', "06" => 'Junio', "07" => 'Julio', "08" => 'Agosto', "09" => 'Septiembre', "10" => 'Octubre', "11" => 'Noviembre', "12" => 'Diciembre');

        if($RHVacationUser == false)
        {
            $month_range = date("m", strtotime($date));
            $year_range = date("Y", strtotime($date));
            $day    = date("d", strtotime($date));
            $month  = date("m", strtotime($date));
            $year   = date("Y", strtotime($date));

            $month_range = $months[$month_range];
            $range = $month_range." ".$year_range;

            $RHVacationUser = new RHVacationUser();
            $RHVacationUser->user_confirm_id   = $user_confirm_id;
            $RHVacationUser->setting_role_id   = $role_id;
            $RHVacationUser->user_id           = $user_id;
            $RHVacationUser->rank              = $range;
            $RHVacationUser->day               = $day;
            $RHVacationUser->month             = $month;
            $RHVacationUser->year              = $year;
            $RHVacationUser->date              = $date;
            $RHVacationUser->save();
        }
    }

    public function getCities(Request $request)
    {
        $data = DB::table('global_cities')->select('id', 'name')->where('department_id',$request->department_id)->orderBy('name', 'asc')->get();
        return response()->json($data);
    }

    public function updateCiteInterview(Request $request)
    {
        $row = $request->input();
        $id = $row['id'];
        $cite = $row['cite'];

        $RHInterviews = RHInterviews::find($id);
        $RHInterviews->cite = $cite;
        $success = $RHInterviews->save();

        return response()->json(['success' => $success]);
    }

    public function saveChangeHistory($dataObjectOld, $dataObjectNew, $id)
    {
        $dataOld = $dataObjectOld->toArray();
        $dataNew = $dataObjectNew->toArray();

        $form_fields = [
            "blood_type_id"         =>"Tipo de sangre",
            "document_id"           =>"Tipo de documento",
            "city_id"               =>"Ciudad",
            "first_name"            =>"Primer nombre",
            "middle_name"           =>"Segundo nombre",
            "last_name"             =>"Primer Apellido",
            "second_last_name"      =>"Segundo Apellido",
            "birth_date"            =>"Fecha de cumpleaños",
            "document_number"       =>"Numero de documento",
            "expiration_date"       =>"Fecha de expiración",
            "email"                 =>"email",
            "mobile_number"         =>"Numero de telefono",
            "address"               =>"Dirección",
            "neighborhood"          =>"Barrio",
            "lives_with"            =>"Vive con",
            "emergency_contact"     =>"Contacto de emergencia",
            "emergency_phone"       =>"Telefono de emergencia",
            "he_has_children"       =>"Tiene Hijos",
            "availability"          =>"Disponibilidad",
            "was_model"             =>"¿Ya ha trabajado como Modelo Webcam Antes?",
            "which_study"           =>"¿En que estudio?",
            "how_long"              =>"¿Por cuanto tiempo?",
            "work_pages"            =>"Páginas que trabajó",
            "how_much"              =>"¿Cuánto facturaba?",
            "retirement_reason"     =>"retirement_reason",
            "edu_level"             =>"Nivel de estudio",
            "edu_final"             =>"Año de finalización",
            "edu_name_inst"         =>"Nombre de la institución",
            "edu_city"              =>"Ciudad de la institución",
            "edu_validate"          =>"Cursa Estudios actualmente",
            "edu_type_study"        =>"Que tipo de Estudios ?",
            "edu_time_final"        =>"Cuanto falta para finalizar ?",
            "edu_name_inst_current" =>"Nombre de la institución ?",
            "edu_schedule"          =>"Horarios de dichos Estudios",
            "edu_others"            =>"Otros conocimientos ?",
            "person_charge"         =>"Ha tenido personal a cargo",
            "count_person"          =>"Cantidad de personal a cargo",
            "unemployment_time"     =>"Tiempo desempleado",
            "developed_activities"  =>"Actividades desarrolladas durante este tiempo",
            "know_business"         =>"¿Conoce la empresa? ¿Qué información tiene de ella?",
            "meet_us"               =>"¿Cómo se dio Cuenta de nosotros?",
            "recommended_name"      => "Recomendado por",
            "strengths"             =>"¿Cuáles son sus mayores fortalezas?",
            "personality"           =>"¿Qué aspectos de su personalidad considera podría mejorar?",
            "visualize"             =>"¿Cómo se visualiza en un año? ¿Qué proyectos tiene?",
            "health_state"          =>"¿Cuál es su estado de salud actual?",
            "wage_aspiration"       =>"Aspiración Salarial",
            "observations"          =>"Observaciones",
            "it_adapts"             =>"Concepto Final",
            "not_adapts_reason"     =>"Razon por la cual no adapta",
        ];

        foreach($form_fields as $field => $value)
        {
            if(isset($dataNew[$field]))
            {
                if($dataNew[$field] != $dataOld[$field])
                {
                    $new = $dataNew[$field];
                    $old = $dataOld[$field];

                    if($field == 'document_id'){
                        $new = $dataObjectNew->RHInterviewToDocument->type_document;
                        $old = $dataObjectOld->RHInterviewToDocument->type_document;
                    }

                    if($field == 'city_id'){
                        $new = $dataObjectNew->RHInterviewToCity->name;
                        $old = $dataObjectOld->RHInterviewToCity->name;
                    }

                    if($field == 'blood_type_id'){
                        $new = $dataObjectNew->RHInterviewToBloodType->name;
                        $old = $dataObjectOld->RHInterviewToBloodType->name;
                    }

                    if($field == 'it_adapts'){
                        $new = $this->itAdaptsValue($dataObjectNew->it_adapts);
                        $old = $this->itAdaptsValue($dataObjectOld->it_adapts);
                    }

                    if($field == 'he_has_children'){
                        $new = $this->replyValue($dataObjectNew->he_has_children);
                        $old = $this->replyValue($dataObjectOld->he_has_children);
                    }

                    if($field == 'was_model'){
                        $new = $this->replyValue($dataObjectNew->was_model);
                        $old = $this->replyValue($dataObjectOld->was_model);
                    }

                    if($field == 'person_charge'){
                        $new = $this->replyValue($dataObjectNew->person_charge);
                        $old = $this->replyValue($dataObjectOld->person_charge);
                    }

                    if($field == 'edu_validate'){
                        $new = $this->replyValue($dataObjectNew->edu_validate);
                        $old = $this->replyValue($dataObjectOld->edu_validate);
                    }

                    if($field == 'availability'){
                        $new = $this->availabilityValue($dataObjectNew->availability);
                        $old = $this->availabilityValue($dataObjectOld->availability);
                    }

                    $RhInterviewHistory = new RhInterviewHistory();
                    $RhInterviewHistory->rh_interview_id = $id;
                    $RhInterviewHistory->user_id = Auth::user()->id;
                    $RhInterviewHistory->field = $value;
                    $RhInterviewHistory->previous_value = $old;
                    $RhInterviewHistory->new_value = $new;
                    $RhInterviewHistory->save();
                }
            }

        }

    }

    public function itAdaptsValue($data)
    {
        if($data == 0){
            return "No adapta al perfil";
        }
        else{
            return "Adapta al perfil";
        }
    }

    public function replyValue($data)
    {
        if($data == 0){
            return "No";
        }
        else{
            return "Si";
        }
    }

    public function availabilityValue($data)
    {
        if($data == 'morning')
            $result = "Mañana";

        if($data == 'afternoon')
            $result = "Tarde";

        if($data == 'night')
            $result = "Noche";

        if($data == 'anytime')
            $result = "Cualquiera";

        return $result;
    }

    public function getDates()
    {
        $today  = Carbon::now()->format('Y-m-d');

        $month  = Carbon::now()->format('m');
        $year   = Carbon::now()->format('Y');


        $first_start_date   = date('Y-m-d', strtotime('1-'.$month.'-'.$year));
        $first_end_date     = date('Y-m-d', strtotime('15-'.$month.'-'.$year));

        $second_start_day  = date('Y-m-d', strtotime('16-'.$month.'-'.$year));
        $second_end_day    = Carbon::now()->endOfMonth()->format('Y-m-d');

        $start_day = "";
        $end_day = "";

        if(($today >= $first_start_date) && ($today <= $first_end_date))
        {
            $start_day  = $first_start_date;
            $end_day    = $first_end_date;
            $fortnight  = 1;
        }

        if(($today >= $second_start_day) && ($today <= $second_end_day))
        {
            $start_day  = $second_start_day;
            $end_day    = $second_end_day;
            $fortnight  = 2;
        }

        return array ($start_day, $end_day, $month, $year, $fortnight);
    }

    public function getExtraHourHistory(Request $request)
    {

        $periot = $request->periot;
        $user   = $request->user;


        if($user == 0)
        {
            $a = RHExtraHours::where('range',$periot)->where('state_id',1)->orderBy('created_at','desc')->get();
            $b = RHExtraHours::where('range',$periot)->where('state_id','<>',1)->orderBy('updated_at','desc')->get();
        }
        else
        {
            $a = RHExtraHours::where('range',$periot)->where('user_id',$user)->where('state_id',1)->orderBy('created_at','desc')->get();
            $b = RHExtraHours::where('range',$periot)->where('user_id',$user)->where('state_id','<>',1)->orderBy('updated_at','desc')->get();
        }

        $RHExtraHours = $a->merge($b);

        $result = [];

        foreach($RHExtraHours as $ExtraHour)
        {
            $user = $ExtraHour->RHExtraHoursToUsers->roleUserShortName();
            $extra_reason = "<textarea class='form-control' onpaste='return false' onfocus='this.blur()' style='height: 74px; width: 390px; resize: none;' disabled>".($this->accents($ExtraHour->extra_reason))."</textarea>";
            $application_date = $ExtraHour->application_date;
            $duration =  "<span class='text-muted'> Comienza: $ExtraHour->start_time</span><br>
    <span class='text-muted'> Termina: $ExtraHour->end_time</span><br>";
            $resume    = "<span class='text-muted'>Diurnos: ($ExtraHour->daytime_minutes) min | $($ExtraHour->daytime_total)</span><br>
                                    <span class='text-muted'>Nocturnos: ($ExtraHour->night_minutes) min | $($ExtraHour->night_total)</span><br>";

            $comment_denied = "El usuario  ha rechazado la solicitud por la razon de ".$ExtraHour->comment_denied.".";


            $total              = "<div style='color:lime'>$ ".number_format($ExtraHour->total, '0', ',', '.')."</div>";
            $state_id = $ExtraHour->state_id;
            if($state_id == 1)
            {
                $state_id = "<button class='btn btn-info btn-sm' style = 'margin-left: 11px;'><i class='fas fa-bell'></i></button>";
            }
            if($state_id == 2)
            {
                $state_id = "<button class='btn btn-success btn-sm' style = 'margin-left: 11px;'><i class='fas fa-check'></i></button>";
            }
            if($state_id == 3)
            {
                $state_id = "<a class='btn btn-danger btn-sm' style = 'margin-left: 11px;' data-toggle='tooltip' data-placement='top' title='".$comment_denied."'><i class='fas fa-times'></i></a>";
            }
            $result[] = [
                "user" => $user,
                "extra_reason"      => $extra_reason,
                "date_request"      => $application_date,
                "duration"          => $duration,
                "resume"            => $resume,
                "total"             => $total,
                "state_id"          => $state_id,
            ];

        }
        return datatables($result)
        ->rawColumns(['user', 'extra_reason', 'date_request', 'duration', 'resume', 'total', 'state_id'])
        ->toJson();
    }

    public function getExtraHourHistoryProcess(Request $request)
    {


        $RHExtraHours = RHExtraHours::where('state_id',1)->orderBy('created_at')->get();

        $result = [];

        foreach($RHExtraHours as $ExtraHour)
        {
            $id                 = $ExtraHour->id;
            $user               = $ExtraHour->RHExtraHoursToUsers->roleUserShortName();
            $extra_reason       = "<textarea class='form-control' onpaste='return false' onfocus='this.blur()' style='height: 74px; width: 390px; resize: none;' disabled>".($ExtraHour->extra_reason)."</textarea>";
            $application_date   = $ExtraHour->application_date;
            $duration         = "<span class='text-muted'> Comienza: $ExtraHour->start_time</span><br>
    <span class='text-muted'> Termina: $ExtraHour->end_time</span><br>";
            $resume    = "<span class='text-muted'>Diurnos: ($ExtraHour->daytime_minutes) min | $($ExtraHour->daytime_total)</span><br>
                                    <span class='text-muted'>Nocturnos: ($ExtraHour->night_minutes) min | $($ExtraHour->night_total)</span><br>";

            $total              = "<div style='color:lime'>$ ".number_format($ExtraHour->total, '0', ',', '.')."</div>";
            $state_id           = "";
            if (Auth::user()->can('human-resources-extra-hour-approve')){
                $state_id           = "<a onclick='approbe(".$id.")' class='btn btn-success btn-sm' style = 'margin-left: 11px;'><i class='fas fa-check'></i></a>";
                $state_id           = $state_id."<a onclick='disapprove(".$id.")' class='btn btn-danger btn-sm' style = 'margin-left: 11px;'><i class='fas fa-times'></i></a>";
            }

            $result[] = [
                "user"              => $user,
                "extra_reason"      => $extra_reason,
                "date_request"      => $application_date,
                "duration"          => $duration,
                "resume"            => $resume,
                "total"             => $total,
                "state_id"          => $state_id,
            ];

        }
        return datatables($result)
        ->rawColumns(['user', 'extra_reason', 'date_request', 'duration', 'resume', 'total', 'state_id'])
        ->toJson();
    }

    public function getExtraHourProcess()
    {

        $RHExtraHours = RHExtraHours::where('state_id',1)->orderBy('updated_at','desc')->get();

        $result = [];

        foreach($RHExtraHours as $ExtraHour)
        {
            $user               = $ExtraHour->RHExtraHoursToUsers->roleUserShortName();
            $extra_reason       = $ExtraHour->extra_reason;
            $application_date   = $ExtraHour->application_date;
            $start_time         = $ExtraHour->start_time;
            $end_time           = $ExtraHour->end_time;
            $daytime_minutes    = ($ExtraHour->daytime_minutes)." min";
            $night_minutes      = ($ExtraHour->night_minutes)." min";
            $daytime_total      = "$".($ExtraHour->daytime_total);
            $night_total        = "$".($ExtraHour->night_total);
            $total              = "$".($ExtraHour->total);
            $action             = $ExtraHour->id;

            $result[] = [
                "user"              => $user,
                "extra_reason"      => $extra_reason,
                "date_request"      => $application_date,
                "start_time"        => $start_time,
                "end_time"          => $end_time,
                "daytime_minutes"   => $daytime_minutes,
                "night_minutes"     => $night_minutes,
                "daytime_total"     => $daytime_total,
                "night_total"       => $night_total,
                "total"             => $total,
                "action"            => $action,
            ];
        }
        return datatables($result)
        ->rawColumns(['user', 'extra_reason', 'date_request', 'start_time', 'end_time', 'daytime_minutes', 'night_minutes', 'daytime_total', 'night_total', 'total', 'state_id'])
        ->toJson();
    }

    public function referred()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $roles = SettingRole::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('adminModules.rh.ReferredModels.index')->with(compact(['roles', 'user_permission', 'departments']));
    }

    public function createReferredModel(Request $request)
    {
        try {
            DB::beginTransaction();

            $model                    = new ReferredModel();
            $model->first_name        = $request->first_name;
            $model->middle_name       = $request->middle_name;
            $model->last_name         = $request->last_name;
            $model->second_last_name  = $request->second_last_name;
            $model->phone_number      = $request->phone;
            $model->email             = $request->email;
            $model->department_id     = $request->department_id;
            $model->city_id           = $request->city_id;
            $model->model_prospect_id = isset($request->prospect_id) ? $request->prospect_id : NULL;
            $model->created_by        = Auth::user()->id;
            $model->studio_creator_id = tenant('id');
            $success = $model->save();

            // create in old GB
            $prospect = DB::connection('gbmedia')
                ->insert('insert into prospectos_referidos
                                  (
                                      new_id,
                                      first_name,
                                      second_name,
                                      first_lastname,
                                      second_lastname,
                                      phone_number,
                                      email,
                                      municipality_id,
                                      model_prospect_id,
                                      studio_creator_id,
                                      created_by,
                                      created_at
                                  )
                                  values
                                  (
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?
                                  )',
                    [
                        $model->id,
                        utf8_encode($model->first_name),
                        utf8_encode($model->middle_name),
                        utf8_encode($model->last_name),
                        utf8_encode($model->second_last_name),
                        utf8_encode($model->phone_number),
                        utf8_encode($model->email),
                        $model->city_id,
                        isset($request->prospect_id) ? $request->prospect_id : NULL,
                        1,
                        Auth::user()->old_user_id,
                        Carbon::now()->toDateTimeString(),
                    ]
                );

            if($success && $request->add_images == 'true' && count($request->images) > 0) {
                foreach ($request->images AS $image) {
                    $model_img = $this->tenantUploadFile($image, 'rh/referrals', tenant('studio_slug'));

                    $document = new ReferredModelImage();
                    $document->referred_model_id = $model->id;
                    $document->path = $model_img;
                    $document->save();

                    $new_prospect = DB::connection('gbmedia')->select("SELECT MAX(id) AS id FROM prospectos_referidos;");

                    $prospect = DB::connection('gbmedia')
                        ->update('insert into prospectos_referidos_imagenes
                                  (
                                      prospect_id,
                                      image,
                                      type
                                  )
                                  values
                                  (
                                        ?,
                                        ?,
                                        ?
                                  )',
                            [
                                $new_prospect[0]->id,
                                $model_img,
                                'image',
                            ]
                        );
                }
            }
            
            $alarm = RHAlarm::query()->update(['rha_interviews' => 1]);
            DB::commit();

            return response()->json(['success' => $success, 'id' => $model->id]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editReferredModel(Request $request)
    {
        try {
            DB::beginTransaction();

            $model = ReferredModel::find($request->id);

            $model->first_name = $request->first_name;
            $model->middle_name = $request->middle_name;
            $model->last_name = $request->last_name;
            $model->second_last_name = $request->second_last_name;
            $model->phone_number = $request->phone;
            $model->email = $request->email;
            $model->department_id = $request->department_id;
            $model->city_id = $request->city_id;
            $model->created_by = Auth::user()->id;
            $success = $model->save();

            $new_prospect = DB::connection('gbmedia')->select("SELECT * FROM prospectos_referidos WHERE new_id = $model->id;");
            $new_prospect_id = null;

            if(count($new_prospect) > 0) {
                $new_prospect_id = $new_prospect[0]->id;

                DB::connection('gbmedia')
                    ->update("update
                                        prospectos_referidos
                                    set
                                        first_name = '$request->first_name',
                                        second_name = '$request->middle_name',
                                        first_lastname = '$request->last_name',
                                        second_lastname = '$request->second_last_name',
                                        phone_number = '$request->phone',
                                        email = '$request->email',
                                        department_id = '$request->department_id',
                                        municipality_id = '$request->city_id'
                                    where id = $new_prospect_id");
            }

            if($success && $request->add_images == 'true' && count($request->images) > 0) {
                foreach ($request->images AS $image) {
                    $model_img = $this->tenantUploadFile($image, 'rh/referrals', tenant('studio_slug'));

                    $document = new ReferredModelImage();
                    $document->referred_model_id = $model->id;
                    $document->path = $model_img;
                    $document->save();
                }

                if(!is_null($new_prospect_id)) {
                    $prospect = DB::connection('gbmedia')
                        ->update('insert into prospectos_referidos_imagenes
                                  (
                                      prospect_id,
                                      image,
                                      type
                                  )
                                  values
                                  (
                                        ?,
                                        ?,
                                        ?
                                  )',
                            [
                                $new_prospect[0]->id,
                                $model_img,
                                'image',
                            ]
                        );
                }
            }

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function deleteReferredModel(Request $request)
    {
        try {
            DB::beginTransaction();

            ReferredModelImage::where('referred_model_id', $request->id)->delete();
            ReferredModelSeen::where('referred_model_id', $request->id)->delete();
            $success = ReferredModel::find($request->id)->delete();

            $new_prospect = DB::connection('gbmedia')->select("SELECT * FROM prospectos_referidos WHERE new_id = $request->id;");

            if(count($new_prospect) > 0) {
                $new_prospect_id = $new_prospect[0]->id;

                DB::connection('gbmedia')
                    ->delete("delete from prospectos_referidos where id = $new_prospect_id");
            }

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function referModel(Request $request)
    {
        try {
            DB::beginTransaction();

            $model = ReferredModel::find($request->id);
            $model->status = $request->status;
            $model->referred_by = Auth::user()->id;
            $model->referred_date = Carbon::now()->toDateTimeString();
            $success = $model->save();

            $old_user_id = Auth::user()->old_user_id;
            $date = Carbon::now()->toDateTimeString();

            $new_prospect = DB::connection('gbmedia')->select("SELECT * FROM prospectos_referidos WHERE new_id = $model->id;");
            $new_prospect_id = $new_prospect[0]->id;

            if($request->status)
            {
                DB::connection('gbmedia')
                    ->update("update prospectos_referidos set status = 1, referred_by = $old_user_id, referred_date = '$date' WHERE id = $new_prospect_id");

                $studios = DB::connection('gbmedia')
                    ->select("SELECT
                                    stu.x_sub_id AS studio_id,
                                    stu.nombre
                                FROM
                                    sec_propietario pro
                                    INNER JOIN x_subestudios stu ON stu.pro_id_sub = pro.pro_id
                                    INNER JOIN prospectos_referidos_studios prs ON prs.studio_id = stu.x_sub_id
                                WHERE
                                    pro_ciudad = ?;",
                        [
                            $model->city_id,
                        ]
                    );

                foreach ($studios AS $studio) {
                    $current_date = Carbon::now()->toDateTimeString();
                    DB::connection('gbmedia')->insert("INSERT INTO prospectos_referidos_comp (prospect_id, studio_id, created_at) VALUES ('$new_prospect_id', '$studio->studio_id', '$current_date');");
                }
            }
            else
            {
                DB::connection('gbmedia')
                    ->update("update prospectos_referidos set status = 1, referred_by = NULL, referred_date = NULL WHERE id = $new_prospect_id");

                DB::connection('gbmedia')
                    ->delete("delete from prospectos_referidos_comp where prospect_id = $new_prospect_id");
            }

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function seenReferredModel(Request $request)
    {
        try {
            DB::beginTransaction();

            $model = ReferredModelSeen::firstOrCreate(
                [
                    'referred_model_id' => $request->id
                ],
                [
                    'referred_model_id' => $request->id,
                    'user_id' => Auth::user()->id,
                ]
            );

            $model->referred_model_id = $request->id;
            $model->user_id = Auth::user()->id;
            $success = $model->save();

            DB::commit();

            return response()->json(['success' => $success, 'referred_model_id' => $model->referred_model_id]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function getReferredModels()
    {
        $data = [];

        $models = ReferredModel::where('status', 0)->where('model_prospect_id', NULL)->orWhere('status', 1)->where('model_prospect_id', NULL)->get();

        foreach ($models AS $model) {
            $images = [];

            foreach ($model->images AS $image) {
                $images[] = [
                    'src' => global_asset("../storage/app/public/" . tenant('studio_slug') . "/rh/referrals/" . $image->path),
                ];
            }

            $data[] = [
                'id' => $model->id,
                'first_name' => $model->first_name,
                'middle_name' => $model->middle_name,
                'last_name' => $model->last_name,
                'second_last_name' => $model->second_last_name,
                'full_name' => "$model->first_name $model->last_name",
                'phone_number' => $model->phone_number,
                'email' => $model->email,
                'department_id' => $model->department->id,
                'department_name' => $model->department->name,
                'city_id' => $model->city->id,
                'city_name' => "{$model->department->name} / {$model->city->name}",
                'status' => $model->status,
                'studio_creator_id' => 1,
                'studio_creator_name' => 'Grupo Bedoya',
                'images' => $images,
                'seen' => ReferredModelSeen::where('referred_model_id', $model->id)->where('user_id', Auth::user()->id)->exists(),
                'created_at' => Carbon::parse($model->created_at)->format('d/m/Y'),
            ];
        }

        $alarm = RHAlarm::where('user_id', Auth::user()->id)->update(['rha_interviews' => 0]);

        return response()->json($data);
    }


    public function updateHourRequest(Request $request)
    {
        $id                 = $request->request_id;
        $user_confirm_id    = Auth::user()->id;
        $user_confirm_id    = Auth::user()->id;
        $comment_denied     = $request->reason_deny;
        $state_id           = $request->state_id;

        if($state_id == 3)
        {
            $this->validate($request,
                [
                    'reason_deny'           => 'required',
                ],
                [
                    'reason_deny.required'  => 'Este campo es requerido.',
                ]
            );
        }

        try
        {
            DB::beginTransaction();
            //update vacation Request
            $RHVacationRequest = RHExtraHours::find($id);
            $RHVacationRequest->user_acep_den_id        = $user_confirm_id;
            $RHVacationRequest->state_id                = $state_id;
            $RHVacationRequest->comment_denied          = $comment_denied;
            $RHVacationRequest->save();


            if($state_id == 2)
            {
                $now = Carbon::now()->day;
                $last_day_of_month = Carbon::now()->endOfMonth()->day;
                if(($now >= 1 && $now <= 14) || ($now == $last_day_of_month))
                {
                    $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-07";

                    if($now == $last_day_of_month) {
                        $date = Carbon::now()->addDay();
                        $for_date = $date->year . "-" . $date->month . "-07";
                    }
                }
                else
                {
                    $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-27";
                }

                $movements = new PayrollMovement;
                $movements->user_id         = $RHVacationRequest->user_id;
                $movements->payroll_type_id = '14';
                $movements->amount          = $RHVacationRequest->total;
                $movements->created_by      = Auth::user()->id;
                $movements->comment         = "Hora extra realizada el dia ".$RHVacationRequest->application_date." con un valor de : $".$RHVacationRequest->total;
                $movements->for_date        = $for_date;
                $movements->save();

                $payroll_controller = new PayrollController();
                $payroll_controller->calculateUserPayroll($RHVacationRequest->user_id);
            }

            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }

    }

    public function execute()
    {
        try {
            DB::beginTransaction();

            $GB_roles = [
                'Administradora' => 2,
                'Asesor Comercial' => 30,
                'Asistente Administrativa' => 2,
                'Auxiliar Administrativo' => 33,
                'Auxiliar Boutique' => 21,
                'Auxiliar Contable' => 22,
                'Cafeteria y Aseo' => 15,
                'Desarrollador Web' => 12,
                'Entrenadora de Modelos' => 8,
                'Fotografo' => 9,
                'Influencer' => 28,
                'Mantenimiento y Construccion' => 16,
                'Maquilladora' => 17,
                'Mayordomos' => 26,
                'Mensajero' => 19,
                'Monitora' => 6,
                'Modelo' => 14,
                'NiÃ±era' => 27,
                'Profesor Ingles' => 18,
                'Programador' => 11,
                'Psicologa' => 36,
                'Publicista' => 20,
                'Recursos Humanos' => 7,
                'Recursos Humanos Operativo' => 35,
                'Secretaria' => 4,
                'Secretariado' => 4,
                'Soporte' => 4,
                'Tecnico de Sistemas' => 13,
                'Videografo' => 10,
            ];

            $GB_blood_types = [
                '' => 1,
                'Indefinido' => 1,
                'A+' => 1,
                'B+' => 2,
                'O+' => 3,
                'AB+' => 4,
                'A-' => 5,
                'B-' => 6,
                'O-' => 7,
                'AB-' => 8,
            ];

            $GB_availability = [
                'maÃ±ana' => 'morning',
                'mañana' => 'morning',
                'tarde' => 'afternoon',
                'noche' => 'night',
                'cualquiera' => 'anytime',
            ];

            $min_id = 1; // Hasta aqui se migró. 2021-01-04 11:30
            $max_id = 2048;

            $prospects = DB::connection('gbmedia')->table('prospecto')->whereBetween('id', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('rh_interviews', 'referred'))
            {
                Schema::table('rh_interviews', function (Blueprint $table) {
                    $table->integer('referred')->nullable();
                });
            }

            if (!Schema::hasColumn('rh_interviews', 'old_prospect_id'))
            {
                Schema::table('rh_interviews', function (Blueprint $table) {
                    $table->integer('old_prospect_id')->nullable();
                });
            }

            foreach ($prospects AS $prospect) {
                $interview_date = $prospect->fecha_entrevista;

                $user_name = trim($prospect->entrevistador);
                $exploded = explode(' ', $user_name);

                if(!empty($user_name))
                {
                    $user = User::where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();

                    if (is_object($user)) {
                        $user_interviewer_id = $user->id;
                    } else {
                        $user_interviewer_id = 473;
                    }
                }
                else
                {
                    $user_interviewer_id = 473;
                }

                // is_user
                $is_user = 0;
                $user_id = null;

                if($prospect->es_user == 'si') {
                    $is_user = 1;
                    $user = User::select('id')->where('old_user_id', $prospect->id_usuario)->first();

                    if(!is_null($user)) {
                        $user_id = $user->id;
                    } else {
                        $user_id = null;
                    }
                }

                $blood_type = $GB_blood_types[$prospect->rh];

                if(is_numeric($prospect->ciudad))
                {
                    $city = City::where('id', $prospect->ciudad)->first();
                    $city_id = $city->id;
                    $department_id = $city->department_id;
                }
                else
                {
                    $city_id = 150;
                    $department_id = 24;
                }

                $setting_role_id = !empty($prospect->cargo) ? $GB_roles[$prospect->cargo] : 14;
                $first_name = $prospect->nombre;
                $middle_name = $prospect->segundo_nombre;
                $last_name = $prospect->apellidos;
                $second_last_name = $prospect->segundo_apellido;
                $birth_date = $prospect->fecha_nacimiento == '0000-00-00' ? '1990-01-01' : $prospect->fecha_nacimiento;
                $document_id = 1;
                $document_number = $prospect->documento_numero;
                $expiration_date = !empty($prospect->fecha_vencimiento) ? $prospect->fecha_vencimiento : null;
                $email = trim($prospect->email);
                $mobile_number = $prospect->telefono;
                $address = $prospect->direccion;
                $neighborhood = $prospect->barrio;
                $lives_with = $prospect->vive_con;
                $emergency_contact = $prospect->contacto_emergencia;
                $emergency_phone = $prospect->telefono_emergencia;
                $he_has_children = $prospect->tiene_hijos == 'si' ? 1 : 0;
                $availability = is_null($prospect->disponibilidad) || empty($prospect->disponibilidad) ? 'morning' : $GB_availability[$prospect->disponibilidad];
                $was_model = $prospect->trabajo_como_modelo == 'si' ? 1 : 0;
                $which_study = $prospect->que_estudio;
                $how_long = $prospect->cuanto_tiempo;
                $work_pages = $prospect->paginas_trab;
                $how_much = $prospect->cuanto_facturaba;
                $retirement_reason = $prospect->retiro_modelo;
                $edu_level = !empty($prospect->edu_nivel) ? $prospect->edu_nivel : null;
                $edu_final = $prospect->edu_final;
                $edu_name_inst = $prospect->edu_nombre_inst;
                $edu_city = $prospect->edu_ciudad;
                $edu_title = $prospect->edu_titulo;
                $edu_validate = $prospect->edu_validate;
                $edu_type_study = $prospect->edu_tipo_estudio;
                $edu_time_final = $prospect->edu_time_final;
                $edu_name_inst_current = $prospect->edu_nombre_inst_actual;
                $edu_schedule = $prospect->edu_horarios;
                $edu_others = $prospect->edu_otros;
                $person_charge = $prospect->personal_cargo == 'si' ? 1 : 0;
                $count_person = is_numeric($prospect->cant_personas_cargo) ? $prospect->cant_personas_cargo : 0;
                $unemployment_time = $prospect->tiempo_desempleo;
                $developed_activities = $prospect->actividades_desarrolladas;
                $know_business = $prospect->conoce_empresa;
                $meet_us = $prospect->dio_cuenta;
                $recommended_name = $prospect->recomendado;
                $strengths = $prospect->fortalezas;
                $personality = $prospect->personalidad;
                $visualize = $prospect->visualiza;
                $health_state = $prospect->estado_salud;
                $wage_aspiration = $prospect->aspiracion_salarial;
                $observations = $prospect->observaciones;
                $it_adapts = $prospect->se_adapta == 'si' ? 1 : 0;
                $not_adapts_reason = $prospect->no_adapta_razon;
                $referred = $prospect->referred;
                $cite = $prospect->citar;

                if(!is_null($cite)) {
                    $cite = 'citado' ? 1 : 0;
                }

                $created_at = $interview_date;
                $updated_at = $prospect->updated_at;

                $created_prospect = RHInterviews::firstOrCreate(
                    [
                        'old_prospect_id' => $prospect->id,
                    ],
                    [
                        'user_interviewer_id' => $user_interviewer_id,
                        'user_id' => $user_id,
                        'setting_role_id' => $setting_role_id,
                        'document_id' => $document_id,
                        'blood_type_id' => $blood_type,
                        'department_id' => $department_id,
                        'city_id' => $city_id,
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'last_name' => $last_name,
                        'second_last_name' => $second_last_name,
                        'birth_date' => $birth_date,
                        'document_number' => $document_number,
                        'expiration_date' => $expiration_date,
                        'email' => $email,
                        'mobile_number' => $mobile_number,
                        'address' => $address,
                        'neighborhood' => $neighborhood,
                        'lives_with' => $lives_with,
                        'emergency_contact' => $emergency_contact,
                        'emergency_phone' => $emergency_phone,
                        'he_has_children' => $he_has_children,
                        'availability' => $availability,
                        'was_model' => $was_model,
                        'which_study' => $which_study,
                        'how_long' => $how_long,
                        'work_pages' => $work_pages,
                        'how_much' => $how_much,
                        'retirement_reason' => $retirement_reason,
                        'edu_level' => $edu_level,
                        'edu_final' => $edu_final,
                        'edu_name_inst' => $edu_name_inst,
                        'edu_city' => $edu_city,
                        'edu_title' => $edu_title,
                        'edu_validate' => $edu_validate,
                        'edu_type_study' => $edu_type_study,
                        'edu_time_final' => $edu_time_final,
                        'edu_name_inst_current' => $edu_name_inst_current,
                        'edu_schedule' => $edu_schedule,
                        'edu_others' => $edu_others,
                        'person_charge' => $person_charge,
                        'count_person' => $count_person,
                        'unemployment_time' => $unemployment_time,
                        'developed_activities' => $developed_activities,
                        'know_business' => $know_business,
                        'meet_us' => $meet_us,
                        'recommended_name' => $recommended_name,
                        'strengths' => $strengths,
                        'personality' => $personality,
                        'visualize' => $visualize,
                        'health_state' => $health_state,
                        'wage_aspiration' => $wage_aspiration,
                        'observations' => $observations,
                        'it_adapts' => $it_adapts,
                        'not_adapts_reason' => $not_adapts_reason,
                        'is_user' => $is_user,
                        'cite' => $cite,
                        'referred' => $referred,
                        'old_prospect_id' => $prospect->id,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                    ]
                );

                $created_prospect->user_interviewer_id = $user_interviewer_id;
                $created_prospect->user_id = $user_id;
                $created_prospect->setting_role_id = $setting_role_id;
                $created_prospect->document_id = $document_id;
                $created_prospect->blood_type_id = $blood_type;
                $created_prospect->department_id = $department_id;
                $created_prospect->city_id = $city_id;
                $created_prospect->first_name = $first_name;
                $created_prospect->middle_name = $middle_name;
                $created_prospect->last_name = $last_name;
                $created_prospect->second_last_name = $second_last_name;
                $created_prospect->birth_date = $birth_date;
                $created_prospect->document_number = $document_number;
                $created_prospect->expiration_date = $expiration_date;
                $created_prospect->email = $email;
                $created_prospect->mobile_number = $mobile_number;
                $created_prospect->address = $address;
                $created_prospect->neighborhood = $neighborhood;
                $created_prospect->lives_with = $lives_with;
                $created_prospect->emergency_contact = $emergency_contact;
                $created_prospect->emergency_phone = $emergency_phone;
                $created_prospect->he_has_children = $he_has_children;
                $created_prospect->availability = $availability;
                $created_prospect->was_model = $was_model;
                $created_prospect->which_study = $which_study;
                $created_prospect->how_long = $how_long;
                $created_prospect->work_pages = $work_pages;
                $created_prospect->how_much = $how_much;
                $created_prospect->retirement_reason = $retirement_reason;
                $created_prospect->edu_level = $edu_level;
                $created_prospect->edu_final = $edu_final;
                $created_prospect->edu_name_inst = $edu_name_inst;
                $created_prospect->edu_city = $edu_city;
                $created_prospect->edu_title = $edu_title;
                $created_prospect->edu_validate = $edu_validate;
                $created_prospect->edu_type_study = $edu_type_study;
                $created_prospect->edu_time_final = $edu_time_final;
                $created_prospect->edu_name_inst_current = $edu_name_inst_current;
                $created_prospect->edu_schedule = $edu_schedule;
                $created_prospect->edu_others = $edu_others;
                $created_prospect->person_charge = $person_charge;
                $created_prospect->count_person = $count_person;
                $created_prospect->unemployment_time = $unemployment_time;
                $created_prospect->developed_activities = $developed_activities;
                $created_prospect->know_business = $know_business;
                $created_prospect->meet_us = $meet_us;
                $created_prospect->recommended_name = $recommended_name;
                $created_prospect->strengths = $strengths;
                $created_prospect->personality = $personality;
                $created_prospect->visualize = $visualize;
                $created_prospect->health_state = $health_state;
                $created_prospect->wage_aspiration = $wage_aspiration;
                $created_prospect->observations = $observations;
                $created_prospect->it_adapts = $it_adapts;
                $created_prospect->not_adapts_reason = $not_adapts_reason;
                $created_prospect->is_user = $is_user;
                $created_prospect->cite = $cite;
                $created_prospect->referred = $referred;
                $created_prospect->created_at = $created_at;
                $created_prospect->updated_at = $updated_at;
                $created_prospect->old_prospect_id = $prospect->id;
                $created_prospect->save();

                if($created_prospect->wasRecentlyCreated) {
                    if($he_has_children) {
                        for ($i = 1; $i <= 5; $i++) {
                            $son_id = "hijo$i";
                            $son = $prospect->$son_id;

                            if($son) {
                                $interview_son = new RHInterviewSon();
                                $interview_son->rh_interview_id = $created_prospect->id;
                                $interview_son->name = $son;
                                $interview_son->save();
                            }
                        }
                    }

                    if(!empty($prospect->empresa1)) {
                        $name_bussines = $prospect->empresa1;
                        $time_worked = $prospect->tiempo1;
                        $position = $prospect->cargo1;
                        $reason_withdrawal = $prospect->retiro1;

                        $work = new RHWorkingInfo();
                        $work->rh_interview_id = $created_prospect->id;
                        $work->name_bussines = $name_bussines;
                        $work->time_worked = $time_worked;
                        $work->position = $position;
                        $work->reason_withdrawal = $reason_withdrawal;
                        $work->save();
                    }

                    if(!empty($prospect->empresa2)) {
                        $name_bussines = $prospect->empresa2;
                        $time_worked = $prospect->tiempo2;
                        $position = $prospect->cargo2;
                        $reason_withdrawal = $prospect->retiro2;

                        $work = new RHWorkingInfo();
                        $work->rh_interview_id = $created_prospect->id;
                        $work->name_bussines = $name_bussines;
                        $work->time_worked = $time_worked;
                        $work->position = $position;
                        $work->reason_withdrawal = $reason_withdrawal;
                        $work->save();
                    }

                    if(!empty($prospect->empresa3)) {
                        $name_bussines = $prospect->empresa3;
                        $time_worked = $prospect->tiempo3;
                        $position = $prospect->cargo3;
                        $reason_withdrawal = $prospect->retiro3;

                        $work = new RHWorkingInfo();
                        $work->rh_interview_id = $created_prospect->id;
                        $work->name_bussines = $name_bussines;
                        $work->time_worked = $time_worked;
                        $work->position = $position;
                        $work->reason_withdrawal = $reason_withdrawal;
                        $work->save();
                    }
                }

                if($prospect->cargo == 'Modelo') {
                    $images = DB::connection('gbmedia')->table('prospecto_img')->where('id_prospecto', $prospect->id)->get();

                    foreach ($images AS $image) {
                        $interview_img = RHInterviewImg::firstOrCreate(
                            [
                                'rh_interview_id' => $created_prospect->id,
                            ],
                            [
                                'rh_interview_id' => $created_prospect->id,
                            ]
                        );

                        $interview_img->rh_interview_id = $created_prospect->id;
                        $interview_img->face = $image->rostro;
                        $interview_img->front = $image->frente;
                        $interview_img->side = $image->lado;
                        $interview_img->back = $image->espalda;
                        $interview_img->save();
                    }
                }

                if(!is_null($created_prospect->user_id)) {
                    $changes = DB::connection('gbmedia')->table('prospecto_historial')->where('ph_id_prospecto', $prospect->id)->get();

                    foreach ($changes AS $change) {
                        $history = RhInterviewHistory::firstOrCreate(
                            [
                                'rh_interview_id' => $created_prospect->id,
                                'created_at' => $change->created_add,
                            ],
                            [
                                'rh_interview_id' => $created_prospect->id,
                                'user_id' => $created_prospect->user_id,
                                'field' => $change->campo,
                                'previous_value' => $change->ph_anterior,
                                'new_value' => $change->ph_nuevo,
                                'created_at' => $change->created_add,
                                'updated_at' => $change->created_add,
                            ]
                        );

                        $history->rh_interview_id = $created_prospect->id;
                        $history->user_id = $created_prospect->user_id;
                        $history->field = $change->campo;
                        $history->previous_value = $change->ph_anterior;
                        $history->new_value = $change->ph_nuevo;
                        $history->created_at = $change->created_add;
                        $history->updated_at = $change->created_add;
                        $history->save();
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

    public function extraHoursExecute()
    {
        try {
            DB::beginTransaction();

            $GB_status = [
                'solicitando' => 1,
                'asignado' => 2,
                'desaprobado' => 3,
            ];

            $min_id = 5001;
            $max_id = 10118;

            $hours = DB::connection('gbmedia')->table('rh_horas_extras')->whereBetween('id', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('rh_extra_hours', 'old_extra_hour_id'))
            {
                Schema::table('rh_extra_hours', function (Blueprint $table) {
                    $table->integer('old_extra_hour_id')->nullable();
                });
            }

            foreach ($hours AS $hour) {
                $user = User::select('id')->where('old_user_id', $hour->id_usuario)->first();

                if(is_object($user)) {
                    $user_id = $user->id;
                } else {
                    $user_id = 2;
                }

                $user_accepts = trim($hour->usuario_acep_den);
                $exploded = explode(' ', $user_accepts);

                if(!empty($user_accepts))
                {
                    $user = User::where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();

                    if (is_object($user)) {
                        $user_acep_den_id = $user->id;
                    } else {
                        $user_acep_den_id = 473;
                    }
                }
                else
                {
                    $user_acep_den_id = 473;
                }

                $daytime_minutes = $hour->diurnos;
                $daytime_total = 0;

                if (!empty($daytime_minutes) && !is_null($daytime_minutes)) {
                    $daytime_exploded = explode('/', $daytime_minutes);
                    $daytime_minutes = $daytime_exploded[0];
                    $daytime_total = $daytime_exploded[1];
                }

                $night_minutes = $hour->nocturnos;
                $night_total = 0;

                if (!empty($night_minutes) && !is_null($night_minutes)) {
                    $night_exploded = explode('/', $night_minutes);
                    $night_minutes = $night_exploded[0];
                    $night_total = $night_exploded[1];
                }

                $comment = trim($hour->comentario);

                if (!empty($comment) && !is_null($comment)) {
                    $comment_exploded = explode(' ', $comment);

                    if($comment_exploded[0] == 'Comienza') {
                        $start_time = $comment_exploded[1];
                        $end_time = $comment_exploded[3];
                    } else {
                        $start_time = $comment_exploded[3];
                        $end_time = $comment_exploded[7];
                    }
                }

                $total = $hour->total_dinero;
                $total_extras = $hour->total_extras;
                $extra_reason = $hour->razon_extras;
                $state_id = $GB_status[$hour->estado];
                $comment_denied = $hour->comentario_denegado;
                $application_date = $hour->fecha_solicitud;
                $review_date = $hour->fecha_revision;
                $range = $hour->rango;
                $range_revision = $hour->rango_revision;
                $day = $hour->dia;
                $month = $hour->mes;
                $year = $hour->year;

                $created_extra_hour = RHExtraHours::firstOrCreate(
                    [
                        'old_extra_hour_id' => $hour->id,
                    ],
                    [
                        'user_id' => $user_id,
                    ]
                );

                $created_extra_hour->state_id = $state_id;
                $created_extra_hour->user_id = $user_id;
                $created_extra_hour->user_acep_den_id = $user_acep_den_id;
                $created_extra_hour->start_time = $start_time;
                $created_extra_hour->end_time = $end_time;
                $created_extra_hour->daytime_minutes = $daytime_minutes;
                $created_extra_hour->daytime_total = $daytime_total;
                $created_extra_hour->night_minutes = $night_minutes;
                $created_extra_hour->night_total = $night_total;
                $created_extra_hour->total = $total;
                $created_extra_hour->total_extras = $total_extras;
                $created_extra_hour->extra_reason = $extra_reason;
                $created_extra_hour->state_id = $state_id;
                $created_extra_hour->comment_denied = $comment_denied;
                $created_extra_hour->application_date = $application_date;
                $created_extra_hour->review_date = $review_date;
                $created_extra_hour->range = $range;
                $created_extra_hour->range_revision = $range_revision;
                $created_extra_hour->day = $day;
                $created_extra_hour->month = $month;
                $created_extra_hour->year = $year;
                $created_extra_hour->old_extra_hour_id = $hour->id;
                $created_extra_hour->created_at = $application_date;
                $created_extra_hour->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function vacationsExecute()
    {
        try {
            DB::beginTransaction();

            $GB_status = [
                'solicitando' => 1,
                'anotado' => 2,
                'aprobado' => 2,
                'denegado' => 3,
            ];

            $min_id = 1;
            $max_id = 244; // Ultimo migrado hasta el 2021-01-04 17:56

            $requests = DB::connection('gbmedia')->table('solicitud_vacaciones')->whereBetween('id', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('rh_vacation_requests', 'old_vacation_request_id'))
            {
                Schema::table('rh_vacation_requests', function (Blueprint $table) {
                    $table->integer('old_vacation_request_id')->nullable();
                });
            }

            foreach ($requests AS $request) {
                $user = User::select('id')->where('old_user_id', $request->fk_u_id)->first();
                $user_id = $user->id;

                $user_confirm = trim($request->usuario_confirma);

                if(!empty($user_confirm))
                {
                    $exploded = explode(' ', $user_confirm);

                    $user = User::where('first_name', $exploded[0])->where('last_name', utf8_decode($exploded[1]))->first();

                    if (is_object($user)) {
                        $user_confirm_id = $user->id;
                    } else {
                        $user_confirm_id = 2;
                    }
                }
                else
                {
                    $user_confirm_id = 2;
                }

                $created_at = $request->fecha_solicitud;
                $start_date = $request->fecha_inicio;
                $end_date = $request->fecha_fin;
                $reason_deny = utf8_decode($request->razon_denegar);
                $rh_vacation_status_id = $GB_status[$request->estado];

                $created_vacation_request = RHVacationRequest::firstOrCreate(
                    [
                        'old_vacation_request_id' => $request->id,
                    ],
                    [
                        'user_id' => $user_id,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                    ]
                );

                $created_vacation_request->user_id = $user_id;
                $created_vacation_request->user_confirm_id = $user_confirm_id;
                $created_vacation_request->rh_vacation_status_id = $rh_vacation_status_id;
                $created_vacation_request->start_date = $start_date;
                $created_vacation_request->end_date = $end_date;
                $created_vacation_request->reason_deny = $reason_deny;
                $created_vacation_request->created_at = $created_at;
                $created_vacation_request->old_vacation_request_id = $request->id;
                $created_vacation_request->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function usersVacationsExecute()
    {
        try {
            DB::beginTransaction();

            $GB_roles = [
                'Administradora' => 2,
                'Asesor Comercial' => 30,
                'Asistente Administrativa' => 2,
                'Auxiliar Administrativo' => 33,
                'Auxiliar Boutique' => 21,
                'Auxiliar Contable' => 22,
                'Cafeteria y Aseo' => 15,
                'Desarrollador Web' => 12,
                'Entrenadora de Modelos' => 8,
                'Fotografo' => 9,
                'Influencer' => 28,
                'Mantenimiento y Construccion' => 16,
                'Maquilladora' => 17,
                'Mayordomos' => 26,
                'Mensajero' => 19,
                'Monitora' => 6,
                'Modelo' => 14,
                'NiÃ±era' => 27,
                'Profesor Ingles' => 18,
                'Programador' => 11,
                'Psicologa' => 36,
                'Publicista' => 20,
                'Recursos Humanos' => 7,
                'Recursos Humanos Operativo' => 35,
                'Secretaria' => 4,
                'Secretariado' => 4,
                'Soporte' => 4,
                'Tecnico de Sistemas' => 13,
                'Videografo' => 10,
            ];

            $min_id = 1;
            $max_id = 851; // Ultimo migrado hasta el 2021-01-04 17:56

            $vacations = DB::connection('gbmedia')->table('vacaciones_usuario')->whereBetween('id', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('rh_vacation_user', 'old_vacation_id'))
            {
                Schema::table('rh_vacation_user', function (Blueprint $table) {
                    $table->integer('old_vacation_id')->nullable();
                });
            }

            foreach ($vacations AS $vacation) {
                $user_vacation = utf8_decode(trim($vacation->usuario_vac));
                $exploded = explode(' ', $user_vacation);

                $first_name = str_replace('Ã‘', 'ñ', $exploded[0]);
                $first_name = str_replace('Ã±', 'ñ', $first_name);

                $last_name = str_replace('Ã‘', 'ñ', $exploded[1]);
                $last_name = str_replace('Ã±', 'ñ', $last_name);

                $user_vacation = User::select('id', 'setting_role_id')->where('first_name', $first_name)->where('last_name', $last_name)->first();
                $user_id = $user_vacation->id;

                $user_confirm = trim($vacation->usuario_asigna);
                if(!empty($user_confirm))
                {
                    $exploded = explode(' ', $user_confirm);

                    $user = User::where('first_name', $exploded[0])->where('last_name', utf8_decode($exploded[1]))->first();

                    if (is_object($user)) {
                        $user_confirm_id = $user->id;
                    } else {
                        $user_confirm_id = 2;
                    }
                }
                else
                {
                    $user_confirm_id = 2;
                }

                $rank = $vacation->rango;
                $date = $vacation->fecha;
                $day = $vacation->dia;
                $month = $vacation->mes;
                $year = $vacation->year;
                $setting_role_id = trim($vacation->rol);

                if (empty($setting_role_id))
                {
                    $setting_role_id = $user_vacation->setting_role_id;
                }
                else
                {
                    $setting_role_id = $GB_roles[$setting_role_id];
                }

                $created_vacation_user = RHVacationUser::firstOrCreate(
                    [
                        'old_vacation_id' => $vacation->id,
                    ],
                    [
                        'user_id' => $user_id,
                        'setting_role_id' => $setting_role_id,
                        'rank' => $rank,
                        'date' => $date,
                        'day' => $day,
                        'month' => $month,
                        'year' => $year,
                    ]
                );

                $created_vacation_user->user_id = $user_id;
                $created_vacation_user->user_confirm_id = $user_confirm_id;
                $created_vacation_user->setting_role_id = $setting_role_id;
                $created_vacation_user->rank = $rank;
                $created_vacation_user->date = $date;
                $created_vacation_user->day = $day;
                $created_vacation_user->month = $month;
                $created_vacation_user->year = $year;
                $created_vacation_user->old_vacation_id = $vacation->id;
                $created_vacation_user->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function referredProspectsExecute()
    {
        try {
            DB::beginTransaction();

            $min_id = 1;
            $max_id = 1200; // Ultimo migrado hasta el 2021-01-04 17:56

            $prospects = DB::connection('gbmedia')->table('prospectos_referidos')->whereBetween('id', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('referred_models', 'old_prospect_id'))
            {
                Schema::table('referred_models', function (Blueprint $table) {
                    $table->integer('old_prospect_id')->nullable();
                });
            }

            if (!Schema::hasColumn('referred_model_images', 'old_prospect_image_id'))
            {
                Schema::table('referred_model_images', function (Blueprint $table) {
                    $table->integer('old_prospect_image_id')->nullable();
                });
            }

            foreach ($prospects AS $prospect) {
                $first_name = $this->accents($prospect->first_name);
                $middle_name = $this->accents($prospect->second_name);
                $last_name = $this->accents($prospect->first_lastname);
                $second_last_name = $this->accents($prospect->second_lastname);
                $phone_number = $prospect->phone_number;
                $email = $prospect->email;
                $department = City::select('department_id')->where('id', $prospect->municipality_id)->first();
                $department_id = $department->department_id;
                $city_id = $prospect->municipality_id;
                $status = (is_null($prospect->referred_by) ? 0 : $prospect->status);
                $created_at = $prospect->created_at;
                $updated_at = $prospect->updated_at;

                $created_by = $prospect->created_by;
                $creator = User::where('old_user_id', $created_by)->first();

                if(!is_null($creator)) {
                    $created_by_id = $creator->id;
                } else {
                    $created_by_id = 3;
                }

                $studio_creator_id = $prospect->studio_creator_id;

                $referred_by = $prospect->referred_by;
                $referrer = User::where('old_user_id', $referred_by)->first();

                if(!is_null($referrer))
                {
                    $referred_by_id = $referrer->id;
                } else {
                    $referred_by_id = 3;
                }

                $referred_date = $prospect->referred_date;
                $converted_studio_id = $prospect->converted_studio_id;
                $converted_studio_date = $prospect->converted_studio_date;

                $created_prospect = ReferredModel::firstOrCreate(
                    [
                        'old_prospect_id' => $prospect->id,
                    ],
                    [
                        'department_id' => $department_id,
                        'city_id' => $city_id,
                        'status' => $status,
                        'old_prospect_id' => $prospect->id,
                    ]
                );

                $created_prospect->first_name = $first_name;
                $created_prospect->middle_name = $middle_name;
                $created_prospect->last_name = $last_name;
                $created_prospect->second_last_name = $second_last_name;
                $created_prospect->phone_number = $phone_number;
                $created_prospect->email = $email;
                $created_prospect->department_id = $department_id;
                $created_prospect->city_id = $city_id;
                $created_prospect->status = $status;
                $created_prospect->studio_creator_id = $studio_creator_id;
                $created_prospect->created_by = $created_by_id;
                $created_prospect->referred_by = $referred_by_id;
                $created_prospect->referred_date = $referred_date;
                $created_prospect->converted_studio_id = $converted_studio_id;
                $created_prospect->converted_studio_date = $converted_studio_date;
                $created_prospect->created_at = $created_at;
                $created_prospect->updated_at = $updated_at;
                $created_prospect->old_prospect_id = $prospect->id;
                $created_prospect->save();

                $images = DB::connection('gbmedia')->table('prospectos_referidos_imagenes')->where('prospect_id', $prospect->id)->get();

                if(!is_null($images)) {
                    foreach ($images AS $image) {
                        $created_prospect_image = ReferredModelImage::firstOrCreate(
                            [
                                'old_prospect_image_id' => $image->id,
                            ],
                            [
                                'referred_model_id' => $created_prospect->id,
                                'path' => $image->image,
                            ]
                        );

                        $created_prospect_image->referred_model_id = $created_prospect->id;
                        $created_prospect_image->path = $image->image;
                        $created_prospect_image->save();
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

    public function referredSeenProspectsExecute()
    {
        $min_id = 1;
        $max_id = 1200; // Ultimo migrado hasta el 2021-01-04 17:56

        $seen = DB::connection('gbmedia')->table('prospectos_referidos_vistos')->whereBetween('prospect_id', [$min_id, $max_id])->get();

        try {
            DB::beginTransaction();

            foreach ($seen AS $item) {
                $viewed_user = User::where('old_user_id', $item->user_id)->first();
                $model = ReferredModel::where('old_prospect_id', $item->prospect_id)->first();
                $referred_model_id = $model->id;

                $created_prospect_image = ReferredModelSeen::firstOrCreate(
                    [
                        'referred_model_id' => $referred_model_id,
                        'user_id' => $viewed_user->id,
                    ],
                    [
                        'referred_model_id' => $referred_model_id,
                        'user_id' => $viewed_user->id,
                    ]
                );

                $created_prospect_image->referred_model_id = $referred_model_id;
                $created_prospect_image->user_id           = $viewed_user->id;
                $created_prospect_image->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function referredToStudiosExecute()
    {
        $studios = DB::connection('gbmedia')->table('prospectos_referidos_studios')->get();

        try {
            DB::beginTransaction();

            foreach ($studios AS $studio) {
                $created_studio = ReferredModelStudio::firstOrCreate(
                    [
                        'studio_id' => $studio->studio_id,
                    ],
                    [
                        'studio_id' => $studio->studio_id,
                    ]
                );

                $created_studio->studio_id = $studio->studio_id;
                $created_studio->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function referredSharedToStudiosExecute()
    {
        $min_id = 1;
        $max_id = 19200;

        $items = DB::connection('gbmedia')->table('prospectos_referidos_comp')->whereBetween('id', [$min_id, $max_id])->get();

        try {
            DB::beginTransaction();

            foreach ($items AS $item) {
                $model = ReferredModel::where('old_prospect_id', $item->prospect_id)->first();

                $created_shared_studio = ReferredModelShared::firstOrCreate(
                    [
                        'referred_model_id' => $model->id,
                        'studio_id' => $item->studio_id,
                    ],
                    [
                        'referred_model_id' => $model->id,
                        'studio_id' => $item->studio_id,
                    ]
                );

                $created_shared_studio->referred_model_id = $model->id;
                $created_shared_studio->studio_id = $item->studio_id;
                $created_shared_studio->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function creteInterviewsCiteTasksExecute()
    {
        $min_id = 2023;
        $max_id = 2037;

        $prospects = RHInterviews::whereBetween('id', [$min_id, $max_id])->where('cite', 1)->get();

        try {
            DB::beginTransaction();

            foreach ($prospects AS $prospect) {
                $role = $prospect->RHInterviewToRole->name;

                $task_controller = new TaskController();

                $task = new Task();
                $task->created_by_type = 1; // User
                $task->created_by = Auth::user()->id;
                $task->title = "Nuevo/a $role: $prospect->first_name $prospect->last_name";
                $task->status = 0;
                $task->should_finish = Carbon::now()->addDay();
                $task->terminated_by = 0;
                $task->code = $task_controller->generateCode();
                $created = $task->save();

                //Gerente, Asistente Administrativa, Recursos Humanos, Recursos Humanos Operativo, Auxiliar Nómina, Psicologa, Administradora
                $receivers = [
                    'to_roles' => [
                        ['id' => 1, 'name' => 'Gerente'],
                        ['id' => 3, 'name' => 'Administrador/a'],
                        ['id' => 2, 'name' => 'Asistente Administrativo'],
                        ['id' => 7, 'name' => 'Recursos Humanos'],
                        ['id' => 35, 'name' => 'Recursos Humanos Operativo'],
                        ['id' => 40, 'name' => 'Auxiliar Nomina'],
                        ['id' => 36, 'name' => 'Psicólogo/a'],
                    ],
                    'to_users' => [],
                    'to_models' => [],
                ];

                $request_object = new Request(['receivers' => json_encode($receivers), 'task_id' => $task->id, 'from_add_receivers' => 0]);
                $task_controller->addReceivers($request_object);

                if ($created) {

                    if ($prospect->setting_role_id == 14) // model
                    {
                        $task_comment = new TaskComment();
                        $task_comment->task_id = $task->id;
                        $task_comment->user_id = Auth::user()->id;
                        $task_comment->comment = "Citar a la Modelo que asistió a entrevista $prospect->first_name $prospect->last_name para iniciar proceso de documentación y fotografía";
                        $task_comment->save();

                        $file = null;

                        if(!is_null($prospect->RHInterviewToImg->face))
                        {
                            $file = $prospect->RHInterviewToImg->face;
                        }
                        elseif (!is_null($prospect->RHInterviewToImg->front))
                        {
                            $file = $prospect->RHInterviewToImg->front;
                        }
                        elseif (!is_null($prospect->RHInterviewToImg->side))
                        {
                            $file = $prospect->RHInterviewToImg->side;
                        }
                        elseif (!is_null($prospect->RHInterviewToImg->back))
                        {
                            $file = $prospect->RHInterviewToImg->back;
                        }

                        if(!is_null($file)) {
                            $copy = @\File::copy(base_path("storage/app/public/" . tenant('studio_slug') . "/rh//model_img/" . $file), base_path("storage/app/public/" . tenant('studio_slug') . "/task/" . $file));

                            $task_comment_attachment = new TaskCommentAttachment();
                            $task_comment_attachment->task_comments_id = $task_comment->id;
                            $task_comment_attachment->file = $file;
                            $task_comment_attachment->save();
                        }
                    }
                    else
                    {
                        $task_comment = new TaskComment();
                        $task_comment->task_id = $task->id;
                        $task_comment->user_id = Auth::user()->id;
                        $task_comment->comment = "Citar al $role que asistió a entrevista $prospect->first_name $prospect->last_name para iniciar proceso de contratación y labores.";
                        $task_comment->save();
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
