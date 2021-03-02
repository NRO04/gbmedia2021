<?php

namespace App\Http\Controllers\Cafeteria;

use App\Exports\Cafeteria\Orders;
use App\Exports\Payroll\SalaryIncrease;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tasks\TaskController;
use App\Models\Payrolls\PayrollIncrease;
use App\Models\Payrolls\PayrollMovement;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Models\Satellite\SatellitePaymentDeduction;
use App\Models\Tasks\TaskComment;
use App\Models\Tasks\TaskCommentAttachment;
use App\Models\Cafeteria\CafeteriaBreakfastCategory;
use App\Models\Cafeteria\CafeteriaMenu;
use App\Models\Cafeteria\CafeteriaOrder;
use App\Models\Cafeteria\CafeteriaType;
use App\Models\Settings\SettingLocation;
use App\Models\Tasks\Task;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\TraitGlobal;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class CafeteriaController extends Controller
{
    use TraitGlobal;

    protected $menu_colors = [
        1 => '#4799eb',
        2 => '#229649',
        3 => '#3723e4',
        4 => '#ff521b',
    ];

    public function __construct()
    {
        $this->middleware('auth');

        // Access to only certain methods
        $this->middleware('permission:menu')->only('menu');
    }

    public function menu()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $types = CafeteriaType::all();

        $min_date = CafeteriaOrder::min('date');
        $max_date = CafeteriaOrder::max('date');

        $weeks = $this->getDistinctWeeksBetweenDates($min_date, $max_date);

        foreach ($weeks AS $key => $week) {
            $have_orders = CafeteriaOrder::orderBy('created_at', 'DESC')->whereBetween('date', [$week->start, $week->end])->get();

            if($have_orders->count() == 0) {
                unset($weeks[$key]);
            }
        }

        return view('adminModules.cafeteria.menu')->with(compact(['types', 'weeks', 'user_permission']));
    }

    public function orders()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        $cafeteria_types = CafeteriaType::all();
        $locations = SettingLocation::where('name', '!=', 'All')->get();
        $breakfast_categories = CafeteriaBreakfastCategory::with('breakfastTypes')->get();
        $users = User::selectRaw('id, CASE WHEN setting_role_id = 14 THEN nick ELSE CONCAT(first_name, " ", last_name) END AS show_name')->orderBy('show_name', 'asc')->get();

        return view('adminModules.cafeteria.orders')->with(compact(['cafeteria_types', 'locations', 'breakfast_categories', 'user_permission', 'users']));
    }

    public function getCafeteriaTypes()
    {
        $cafeteria_types = CafeteriaType::all();
        return response()->json($cafeteria_types);
    }

    public function getMenus(Request $request)
    {
        $data = [];

        $menus = CafeteriaMenu::with('orders')->get();

        foreach ($menus AS $menu) {
            $orders = [];

            foreach($menu->orders AS $order) {
                $orders[] = (object)[
                    'user' => $order->user->roleUserShortName(),
                    'observations' => $order->observations,
                    'quantity' => $order->quantity,
                    'total' => $order->total,
                ];
            }

            $data[] = (object)[
                "menu_id" => $menu->id,
                "description" => $menu->description,
                "price" => $menu->price,
                "type" => $menu->cafeteria_type_id,
                "menu_date" => $menu->date,
                "title" => Carbon::parse($menu->cafeteriaType->time)->format('H:i') . " | " . $menu->cafeteriaType->name,
                "start" => $menu->date,
                "icon" => 'hamburger',
                "color" => $this->menu_colors[$menu->cafeteria_type_id],
                "orders" => $orders,
            ];
        }

        return response()->json($data);
    }

    public function getOrders(Request $request)
    {
        $data = [];

        $user_id = Auth::user()->id;
        $orders = CafeteriaOrder::where('user_id', $user_id)->get();

        foreach ($orders AS $order) {
            if($order->cafeteria_menu_id == 0) {
                $title = "09:00 | Desayuno";
                $color = $this->menu_colors[1];
                $start = $order->date;
                $price = $order->total;
                $description = $order->observations;
                $observations = null;
            } else {
                $title = Carbon::parse($order->cafeteriaMenu->cafeteriaType->time)->format('H:i') . " | " . $order->cafeteriaMenu->cafeteriaType->name;
                $color = $this->menu_colors[$order->cafeteriaMenu->cafeteria_type_id];
                $start = $order->cafeteriaMenu->date;
                $price = $order->cafeteriaMenu->price;
                $description = $order->cafeteriaMenu->description;
                $observations = $order->observations;
            }

            $data[] = (object)[
                "order_id" => $order->id,
                "observations" => $observations,
                "description" => $description,
                "price" => $price,
                "location_id" => $order->location_id,
                "quantity" => $order->quantity,
                "total" => $order->total,
                "payment_date" => $order->payment_date,
                "title" => $title,
                "start" => $start,
                "icon" => 'hamburger',
                "color" => $color
            ];
        }

        return response()->json($data);
    }

    public function saveMenu(Request $request)
    {
        $this->validate($request,
            [
                'type' => 'required',
                'description' => 'required|max:255',
                'price' => 'required|max:6',
            ],
            [
                'type.required' => 'Seleccione el tipo de menú',
                'description.required' => 'Ingrese la descripción del menú',
                'price.required' => 'Ingrese el precio del menú',
            ]
        );

        try {
            DB::beginTransaction();

            $menu = new CafeteriaMenu();

            $exists = $menu::where('cafeteria_type_id', $request->type)->where('date', $request->date)->get();

            if ($exists->count() > 0) {
                return response()->json(['success' => false, 'exists' => true]);
            }

            $menu->description = $request->description;
            $menu->cafeteria_type_id = $request->type;
            $menu->price = $request->price;
            $menu->date = $request->date;
            $menu->created_by = Auth::user()->id;
            $success = $menu->save();

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function editMenu(Request $request)
    {
        $this->validate($request,
            [
                'description' => 'required|max:255',
                'price' => 'required|max:6',
            ],
            [
                'description.required' => 'Ingrese la descripción del menú',
                'price.required' => 'Ingrese el precio del menú',
            ]
        );

        try {
            DB::beginTransaction();

            $menu = CafeteriaMenu::find($request->id);

            $menu->description = $request->description;
            $menu->price = $request->price;
            $success = $menu->save();

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function getDayMenu(Request $request)
    {
        $menu = CafeteriaMenu::where('date', $request->date)->where('cafeteria_type_id', $request->type)->first();

        if(is_null($menu)) {
            return response()->json(['success' => true, 'exists' => false]);
        }

        return response()->json(['success' => true, 'exists' => true, 'menu' => $menu]);
    }

    public function saveOrder(Request $request)
    {
        $this->validate($request,
            [
                'menu_id' => 'required',
            ],
            [
                'menu_id.required' => 'Debe seleccionar el tipo de menú',
            ]
        );

        try {
            $exists = CafeteriaOrder::where('cafeteria_menu_id', $request->menu_id)->where('user_id', $request->user_id)->where('date', $request->date)->get();

            if($exists->count() > 0) {
                return response()->json(['success' => false, 'exists' => true]);
            }

            DB::beginTransaction();

            if($request->menu_id === 0)
            {
                $cafeteria_id          = 0;
                $cafeteria_type_id     = 1;
                $cafeteria_type_name   = 'Desayuno';
                $cafeteria_description = "$request->details" . (!empty($request->observations) ? " ($request->observations)": '');
                $menu_date             = $request->date;
                $menu_description = "";
            }
            else
            {
                $menu = CafeteriaMenu::find($request->menu_id);
                $cafeteria_id          = $menu->id;
                $cafeteria_type_id     = $menu->cafeteria_type_id;
                $cafeteria_type_name   = $menu->cafeteriaType->name;
                $cafeteria_description = !empty($request->observations) ? "$request->observations": '';
                $menu_date             = $menu->date;
                $menu_description      = $menu->description;
            }

            $order_day = Carbon::parse($menu_date)->day;
            $last_day_of_month = Carbon::now()->endOfMonth()->day;

            if(($order_day >= 1 && $order_day <= 14) || ($order_day == $last_day_of_month))
            {
                $for_date = Carbon::parse($menu_date)->year . "-" . Carbon::parse($menu_date)->month . "-07";

                if($order_day == $last_day_of_month) {
                    $date = Carbon::parse($menu_date)->addDay();
                    $for_date = $date->year . "-" . $date->month . "-07";
                }
            }
            else
            {
                $for_date = Carbon::parse($menu_date)->year . "-" . Carbon::parse($menu_date)->month . "-27";
            }

            $order = new CafeteriaOrder();
            $order->observations = $cafeteria_description;
            $order->user_id = $request->user_id;
            $order->cafeteria_menu_id = $request->menu_id;
            $order->location_id = $request->location_id;
            $order->quantity = $request->quantity;
            $order->total = round($request->total, 2);
            $order->date = $menu_date;
            $order->payment_date = $for_date;
            $success = $order->save();

            if($success) {
                $current_date = Carbon::now()->format('Y-m-d');
                $should_finish = Carbon::parse($current_date)->addDay(1);

                if ($menu_date == $current_date) {
                    $task_controller = new TaskController();

                    $cafeteria_task_date = Task::where('created_by_type', 0)->where('created_by', 15)->where('cafeteria_type_id', $cafeteria_type_id)->whereRaw("DATE(`created_at`) = '$current_date'")->first();

                    if (is_null($cafeteria_task_date)) {
                        // Create task
                        $task = new Task();
                        $task->created_by_type = 0; // Role
                        $task->created_by = 15; // Cafetería y Aseo
                        $task->cafeteria_type_id = $cafeteria_type_id; // Cafeteria type
                        $task->title = "Pedidos " . strtoupper($cafeteria_type_name) . " " . Carbon::parse($menu_date)->format('d/M/Y');
                        $task->status = 0;
                        $task->should_finish = $should_finish;
                        $task->terminated_by = 0;
                        $task->code = $task_controller->generateCode();
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
                        $location_orders = CafeteriaOrder::where('cafeteria_menu_id', $cafeteria_id)->where('location_id', $location->id)->where('date', $menu_date)->get();

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

                    $menu_orders = CafeteriaOrder::where('cafeteria_menu_id', $cafeteria_id)->where('date', $menu_date)->get();

                    $task_comment = new TaskComment();
                    $task_comment->task_id = $cafeteria_task_date->id;
                    $task_comment->user_id = 1;
                    $task_comment->comment = "<h5 class='text-bold'>" . ($cafeteria_type_id == 1 ? "Desayunos" : $menu_description) . "</h5><hr> <b>Pedidos</b>:<br><br>" . $locations_orders . "<br> <b>Total</b>: $total_orders";
                    $task_comment->save();

                    $create_file = $this->createCafeteriaOrdersFile($cafeteria_id, $cafeteria_type_id, $menu_date);

                    if ($create_file['success']) {
                        $filename = $create_file['filename'];

                        $task_comment_attachment = new TaskCommentAttachment();
                        $task_comment_attachment->task_comments_id = $task_comment->id;
                        $task_comment_attachment->file = $filename;
                        $task_comment_attachment->save();
                    }
                }

                $user_order = User::find($request->user_id);

                if ($user_order->setting_role_id != 14) // If NOT a model
                {
                    if($request->menu_id == 0) {
                        $cafeteria_description_show = "$cafeteria_type_name - $cafeteria_description";
                    } else {
                        $cafeteria_description_show = "$cafeteria_type_name - $menu_description" . (!empty($request->observations) ? " ($request->observations)" : "") . "";
                    }

                    $payroll_movement = new PayrollMovement();
                    $payroll_movement->user_id = $request->user_id;
                    $payroll_movement->payroll_type_id = 7; // Cafeteria
                    $payroll_movement->amount = round($request->total, 2);
                    $payroll_movement->created_by = auth()->user()->id;
                    $payroll_movement->comment = "$cafeteria_description_show (Cantidad: $request->quantity)";
                    $payroll_movement->for_date = $for_date;
                    $payroll_movement->automatic = 1;
                    $payroll_movement->save();
                }
                else
                {
                    $owner = SatelliteOwner::where('user_id', $request->user_id)->first();

                    if(is_null($owner)) {
                        DB::rollback();
                        return response()->json(['success' => false, 'msg' => 'No se pudo registrar el pedido. No existe un propietario asignado a la modelo.', 'code' => 1]);
                    }

                    if($request->menu_id == 0) {
                        $cafeteria_description_show = "$cafeteria_type_name - $cafeteria_description";
                    } else {
                        $cafeteria_description_show = "$cafeteria_type_name - $menu_description" . (!empty($request->observations) ? " ($request->observations)" : "") . "";
                    }

                    $owner_id = $owner->id;
                    $owner_last_payment_date = SatellitePaymentAccount::where('owner_id', $owner_id)->max('payment_date');

                    $deducction = new SatellitePaymentDeduction();
                    $deducction->payment_date = $owner_last_payment_date;
                    $deducction->owner_id = $owner_id;
                    $deducction->deduction_to = 3; // Valor pago
                    $deducction->total = round($request->total, 2);
                    $deducction->amount = round($request->total, 2);
                    $deducction->description = "Cafetería: $cafeteria_description_show (Cantidad: $request->quantity)";
                    $deducction->type = 2;
                    $deducction->type_foreign_id = $order->id;
                    $deducction->created_by = Auth::user()->id;
                    $deducction->save();
                }
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function editMaxOrderTime(Request $request)
    {
        $this->validate($request,
            [
                'Desayuno' => 'required',
                'Almuerzo' => 'required',
                'Refrigerio' => 'required',
                'Trasnocho' => 'required',
            ],
            [
                'Desayuno.required' => 'Debe seleccionar la hora máxima de pedido',
                'Almuerzo.required' => 'Debe seleccionar la hora máxima de pedido',
                'Refrigerio.required' => 'Debe seleccionar la hora máxima de pedido',
                'Trasnocho.required' => 'Debe seleccionar la hora máxima de pedido',
            ]
        );

        try {
            DB::beginTransaction();

            $cafeteria_types = CafeteriaType::all();

            foreach ($cafeteria_types AS $type) {
                $cafeteria_type = CafeteriaType::find($type->id);

                switch ($type->id) {
                    case 1:
                        $cafeteria_type->max_order_time = $request->Desayuno;
                        break;

                    case 2:
                        $cafeteria_type->max_order_time = $request->Almuerzo;
                        break;

                    case 3:
                        $cafeteria_type->max_order_time = $request->Refrigerio;
                        break;

                    case 4:
                        $cafeteria_type->max_order_time = $request->Trasnocho;
                        break;
                }

                $cafeteria_type->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function getWeekTotalSales(Request $request)
    {
        $types = [];

        $cafeteria_types = CafeteriaType::all();

        foreach($cafeteria_types AS $type) {
            $types[$type->id] = 0;
        }

        $cafeteria_orders = CafeteriaOrder::whereBetween('date', [$request->start, $request->end])->get();

        foreach($cafeteria_orders AS $order) {
            $total = $order->total;
            $cafeteria_type_id = $order->cafeteria_menu_id == 0 ? 1 : $order->cafeteriaMenu->cafeteriaType->id;
            $types[$cafeteria_type_id] = $types[$cafeteria_type_id] + $total;
        }

        return response()->json(['success' => true, 'totals' => $types]);
    }

    public function createCafeteriaOrdersFile($menu_id, $type, $date, $studio_slug = null)
    {
        $orders = [];

        $locations = SettingLocation::all();

        if($type == 1) { // Desayuno
            $cafeteria_type_name = 'Desayuno';
            $menu_description = '';
        } else {
            $menu = CafeteriaMenu::find($menu_id);
            $cafeteria_type_name = $menu->cafeteriaType->name;
            $date = $menu->date;
            $menu_description = $menu->description;
        }

        foreach ($locations AS $location) {
            if($type == 1) { // Desayuno
                $location_orders = CafeteriaOrder::where('cafeteria_menu_id', 0)->where('location_id', $location->id)->where('date', $date)->get();
            } else {
                $location_orders = CafeteriaOrder::where('cafeteria_menu_id', $menu_id)->where('location_id', $location->id)->get();
            }

            if($location_orders->count() > 0) {
                foreach($location_orders AS $order) {
                    $orders[$location->name][] = [
                        'name' => $order->user->roleUserShortName(),
                        'observations' => $order->observations,
                        'quantity' => $order->quantity,
                    ];
                }
            }
        }

        $filename = "Pedidos " . $cafeteria_type_name . " - ($date).xlsx";

        if(Storage::exists(tenant('studio_slug') . "/task/$filename")) {
            Storage::delete(tenant('studio_slug') . "/task/$filename");
        }

        $success = Excel::store(new Orders($orders, $cafeteria_type_name, $menu_description), "public/$studio_slug/task/$filename");

        return ['success' => $success, 'filename' => $filename];
    }

    public function CRON_createDayCafeteriaTask($studio_slug = 'GB', $date = null)
    {
        $date = is_null($date) ? Carbon::now()->toDateString() : $date;

        $day_orders = CafeteriaOrder::where('date', $date)->get();

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
                    $task->title = "Pedidos <b>" . strtoupper($cafeteria_type_name) . "</b> " . Carbon::parse($date)->format('d/M/Y');
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

                $create_file = $this->createCafeteriaOrdersFile($order->cafeteria_menu_id, $type, $date, $studio_slug);

                if ($create_file['success']) {
                    $filename = $create_file['filename'];

                    $task_comment_attachment = new TaskCommentAttachment();
                    $task_comment_attachment->task_comments_id = $task_comment->id;
                    $task_comment_attachment->file = $filename;
                    $task_comment_attachment->save();
                }
            }
        }
    }
}
