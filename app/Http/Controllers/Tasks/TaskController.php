<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\Models\Settings\SettingRole;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskRolesReceivers;
use App\Models\Tasks\TaskUsersReceivers;
use App\Models\Tasks\TaskComment;
use App\Models\Tasks\TaskCommentAttachment;
use App\Models\Tasks\TaskUserStatus;
use App\Models\Tasks\TaskUserFolder;
use App\Traits\TraitGlobal;
use App\User;
use Auth;
use DB;
use DataTables;
use Carbon\Carbon;
use App\Events\NewMessage;
use App\Notifications\TaskCommentNotification;
use Illuminate\Support\Facades\Schema;

class TaskController extends Controller
{
    use TraitGlobal;

    public function list()
    {
        $roles = SettingRole::orderBy('name', 'ASC')->get();
        $users = User::where('setting_role_id', '!=', 14)->where('status', 1)->orderBy('first_name', 'ASC')->get();
        $models = User::where('setting_role_id', '=', 14)->where('status', 1)->orderBy('nick', 'ASC')->get();
        return view('adminModules.task.list')->with([
            'roles' => $roles,
            'users' => $users,
            'models' => $models,
        ]);
    }

    public function generateCode()
    {
        $random_letters = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3);
        $random_numbers = substr(str_shuffle("0123456789"), 0, 4);
        $code = $random_letters . $random_numbers;
        $exists = Task::where('code', '=', $code)->exists();
        if ($exists) {
            $this->generateCode();
        }
        return $code;
    }

    public function store(Request $request)
    {
        $this->folderExists('task');
        $this->validate(
            $request,
            [
                'title' => 'required|max:150',
                'comment' => 'required',
            ],
            [
                'title.required' => 'Este campo es obligatorio',
                'title.max' => 'Este campo no debe exceder los :max caracteres',
                'comment.required' => 'Este campo es obligatorio',
            ]
        );

        try {
            DB::beginTransaction();

            $receivers = $request->receivers;

            $radio_time = $request->time_aprox;
            if ($radio_time == 'option-hours') {
                $time = $request->input('select_hours');
                $time = "+" . $time . " hours";
            } else {
                $time = $request->input('select_days');
                $time = "+" . $time . " day";
            }
            $should_finish = date('Y-m-d H:i:s');
            $should_finish = strtotime($time, strtotime($should_finish));
            $should_finish = date('Y-m-d H:i:s', $should_finish);

            $task = new Task();
            $task->created_by_type = 1;
            $task->created_by = Auth::user()->id;
            $task->title = $request->input('title');
            $task->status = 0;
            $task->should_finish = $should_finish;
            $task->terminated_by = 0;
            $task->code = $this->generateCode();
            $task->save();

            $requestObj = new Request(array('receivers' => $receivers, 'task_id' => $task->id, 'from_add_receivers' => 0));
            $this->addReceivers($requestObj);


            //add all gerentes
            $users = User::where('setting_role_id', '=', 1)->get();
            foreach ($users as $key => $user) {
                $exists = TaskUserStatus::where('user_id', '=', $user->id)->where('task_id', '=', $task->id)->exists();
                if ($exists == false) {
                    $task_user_status = new TaskUserStatus();
                    $task_user_status->task_id = $task->id;
                    $task_user_status->user_id = $user->id;
                    $task_user_status->save();
                }
            }

            $exists = TaskUserStatus::where('user_id', '=', Auth::user()->id)->where('task_id', '=', $task->id)->exists();
            if ($exists == false) {
                $task_user_status = new TaskUserStatus();
                $task_user_status->task_id = $task->id;
                $task_user_status->user_id = Auth::user()->id;
                $task_user_status->save();
            }

            $task_comment = new TaskComment();
            $task_comment->task_id = $task->id;
            $task_comment->user_id = Auth::user()->id;
            $task_comment->comment = $request->input('comment');
            $task_comment->save();

            if ($request->file('inputfile')) {
                $files = $request->file('inputfile');
                foreach ($files as $file) {
                    $task_comment_attachment = new TaskCommentAttachment();
                    $task_comment_attachment->task_comments_id = $task_comment->id;
                    $task_comment_attachment->file = $this->uploadFile($file, 'task');
                    $task_comment_attachment->save();
                }
            }


            $receivers = TaskUserStatus::where('task_id',  $task->id)->get();
            foreach ($receivers as $receiver) {
                $user = $receiver->user;
                if ($user->id != Auth::user()->id) {
                    //$user->notify(new TaskCommentNotification($task_comment, $task));
                }
            }


            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function storeFolder(Request $request)
    {
        $this->validate(
            $request,
            [
                'name_folder' => 'required|max:17',
            ],
            [
                'name_folder.required' => 'Este campo es obligatorio',
                'name_folder.max' => 'Este campo no debe exceder los :max caracteres',
            ]
        );

        $folder = new TaskUserFolder();
        $folder->name = $request->name_folder;
        $folder->user_id = Auth::user()->id;
        $folder->save();
        return response()->json(['success' => true]);
    }

    public function getTasks(Request $request)
    {
        if ($request->ajax()) {

            
            //pendientes
            if ($request->seeing['status'] == 0 && $request->seeing['folder'] == 0) {               
                
                $data = Task::join('task_user_status', 'tasks.id', '=', 'task_user_status.task_id')
                    ->select("tasks.*", 'task_user_status.status as status_user', 'task_user_status.id as task_status_id', 'task_user_status.pulsing as pulsing')
                    ->where('task_user_status.user_id', '=', Auth::user()->id)
                    ->where('task_user_status.status', '=', 0)
                    ->where('task_user_status.folder', '=', 0)
                    ->where('tasks.status', '=', 0)
                    ->orderBy('pulsing', 'desc')->orderBy('task_user_status.created_at', 'asc')->get()
                    ;
            }
            //en alguna carpeta
            elseif ($request->seeing['status'] == 0 && $request->seeing['folder'] != 0) {
                
                $data = Task::join('task_user_status', 'tasks.id', '=', 'task_user_status.task_id')
                    ->select("tasks.*", 'task_user_status.status as status_user', 'task_user_status.id as task_status_id', 'task_user_status.pulsing as pulsing')
                    ->where('task_user_status.user_id', '=', Auth::user()->id)
                    ->where('task_user_status.status', '=', 0)
                    ->where('task_user_status.folder', '=', $request->seeing['folder'])
                    ->where('tasks.status', '=', 0)
                    ->orderBy('pulsing', 'desc')->orderBy('task_user_status.created_at', 'asc')->get()
                    ;
            }
            //finalizados
            else {
                
                $data = Task::join('task_user_status', 'tasks.id', '=', 'task_user_status.task_id')
                    ->select("tasks.*", 'task_user_status.status as status_user', 'task_user_status.id as task_status_id', 'task_user_status.pulsing as pulsing')
                    ->where('task_user_status.user_id', '=', Auth::user()->id)
                    ->where('task_user_status.status', '=', 1)
                    ->orWhere('task_user_status.user_id', '=', Auth::user()->id)
                    ->where('tasks.status', 1)
                    ->orderBy('pulsing', 'desc')->orderBy('task_user_status.created_at', 'asc')->get()
                    ;

               
            }

            
            if($request->seeing['customDisplay']==1){

                $records = [];

                $currentDate = date("Y-m-d h:i:sa");

                foreach($data as $record){
                                        
                    if(Auth::user()->id == $record->created_by){
                        array_push($records, $record);  
                    }
                                 
                }
                
                $data = $records; 
            
            }
                        
            if($request->seeing['customDisplay']==3){

                $records = [];

                $currentDate = date("Y-m-d h:i:sa");

                foreach($data as $record){
                    //echo $record->should_finish; 
                    $interval = (strtotime($record->should_finish) - strtotime($currentDate)) / (60 * 60); 

                    if(24 < $interval && $interval < 72){
                        array_push($records, $record);  
                    }
                                 
                }
                
                $data = $records; 
            
            }


            if($request->seeing['customDisplay']==3){

                $records = [];

                $currentDate = date("Y-m-d h:i:sa");

                foreach($data as $record){
                    //echo $record->should_finish; 
                    $interval = (strtotime($record->should_finish) - strtotime($currentDate)) / (60 * 60); 

                    if(24 < $interval && $interval < 72){
                        array_push($records, $record);  
                    }
                                 
                }
                
                $data = $records; 
            
            }

            if($request->seeing['customDisplay']==2){

                $records = [];

                $currentDate = date("Y-m-d h:i:sa");

                foreach($data as $record){
                    //echo $record->should_finish; 
                    $interval = (strtotime($record->should_finish) - strtotime($currentDate)) / (60 * 60); 

                    if($interval < 24 && $interval > 0 || 0 > $interval){
                        array_push($records, $record);  
                    }
                                 
                }
                
                $data = $records; 
            
            }

            if($request->seeing['customDisplay']==4){

                $records = [];

                $currentDate = date("Y-m-d h:i:sa");

                foreach($data as $record){
                    //echo $record->should_finish; 
                    $interval = (strtotime($record->should_finish) - strtotime($currentDate)) / (60 * 60); 

                    if(72 < $interval){
                        array_push($records, $record);  
                    }
                                 
                }
                
                $data = $records; 
            
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return 'row_' . $row->id;
                })
                ->addColumn('DT_RowClass', function ($row) {

                    $currentDate = date("Y-m-d h:i:sa");

                    $interval = (strtotime($row->should_finish) - strtotime($currentDate)) / (60 * 60);

                    $taskClass = "";

                    if ($interval < 24 && $interval > 0) {
                        $taskClass = "highUrgencyTask";
                    } else if (24 < $interval && $interval < 72) {
                        $taskClass = "midUrgencyTask";
                    } else if (0 > $interval) {
                        $taskClass = "expiredTask";
                    } else {
                        $taskClass = "lowUrgencyTask";
                    }

                    $createdByUser = "";

                    if (Auth::user()->id == $row->created_by) {
                        $createdByUser = "createByUser";
                    }

                    $taskStatus = "activeTask";

                    if ($row->status == 1) {
                        $taskStatus = "finnishedTask";
                    }

                    return $createdByUser . ' ' . $taskClass . ' ' . $taskStatus;
                })
                ->addColumn('bolt', function ($row) {
                    $active = ($row->pulsing == 1 && $row->status == 0) ? "pulsing-active" : "";
                    $result = "<i class='fas fa-bolt $active' id='pulsing-{$row->id}'></i>";
                    return $result;
                })
                ->addColumn('img', function ($row) {
                    if ($row->created_by_type == 1) {
                        $user = User::find($row->created_by);
                        $src = is_null($user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
                    } else {
                        $role = SettingRole::find($row->created_by);
                        $src = asset("assets/img/avatars/root.png");
                    }

                    $result = "<div title='" . $row->id . "' class='c-avatar'><img class='c-avatar-img' src='{$src}'><span class='c-avatar-status bg-success'></span></div>";
                    return $result;
                })
                ->addColumn('info_task', function ($row) {
                    if ($row->created_by_type == 1) {
                        $user = User::find($row->created_by);
                        if ($user != null) {
                            $created_by = ($user->setting_role_id == 14) ?  $user->nick : $user->first_name . ' ' . $user->last_name;
                            $src = global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/{$user->avatar}");
                        } else {
                            $created_by = "Error";
                            $src = url("assets/img/avatars/5.jpg");
                        }
                    } else {
                        $role = SettingRole::find($row->created_by);
                        $created_by = $role->name;
                        $src = url("assets/img/avatars/5.jpg");
                    }
                    $date = Carbon::parse($row->created_at, 'UTC');
                    $created_at = $date->isoFormat('MMMM Do YYYY, h:mm a');
                    $result = "<div title='$row->id'>{$row->title}</div><div class='small text-muted'><span title='{$row->task_id}'>Publicado por: {$created_by}</span> | {$created_at} | Nr. {$row->code}</div>";
                    return $result;
                })
                ->addColumn('info_task_extra', function ($row) {
                    if ($row->created_by_type == 1) {
                        $user = User::find($row->created_by);
                        $created_by = ($user->setting_role_id == 14) ?  $user->nick : $user->first_name . ' ' . $user->last_name;
                        $src = global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/{$user->avatar}");
                    } else {
                        $role = SettingRole::find($row->created_by);
                        $created_by = $role->name;
                        $src = url("assets/img/avatars/5.jpg");
                    }
                    $date = Carbon::parse($row->created_at, 'UTC');
                    $created_at = $date->isoFormat('MMMM Do YYYY, h:mm a');
                    $result = "<div class='small text-muted'><span title='{$row->task_id}'>Publicado por: {$created_by}</span> | {$created_at} | Nr. {$row->code}</div>";
                    return $result;
                })
                ->addColumn('time', function ($row) {
                    $date = date("Y-m-d H:i:s");
                    $clock = "1d 22:05:03";
                    $progress_xc = "100%";
                    $should_finish_date = "should_finish_date";
                    if ($row->should_finish < $date) {
                        $clock = "Caducado";
                        $progress_xc = "0%";
                        $should_finish_date = "should_finish_date_done";
                    }
                    $result = "<div class='clearfix'><div style='width: 100px' class='float-left'><small class='text-muted' id='should_finish_time-$row->id'>$clock</small></div></div><div class='progress progress-xs'><div class='' role='progressbar' style='width: $progress_xc' aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' id='should_finish_progress-$row->id'></div><input type='hidden' name='$should_finish_date' id='should_finish_date-$row->id' value='" . $row->should_finish . "'><input type='hidden' name='created_at_date' id='created_at_date-$row->id' value='" . $row->created_at . "'></div>";
                    return $result;
                })
                ->addColumn('move', function ($row) {
                    if ($row->status == 1) {
                        $user = User::find($row->terminated_by);
                        if ($user != null) {
                            $fullname = $user->first_name . " " . $user->last_name;
                        } else {
                            $fullname = "Error";
                        }

                        $result = "<div class='text-danger'>Finalizado por: {$fullname}</div><div class='small text-muted'><span>{$row->updated_at}</div>";
                    } elseif ($row->status_user == 1) {
                        $result = "<span class='text-danger p-2'>Retirado</span>";
                    } else {
                        $folders = TaskUserFolder::where('user_id', '=', Auth::user()->id)->get();
                        $items_folder = "<a class='dropdown-item' href='#' onclick='SendToFolder($row->task_status_id,0)'>Pendientes</a>";
                        foreach ($folders as $folder) {
                            $folder_id = $folder['id'];
                            $folder_name = $folder['name'];
                            $items_folder .= "<a class='dropdown-item' href='#' onclick='SendToFolder($row->task_status_id,$folder_id)'>$folder_name</a>";
                        }
                        $result = "<div class='btn-toolbar' role='toolbar'><div class='btn-group'><button class='btn btn-secondary btn-sm dropdown-toggle' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>Archivar</button><div class='dropdown-menu'>$items_folder</div></div></div>";
                    }

                    return $result;
                })

                ->rawColumns(['bolt', 'img', 'info_task', 'time', 'move', 'info_task_extra', 'DT_RowId', 'DT_RowClass'])
                ->make(true);
        }
    }

    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }

    public function getFolders(Request $request)
    {
        $active = 0;
        $result = "<div class='col-lg-12 d-flex pr-0 pl-0 text-success' id='admin-folder'>
            <div class='col-lg-2 pr-0 pl-0' style='cursor: pointer'>
                <i class='fas fa-cogs'></i>
            </div>
            <div class='col-lg-10 pr-0 pl-0'>
                <label style='cursor: pointer'> Administrar Carpetas</label>
            </div>
        </div> <hr>
        ";
        $folders = TaskUserFolder::where('user_id', '=', Auth::user()->id)->get();
        $count = TaskUserStatus::where('user_id', '=', Auth::user()->id)->where('status', '=', 0)->where('folder', '=', 0)->count();
        
        $lowUrgencyColor = "darkolivegreen";
        $midUrgencyColor = "darkgoldenrod";
        $highUrgencyColor = "brown";
        $myTaskColor = "slateBlue";

        if($request->seeing['customDisplay']==3){        
            $lowUrgencyColor = "darkolivegreen";
            $midUrgencyColor = "gold";
            $highUrgencyColor = "brown";         
            $myTaskColor = "slateBlue";
        }elseif($request->seeing['customDisplay']==2){
            $lowUrgencyColor = "darkolivegreen";
            $midUrgencyColor = "darkgoldenrod";
            $highUrgencyColor = "red";
            $myTaskColor = "slateBlue";
        }elseif($request->seeing['customDisplay']==4){
            $lowUrgencyColor = "green";
            $midUrgencyColor = "darkgoldenrod";
            $highUrgencyColor = "brown";
            $myTaskColor = "slateBlue";
        }elseif($request->seeing['customDisplay']==1){
            $lowUrgencyColor = "darkolivegreen";
            $midUrgencyColor = "darkgoldenrod";
            $highUrgencyColor = "brown";
            $myTaskColor = "DodgerBlue";
        }
        
        
        $result .=  "
            <div style='border-color: #4f5d73;'>
            <div id='myTaskFolder' class='col-lg-12 d-flex pr-0 pl-1 taskFolder' 
                            onclick='displayCustomTask(this.id)' 
                            style='cursor: pointer;color:$myTaskColor '>
                            <div class='col-lg-2 pr-0 pl-0'>
                                <i class='fa fa-inbox'></i>
                            </div>
                            <div class='col-lg-10 pr-0 pl-0'>
                                <label style='cursor: pointer'> Mis Trabajos</label>
                            </div>
                            
                        </div>";

        $result .=  "          
            <div id='highUrgencyFolder' class='col-lg-12 d-flex pr-0 pl-1 taskFolder' 
                            onclick='displayCustomTask(this.id)' 
                            style='cursor: pointer;color:$highUrgencyColor'>
                            <div class='col-lg-2 pr-0 pl-0'>
                                <i class='fas fa-arrow-alt-circle-up'></i>
                            </div>
                            <div class='col-lg-10 pr-0 pl-0'>
                                <label style='cursor: pointer'> Urgentes</label>
                            </div>
                            
                        </div>";

            $result .=  "<div id='midUrgencyFolder' class='col-lg-12 d-flex pr-0 pl-1 taskFolder' 
                            onclick='displayCustomTask(this.id)' 
                            style='cursor: pointer;color:$midUrgencyColor'>
                            <div class='col-lg-2 pr-0 pl-0'>
                                <i class='fas fa-arrow-alt-circle-right'></i>
                            </div>
                            <div class='col-lg-10 pr-0 pl-0'>
                                <label style='cursor: pointer'> Media</label>
                            </div>
                        </div>";

            $result .=  "<div id='lowUrgencyFolder' class='col-lg-12 d-flex pr-0 pl-1 taskFolder' 
                            onclick='displayCustomTask(this.id)' 
                            style='cursor: pointer;color:$lowUrgencyColor'>
                            <div class='col-lg-2 pr-0 pl-0'>
                                <i class='fas fa-arrow-alt-circle-down'></i>
                            </div>
                            <div class='col-lg-10 pr-0 pl-0'>
                                <label style='cursor: pointer'> Baja</label>
                            </div>
                        </div>
                    </div> <hr>
                    ";
        if ($request->seeing['status'] == 0 && $request->seeing['folder'] == 0)
            $active = 1;
        $result .= $this->createFolderStructure("Pendientes", $count, 0, 0, $active);

        foreach ($folders as $folder) {
            $active = 0;
            if ($request->seeing['status'] == 0 && $request->seeing['folder'] == $folder['id'])
                $active = 1;

            $count = TaskUserStatus::where('user_id', '=', Auth::user()->id)->where('folder', '=', $folder['id'])->count();
            $result .= $this->createFolderStructure($folder['name'], $count, 0, $folder['id'], $active);
        }

        $active = 0;
        if ($request->seeing['status'] == 1)
            $active = 1;

        $count = TaskUserStatus::where('user_id', '=', Auth::user()->id)->where('status', '=', 1)->count();
        $result .= $this->createFolderStructure("Finalizados", 0, 1, 0, $active);



        return $result;
    }

    public function createFolderStructure($folder_name, $badge, $status, $folder, $active)
    {
        $icon = ($status == 1) ? "fa fa-trash" : (($status == 0 && $folder == 0) ? "fas fa-bolt" : "fa fa-folder");
        $active = ($active == 1) ? "" : "text-muted";

        $badge = ($badge == 0) ? "" : "<span class='badge badge-pill $active badge-secondary' style='position: absolute; top: 4px; right: 2px'>$badge</span>";
        $result = "<div class='col-lg-12 d-flex pr-0 pl-1 $active' onclick='ShowTaskFolder($status,$folder)' style='cursor: pointer'>
            <div class='col-lg-2 pr-0 pl-0'>
                <i class='$icon'></i>
            </div>
            <div class='col-lg-10 pr-0 pl-0'>
                <label style='cursor: pointer'> $folder_name $badge</label>
            </div>
        </div>";
        return $result;
    }

    public function updateFolderDestination(Request $request)
    {
        $task_status = TaskUserStatus::find($request->task_status_id);
        $task_status->folder = $request->folder;
        $task_status->status = 0;
        $task_status->save();
    }

    public function updateFolderName(Request $request)
    {
        $task_folder_id = $request->id;
        $task_folder = TaskUserFolder::find($task_folder_id);
        $task_folder->name = $request->value;
        $task_folder->save();
    }

    public function deleteFolder(Request $request)
    {
        try {
            DB::beginTransaction();

            $task_folder_id = $request->id;
            $task_folder = TaskUserFolder::find($task_folder_id);

            $task_status = TaskUserStatus::where('folder', '=', $task_folder_id)->get();
            foreach ($task_status as $status) {
                $status->folder = 0;
                $status->save();
            }

            $task_folder->delete();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function getAdminFolders()
    {
        $folders = TaskUserFolder::where('user_id', '=', Auth::user()->id)->get();
        $alert = "<div class='alert alert-primary col-lg-5' role='alert'>This is a primary alert—check it out!</div>";
        $result = "<table class='table table-hover table-striped'><tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>";
        foreach ($folders as $key => $folder) {
            $folder_id = $folder['id'];
            $folder_name = $folder['name'];
            $result .= "<tr id='tr-folder-$folder_id'>";
            $result .= "<td>" . ($key + 1) . "</td>";
            $result .= "<td><input class='form-control' name='edit_folder_name' id='folder-$folder_id' value='$folder_name' onkeyup='updateFolderName($folder_id)'></td>";
            $result .= "<td><button class='btn btn-danger btn-sm' onclick='deleteFolder($folder_id)'><i class='fa fa-trash'></i></button></td>";
            $result .= "</tr>";
        }
        $result .= "</table>";
        echo $result;
    }

    public function titleComment(Request $request)
    {
        $task = Task::where('id', '=', $request->id)->first();
        if ($task->created_by_type == 1) {
            $user = User::where('id', '=', $task->created_by)->get();
            $created_by = ($user[0]->setting_role_id == 14) ?  $user[0]->nick : $user[0]->first_name . ' ' . $user[0]->last_name;
            $src = is_null($user[0]->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user[0]->avatar);
        } else {
            $role = SettingRole::where('id', '=', $task->created_by)->get();
            $created_by = $role[0]->name;
            $src = url("assets/img/avatars/root.png");
        }
        $date = Carbon::parse($task->created_at, 'UTC');
        $created_at = $date->isoFormat('MMMM Do YYYY, h:mm a');
        $finish_in = Carbon::parse($task->should_finish)->diffForHumans();

        $result = "
                    <div class='col-lg-1'>
                    <div class='c-avatar'>
                        <img class='c-avatar-img' src='{$src}'>
                        <span class='c-avatar-status bg-success'></span>
                    </div>
                    </div>
                    <div class='col-lg-11'>
                        <h5 class='text-success'>" . $this->accents($task->title) . "</h5>
                        Publicado por: {$created_by} <span class='small text-muted'> | {$created_at} | Nr. {$task->code}</span> <span class='small text-muted text-muted'>| Finalización: $finish_in</span>
                    </div>";
        $permission['extend_time'] = (Auth::user()->can('task-time') || Auth::user()->id == $task->created_by) ? 1 : 0;
        $permission['add_receivers'] = (Auth::user()->can('task-receivers-create') || Auth::user()->id == $task->created_by) ? 1 : 0;
        $permission['remove_receivers'] = (Auth::user()->can('task-receivers-delete')) ? 1 : 0;
        $permission['finalized'] = (Auth::user()->can('task-finalized') || Auth::user()->id == $task->created_by) ? 1 : 0;
        return response()->json(['result' => $result, 'permission' => $permission]);
    }

    public function receiversComments(Request $request)
    {
        $receivers = "";
        $task_roles = TaskRolesReceivers::where('task_id', '=', $request->id)->get();
        foreach ($task_roles as $key => $task_role) {
            $receivers .= ($receivers == "") ? $task_role->role->name : ", " . $task_role->role->name;
        }

        $task_users = TaskUsersReceivers::where('task_id', '=', $request->id)->get();
        foreach ($task_users as $key => $task_user) {
            $full_name = ($task_user->user->setting_role_id == 14) ? $task_user->user->nick : $task_user->user->first_name . " " . $task_user->user->last_name;
            $receivers .= ($receivers == "") ? $full_name : ", " . $full_name;
        }
        $receivers = "Recipientes: <span class='text-info'>" . $receivers . "</span>";

        return $receivers;
    }

    public function contentComments(Request $request)
    {
        $result["comments"] = "";
        $result["terminated"] = 0;
        $result["signed_out"] = 0;

        $task = Task::find($request->id);
        $task_user_status = TaskUserStatus::where('task_id', $request->id)->where('user_id', Auth::user()->id)->get();

        if ($task->status == 1)
            $result["terminated"] = 1;
        if ($task_user_status[0]->status == 1)
            $result["signed_out"] = 1;


        $task_comments = TaskComment::where('task_id', '=', $request->id)->get();
        foreach ($task_comments as $key => $comment) {

            $full_name = ($comment->user->setting_role_id == 14) ? $comment->user->nick : $comment->user->first_name . " " . $comment->user->last_name;

            $files = TaskCommentAttachment::where('task_comments_id', $comment->id)->get();
            $content_files = "";
            foreach ($files as $key => $file) {
                $type = explode(".", $file->file);
                $type = $type[1];
                $type = strtolower($type);

                if ($type == "jpg" || $type == "jpeg"  || $type == "png") {
                    $url = "../../storage/app/public/" . tenant('studio_slug') . "/task/$file->file";
                    $content_files .= "<div class='col-lg-1 border border-info gallery mr-1 bg-dark' style='cursor: zoom-in; border-radius: 3px; padding: 5px; '>
                        <a href='$url'>
                        <img style=' height: 32px; margin-left:20px; cursor: zoom-in;' src='" . asset("images/svg/image.svg") . "'>
                        </a>
                    </div>";
                } else {

                    $svg = "contract.svg";
                    if ($type == "csv" || $type == "xls" || $type == "xlsx")
                        $svg = "excel.svg";
                    if ($type == "doc" || $type == "docx")
                        $svg = "word.svg";
                    if ($type == "pdf")
                        $svg = "pdf.svg";

                    $doc = '"' . $file->file . '"';
                    $type = '"' . $type . '"';

                    $content_files .= "<div class='col-lg-1 border border-info mr-1 bg-dark' style='cursor: pointer; border-radius: 3px; padding: 5px; '>
                        <img onclick='embedDocuments($doc, $type)' style='height: 32px; margin-left:20px;' src='" . asset("images/svg/$svg") . "'></div>";
                }
            }

            $src = is_null($comment->user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $comment->user->avatar);
            $result["comments"] .=
                "<div class='card mb-2'>
                    <div class='card-body p-2 row'>
                        <div class='col-12 mb-3'>
                            <div class='row'>
                                <div class='col-sm-1 text-center'>
                                     <div class='c-avatar'>
                                        <img class='c-avatar-img' src='{$src}'>
                                        <span class='c-avatar-status bg-success'></span>
                                    </div>
                                </div>
                                <div class='col-11 pl-0'>
                                    <h6 class='text-muted mb-0'>{$full_name}</h6>
                                    <small class='text-muted'>" . ucfirst( Carbon::now() ->diffForHumans(Carbon::parse($comment->created_at),true,false,6) )  . " (" . Carbon::parse($comment->created_at)->format('d/M/Y h:i a') . ")</small>
                                </div>
                            </div>
                        </div>
                        <div class='col-sm-12'>
                            " . $this->accents($comment->comment) . "
                        </div>
                            <div class='col-12'>
                                <div class='col-12'>
                                    <div class='row'>
                                        $content_files
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>";
        }

        $task_id = $request->id;
        //$this->notificationSeen($task_id);
        //event(new NewMessage());
        return $result;
    }

    public function notificationSeen($task_id)
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications->where("type", "App\Notifications\TaskCommentNotification")->all();
        foreach ($notifications as $notification) {
            if ($notification->data['task_id'] == $task_id) {
                $notification->markAsRead();
            }
        }
    }

    public function createComments(Request $request)
    {
        $this->folderExists('task');
        $this->validate(
            $request,
            [
                'replaycomment' => 'required',
            ],
            [
                'replaycomment.required' => 'Este campo es obligatorio',
            ]
        );

        $task = Task::find($request->task_id);
        $task_user_status = TaskUserStatus::where('task_id', $request->task_id)->where('user_id', Auth::user()->id)->get();

        if ($task->status == 1)
            return response()->json(['success' => false]);
        if ($task_user_status[0]->status == 1)
            return response()->json(['success' => false]);


        try {
            DB::beginTransaction();

            $task_comment = new TaskComment();
            $task_comment->task_id = $request->input('task_id');
            $task_comment->user_id = Auth::user()->id;
            $task_comment->comment = $request->input('replaycomment');
            $task_comment->save();

            TaskUserStatus::where('task_id', $request->task_id)->where('user_id', "!=", Auth::user()->id)->update([
                'pulsing' => 1,
            ]);

            if ($request->file('inputfileComment')) {
                $files = $request->file('inputfileComment');
                foreach ($files as $file) {
                    $task_comment_attachment = new TaskCommentAttachment();
                    $task_comment_attachment->task_comments_id = $task_comment->id;
                    $task_comment_attachment->file = $this->uploadFile($file, 'task');
                    $task_comment_attachment->save();
                }
            }

            /*$message = $request->input('replaycomment');
            event(new NewMessage($message));*/
            //de esta forma yo no recibo el evento
            //broadcast(new NewMessage($message))->toOthers();

            $task = $task_comment->task;
            $receivers = TaskUserStatus::where('task_id',  $request->input('task_id'))->get();
            foreach ($receivers as $receiver) {
                $user = $receiver->user;
                if ($user->id != Auth::user()->id) {
                    //$user->notify(new TaskCommentNotification($task_comment, $task));
                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function notifications(Request $request)
    {
        $result = [];
        $notifications = $request->user()->unreadNotifications->where('type', 'App\Notifications\TaskCommentNotification')->all();
        foreach ($notifications as $notification) {
            $result[] = $notification;
        }
        return $result;
    }

    public function updatePulsing(Request $request)
    {
        $task_user_status = TaskUserStatus::where("task_id", $request->task_id)->where("user_id", Auth::user()->id)->get();
        $task_user_status = TaskUserStatus::find($task_user_status[0]->id);
        $task_user_status->pulsing = $request->pulsing;
        $task_user_status->save();
    }

    public function signOutTask(Request $request)
    {
        try {
            DB::beginTransaction();

            $task = Task::find($request->task_id);
            if ($task->created_by_type == 1 && $task->created_by == Auth::user()->id) {
                return response()->json(['success' => false]);
            }

            $task_user_status = TaskUserStatus::where("task_id", $request->task_id)->where("user_id", Auth::user()->id)->first();
            $task_user_status->status = 1;
            $task_user_status->pulsing = 0;
            $task_user_status->folder = 0;
            $task_user_status->save();

            $task_comment = new TaskComment;
            $task_comment->task_id = $request->task_id;
            $task_comment->user_id = Auth::user()->id;
            $task_comment->comment = "<span class='text-danger'>Se ha retirado de este trabajo<span>";
            $task_comment->save();

            $this->removeUsersReceivers($request->task_id, Auth::user()->id);

            /*$receivers = TaskUserStatus::where('task_id', $request->task_id)->get();
            foreach ($receivers as $receiver) {
                $user = $receiver->user;
                if ($user->id != Auth::user()->id)
                {
                    $user->notify(new TaskCommentNotification($task_comment, $task));
                }

            }*/

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function removeUsersReceivers($task_id, $user_id)
    {
        TaskUsersReceivers::where("task_id", $task_id)->where("user_id", $user_id)->delete();
    }

    public function noInRolesReceivers(Request $request)
    {
        $roles = SettingRole::orderBy('name', 'ASC')->get();
        $result = [];
        $cont = 0;
        foreach ($roles as $role) {
            $exists = TaskRolesReceivers::where('task_id', $request->task_id)->where('setting_role_id', $role->id)->exists();
            if ($exists == false) {
                $result[$cont]['id'] = $role->id;
                $result[$cont]['name'] = $role->name;
                $cont++;
            }
        }
        return response()->json($result);
    }

    public function noInUsersReceivers(Request $request)
    {
        if ($request->type == 0) {
            $users = User::where('setting_role_id', '!=', 14)->where('status', 1)->where('is_admin', 0)->orderBy('first_name', 'ASC')->get();
        } else {
            $users = User::where('setting_role_id', 14)->where('status', 1)->where('is_admin', 0)->orderBy('nick', 'ASC')->get();
        }

        $task = Task::find($request->task_id);

        $result = [];
        $cont = 0;
        foreach ($users as $user) {
            if ($task->created_by_type == 1 && $task->created_by == $user->id) {
                continue;
            }

            $exists = TaskUsersReceivers::where('task_id', $request->task_id)->where('user_id', $user->id)->exists();
            if ($exists == false) {
                $result[$cont]['id'] = $user->id;
                if ($request->type == 0)
                    $result[$cont]['fullname'] = $user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name . ' ' . $user->second_last_name;
                else
                    $result[$cont]['fullname'] = $user->nick;

                $cont++;
            }
        }
        return response()->json($result);
    }

    public function addReceivers(Request $request)
    {
        $receivers = json_decode($request->receivers);
        $to_roles = $receivers->to_roles;
        $to_users = $receivers->to_users;
        $to_models = $receivers->to_models;
        $comment = "";

        try {
            DB::beginTransaction();

            //add roles to receivers and users to status
            for ($i = 0; $i < count($to_roles); $i++) {

                $exists = TaskRolesReceivers::where('setting_role_id', '=', $to_roles[$i]->id)->where('task_id', '=', $request->task_id)->exists();

                if ($exists == false) {
                    $task_roles_receivers = new TaskRolesReceivers();
                    $task_roles_receivers->task_id = $request->task_id;
                    $task_roles_receivers->setting_role_id = $to_roles[$i]->id;
                    $task_roles_receivers->save();

                    $comment .= ($comment == "") ?  $to_roles[$i]->name : ", " . $to_roles[$i]->name;
                }

                $users = User::where('setting_role_id', '=', $to_roles[$i]->id)->get();
                foreach ($users as $key => $user) {
                    $exists = TaskUserStatus::where('user_id', '=', $user->id)->where('task_id', '=', $request->task_id)->exists();
                    if ($exists == false) {
                        $task_user_status = new TaskUserStatus();
                        $task_user_status->task_id = $request->task_id;
                        $task_user_status->user_id = $user->id;
                        $task_user_status->save();
                    }
                }
            }
            //add users to receivers and status
            for ($i = 0; $i < count($to_users); $i++) {
                $exists = TaskUsersReceivers::where('user_id', '=', $to_users[$i]->id)->where('task_id', '=', $request->task_id)->exists();

                if ($exists == false) {
                    $task_users_receivers = new TaskUsersReceivers();
                    $task_users_receivers->task_id = $request->task_id;
                    $task_users_receivers->user_id = $to_users[$i]->id;
                    $task_users_receivers->save();

                    $comment .= ($comment == "") ?  $to_users[$i]->name : ", " . $to_users[$i]->name;
                }

                $exists = TaskUserStatus::where('user_id', '=', $to_users[$i]->id)->where('task_id', '=', $request->task_id)->exists();

                if ($exists == false) {
                    $task_users_status = new TaskUserStatus();
                    $task_users_status->task_id = $request->task_id;
                    $task_users_status->user_id = $to_users[$i]->id;
                    $task_users_status->save();
                }
            }

            //add models to receivers and status
            for ($i = 0; $i < count($to_models); $i++) {
                $exists = TaskUsersReceivers::where('user_id', '=', $to_models[$i]->id)->where('task_id', '=', $request->task_id)->exists();

                if ($exists == false) {
                    $task_users_receivers = new TaskUsersReceivers();
                    $task_users_receivers->task_id = $request->task_id;
                    $task_users_receivers->user_id = $to_models[$i]->id;
                    $task_users_receivers->save();

                    $comment .= ($comment == "") ?  $to_models[$i]->name : ", " . $to_models[$i]->name;
                }

                $exists = TaskUserStatus::where('user_id', '=', $to_models[$i]->id)->where('task_id', '=', $request->task_id)->exists();
                if ($exists == false) {
                    $task_user_status = new TaskUserStatus();
                    $task_user_status->task_id = $request->task_id;
                    $task_user_status->user_id = $to_models[$i]->id;
                    $task_user_status->save();
                }
            }

            if ($request->from_add_receivers == 1) {
                $task_comment = new TaskComment();
                $task_comment->task_id = $request->task_id;
                $task_comment->user_id = Auth::user()->id;
                $task_comment->comment = "Recipiente agregado: <span class='text-success'>" . $comment . "</span>";
                $task_comment->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function getReceiversJson(Request $request)
    {
        $to_roles = [];
        $to_users = [];

        $cont = 0;
        $task_roles = TaskRolesReceivers::where('task_id', '=', $request->task_id)->get();
        foreach ($task_roles as $key => $task_role) {
            $to_roles[$cont]['id'] = $task_role->setting_role_id;
            $to_roles[$cont]['name'] = $task_role->role->name;
            $cont++;
        }

        $cont = 0;
        $task_users = TaskUsersReceivers::where('task_id', '=', $request->task_id)->get();
        foreach ($task_users as $key => $task_user) {

            $full_name = ($task_user->user->setting_role_id == 14) ? $task_user->user->nick : $task_user->user->first_name . " " . $task_user->user->last_name;
            $to_users[$cont]['id'] = $task_user->user_id;
            $to_users[$cont]['name'] = $full_name;
            $cont++;
        }
        $receivers['to_roles'] = $to_roles;
        $receivers['to_users'] = $to_users;

        return $receivers;
    }

    public function removeReceivers(Request $request)
    {
        $receivers = json_decode($request->receivers);
        $to_roles = $receivers->to_roles;
        $to_users = $receivers->to_users;
        $comment = "";

        try {
            DB::beginTransaction();

            //remove roles to receivers and users to status
            for ($i = 0; $i < count($to_roles); $i++) {

                $comment .= ($comment == "") ?  $to_roles[$i]->name : ", " . $to_roles[$i]->name;

                $task_roles_receivers = TaskRolesReceivers::where('setting_role_id', '=', $to_roles[$i]->id)->where('task_id', '=', $request->task_id)->get();

                $task_roles_receivers = TaskRolesReceivers::find($task_roles_receivers[0]->id);
                $task_roles_receivers->delete();

                $users = User::where('setting_role_id', '=', $to_roles[$i]->id)->get();
                foreach ($users as $key => $user) {
                    $task_user_status = TaskUserStatus::where('user_id', '=', $user->id)->where('task_id', '=', $request->task_id)->get();
                    $task_user_status = TaskUserStatus::find($task_user_status[0]->id);
                    $task_user_status->delete();
                }
            }

            //remove users to receivers and status
            for ($i = 0; $i < count($to_users); $i++) {

                $comment .= ($comment == "") ?  $to_users[$i]->name : ", " . $to_users[$i]->name;

                $this->removeUsersReceivers($request->task_id, $to_users[$i]->id);

                $task_user_status = TaskUserStatus::where('user_id', '=', $to_users[$i]->id)->where('task_id', '=', $request->task_id)->get();
                $task_user_status = TaskUserStatus::find($task_user_status[0]->id);
                $task_user_status->delete();
            }

            $task_comment = new TaskComment();
            $task_comment->task_id = $request->task_id;
            $task_comment->user_id = Auth::user()->id;
            $task_comment->comment = "Recipiente eliminado: <span class='text-danger'>" . $comment . "</span>";
            $task_comment->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function extendTime(Request $request)
    {
        try {
            DB::beginTransaction();
            $radio_time = $request->time_aprox_extend;
            if ($radio_time == 'option-hours_extend') {
                $time = $request->input('select_hours_extend');
                $time = "+" . $time . " hours";
            } else {
                $time = $request->input('select_days_extend');
                $time = "+" . $time . " day";
            }

            $task = Task::find($request->task_id);
            $should_finish = $task->should_finish;

            /** 
             * Si el trabajo está caducado se extiende el tiempo a partir de la fecha actual,
             * de lo contrario se extiende a partir de la fecha de finalización
             *  **/
            if ($should_finish <= Carbon::now()) {
                $should_finish = strtotime($time, strtotime(Carbon::now()));
                $should_finish = date('Y-m-d H:i:s', $should_finish);
            } else {
                $should_finish = strtotime($time, strtotime($should_finish));
                $should_finish = date('Y-m-d H:i:s', $should_finish);
            }

            $task->should_finish = $should_finish;
            $task->save();

            $comment = ($radio_time == 'option-hours_extend') ? (($request->select_hours_extend == 1) ? $request->select_hours_extend . " hora" : $request->select_hours_extend . " horas") : (($request->select_days_extend == 1) ? $request->select_days_extend . " dia" : $request->select_days_extend . " dias");

            $task_comment = new TaskComment();
            $task_comment->task_id = $request->task_id;
            $task_comment->user_id = Auth::user()->id;
            $task_comment->comment = "Se ha alargado el tiempo en : <span class='text-info'>" . $comment . "</span>";
            $task_comment->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function finishTask(Request $request)
    {
        try {
            DB::beginTransaction();

            $task = Task::find($request->task_id);
            $task->status = 1;
            $task->terminated_by = Auth::user()->id;
            $task->save();

            DB::table('task_user_status')->where('task_id', $request->task_id)->update([
                'folder' => 0,
                'pulsing' => 0,
                'status' => 1,
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    function clean($string)
    {
        $not_allowed = [
            "ðŸ˜„",
            "ðŸ˜ƒ",
            "ðŸ˜€",
            "ðŸ˜Š",
            "â˜ºï¸",
            "ðŸ˜‰",
            "ðŸ˜",
            "ðŸ˜˜",
            "ðŸ˜š",
            "ðŸ˜—",
            "ðŸ˜™",
            "ðŸ˜œ",
            "ðŸ˜",
            "ðŸ˜›",
            "ðŸ˜³",
            "ðŸ˜",
            "ðŸ˜”",
            "ðŸ˜Œ",
            "ðŸ˜’",
            "ðŸ˜ž",
            "ðŸ˜£",
            "ðŸ˜¢",
            "ðŸ˜‚",
            "ðŸ˜­",
            "ðŸ˜ª",
            "ðŸ˜¥",
            "ðŸ˜°",
            "ðŸ˜…",
            "ðŸ˜“",
            "ðŸ˜©",
            "ðŸ˜«",
            "ðŸ˜¨",
            "ðŸ˜±",
            "ðŸ˜ ",
            "ðŸ˜¡",
            "ðŸ˜¤",
            "ðŸ˜–",
            "ðŸ˜†",
            "ðŸ˜‹",
            "ðŸ˜·",
            "ðŸ˜Ž",
            "ðŸ˜´",
            "ðŸ˜µ",
            "ðŸ˜²",
            "ðŸ˜Ÿ",
            "ðŸ˜¦",
            "ðŸ˜§",
            "ðŸ˜ˆ",
            "ðŸ‘¿",
            "ðŸ˜®",
            "ðŸ˜¬",
            "ðŸ˜",
            "ðŸ˜•",
            "ðŸ˜¯",
            "ðŸ˜¶",
            "ðŸ˜‡",
            "ðŸ˜",
            "ðŸ˜‘",
            "ðŸ‘²",
            "ðŸ‘²ðŸ»",
            "ðŸ‘²ðŸ¼",
            "ðŸ‘²ðŸ½",
            "ðŸ‘²ðŸ¾",
            "ðŸ‘²ðŸ¿",
            "ðŸ‘³",
            "ðŸ‘³ðŸ»",
            "ðŸ‘³ðŸ¼",
            "ðŸ‘³ðŸ½",
            "ðŸ‘³ðŸ¾",
            "ðŸ‘³ðŸ¿",
            "ðŸ‘®",
            "ðŸ‘®ðŸ»",
            "ðŸ‘®ðŸ¼",
            "ðŸ‘®ðŸ½",
            "ðŸ‘®ðŸ¾",
            "ðŸ‘®ðŸ¿",
            "ðŸ‘·",
            "ðŸ‘·ðŸ»",
            "ðŸ‘·ðŸ¼",
            "ðŸ‘·ðŸ½",
            "ðŸ‘·ðŸ¾",
            "ðŸ‘·ðŸ¿",
            "ðŸ’‚",
            "ðŸ’‚ðŸ»",
            "ðŸ’‚ðŸ¼",
            "ðŸ’‚ðŸ½",
            "ðŸ’‚ðŸ¾",
            "ðŸ’‚ðŸ¿",
            "ðŸ‘¶",
            "ðŸ‘¶ðŸ»",
            "ðŸ‘¶ðŸ¼",
            "ðŸ‘¶ðŸ½",
            "ðŸ‘¶ðŸ¾",
            "ðŸ‘¶ðŸ¿",
            "ðŸ‘¦",
            "ðŸ‘¦ðŸ»",
            "ðŸ‘¦ðŸ¼",
            "ðŸ‘¦ðŸ½",
            "ðŸ‘¦ðŸ¾",
            "ðŸ‘¦ðŸ¿",
            "ðŸ‘§",
            "ðŸ‘§ðŸ»",
            "ðŸ‘§ðŸ¼",
            "ðŸ‘§ðŸ½",
            "ðŸ‘§ðŸ¾",
            "ðŸ‘§ðŸ¿",
            "ðŸ‘¨",
            "ðŸ‘¨ðŸ»",
            "ðŸ‘¨ðŸ¼",
            "ðŸ‘¨ðŸ½",
            "ðŸ‘¨ðŸ¾",
            "ðŸ‘¨ðŸ¿",
            "ðŸ‘©",
            "ðŸ‘©ðŸ»",
            "ðŸ‘©ðŸ¼",
            "ðŸ‘©ðŸ½",
            "ðŸ‘©ðŸ¾",
            "ðŸ‘©ðŸ¿",
            "ðŸ‘´",
            "ðŸ‘´ðŸ»",
            "ðŸ‘´ðŸ¼",
            "ðŸ‘´ðŸ½",
            "ðŸ‘´ðŸ¾",
            "ðŸ‘´ðŸ¿",
            "ðŸ‘µ",
            "ðŸ‘µðŸ»",
            "ðŸ‘µðŸ¼",
            "ðŸ‘µðŸ½",
            "ðŸ‘µðŸ¾",
            "ðŸ‘µðŸ¿",
            "ðŸ‘±",
            "ðŸ‘±ðŸ»",
            "ðŸ‘±ðŸ¼",
            "ðŸ‘±ðŸ½",
            "ðŸ‘±ðŸ¾",
            "ðŸ‘±ðŸ¿",
            "ðŸ‘¼",
            "ðŸ‘¼ðŸ»",
            "ðŸ‘¼ðŸ¼",
            "ðŸ‘¼ðŸ½",
            "ðŸ‘¼ðŸ¾",
            "ðŸ‘¼ðŸ¿",
            "ðŸ‘¸",
            "ðŸ‘¸ðŸ»",
            "ðŸ‘¸ðŸ¼",
            "ðŸ‘¸ðŸ½",
            "ðŸ‘¸ðŸ¾",
            "ðŸ‘¸ðŸ¿",
            "ðŸ˜º",
            "ðŸ˜¸",
            "ðŸ˜»",
            "ðŸ˜½",
            "ðŸ˜¼",
            "ðŸ™€",
            "ðŸ˜¿",
            "ðŸ˜¹",
            "ðŸ˜¾",
            "ðŸ‘¹",
            "ðŸ‘º",
            "ðŸ™ˆ",
            "ðŸ™‰",
            "ðŸ™Š",
            "ðŸ’€",
            "ðŸ‘½",
            "ðŸ’©",
            "ðŸ”¥",
            "âœ¨",
            "ðŸŒŸ",
            "ðŸ’«",
            "ðŸ’¥",
            "ðŸ’¢",
            "ðŸ’¦",
            "ðŸ’§",
            "ðŸ’¤",
            "ðŸ’¨",
            "ðŸ‘‚",
            "ðŸ‘‚ðŸ»",
            "ðŸ‘‚ðŸ¼",
            "ðŸ‘‚ðŸ½",
            "ðŸ‘‚ðŸ¾",
            "ðŸ‘‚ðŸ¿",
            "ðŸ‘€",
            "ðŸ‘ƒ",
            "ðŸ‘ƒðŸ»",
            "ðŸ‘ƒðŸ¼",
            "ðŸ‘ƒðŸ½",
            "ðŸ‘ƒðŸ¾",
            "ðŸ‘ƒðŸ¿",
            "ðŸ‘…",
            "ðŸ‘„",
            "ðŸ‘",
            "ðŸ‘ðŸ»",
            "ðŸ‘ðŸ¼",
            "ðŸ‘ðŸ½",
            "ðŸ‘ðŸ¾",
            "ðŸ‘ðŸ¿",
            "ðŸ‘Ž",
            "ðŸ‘ŽðŸ»",
            "ðŸ‘ŽðŸ¼",
            "ðŸ‘ŽðŸ½",
            "ðŸ‘ŽðŸ¾",
            "ðŸ‘ŽðŸ¿",
            "ðŸ‘Œ",
            "ðŸ‘ŒðŸ»",
            "ðŸ‘ŒðŸ¼",
            "ðŸ‘ŒðŸ½",
            "ðŸ‘ŒðŸ¾",
            "ðŸ‘ŒðŸ¿",
            "ðŸ‘Š",
            "ðŸ‘ŠðŸ»",
            "ðŸ‘ŠðŸ¼",
            "ðŸ‘ŠðŸ½",
            "ðŸ‘ŠðŸ¾",
            "ðŸ‘ŠðŸ¿",
            "âœŠ",
            "âœŠðŸ»",
            "âœŠðŸ¼",
            "âœŠðŸ½",
            "âœŠðŸ¾",
            "âœŠðŸ¿",
            "âœŒï¸",
            "âœŒðŸ»",
            "âœŒðŸ¼",
            "âœŒðŸ½",
            "âœŒðŸ¾",
            "âœŒðŸ¿",
            "ðŸ‘‹",
            "ðŸ‘‹ðŸ»",
            "ðŸ‘‹ðŸ¼",
            "ðŸ‘‹ðŸ½",
            "ðŸ‘‹ðŸ¾",
            "ðŸ‘‹ðŸ¿",
            "âœ‹",
            "âœ‹ðŸ»",
            "âœ‹ðŸ¼",
            "âœ‹ðŸ½",
            "âœ‹ðŸ¾",
            "âœ‹ðŸ¿",
            "ðŸ‘",
            "ðŸ‘ðŸ»",
            "ðŸ‘ðŸ¼",
            "ðŸ‘ðŸ½",
            "ðŸ‘ðŸ¾",
            "ðŸ‘ðŸ¿",
            "ðŸ‘†",
            "ðŸ‘†ðŸ»",
            "ðŸ‘†ðŸ¼",
            "ðŸ‘†ðŸ½",
            "ðŸ‘†ðŸ¾",
            "ðŸ‘†ðŸ¿",
            "ðŸ‘‡",
            "ðŸ‘‡ðŸ»",
            "ðŸ‘‡ðŸ¼",
            "ðŸ‘‡ðŸ½",
            "ðŸ‘‡ðŸ¾",
            "ðŸ‘‡ðŸ¿",
            "ðŸ‘‰",
            "ðŸ‘‰ðŸ»",
            "ðŸ‘‰ðŸ¼",
            "ðŸ‘‰ðŸ½",
            "ðŸ‘‰ðŸ¾",
            "ðŸ‘‰ðŸ¿",
            "ðŸ‘ˆ",
            "ðŸ‘ˆðŸ»",
            "ðŸ‘ˆðŸ¼",
            "ðŸ‘ˆðŸ½",
            "ðŸ‘ˆðŸ¾",
            "ðŸ‘ˆðŸ¿",
            "ðŸ™Œ",
            "ðŸ™ŒðŸ»",
            "ðŸ™ŒðŸ¼",
            "ðŸ™ŒðŸ½",
            "ðŸ™ŒðŸ¾",
            "ðŸ™ŒðŸ¿",
            "ðŸ™",
            "ðŸ™ðŸ»",
            "ðŸ™ðŸ¼",
            "ðŸ™ðŸ½",
            "ðŸ™ðŸ¾",
            "ðŸ™ðŸ¿",
            "â˜ï¸",
            "â˜ðŸ»",
            "â˜ðŸ¼",
            "â˜ðŸ½",
            "â˜ðŸ¾",
            "â˜ðŸ¿",
            "ðŸ‘",
            "ðŸ‘ðŸ»",
            "ðŸ‘ðŸ¼",
            "ðŸ‘ðŸ½",
            "ðŸ‘ðŸ¾",
            "ðŸ‘ðŸ¿",
            "ðŸ’ª",
            "ðŸ’ªðŸ»",
            "ðŸ’ªðŸ¼",
            "ðŸ’ªðŸ½",
            "ðŸ’ªðŸ¾",
            "ðŸ’ªðŸ¿",
            "ðŸš¶",
            "ðŸš¶ðŸ»",
            "ðŸš¶ðŸ¼",
            "ðŸš¶ðŸ½",
            "ðŸš¶ðŸ¾",
            "ðŸš¶ðŸ¿",
            "ðŸƒ",
            "ðŸƒðŸ»",
            "ðŸƒðŸ¼",
            "ðŸƒðŸ½",
            "ðŸƒðŸ¾",
            "ðŸƒðŸ¿",
            "ðŸ’ƒ",
            "ðŸ’ƒðŸ»",
            "ðŸ’ƒðŸ¼",
            "ðŸ’ƒðŸ½",
            "ðŸ’ƒðŸ¾",
            "ðŸ’ƒðŸ¿",
            "ðŸ‘«",
            "ðŸ‘ª",
            "ðŸ‘¨â€ðŸ‘©â€ðŸ‘§",
            "ðŸ‘¨â€ðŸ‘©â€ðŸ‘§â€ðŸ‘¦",
            "ðŸ‘¨â€ðŸ‘©â€ðŸ‘¦â€ðŸ‘¦",
            "ðŸ‘¨â€ðŸ‘©â€ðŸ‘§â€ðŸ‘§",
            "ðŸ‘©â€ðŸ‘©â€ðŸ‘¦",
            "ðŸ‘©â€ðŸ‘©â€ðŸ‘§",
            "ðŸ‘©â€ðŸ‘©â€ðŸ‘§â€ðŸ‘¦",
            "ðŸ‘©â€ðŸ‘©â€ðŸ‘¦â€ðŸ‘¦",
            "ðŸ‘©â€ðŸ‘©â€ðŸ‘§â€ðŸ‘§",
            "ðŸ‘¨â€ðŸ‘¨â€ðŸ‘¦",
            "ðŸ‘¨â€ðŸ‘¨â€ðŸ‘§",
            "ðŸ‘¨â€ðŸ‘¨â€ðŸ‘§â€ðŸ‘¦",
            "ðŸ‘¨â€ðŸ‘¨â€ðŸ‘¦â€ðŸ‘¦",
            "ðŸ‘¨â€ðŸ‘¨â€ðŸ‘§â€ðŸ‘§",
            "ðŸ‘¬",
            "ðŸ‘­",
            "ðŸ’",
            "ðŸ’‘",
            "ðŸ‘¯",
            "ðŸ™†",
            "ðŸ™†ðŸ»",
            "ðŸ™†ðŸ¼",
            "ðŸ™†ðŸ½",
            "ðŸ™†ðŸ¾",
            "ðŸ™†ðŸ¿",
            "ðŸ™…",
            "ðŸ™…ðŸ»",
            "ðŸ™…ðŸ¼",
            "ðŸ™…ðŸ½",
            "ðŸ™…ðŸ¾",
            "ðŸ™…ðŸ¿",
            "ðŸ’",
            "ðŸ’ðŸ»",
            "ðŸ’ðŸ¼",
            "ðŸ’ðŸ½",
            "ðŸ’ðŸ¾",
            "ðŸ’ðŸ¿",
            "ðŸ™‹",
            "ðŸ™‹ðŸ»",
            "ðŸ™‹ðŸ¼",
            "ðŸ™‹ðŸ½",
            "ðŸ™‹ðŸ¾",
            "ðŸ™‹ðŸ¿",
            "ðŸ’†",
            "ðŸ’†ðŸ»",
            "ðŸ’†ðŸ¼",
            "ðŸ’†ðŸ½",
            "ðŸ’†ðŸ¾",
            "ðŸ’†ðŸ¿",
            "ðŸ’‡",
            "ðŸ’‡ðŸ»",
            "ðŸ’‡ðŸ¼",
            "ðŸ’‡ðŸ½",
            "ðŸ’‡ðŸ¾",
            "ðŸ’‡ðŸ¿",
            "ðŸ’…",
            "ðŸ’…ðŸ»",
            "ðŸ’…ðŸ¼",
            "ðŸ’…ðŸ½",
            "ðŸ’…ðŸ¾",
            "ðŸ’…ðŸ¿",
            "ðŸ‘°",
            "ðŸ‘°ðŸ»",
            "ðŸ‘°ðŸ¼",
            "ðŸ‘°ðŸ½",
            "ðŸ‘°ðŸ¾",
            "ðŸ‘°ðŸ¿",
            "ðŸ™Ž",
            "ðŸ™ŽðŸ»",
            "ðŸ™ŽðŸ¼",
            "ðŸ™ŽðŸ½",
            "ðŸ™ŽðŸ¾",
            "ðŸ™ŽðŸ¿",
            "ðŸ™",
            "ðŸ™ðŸ»",
            "ðŸ™ðŸ¼",
            "ðŸ™ðŸ½",
            "ðŸ™ðŸ¾",
            "ðŸ™ðŸ¿",
            "ðŸ™‡",
            "ðŸ™‡ðŸ»",
            "ðŸ™‡ðŸ¼",
            "ðŸ™‡ðŸ½",
            "ðŸ™‡ðŸ¾",
            "ðŸ™‡ðŸ¿",
            "ðŸŽ©",
            "ðŸ‘‘",
            "ðŸ‘’",
            "ðŸ‘Ÿ",
            "ðŸ‘ž",
            "ðŸ‘¡",
            "ðŸ‘ ",
            "ðŸ‘¢",
            "ðŸ‘•",
            "ðŸ‘”",
            "ðŸ‘š",
            "ðŸ‘—",
            "ðŸŽ½",
            "ðŸ‘–",
            "ðŸ‘˜",
            "ðŸ‘™",
            "ðŸ’¼",
            "ðŸ‘œ",
            "ðŸ‘",
            "ðŸ‘›",
            "ðŸ‘“",
            "ðŸŽ€",
            "ðŸŒ‚",
            "ðŸ’„",
            "ðŸ’›",
            "ðŸ’™",
            "ðŸ’œ",
            "ðŸ’š",
            "â¤ï¸",
            "ðŸ’”",
            "ðŸ’—",
            "ðŸ’“",
            "ðŸ’•",
            "ðŸ’–",
            "ðŸ’ž",
            "ðŸ’˜",
            "ðŸ’Œ",
            "ðŸ’‹",
            "ðŸ’",
            "ðŸ’Ž",
            "ðŸ‘¤",
            "ðŸ‘¥",
            "ðŸ’¬",
            "ðŸ‘£",
            "ðŸ’­",
            "ðŸ¶",
            "ðŸº",
            "ðŸ±",
            "ðŸ­",
            "ðŸ¹",
            "ðŸ°",
            "ðŸ¸",
            "ðŸ¯",
            "ðŸ¨",
            "ðŸ»",
            "ðŸ·",
            "ðŸ½",
            "ðŸ®",
            "ðŸ—",
            "ðŸµ",
            "ðŸ’",
            "ðŸ´",
            "ðŸ‘",
            "ðŸ˜",
            "ðŸ¼",
            "ðŸ§",
            "ðŸ¦",
            "ðŸ¤",
            "ðŸ¥",
            "ðŸ£",
            "ðŸ”",
            "ðŸ",
            "ðŸ¢",
            "ðŸ›",
            "ðŸ",
            "ðŸœ",
            "ðŸž",
            "ðŸŒ",
            "ðŸ™",
            "ðŸš",
            "ðŸ ",
            "ðŸŸ",
            "ðŸ¬",
            "ðŸ³",
            "ðŸ‹",
            "ðŸ„",
            "ðŸ",
            "ðŸ€",
            "ðŸƒ",
            "ðŸ…",
            "ðŸ‡",
            "ðŸ‰",
            "ðŸŽ",
            "ðŸ",
            "ðŸ“",
            "ðŸ•",
            "ðŸ–",
            "ðŸ",
            "ðŸ‚",
            "ðŸ²",
            "ðŸ¡",
            "ðŸŠ",
            "ðŸ«",
            "ðŸª",
            "ðŸ†",
            "ðŸˆ",
            "ðŸ©",
            "ðŸ¾",
            "ðŸ’",
            "ðŸŒ¸",
            "ðŸŒ·",
            "ðŸ€",
            "ðŸŒ¹",
            "ðŸŒ»",
            "ðŸŒº",
            "ðŸ",
            "ðŸƒ",
            "ðŸ‚",
            "ðŸŒ¿",
            "ðŸŒ¾",
            "ðŸ„",
            "ðŸŒµ",
            "ðŸŒ´",
            "ðŸŒ²",
            "ðŸŒ³",
            "ðŸŒ°",
            "ðŸŒ±",
            "ðŸŒ¼",
            "ðŸŒ",
            "ðŸŒž",
            "ðŸŒ",
            "ðŸŒš",
            "ðŸŒ‘",
            "ðŸŒ’",
            "ðŸŒ“",
            "ðŸŒ”",
            "ðŸŒ•",
            "ðŸŒ–",
            "ðŸŒ—",
            "ðŸŒ˜",
            "ðŸŒœ",
            "ðŸŒ›",
            "ðŸŒ™",
            "ðŸŒ",
            "ðŸŒŽ",
            "ðŸŒ",
            "ðŸŒ‹",
            "ðŸŒŒ",
            "ðŸŒ ",
            "â­ï¸",
            "â˜€ï¸",
            "â›…ï¸",
            "â˜ï¸",
            "âš¡ï¸",
            "â˜”ï¸",
            "â„ï¸",
            "â›„ï¸",
            "ðŸŒ€",
            "ðŸŒ",
            "ðŸŒˆ",
            "ðŸŒŠ",
            "ðŸŽ",
            "ðŸ’",
            "ðŸŽŽ",
            "ðŸŽ’",
            "ðŸŽ“",
            "ðŸŽ",
            "ðŸŽ†",
            "ðŸŽ‡",
            "ðŸŽ",
            "ðŸŽ‘",
            "ðŸŽƒ",
            "ðŸ‘»",
            "ðŸŽ…",
            "ðŸŽ…ðŸ»",
            "ðŸŽ…ðŸ¼",
            "ðŸŽ…ðŸ½",
            "ðŸŽ…ðŸ¾",
            "ðŸŽ…ðŸ¿",
            "ðŸŽ„",
            "ðŸŽ",
            "ðŸŽ‹",
            "ðŸŽ‰",
            "ðŸŽŠ",
            "ðŸŽˆ",
            "ðŸŽŒ",
            "ðŸ”®",
            "ðŸŽ¥",
            "ðŸ“·",
            "ðŸ“¹",
            "ðŸ“¼",
            "ðŸ’¿",
            "ðŸ“€",
            "ðŸ’½",
            "ðŸ’¾",
            "ðŸ’»",
            "ðŸ“±",
            "â˜Žï¸",
            "ðŸ“ž",
            "ðŸ“Ÿ",
            "ðŸ“ ",
            "ðŸ“¡",
            "ðŸ“º",
            "ðŸ“»",
            "ðŸ”Š",
            "ðŸ”‰",
            "ðŸ”ˆ",
            "ðŸ”‡",
            "ðŸ””",
            "ðŸ”•",
            "ðŸ“¢",
            "ðŸ“£",
            "â³",
            "âŒ›ï¸",
            "â°",
            "âŒšï¸",
            "ðŸ”“",
            "ðŸ”’",
            "ðŸ”",
            "ðŸ”",
            "ðŸ”‘",
            "ðŸ”Ž",
            "ðŸ’¡",
            "ðŸ”¦",
            "ðŸ”†",
            "ðŸ”…",
            "ðŸ”Œ",
            "ðŸ”‹",
            "ðŸ”",
            "ðŸ›",
            "ðŸ›€",
            "ðŸš¿",
            "ðŸš½",
            "ðŸ”§",
            "ðŸ”©",
            "ðŸ”¨",
            "ðŸšª",
            "ðŸš¬",
            "ðŸ’£",
            "ðŸ”«",
            "ðŸ”ª",
            "ðŸ’Š",
            "ðŸ’‰",
            "ðŸ’°",
            "ðŸ’´",
            "ðŸ’µ",
            "ðŸ’·",
            "ðŸ’¶",
            "ðŸ’³",
            "ðŸ’¸",
            "ðŸ“²",
            "ðŸ“§",
            "ðŸ“¥",
            "ðŸ“¤",
            "âœ‰ï¸",
            "ðŸ“©",
            "ðŸ“¨",
            "ðŸ“¯",
            "ðŸ“«",
            "ðŸ“ª",
            "ðŸ“¬",
            "ðŸ“­",
            "ðŸ“®",
            "ðŸ“¦",
            "ðŸ“",
            "ðŸ“„",
            "ðŸ“ƒ",
            "ðŸ“‘",
            "ðŸ“Š",
            "ðŸ“ˆ",
            "ðŸ“‰",
            "ðŸ“œ",
            "ðŸ“‹",
            "ðŸ“…",
            "ðŸ“†",
            "ðŸ“‡",
            "ðŸ“",
            "ðŸ“‚",
            "âœ‚ï¸",
            "ðŸ“Œ",
            "ðŸ“Ž",
            "âœ’ï¸",
            "âœï¸",
            "ðŸ“",
            "ðŸ“",
            "ðŸ“•",
            "ðŸ“—",
            "ðŸ“˜",
            "ðŸ“™",
            "ðŸ““",
            "ðŸ“”",
            "ðŸ“’",
            "ðŸ“š",
            "ðŸ“–",
            "ðŸ”–",
            "ðŸ“›",
            "ðŸ”¬",
            "ðŸ”­",
            "ðŸ“°",
            "ðŸŽ¨",
            "ðŸŽ¬",
            "ðŸŽ¤",
            "ðŸŽ§",
            "ðŸŽ¼",
            "ðŸŽµ",
            "ðŸŽ¶",
            "ðŸŽ¹",
            "ðŸŽ»",
            "ðŸŽº",
            "ðŸŽ·",
            "ðŸŽ¸",
            "ðŸ‘¾",
            "ðŸŽ®",
            "ðŸƒ",
            "ðŸŽ´",
            "ðŸ€„ï¸",
            "ðŸŽ²",
            "ðŸŽ¯",
            "ðŸˆ",
            "ðŸ€",
            "âš½ï¸",
            "âš¾ï¸",
            "ðŸŽ¾",
            "ðŸŽ±",
            "ðŸ‰",
            "ðŸŽ³",
            "â›³ï¸",
            "ðŸšµ",
            "ðŸšµðŸ»",
            "ðŸšµðŸ¼",
            "ðŸšµðŸ½",
            "ðŸšµðŸ¾",
            "ðŸšµðŸ¿",
            "ðŸš´",
            "ðŸš´ðŸ»",
            "ðŸš´ðŸ¼",
            "ðŸš´ðŸ½",
            "ðŸš´ðŸ¾",
            "ðŸš´ðŸ¿",
            "ðŸ",
            "ðŸ‡",
            "ðŸ‡ðŸ»",
            "ðŸ‡ðŸ¼",
            "ðŸ‡ðŸ½",
            "ðŸ‡ðŸ¾",
            "ðŸ‡ðŸ¿",
            "ðŸ†",
            "ðŸŽ¿",
            "ðŸ‚",
            "ðŸŠ",
            "ðŸŠðŸ»",
            "ðŸŠðŸ¼",
            "ðŸŠðŸ½",
            "ðŸŠðŸ¾",
            "ðŸŠðŸ¿",
            "ðŸ„",
            "ðŸ„ðŸ»",
            "ðŸ„ðŸ¼",
            "ðŸ„ðŸ½",
            "ðŸ„ðŸ¾",
            "ðŸ„ðŸ¿",
            "ðŸŽ£",
            "â˜•ï¸",
            "ðŸµ",
            "ðŸ¶",
            "ðŸ¼",
            "ðŸº",
            "ðŸ»",
            "ðŸ¸",
            "ðŸ¹",
            "ðŸ·",
            "ðŸ´",
            "ðŸ•",
            "ðŸ”",
            "ðŸŸ",
            "ðŸ—",
            "ðŸ–",
            "ðŸ",
            "ðŸ›",
            "ðŸ¤",
            "ðŸ±",
            "ðŸ£",
            "ðŸ¥",
            "ðŸ™",
            "ðŸ˜",
            "ðŸš",
            "ðŸœ",
            "ðŸ²",
            "ðŸ¢",
            "ðŸ¡",
            "ðŸ³",
            "ðŸž",
            "ðŸ©",
            "ðŸ®",
            "ðŸ¦",
            "ðŸ¨",
            "ðŸ§",
            "ðŸŽ‚",
            "ðŸ°",
            "ðŸª",
            "ðŸ«",
            "ðŸ¬",
            "ðŸ­",
            "ðŸ¯",
            "ðŸŽ",
            "ðŸ",
            "ðŸŠ",
            "ðŸ‹",
            "ðŸ’",
            "ðŸ‡",
            "ðŸ‰",
            "ðŸ“",
            "ðŸ‘",
            "ðŸˆ",
            "ðŸŒ",
            "ðŸ",
            "ðŸ",
            "ðŸ ",
            "ðŸ†",
            "ðŸ…",
            "ðŸŒ½",
            "ðŸ ",
            "ðŸ¡",
            "ðŸ«",
            "ðŸ¢",
            "ðŸ£",
            "ðŸ¥",
            "ðŸ¦",
            "ðŸª",
            "ðŸ©",
            "ðŸ¨",
            "ðŸ’’",
            "â›ªï¸",
            "ðŸ¬",
            "ðŸ¤",
            "ðŸŒ‡",
            "ðŸŒ†",
            "ðŸ¯",
            "ðŸ°",
            "â›ºï¸",
            "ðŸ­",
            "ðŸ—¼",
            "ðŸ—¾",
            "ðŸ—»",
            "ðŸŒ„",
            "ðŸŒ…",
            "ðŸŒƒ",
            "ðŸ—½",
            "ðŸŒ‰",
            "ðŸŽ ",
            "ðŸŽ¡",
            "â›²ï¸",
            "ðŸŽ¢",
            "ðŸš¢",
            "â›µï¸",
            "ðŸš¤",
            "ðŸš£",
            "âš“ï¸",
            "ðŸš€",
            "âœˆï¸",
            "ðŸ’º",
            "ðŸš",
            "ðŸš‚",
            "ðŸšŠ",
            "ðŸš‰",
            "ðŸšž",
            "ðŸš†",
            "ðŸš„",
            "ðŸš…",
            "ðŸšˆ",
            "ðŸš‡",
            "ðŸš",
            "ðŸš‹",
            "ðŸšƒ",
            "ðŸšŽ",
            "ðŸšŒ",
            "ðŸš",
            "ðŸš™",
            "ðŸš˜",
            "ðŸš—",
            "ðŸš•",
            "ðŸš–",
            "ðŸš›",
            "ðŸšš",
            "ðŸš¨",
            "ðŸš“",
            "ðŸš”",
            "ðŸš’",
            "ðŸš‘",
            "ðŸš",
            "ðŸš²",
            "ðŸš¡",
            "ðŸšŸ",
            "ðŸš ",
            "ðŸšœ",
            "ðŸ’ˆ",
            "ðŸš",
            "ðŸŽ«",
            "ðŸš¦",
            "ðŸš¥",
            "âš ï¸",
            "ðŸš§",
            "ðŸ”°",
            "â›½ï¸",
            "ðŸ®",
            "ðŸŽ°",
            "â™¨ï¸",
            "ðŸ—¿",
            "ðŸŽª",
            "ðŸŽ­",
            "ðŸ“",
            "ðŸš©",
            "ðŸ‡¦ðŸ‡º",
            "ðŸ‡¦ðŸ‡¹",
            "ðŸ‡§ðŸ‡ª",
            "ðŸ‡§ðŸ‡·",
            "ðŸ‡¨ðŸ‡¦",
            "ðŸ‡¨ðŸ‡±",
            "ðŸ‡¨ðŸ‡³",
            "ðŸ‡¨ðŸ‡´",
            "ðŸ‡©ðŸ‡°",
            "ðŸ‡«ðŸ‡®",
            "ðŸ‡«ðŸ‡·",
            "ðŸ‡©ðŸ‡ª",
            "ðŸ‡­ðŸ‡°",
            "ðŸ‡®ðŸ‡³",
            "ðŸ‡®ðŸ‡©",
            "ðŸ‡®ðŸ‡ª",
            "ðŸ‡®ðŸ‡±",
            "ðŸ‡®ðŸ‡¹",
            "ðŸ‡¯ðŸ‡µ",
            "ðŸ‡°ðŸ‡·",
            "ðŸ‡²ðŸ‡´",
            "ðŸ‡²ðŸ‡¾",
            "ðŸ‡²ðŸ‡½",
            "ðŸ‡³ðŸ‡±",
            "ðŸ‡³ðŸ‡¿",
            "ðŸ‡³ðŸ‡´",
            "ðŸ‡µðŸ‡­",
            "ðŸ‡µðŸ‡±",
            "ðŸ‡µðŸ‡¹",
            "ðŸ‡µðŸ‡·",
            "ðŸ‡·ðŸ‡º",
            "ðŸ‡¸ðŸ‡¦",
            "ðŸ‡¸ðŸ‡¬",
            "ðŸ‡¿ðŸ‡¦",
            "ðŸ‡ªðŸ‡¸",
            "ðŸ‡¸ðŸ‡ª",
            "ðŸ‡¨ðŸ‡­",
            "ðŸ‡¹ðŸ‡·",
            "ðŸ‡¬ðŸ‡§",
            "ðŸ‡ºðŸ‡¸",
            "ðŸ‡¦ðŸ‡ª",
            "ðŸ‡»ðŸ‡³",
            "1ï¸âƒ£",
            "2ï¸âƒ£",
            "3ï¸âƒ£",
            "4ï¸âƒ£",
            "5ï¸âƒ£",
            "6ï¸âƒ£",
            "7ï¸âƒ£",
            "8ï¸âƒ£",
            "9ï¸âƒ£",
            "0ï¸âƒ£",
            "ðŸ”Ÿ",
            "ðŸ”¢",
            "#ï¸âƒ£",
            "ðŸ”£",
            "â¬†ï¸",
            "â¬‡ï¸",
            "â¬…ï¸",
            "âž¡ï¸",
            "ðŸ” ",
            "ðŸ”¡",
            "ðŸ”¤",
            "â†—ï¸",
            "â†–ï¸",
            "â†˜ï¸",
            "â†™ï¸",
            "â†”ï¸",
            "â†•ï¸",
            "ðŸ”„",
            "â—€ï¸",
            "â–¶ï¸",
            "ðŸ”¼",
            "ðŸ”½",
            "â†©ï¸",
            "â†ªï¸",
            "â„¹ï¸",
            "âª",
            "â©",
            "â«",
            "â¬",
            "â¤µï¸",
            "â¤´ï¸",
            "ðŸ†—",
            "ðŸ”€",
            "ðŸ”",
            "ðŸ”‚",
            "ðŸ†•",
            "ðŸ†™",
            "ðŸ†’",
            "ðŸ†“",
            "ðŸ†–",
            "ðŸ“¶",
            "ðŸŽ¦",
            "ðŸˆ",
            "ðŸˆ¯ï¸",
            "ðŸˆ³",
            "ðŸˆµ",
            "ðŸˆ´",
            "ðŸˆ²",
            "ðŸ‰",
            "ðŸˆ¹",
            "ðŸˆº",
            "ðŸˆšï¸",
            "ðŸš»",
            "ðŸš¹",
            "ðŸšº",
            "ðŸš¼",
            "ðŸš¾",
            "ðŸš°",
            "ðŸš®",
            "ðŸ…¿ï¸",
            "â™¿ï¸",
            "ðŸš­",
            "ðŸˆ·",
            "ðŸˆ¸",
            "ðŸˆ‚",
            "â“‚ï¸",
            "ðŸ›‚",
            "ðŸ›„",
            "ðŸ›…",
            "ðŸ›ƒ",
            "ðŸ‰‘",
            "ãŠ™ï¸",
            "ãŠ—ï¸",
            "ðŸ†‘",
            "ðŸ†˜",
            "ðŸ†”",
            "ðŸš«",
            "ðŸ”ž",
            "ðŸ“µ",
            "ðŸš¯",
            "ðŸš±",
            "ðŸš³",
            "ðŸš·",
            "ðŸš¸",
            "â›”ï¸",
            "âœ³ï¸",
            "â‡ï¸",
            "âŽ",
            "âœ…",
            "âœ´ï¸",
            "ðŸ’Ÿ",
            "ðŸ†š",
            "ðŸ“³",
            "ðŸ“´",
            "ðŸ…°",
            "ðŸ…±",
            "ðŸ†Ž",
            "ðŸ…¾",
            "ðŸ’ ",
            "âž¿",
            "â™»ï¸",
            "â™ˆï¸",
            "â™‰ï¸",
            "â™Šï¸",
            "â™‹ï¸",
            "â™Œï¸",
            "â™ï¸",
            "â™Žï¸",
            "â™ï¸",
            "â™ï¸",
            "â™‘ï¸",
            "â™’ï¸",
            "â™“ï¸",
            "â›Ž",
            "ðŸ”¯",
            "ðŸ§",
            "ðŸ’¹",
            "ðŸ’²",
            "ðŸ’±",
            "Â©",
            "Â®",
            "â„¢",
            "âŒ",
            "â€¼ï¸",
            "â‰ï¸",
            "â—ï¸",
            "â“",
            "â•",
            "â”",
            "â­•ï¸",
            "ðŸ”",
            "ðŸ”š",
            "ðŸ”™",
            "ðŸ”›",
            "ðŸ”œ",
            "ðŸ”ƒ",
            "ðŸ•›",
            "ðŸ•§",
            "ðŸ•",
            "ðŸ•œ",
            "ðŸ•‘",
            "ðŸ•",
            "ðŸ•’",
            "ðŸ•ž",
            "ðŸ•“",
            "ðŸ•Ÿ",
            "ðŸ•”",
            "ðŸ• ",
            "ðŸ••",
            "ðŸ•–",
            "ðŸ•—",
            "ðŸ•˜",
            "ðŸ•™",
            "ðŸ•š",
            "ðŸ•¡",
            "ðŸ•¢",
            "ðŸ•£",
            "ðŸ•¤",
            "ðŸ•¥",
            "ðŸ•¦",
            "âœ–ï¸",
            "âž•",
            "âž–",
            "âž—",
            "â™ ï¸",
            "â™¥ï¸",
            "â™£ï¸",
            "â™¦ï¸",
            "ðŸ’®",
            "ðŸ’¯",
            "âœ”ï¸",
            "â˜‘ï¸",
            "ðŸ”˜",
            "ðŸ”—",
            "âž°",
            "ã€°",
            "ã€½ï¸",
            "ðŸ”±",
            "â—¼ï¸",
            "â—»ï¸",
            "â—¾ï¸",
            "â—½ï¸",
            "â–ªï¸",
            "â–«ï¸",
            "ðŸ”º",
            "ðŸ”²",
            "ðŸ”³",
            "âš«ï¸",
            "âšªï¸",
            "ðŸ”´",
            "ðŸ”µ",
            "ðŸ”»",
            "â¬œï¸",
            "â¬›ï¸",
            "ðŸ”¶",
            "ðŸ”·",
            "ðŸ”¸",
            "ðŸ”¹",
            "ðŸ––",
            "ðŸ––ðŸ»",
            "ðŸ––ðŸ¼",
            "ðŸ––ðŸ½",
            "ðŸ––ðŸ¾",
            "ðŸ––ðŸ¿",
            "✓",
            "…",
        ];
        $allowed = [];

        return str_replace($not_allowed, $allowed, $string);
    }

    public function execute()
    {
        //dd('YOU ALREADY DID THIS!');

        try {
            DB::beginTransaction();

            $GB_tasks_roles = [
                1 => 7,
                2 => 10,
                3 => 17,
                4 => 18,
                5 => 1,
            ];

            $min_id = 29707;
            $max_id = 29718;

            $min_id = 29735;
            $max_id = 29774;

            $min_id = 29791;
            $max_id = 29792;

            $tasks = DB::connection('gbmedia')->table('trabajo')->whereBetween('id', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('tasks', 'old_task_id')) {
                Schema::table('tasks', function (Blueprint $table) {
                    $table->string('old_task_id')->nullable();
                });
            }

            //dd($tasks);
            foreach ($tasks as $task) {
                $created_by_type = $task->fk_u_id_c != 0 ? 1 : 2;
                $created_by_id = $created_by_type == 1 ? $task->fk_u_id_c : $task->t_rol_crea;

                if ($created_by_type == 2) {
                    $created_by_id = $GB_tasks_roles[$task->t_rol_crea];
                } else {
                    $creator = User::select('id')->where('old_user_id', $task->fk_u_id_c)->first();

                    if (!is_null($creator)) {
                        $created_by_id = $creator->id;
                    } else {
                        $created_by_id = 594;
                    }
                }

                $title = $task->titulo;
                $title = trim($title);

                $status = $task->estado == 'finalizado' ? 1 : 0;
                $should_finish = (is_null($task->fecha_fin) || empty($task->fecha_fin) || $task->fecha_fin == '0000-00-00' ? null : $task->fecha_fin);
                $code = trim($task->codigo);

                $created_task = Task::firstOrCreate(
                    ['old_task_id' => $task->id],
                    [
                        'created_by_type' => $created_by_type,
                        'created_by' => $created_by_id,
                        'title' => $title,
                        'status' => $status,
                        'should_finish' => $should_finish,
                        'terminated_by' => $status == 1 ? 1 : 0,
                        'code' => $code,
                        'old_task_id' => $task->id,
                        'created_at' => $task->fecha_creacion,
                        'updated_at' => $task->fecha_creacion,
                    ]
                );

                $created_task->created_by_type = $created_by_type;
                $created_task->created_by = $created_by_id;
                $created_task->title = $title;
                $created_task->status = $status;
                $created_task->should_finish = $should_finish;
                $created_task->terminated_by = $status == 1 ? $created_by_id : 0;
                $created_task->code = $code;
                $created_task->old_task_id = $task->id;
                $created_task->created_at = $task->fecha_creacion;
                $created_task->updated_at = $task->fecha_creacion;

                $created_task->save();

                if (!Schema::hasColumn('task_comments', 'old_comment_id')) {
                    Schema::table('task_comments', function (Blueprint $table) {
                        $table->string('old_comment_id')->nullable();
                    });
                }

                $description = $task->descripcion;
                $description = trim($description);

                $first_comment = TaskComment::firstOrCreate(
                    [
                        'task_id' => $created_task->id,
                        'user_id' => $created_by_id,
                        'created_at' => $task->fecha_creacion
                    ],
                    [
                        'task_id' => $created_by_type,
                        'user_id' => $created_by_id,
                        'comment' => $description,
                    ]
                );

                $first_comment->task_id = $created_task->id;
                $first_comment->user_id = $created_by_id;
                $first_comment->comment = $description;
                $ok_first_comment = $first_comment->save();

                if ($ok_first_comment) {
                    if (!is_null($task->archivo) && !empty($task->archivo)) {
                        $file = $task->archivo;

                        $task_comment_file =  TaskCommentAttachment::firstOrCreate(
                            [
                                'task_comments_id' => $first_comment->id,
                                'file' => $file,
                            ],
                            [
                                'task_comments_id' => $first_comment->id,
                                'file' => $file,
                            ]
                        );

                        $task_comment_file->task_comments_id = $first_comment->id;
                        $task_comment_file->file = $file;
                        $task_comment_file->save();
                    }

                    $gb_tasks_files = DB::connection('gbmedia')->table('trabajo_archivos')->where('trabajo_id', $created_task->old_task_id)->get();

                    if ($gb_tasks_files->count() > 0) {
                        foreach ($gb_tasks_files as $gb_task_file) {
                            $task_comment_file = TaskCommentAttachment::firstOrCreate(
                                [
                                    'task_comments_id' => $first_comment->id,
                                    'file' => $gb_task_file->archivo,
                                ],
                                [
                                    'task_comments_id' => $first_comment->id,
                                    'file' => $gb_task_file->archivo,
                                ]
                            );


                            $task_comment_file->task_comments_id = $first_comment->id;
                            $task_comment_file->file = $gb_task_file->archivo;
                            $ok = $task_comment_file->save();
                        }
                    }
                }

                // tasks_attachments
                $tasks_attachments = DB::connection('gbmedia')->table('trabajo_comentario_archivos')->where('trabajo_id', $task->id)->get();

                foreach ($tasks_attachments as $task_attachment) {
                    $new_comment = TaskComment::select('id')->where('old_comment_id', $task_attachment->comentario_id)->first();
                    if (!is_object($new_comment)) {
                        continue;
                    }

                    $task_comment_file = TaskCommentAttachment::firstOrCreate(
                        [
                            'task_comments_id' => $new_comment->id,
                            'file' => $task_attachment->archivo,
                        ],
                        [
                            'task_comments_id' => $new_comment->id,
                            'file' => $task_attachment->archivo,
                        ]
                    );

                    $task_comment_file->task_comments_id = $new_comment->id;
                    $task_comment_file->file = $task_attachment->archivo;
                    $task_comment_file->save();
                }
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function executeTasksUsers()
    {
        try {
            DB::beginTransaction();

            $min_id = 29707;
            $max_id = 29792;

            $tasks = Task::whereBetween('old_task_id', [$min_id, $max_id])->where('status', 0)->get();

            foreach ($tasks as $task) {
                $tasks_users = DB::connection('gbmedia')->table('trabajo_usuario_estado')->where('id_trabajo', $task->old_task_id)->get();

                foreach ($tasks_users as $task_user) {
                    $user = User::select('id')->where('old_user_id', $task_user->fk_u_id)->first();

                    if (!is_object($user)) {
                        continue;
                    }

                    $user_id = $user->id;
                    $status = $task->status;
                    $retired = $task_user->retirado == 1 ? 1 : 0;

                    $created_task_user = TaskUserStatus::firstOrCreate(
                        [
                            'task_id' => $task->id,
                            'user_id' => $user_id,
                        ],
                        [
                            'task_id' => $task->id,
                            'user_id' => $user_id,
                            'status' => $status == 1 || $retired == 1 ? 1 : 0,
                            'pulsing' => $task_user->estado_trab == 'no' ? 1 : 0,
                            'folder' => 0,
                        ]
                    );

                    $created_task_user->task_id = $task->id;
                    $created_task_user->user_id = $user_id;
                    $created_task_user->status = $status == 1 || $retired == 1 ? 1 : 0;
                    $created_task_user->pulsing = $task_user->estado_trab == 'no' ? 1 : 0;
                    $created_task_user->folder = 0;
                    $created_task_user->save();

                    $created_task_user_receiver = TaskUsersReceivers::firstOrCreate(
                        [
                            'task_id' => $task->id,
                            'user_id' => $user_id,
                        ],
                        [
                            'task_id' => $task->id,
                            'user_id' => $user_id,
                        ]
                    );

                    $created_task_user_receiver->task_id = $task->id;
                    $created_task_user_receiver->user_id = $user_id;
                    $created_task_user->save();
                }
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function executeTasksComments()
    {
        try {
            DB::beginTransaction();

            $min_id = 25001;
            $max_id = 30000;

            $tasks_comments = DB::connection('gbmedia')->table('trabajo_comentarios')->whereBetween('id_trabajo', [$min_id, $max_id])->get();

            if (!Schema::hasColumn('task_comments', 'old_comment_id')) {
                Schema::table('task_comments', function (Blueprint $table) {
                    $table->string('old_comment_id')->nullable();
                });
            }

            foreach ($tasks_comments as $task_comment) {
                $task = Task::select('id')->where('old_task_id', $task_comment->id_trabajo)->first();

                if (!is_object($task)) {
                    continue;
                }

                $user_creator = User::select('id')->where('old_user_id', $task_comment->fk_u_id)->first();
                $user_id = $user_creator->id;

                $comment = $task_comment->comentario_trabajo;
                $comment = trim($comment);

                $created_task_comment = TaskComment::firstOrCreate(
                    ['old_comment_id' => $task_comment->id],
                    [
                        'task_id' => $task->id,
                        'user_id' => $user_id,
                        'comment' => $comment,
                        'old_comment_id' => $task_comment->id,
                        'created_at' => $task_comment->fecha_comentario,
                        'updated_at' => $task_comment->fecha_comentario,
                    ]
                );

                $created_task_comment->task_id = $task->id;
                $created_task_comment->user_id = $user_id;
                $created_task_comment->comment = $comment;
                $created_task_comment->old_comment_id = $task_comment->id;
                $created_task_comment->created_at = $task_comment->fecha_comentario;
                $created_task_comment->updated_at = $task_comment->fecha_comentario;
                $created_task_comment->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function executeTasksFiles()
    {
        try {
            DB::beginTransaction();

            $min_id = 5001;
            $max_id = 10000;

            $task_comments = TaskComment::whereBetween('task_id', [$min_id, $max_id])->get();
            //dd($task_comments);

            foreach ($task_comments as $task_comment) {
                $old_task = Task::where('id', $task_comment->task_id)->first();

                if (!is_object($old_task)) {
                    continue;
                }

                $gb_tasks_files = DB::connection('gbmedia')->table('trabajo_archivos')->where('trabajo_id', $old_task->old_task_id)->get();

                if ($gb_tasks_files->count() == 0) {
                    continue;
                }

                $first_comment = TaskComment::where('task_id', $old_task->id)->first();

                foreach ($gb_tasks_files as $gb_task_file) {
                    $task_comment_file = TaskCommentAttachment::firstOrCreate(
                        [
                            'task_comments_id' => $first_comment->id,
                            'file' => $gb_task_file->archivo,
                        ],
                        [
                            'task_comments_id' => $first_comment->id,
                            'file' => $gb_task_file->archivo,
                        ]
                    );

                    $task_comment_file->task_comments_id = $first_comment->id;
                    $task_comment_file->file = $gb_task_file->archivo;
                    $ok = $task_comment_file->save();
                }
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function tasksStatusExecute()
    {
        try {
            DB::beginTransaction();

            $min_id = 25001;
            $max_id = 29706;

            $tasks = DB::connection('gbmedia')->table('trabajo')->whereBetween('id', [$min_id, $max_id])->get();

            foreach ($tasks as $task) {
                $status = $task->estado == 'finalizado' ? 1 : 0;

                $task_status = Task::firstOrCreate(
                    ['old_task_id' => $task->id],
                    [
                        'status' => $status,
                        'terminated_by' => $status == 1 ? 1 : 0,
                        'created_at' => $task->fecha_creacion,
                        'updated_at' => $task->fecha_creacion,
                    ]
                );

                $task_status->terminated_by = $status == 1 ? 1 : 0;
                $task_status->status = $status;
                $task_status->should_finish = $task->fecha_fin;
                $task_status->created_at = $task->fecha_creacion;
                $task_status->updated_at = $task->fecha_creacion;

                $task_status->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }
}
