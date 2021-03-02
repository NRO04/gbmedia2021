<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Maintenance;
use App\Models\Maintenance\MaintenanceAlarm;
use App\Models\Settings\SettingLocation;
use App\Traits\TraitGlobal;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    use TraitGlobal;

    public function __construct()
    {
        $this->middleware('auth');

        // Access to only certain methods
        $this->middleware('permission:maintenance-tasks')->only('index');
    }


    public function index()
    {
        $locations = $this->userLocationAccess();
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view('adminModules.maintenance.maintenance')->with(compact(['user_permission', 'locations']));
    }

    public function getMaintenanceTasks(Request $request)
    {
        $data = [];

        $tasks = Maintenance::where('setting_location_id', $request->location_id)->whereIn('status_id', [1, 2])->get();

        foreach ($tasks AS $task) {
            $viewed = $this->checkUserViewMaintenanceTask($task->id, Auth::user()->id);
            $data[] = [
                'id' => $task->id,
                'viewed' => !is_null($viewed) ? $viewed->viewed : false,
                'date' => Carbon::parse($task->created_at)->format('d/M/Y'),
                'name' => $task->name,
                'is_pending' => $task->status_id == 1 ? true : false,
                'is_verified' => $task->status_id == 2 ? true : false,
                'comment' => $task->comment,
                'updated_date' => Carbon::parse($task->updated_at)->format('d/M/Y h:m a'),
            ];
        }

        return response()->json($data);
    }

    public function getFinishedMaintenanceTasks(Request $request)
    {
        $data = [];

        $tasks = Maintenance::where('setting_location_id', $request->location_id)->where('status_id', 3)->with('settingLocation')->get();

        foreach ($tasks AS $task) {
            $data[] = [
                'id' => $task->id,
                'created_date' => Carbon::parse($task->created_at)->format('d/M/Y'),
                'name' => $task->name,
                'finish_date' => Carbon::parse($task->finish_date)->format('d/M/Y'),
                'total_time' => Carbon::parse($task->finish_date)->diffForHumans($task->created_at),
                'location' => $task->settingLocation->name,
            ];
        }

        return response()->json($data);
    }

    public function createMaintenanceTask(Request $request)
    {
        $this->validate($request,
            [
                'name' => "required|unique:maintenances,name|max:128",
                'location_id' => "required",
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre del trabajo ya existe',
                'location_id.required' => 'Debe seleccionar la locación',
            ]
        );

        try {
            DB::beginTransaction();

            $maintenance_task = new Maintenance();
            $maintenance_task->name = $request->name;
            $maintenance_task->setting_location_id = $request->location_id;
            $maintenance_task->status_id = 1;
            $success = $maintenance_task->save();

            if($success) {
                $this->setAlarmToUsers($maintenance_task->id, false);
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function markAsDone(Request $request)
    {
        try {
            DB::beginTransaction();

            $maintenance_task = Maintenance::find($request->id);
            $maintenance_task->status_id = 2;
            $maintenance_task->finish_date = Carbon::now();
            $success = $maintenance_task->save();

            if($success) {
                $this->setAlarmToUsers($request->id, false);
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function markAsVerified(Request $request)
    {
        try {
            DB::beginTransaction();

            $maintenance_task = Maintenance::find($request->id);
            $maintenance_task->status_id = 3;
            $maintenance_task->finish_date = Carbon::now();
            $success = $maintenance_task->save();

            if($success) {
                $this->setAlarmToUsers($maintenance_task->id, true);
            }

            DB::commit();

            $count = MaintenanceAlarm::where('user_id', Auth::user()->id)->where('viewed', 0)->count();

            return response()->json(['success' => $success, 'count' => $count]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function markAsRejected(Request $request)
    {
        try {
            DB::beginTransaction();

            $maintenance_task = Maintenance::find($request->id);
            $maintenance_task->status_id = 1;
            $maintenance_task->comment = $request->comment;
            $maintenance_task->finish_date = Carbon::now();
            $success = $maintenance_task->save();

            if($success) {
                $this->setAlarmToUsers($request->id, false);
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function markAsViewed(Request $request)
    {
        try {
            DB::beginTransaction();

            $maintenance_task = MaintenanceAlarm::where('maintenance_id', $request->id)->where('user_id', Auth::user()->id)->first();
            $maintenance_task->viewed = 1;
            $success = $maintenance_task->save();

            DB::commit();

            $count = MaintenanceAlarm::where('user_id', Auth::user()->id)->where('viewed', 0)->count();

            return response()->json(['success' => $success, 'count' => $count]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function checkUserViewMaintenanceTask($task_id, $user_id)
    {
        return MaintenanceAlarm::where('maintenance_id', $task_id)->where('user_id', $user_id)->first();
    }

    public function setAlarmToUsers($maintenance_id, $viewed)
    {
        $maintenance_task = Maintenance::find($maintenance_id);

        $users = User::with('roles')->get();

        $user_can_view = $users->reject(function ($user, $key) {
            return !$user->hasAllPermissions('maintenance-tasks');
        });

        foreach ($user_can_view AS $user) {
            $maintenance_alarm = MaintenanceAlarm::firstOrCreate(
                ['maintenance_id' => $maintenance_task->id, 'user_id' => $user->id],
                ['maintenance_id' => $maintenance_task->id, 'user_id' => $user->id, 'viewed' => $viewed]
            );

            $maintenance_alarm->viewed = $viewed;
            $maintenance_alarm->save();
        }
    }
}
