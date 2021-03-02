<?php

namespace App\Console\Commands\EveryDay;

use App\Http\Controllers\Cafeteria\CafeteriaController;
use App\Http\Controllers\Tasks\TaskController;
use App\Models\Cafeteria\CafeteriaMenu;
use App\Models\Cafeteria\CafeteriaOrder;
use App\Models\Cron\Cron;
use App\Models\Settings\SettingLocation;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Models\Tasks\TaskCommentAttachment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class Cafeteria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cafeteria:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $studio_slug = 'GB';
        $date = Carbon::now()->toDateString();

        $day_orders = CafeteriaOrder::where('date', $date)->get();

        $cafeteria_controller = new CafeteriaController();

        if($day_orders->count() > 0) {
            foreach ($day_orders AS $order) {
                if($order->cafeteria_menu_id == 0) { // Desayuno
                    $cafeteria_id        = 0;
                    $cafeteria_type_name = 'Desayuno';
                    $menu_description    = '';
                    $type                = 1;
                } else {
                    $menu = CafeteriaMenu::find($order->cafeteria_menu_id);
                    $cafeteria_id        = $menu->id;
                    $cafeteria_type_name = $menu->cafeteriaType->name;
                    $menu_description    = $menu->description;
                    $type                = $menu->cafeteria_type_id;
                }

                $cafeteria_task_date = Task::where('created_by_type', 0)->where('created_by', 15)->where('cafeteria_type_id', $type)->whereRaw("DATE(`created_at`) = '$date'")->first();

                if (!is_null($cafeteria_task_date)) { continue; }

                if (is_null($cafeteria_task_date)) {
                    $task_controller = new TaskController();
                    $should_finish = Carbon::parse($date)->addDay(1);

                    // Create task
                    $task = new Task();
                    $task->created_by_type = 0; // Role
                    $task->created_by = 15; // Cafetería y Aseo
                    $task->cafeteria_type_id = $type; // Cafeteria type
                    $task->title = "Pedidos " . strtoupper($cafeteria_type_name) . " " . Carbon::parse($date)->format('d/M/Y');
                    $task->status = 0;
                    $task->should_finish = $should_finish;
                    $task->terminated_by = 0;
                    $task->code = $task_controller->generateCode();
                    $task->created_at = $date;
                    $created = $task->save();

                    $cafeteria_task_date = Task::find($task->id);

                    $receivers = [
                        'to_roles' => [['id' => 11, 'name' => 'Programador/a'], ['id' => 6, 'name' => 'Monitor/a'], ['id' => 1, 'name' => 'Gerente']],
                        'to_users' => [],
                        'to_models' => [],
                    ];

                    $request_object = new Request(['receivers' => json_encode($receivers), 'task_id' => $task->id, 'from_add_receivers' => 0]);
                    $task_controller->addReceivers($request_object);
                }

                $locations_orders = '';
                $total_orders = 0;

                $locations = SettingLocation::all();

                foreach ($locations AS $location) {
                    $location_orders = CafeteriaOrder::where('cafeteria_menu_id', $cafeteria_id)->where('location_id', $location->id)->where('date', $date)->get();

                    if ($location_orders->count() > 0) {
                        $count = 0;

                        foreach ($location_orders AS $location_order) {
                            $count = $count + $location_order->quantity;
                        }

                        $locations_orders .= "<span><b>$location->name</b>: $count</span><br>";
                        $total_orders = $total_orders + $count;
                    }
                }

                TaskComment::where('task_id', $cafeteria_task_date->id)->delete(); // Delete all task comments

                $menu_orders = CafeteriaOrder::where('cafeteria_menu_id', $cafeteria_id)->where('date', $date)->get();

                $task_comment = new TaskComment();
                $task_comment->task_id = $cafeteria_task_date->id;
                $task_comment->user_id = 1;
                $task_comment->comment = "<h5 class='text-bold'>" . ($type == 1 ? "Desayunos" : $menu_description) . "</h5><hr> <b>Pedidos</b>:<br><br>" . $locations_orders . "<br> <b>Total</b>: $total_orders";
                $task_comment->save();

                $create_file = $cafeteria_controller->createCafeteriaOrdersFile($order->cafeteria_menu_id, $type, $date, $studio_slug);

                if ($create_file['success']) {
                    $filename = $create_file['filename'];

                    $task_comment_attachment = new TaskCommentAttachment();
                    $task_comment_attachment->task_comments_id = $task_comment->id;
                    $task_comment_attachment->file = $filename;
                    $task_comment_attachment->save();
                }
            }
        }

        $cron = new Cron();
        $cron->cron = "Trabajos Cafeteria";
        $cron->command = $this->signature;
        $cron->created_at = Carbon::now()->toDateTimeString();
        $ok = $cron->save();

        return $ok;
    }
}
