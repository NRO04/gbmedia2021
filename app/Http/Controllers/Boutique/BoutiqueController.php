<?php

namespace App\Http\Controllers\Boutique;

use App\Exports\Boutique\Inventory;
use App\Exports\Boutique\Sales;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tasks\TaskController;
use App\Models\Alarms\Alarm;
use App\Models\Boutique\BoutiqueBlockedUser;
use App\Models\Boutique\BoutiqueBlockedValue;
use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueInventory;
use App\Models\Boutique\BoutiqueInventoryIngress;
use App\Models\Boutique\BoutiqueLog;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductsLog;
use App\Models\Boutique\BoutiqueSale;
use App\Models\Boutique\BoutiqueSatelliteSale;
use App\Models\Cafeteria\CafeteriaOrder;
use App\Models\Payrolls\PayrollBoutique;
use App\Models\Payrolls\PayrollBoutiqueInstallment;
use App\Models\Payrolls\PayrollMovement;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Models\Satellite\SatellitePaymentDeduction;
use App\Models\Settings\SettingLocation;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\User;
use Carbon\Carbon;
use Hamcrest\Core\Set;
use http\Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use App\Traits\TraitGlobal;
use function MongoDB\BSON\toJSON;
use function React\Promise\Stream\first;

class BoutiqueController extends Controller
{
    use TraitGlobal;

    protected $logs_keys = null;

    public function __construct()
    {
        $this->middleware('auth');

        $this->logs_keys = [
            'name' => 'Nombre',
            'unit_price' => 'Precio Unitario',
            'wholesaler_price' => 'Precio Mayorista',
            'image' => 'Imagen',
            'boutique_category_id' => 'Categoría',
            'nationality' => 'Nacionalidad',
            'barcode' => 'Código de Barras',
            'stock_alarm' => 'Alarma Inventario',
            'location_alarm' => 'Alarma Locación',
        ];

        // Access to only certain methods
        $this->middleware('permission:boutique-products')->only('products');
        $this->middleware('permission:boutique-categories')->only('categories');
        $this->middleware('permission:boutique-sales')->only('sales');
        $this->middleware('permission:boutique-satellite-sales')->only('satelliteSales');
        $this->middleware('permission:boutique-inventory')->only('inventoryIngresses');
        $this->middleware('permission:boutique-inventory-create')->only('inventory');
        $this->middleware('permission:boutique-blocked-user')->only('blocks');
        $this->middleware('permission:boutique-massive')->only('massive');
    }

    // VIEWS

    public function products()
    {
        $categories = BoutiqueCategory::orderBy('name')->get();
        $models = User::where('setting_role_id', 14)->where('is_admin', '!=', 1)->where('status', 1)->orderBy('nick', 'asc')->get();
        $users = User::where('setting_role_id', '!=', 14)->where('is_admin', '!=', 1)->where('status', 1)->orderBy('first_name', 'asc')->get();
        $locations = SettingLocation::where('name', '!=', 'All')->get();
        $base_location_name = $this->getBaseLocation()->base_location_name;

        return view('adminModules.boutique.products')->with(compact('locations', 'categories', 'users', 'models', 'base_location_name'));
    }

    public function categories()
    {
        return view('adminModules.boutique.categories');
    }

    public function purchases()
    {
        return view('adminModules.boutique.purchases');
    }

    public function sales()
    {
        $min_date = BoutiqueSale::min('created_at');
        $max_date = BoutiqueSale::max('created_at');

        $weeks = $this->getDistinctWeeksBetweenDates($min_date, $max_date);
        $selected_week = $weeks[0]->formatted;
        $selected_week_start = $weeks[0]->start;
        $selected_week_end = $weeks[0]->end;

        if(is_null($min_date) && is_null($max_date)) {
            $selected_week = Carbon::now()->startOfWeek(Carbon::SUNDAY)->format('d/M/Y') . " - " . Carbon::now()->endOfWeek(Carbon::SATURDAY)->format('d/M/Y');
            $selected_week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $selected_week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
        }

        foreach ($weeks AS $key => $week) {
            $have_purchases = BoutiqueSale::with('product')->orderBy('created_at', 'DESC')->whereBetween(DB::raw('DATE(created_at)'), [$week->start, $week->end])->get();

            if($have_purchases->count() == 0) {
                unset($weeks[$key]);
            }
        }

        return view('adminModules.boutique.sales')->with(compact('weeks', 'selected_week', 'selected_week_start', 'selected_week_end'));
    }

    public function satelliteSales()
    {
        $min_date = BoutiqueSatelliteSale::min('created_at');
        $max_date = BoutiqueSatelliteSale::max('created_at');

        $weeks = $this->getDistinctWeeksBetweenDates($min_date, $max_date);
        $selected_week = $weeks[0]->formatted;
        $selected_week_start = $weeks[0]->start;
        $selected_week_end = $weeks[0]->end;

        if(is_null($min_date) && is_null($max_date)) {
            $selected_week = Carbon::now()->startOfWeek(Carbon::SUNDAY)->format('d/M/Y') . " - " . Carbon::now()->endOfWeek(Carbon::SATURDAY)->format('d/M/Y');
            $selected_week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $selected_week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
        }

        foreach ($weeks AS $key => $week) {
            $have_purchases = BoutiqueSatelliteSale::with('product')->orderBy('created_at', 'DESC')->whereBetween(DB::raw('DATE(created_at)'), [$week->start, $week->end])->get();

            if($have_purchases->count() == 0) {
                unset($weeks[$key]);
            }
        }

        return view('adminModules.boutique.satellite-sales')->with(compact('weeks', 'selected_week', 'selected_week_start', 'selected_week_end'));
    }

    public function blocks()
    {
        $blocked_value = 0;
        $blocked_value_formatted = number_format(0, 0, ',', '.');

        $blocked_value_model = BoutiqueBlockedValue::first();

        if($blocked_value_model) {
            $blocked_value = $blocked_value_model->value;
            $blocked_value_formatted = number_format($blocked_value_model->value, 0, ',', '.');
        }

        $models = User::where('setting_role_id', 14)->where('status', 1)->orderBy('nick', 'asc')->get();
        $users = User::where('setting_role_id', '!=', 14)->where('status', 1)->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();

        return view('adminModules.boutique.blocks')->with(compact('models', 'users', 'blocked_value', 'blocked_value_formatted'));
    }

    public function massive()
    {
        $models = User::where('setting_role_id', 14)->where('status', 1)->orderBy('nick', 'asc')->get();
        $users = User::where('setting_role_id', '!=', 14)->where('status', 1)->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')->get();
        $owners = SatelliteOwner::orderBy('owner', 'asc')->where('is_user', '!=', 1)->get();

        return view('adminModules.boutique.massive')->with(compact('users', 'models', 'owners'));
    }

    public function inventory()
    {
/*        $models = User::where('setting_role_id', 14)->orderBy('nick', 'asc')->get();
        $users = User::where('setting_role_id', '!=', 14)->orderBy('first_name', 'asc')->get();
        $owners = SatelliteOwner::orderBy('owner', 'asc')->get();*/
        $products = BoutiqueProduct::orderBy('name', 'asc')->get();

        return view('adminModules.boutique.inventory')->with(compact('products'));
    }

    public function inventoryIngresses(Request $request)
    {
        $ingresses = [];
        $ingresses_dates = [];
        $selected_datetime = null;

        $inventory_ingresses_dates = BoutiqueInventoryIngress::select('created_at')->groupBy('created_at')->orderBy('created_at', 'desc')->get();

        foreach ($inventory_ingresses_dates AS $ingresses_date) {
            $ingresses_dates[] = (object)[
                'date' => $ingresses_date->created_at,
                'formatted' => Carbon::parse($ingresses_date->created_at)->format('d/m/Y h:m:s a'),
            ];
        }

        if($request->datetime) {
            $selected_datetime = $request->datetime;

            $inventory_ingresses = BoutiqueInventoryIngress::where('created_at', $request->datetime)->get();

            if($inventory_ingresses->count() <= 0) {
                return redirect()->route('boutique.inventory_ingresses');
            }

            $ingresses['info'] = (object)[
                'a1' => (object)[
                    'title' => 'Cambio Dólar (A1)',
                    'value' => $inventory_ingresses[0]->A1,
                ],
                'a2' => (object)[
                    'title' => 'Total Trasporte Pago (A2)',
                    'value' => $inventory_ingresses[0]->A2,
                ],
                'a3' => (object)[
                    'title' => 'Trasporte x Peso (A3)',
                    'value' => $inventory_ingresses[0]->A3,
                ],
                'a4' => (object)[
                    'title' => 'Total Producto Pesos (A4)',
                    'value' => $inventory_ingresses[0]->A4,
                ],
                'a5' => (object)[
                    'title' => 'Total Compra (A5)',
                    'value' => $inventory_ingresses[0]->A5,
                ],
                'a6' => (object)[
                    'title' => 'Venta Tota (A6)',
                    'value' => $inventory_ingresses[0]->A6,
                ],
                'a7' => (object)[
                    'title' => 'Utilidad (A7)',
                    'value' => $inventory_ingresses[0]->A7,
                ],
                'created_by' => $inventory_ingresses[0]->user->roleUserShortName(),
            ];

            foreach ($inventory_ingresses AS $key => $ingress) {
                $ingress->product_name = $ingress->product->name;
                $ingresses['products'][$key] = $ingress;
            }
        }

        return view('adminModules.boutique.inventory-ingresses')->with(compact('ingresses_dates', 'ingresses', 'selected_datetime'));
    }

    // ACTIONS

    public function getProducts(Request $request)
    {
        $all_products = [];
        $products = [];
        $total = 0;

        if ($request->ajax()) {
            if(!$request->category_id && !$request->stock && !$request->location_id)
            {
                $products = BoutiqueProduct::where('active', 1)->with('boutiqueCategory')->with('boutiqueInventory')->orderBy('id')->get();
            }
            else
            {
                if($request->category_id)
                {
                    $products = BoutiqueProduct::where('active', 1)
                        ->where('boutique_category_id', $request->category_id)
                        ->with('boutiqueCategory')
                        ->with('boutiqueInventory')
                        ->orderBy('name')
                        ->get();
                }
                elseif ($request->stock)
                {
                    $search_products = BoutiqueProduct::where('active', 1)
                        ->with('boutiqueCategory')
                        ->with('boutiqueInventory')
                        ->orderBy('name')
                        ->get();

                    foreach ($search_products AS $product) {
                        $product_inventories = $product->boutiqueInventory;

                        foreach ($product_inventories AS $product_inventory) {
                            if($request->stock == 1)
                            {
                                if($product_inventory->quantity > 0) {
                                    $products[] = $product;
                                    break;
                                }
                            }
                            else
                            {
                                if($product_inventory->quantity <= 0) {
                                    $products[] = $product;
                                    break;
                                }
                            }
                        }
                    }

                    $products = collect($products);
                }
                elseif ($request->location_id)
                {
                    $search_products = BoutiqueProduct::where('active', 1)
                        ->with('boutiqueCategory')
                        ->with('boutiqueInventory')
                        ->orderBy('name')
                        ->get();

                    foreach ($search_products AS $product) {
                        $product_inventories = $product->boutiqueInventory;

                        foreach ($product_inventories AS $product_inventory) {
                            if ($product_inventory->quantity > 0 && $product_inventory->setting_location_id == $request->location_id) {
                                $products[] = $product;
                                continue;
                            }
                        }
                    }

                    $products = collect($products);
                }
            }


            foreach ($products AS $product) {
                $total_quantity = 0;

                foreach ($product->boutiqueInventory AS $inventory) {
                    $total_quantity = $total_quantity + $inventory->quantity;
                    $product_total = $inventory->quantity * $product->unit_price;
                }

                if($total_quantity > 0) {
                    $total_product = $total_quantity * $product->unit_price;
                    $total = $total + $total_product;

                    $all_products[] = $product;
                }
            }

            return DataTables::of($all_products)
                ->addIndexColumn()
                ->addColumn('name', function($row) {
                    $name = "<span>$row->name</span>";
                    $name .= "<div class='small text-muted'>ID: $row->id" . (!is_null($row->barcode) ? " | Código: $row->barcode" : "") . "</div>";

                    return $name;
                })
                ->addColumn('image', function($row) {
                    if(!is_null($row->image) && File::exists("../storage/app/public/" . tenant('studio_slug') . "/boutique/" . $row->image)) {
                        $image = "<img src='" . global_asset("../storage/app/public/" . tenant('studio_slug') . "/boutique/$row->image") . "' alt='$row->name' class='img-avatar zoom-img'>";
                    } else {
                        $image = "<img src='" . asset("images/svg/no-photo.svg") . "' title='Sin imagen' class='img-avatar'>";
                    }

                    return $image;
                })
                ->addColumn('unit_price', function($row) {
                    return "$" . number_format($row->unit_price, '0', ',', '.');
                })
                ->addColumn('wholesaler_price', function($row) {
                    return "$" . number_format($row->wholesaler_price, '0', ',', '.');
                })
                ->addColumn('total', function($row) {
                    return "$" . number_format($row->wholesaler_price, '0', ',', '.');
                })
                ->addColumn('category_name', function($row) {
                    return $row->boutiqueCategory->name;
                })
                ->addColumn('quantity', function($row) {
                    $locations_quantities = "";
                    $total_quantity = 0;

                    foreach ($row->boutiqueInventory AS $inventory) {
                        $location_name = $inventory->settingLocation->name;
                        $total_quantity = $total_quantity + $inventory->quantity;
                        $locations_quantities .= "$location_name: $inventory->quantity<br>";
                    }

                    return "<span class='badge badge-secondary' data-html='true' data-toggle='tooltip' title='$locations_quantities'>$total_quantity</span>";
                })
                ->addColumn('total', function($row) {
                    $total_inventory = 0;

                    foreach ($row->boutiqueInventory AS $inventory) {
                        $total = $inventory->quantity * $row->unit_price;
                        $total_inventory = $total_inventory + $total;
                    }

                    return "$" . number_format($total_inventory, '0', ',', '.');
                })
                ->addColumn('actions', function($row) {
                    $sell   = "";
                    $edit   = "";
                    $move   = "";
                    $return = "";
                    $delete = "";
                    $logs   = "";

                    $user = Auth::user();

                    if ($user->can('boutique-products-sell')) {
                        $sell = "<button type='button' title='Vender' class='btn btn-sm btn-primary mr-2' onclick='sellProduct($row->id)'><i class='fa fa-shopping-cart'></i></button>";
                    }

                    if ($user->can('boutique-products-transfer')) {
                        $move = "<button type='button' title='Trasladar' class='btn btn-sm btn-outline-info' onclick='transferProduct($row->id)'><i class='fas fa-share'></i></button>";
                    }

                    if ($user->can('boutique-products-return')) {
                        $return = "<button type='button' title='Regresar' class='btn btn-sm btn-outline-info mr-2' onclick='returnProduct($row->id)'><i class='fas fa-reply'></i></button>";
                    }

                    if ($user->can('boutique-products-edit')) {
                        $edit = "<button type='button' title='Editar' class='btn btn-sm btn-warning' onclick='editProduct($row->id)'><i class='fa fa-edit'></i></button>";
                    }

                    if ($user->can('boutique-products-delete')) {
                        $delete = "<button type='button' title='Inactivar' class='btn btn-sm btn-danger mr-2' onclick='deleteProduct($row->id)'><i class='fas fa-trash'></i></button>";
                    }

                    if ($user->can('boutique-logs')) {
                        $logs = "<button type='button' title='Historial' class='btn btn-sm btn-outline-warning' onclick='logsProduct($row->id)'><i class='fa fa-history'></i></button>";
                    }

                    return "$sell $move $return $edit $delete $logs";
                })
                ->rawColumns(['name', 'image', 'category_name', 'unit_price', 'wholesaler_price', 'total', 'category_name', 'quantity', 'total', 'actions'])
                ->with([
                    'total' => "$" . number_format($total, 0, ',', '.'),
                ])
                ->make(true);
        }
    }

    public function getCategories(Request $request)
    {
        if ($request->ajax()) {
            $categories = BoutiqueCategory::with('products')->orderBy('name')->get();

            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('created_at', function($row) {
                    return Carbon::parse($row->created_at)->format('d / M / Y');
                })
                ->addColumn('products_count', function($row) {
                    return "<span class='badge badge-info'>" . $row->products->count() . "</span>";
                })
                ->addColumn('actions', function($row) {
                    $edit   = "";
                    $delete = "";

                    $user = Auth::user();

                    if ($user->can('boutique-categories-edit')) {
                        $edit = "<button type='button' title='Editar' class='btn btn-sm btn-warning' onclick='edit($row->id)'><i class='fa fa-edit'></i></button>";
                    }

                    if ($user->can('boutique-categories-delete')) {
                        $delete = "<button type='button' id='edit-category-$row->id' title='Eliminar' class='btn btn-sm btn-danger' data-name='$row->name' onclick='remove($row->id)' " . ($row->products->count() > 0 ? 'disabled' : '') . "><i class='fas fa-trash'></i></button>";
                    }

                    return "$edit $delete";
                })
                ->rawColumns(['created_at', 'products_count', 'actions'])
                ->make(true);
        }
    }

    public function getMyPurchases(Request $request)
    {
        if ($request->ajax()) {
            $purchases = BoutiqueSale::where('buyer_user_id', Auth::user()->id)->with('product')->orderBy('created_at', 'DESC')->get();

            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->created_at)->format('d / M / Y');
                })
                ->addColumn('image', function($row) {
                    if(!is_null($row->product->image)) {
                        $image = "<img src='" . global_asset("../storage/app/public/" . tenant('studio_slug') . "/boutique/" . $row->product->image) . "' alt='" . $row->product->name . "' class='img-avatar zoom-img'>";
                    } else {
                        $image = "<img src='" . asset("images/svg/no-photo.svg") . "' title='Sin imagen' class='img-avatar'>";
                    }

                    return $image;
                })
                ->addColumn('unit_price', function($row) {
                    return "$" . number_format($row->product->unit_price, 0, ',', '.');
                })
                ->addColumn('quantity', function($row) {
                    return "<span class='badge badge-secondary'>" . $row->quantity . "</span>";
                })
                ->addColumn('seller', function($row) {
                    return $row->seller->roleUserShortName();
                })
                ->rawColumns(['date', 'image', 'unit_price', 'seller', 'quantity'])
                ->make(true);
        }
    }

    public function getSales(Request $request)
    {
        if ($request->ajax()) {
            $total = 0;

            $purchases = BoutiqueSale::with('product')->orderBy('created_at', 'DESC')->whereBetween(DB::raw('DATE(created_at)'), [$request->start_date, $request->end_date])->get();

            foreach ($purchases AS $purchase) {
                $total = $total + $purchase->total;
            }

            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->created_at)->format('d / M / Y');
                })
                ->addColumn('image', function($row) {
                    if(!is_null($row->product->image) && File::exists("../storage/app/public/" . tenant('studio_slug') . "/boutique/" . $row->product->image)) {
                        $image = "<img src='" . global_asset("../storage/app/public/" . tenant('studio_slug') . "/boutique/" . $row->product->image) . "' alt='" . $row->product->name . "' class='img-avatar zoom-img'>";
                    } else {
                        $image = "<img src='" . asset("images/svg/no-photo.svg") . "' title='Sin imagen' class='img-avatar'>";
                    }

                    return $image;
                })
                ->addColumn('total', function($row) {
                    return "$" . number_format($row->total, 0, ',', '.');
                })
                ->addColumn('quantity', function($row) {
                    return "<span class='badge badge-secondary'>" . $row->quantity . "</span>";
                })
                ->addColumn('buyer', function($row) {
                    return $row->buyer->roleUserShortName();
                })
                ->addColumn('seller', function($row) {
                    return $row->seller->roleUserShortName();
                })
                ->addColumn('actions', function($row) {
                    $delete = "";

                    $user = Auth::user();

                    if ($user->can('boutique-sales-delete')) {
                        $delete = "<button type='button' title='Eliminar venta' class='btn btn-sm btn-danger' onclick='deleteSale($row->id)'><i class='fas fa-trash'></i></button>";
                    }

                    return "$delete";
                })
                ->rawColumns(['date', 'image', 'unit_price', 'buyer', 'seller', 'quantity', 'actions', 'total'])
                ->with([
                    'total' => "$" . number_format($total, 0, ',', '.'),
                ])
                ->make(true);
        }
    }

    public function getSatelliteSales(Request $request)
    {
        if ($request->ajax()) {
            $total = 0;

            $purchases = BoutiqueSatelliteSale::with('product')->orderBy('created_at', 'DESC')->whereBetween(DB::raw('DATE(created_at)'), [$request->start_date, $request->end_date])->get();

            foreach ($purchases AS $purchase) {
                $total = $total + $purchase->total;
            }

            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->created_at)->format('d / M / Y');
                })
                ->addColumn('image', function($row) {
                    if(!is_null($row->product->image) && File::exists("../storage/app/public/" . tenant('studio_slug') . "/boutique/" . $row->product->image)) {
                        $image = "<img src='" . global_asset("../storage/app/public/" . tenant('studio_slug') . "/boutique/" . $row->product->image) . "' alt='" . $row->product->name . "' class='img-avatar zoom-img'>";
                    } else {
                        $image = "<img src='" . asset("images/svg/no-photo.svg") . "' title='Sin imagen' class='img-avatar'>";
                    }

                    return $image;
                })
                ->addColumn('total', function($row) {
                    return "$" . number_format($row->total, 0, ',', '.');
                })
                ->addColumn('quantity', function($row) {
                    return "<span class='badge badge-secondary'>" . $row->quantity . "</span>";
                })
                ->addColumn('buyer', function($row) {
                    return $row->buyer->owner;
                })
                ->addColumn('seller', function($row) {
                    return $row->seller->roleUserShortName();
                })
                ->addColumn('actions', function($row) {
                    $delete = "";

                    $user = Auth::user();

                    if ($user->can('boutique-satellite-sales-delete')) {
                        $delete = "<button type='button' title='Eliminar venta' class='btn btn-sm btn-danger' onclick='deleteSale($row->id)'><i class='fas fa-trash'></i></button>";
                    }

                    return "$delete";
                })
                ->rawColumns(['date', 'image', 'unit_price', 'buyer', 'seller', 'quantity', 'actions', 'total'])
                ->with([
                    'total' => "$" . number_format($total, 0, ',', '.'),
                ])
                ->make(true);
        }
    }

    public function getBlockedUsers(Request $request)
    {
        if ($request->ajax()) {

            $blocked_users = BoutiqueBlockedUser::with('blockedUser:id,first_name,last_name,setting_role_id,nick,avatar')->with('blockedByUser:id,first_name,last_name')->orderBy('created_at', 'DESC')->get();

            return DataTables::of($blocked_users)
                ->addIndexColumn()
                ->addColumn('user', function($row) {
                    return $row->blockedUser->roleUserShortName();
                })
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->created_at)->format('d / M / Y');
                })
                ->addColumn('image', function($row) {
                    if(!is_null($row->blockedUser->avatar) && File::exists("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $row->blockedUser->avatar)) {
                        $image = "<img src='" . global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $row->blockedUser->avatar) . "' alt='" . $row->blockedUser->name . "' class='img-avatar zoom-img'>";
                    } else {
                        $image = "<img src='" . asset("images/svg/no-photo.svg") . "' title='Sin imagen' class='img-avatar'>";
                    }

                    return $image;
                })
                ->addColumn('blocked_by', function($row) {
                    return $row->blockedByUser->roleUserShortName();
                })
                ->addColumn('actions', function($row) {
                    $delete = "";

                    $user = Auth::user();

                    if ($user->can('boutique-blocked-user-delete')) {
                        $delete = "<button type='button' title='Eliminar usuario' class='btn btn-sm btn-danger' onclick='deleteUserBlocked($row->id)'><i class='fas fa-trash'></i></button>";
                    }

                    return "$delete";
                })
                ->rawColumns(['date', 'image', 'blocked_by', 'actions'])
                ->make(true);
        }
    }

    public function getProduct(Request $request)
    {
        $product = BoutiqueProduct::where('id', $request->id)->with('boutiqueInventory')->first();
        $product->unit_price_format = "$" . number_format($product->unit_price, '0', ',', '.');
        $product->wholesaler_price_format = "$" . number_format($product->wholesaler_price, '0', ',', '.');
        $locations = SettingLocation::where('id', '!=', 1)->get();

        $locations_stock = [];

        foreach ($product->boutiqueInventory AS $item) {
            $locations_stock[$item->settingLocation->id] = [
                'id' => $item->settingLocation->id,
                'name' => $item->settingLocation->name,
                'base' => $item->settingLocation->base,
                'quantity' => $item->quantity,
            ];
        }

        foreach ($locations AS $location) {
            if(!isset($locations_stock[$location->id])) {
                $locations_stock[$location->id] = [
                    'id' => $location->id,
                    'name' => $location->name,
                    'base' => $location->base,
                    'quantity' => 0,
                ];
            }
        }

        unset($product->boutiqueInventory);
        $product->locations_inventory = $locations_stock;

        if(!is_null($product->image)) {
            $product->image = global_asset("../storage/app/public/" . tenant('studio_slug') . "/boutique/$product->image");
        } else {
            $product->image = asset("images/svg/no-photo.svg");
        }

        return $product;
    }

    public function getProductLocationQuantity(Request $request)
    {
        $product = BoutiqueProduct::select('boutique_inventories.*')
            ->join('boutique_inventories', 'boutique_inventories.boutique_product_id', '=', 'boutique_products.id')
            ->where('boutique_products.id', $request->product_id)
            ->where('boutique_inventories.setting_location_id', $request->location_id)
            ->first();

        return $product;
    }

    public function getLogs(Request $request)
    {
        $logs = [];

        if ($request->ajax()) {
            $product_logs = BoutiqueLog::all();

            return DataTables::of($product_logs)
                ->addIndexColumn()
                ->addColumn('user', function($row) {
                    return $row->createdBy->roleUserShortName();
                })
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->created_at)->format('d/M/y H:i');
                })
                ->rawColumns(['date'])
                ->make(true);
        }
    }

    public function getProductLogs(Request $request)
    {
        $logs = [];

        if ($request->ajax()) {
            $product_logs = BoutiqueProduct::where('id', $request->product_id)->with('boutiqueProductLogs')->first();
            $product_name = $product_logs->name;

            foreach ($product_logs->boutiqueProductLogs AS $log) {
                $quantity = $log->type == 'sell' ? $log->quantity : null;

                $old_inventory = $log->old_inventory_quantity;
                $new_inventory = $log->new_inventory_quantity;

                $logs[] = (object)[
                    'action' => $log->action,
                    'quantity' => (int)$quantity,
                    'old_inventory_quantity' => !is_null($old_inventory) ? $old_inventory : '-',
                    'new_inventory_quantity' => !is_null($new_inventory) ? $new_inventory : '-',
                    'user' => $log->createdBy->roleUserShortName(),
                    'date' => $log->created_at,
                    'ip' => $log->ip_address,
                ];
            }

            return DataTables::of($logs)
                ->addIndexColumn()
                ->addColumn('date', function($row) {
                    return Carbon::parse($row->date)->format('Y-m-d h:i a');
                })
                ->rawColumns(['date'])
                ->with([
                    'product_name' => $product_name,
                ])
                ->make(true);
        }
    }

    public function saveProduct(Request $request)
    {
        $this->validate($request,
            [
                'name' => "required|unique:boutique_products,name|max:128",
                'nationality' => "required",
                'category' => "required",
                'barcode' => "nullable|unique:boutique_products,barcode|max:20",
                'stock_alarm' => "numeric",
                'location_alarm' => "numeric",
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre del producto ya existe',
                'nationality.required' => 'Debe seleccionar la nacionalidad del producto',
                'category.required' => 'Debe seleccionar la categoria del producto',
                'barcode.unique' => 'El código de barras ya está asignado a un producto',
                'stock_alarm' => "Debe ser un valor númerico",
                'location_alarm' => "Debe ser un valor númerico",
            ]
        );

        try {
            DB::beginTransaction();

            $product = new BoutiqueProduct();

            $image = $request->file('image');

            if($image) {
                $product->image = $this->tenantUploadFile($image, 'boutique', tenant('studio_slug'));

            }

            if($request->category == 'other') {
                $category = new BoutiqueCategory();
                $category->name = $request->new_category;
                $category->save();
            }

            $product->name = $request->name;
            $product->nationality = $request->nationality;
            $product->boutique_category_id = ($request->category == 'other' ? $category->id : $request->category);
            $product->barcode = $request->barcode;
            $product->stock_alarm = $request->stock_alarm;
            $product->location_alarm = $request->location_alarm;
            $success = $product->save();

            if ($success) {
                $locations = SettingLocation::where('name', '!=', 'All')->get();

                foreach ($locations AS $location) {
                    $productInventory = new BoutiqueInventory();
                    $productInventory->boutique_product_id = $product->id;
                    $productInventory->setting_location_id = $location->id;
                    $productInventory->quantity = 0;
                    $productInventory->save();
                }

                $this->saveProductLog('create', $product->id, "Creado Producto: $request->name");
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function saveCategory(Request $request)
    {
        $this->validate($request,
            [
                'name' => "required|unique:boutique_categories,name|max:128",
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre de la categoría ya existe',
            ]
        );

        try {
            DB::beginTransaction();

            $category = new BoutiqueCategory();
            $category->name = $request->name;
            $success = $category->save();

            if ($success) {
                $this->saveLog('category', $category->id, "Creada categoría: '$request->name'");
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function saveLog($type, $type_id, $action)
    {
        $log = new BoutiqueLog();
        $log->type = $type;
        $log->type_item_id = $type_id;
        $log->created_by = Auth::user()->id;
        $log->action = $action;
        $log->ip_address = Request()->ip();
        $log->save();
    }

    public function saveProductLog($type, $product_id, $action, $old_inventory = null, $new_inventory = null)
    {
        $log = new BoutiqueProductsLog();
        $log->type = $type;
        $log->boutique_product_id = $product_id;
        $log->created_by = Auth::user()->id;
        $log->action = $action;
        $log->ip_address = Request()->ip();

        if(!is_null($old_inventory)) {
            $log->old_inventory_quantity = $old_inventory;
        }

        if(!is_null($new_inventory)) {
            $log->new_inventory_quantity = $new_inventory;
        }

        $log->save();
    }

    public function saveBlockValue(Request $request)
    {
        try {
            DB::beginTransaction();
            $original_value = 0;

            $exists = BoutiqueBlockedValue::first();

            if (!$exists) {
                $blocked_value = new BoutiqueBlockedValue();
                $blocked_value->value = $request->value;
                $success = $blocked_value->save();
            } else {
                $blocked_value = BoutiqueBlockedValue::first();
                $original_value = $blocked_value->value;
                $blocked_value->value = $request->value;
                $success = $blocked_value->save();
            }

            if ($success) {
                $this->saveLog('edit_block_value', $blocked_value->id, "Modificado Valor Máximo de Compra: '$original_value' por '$blocked_value->value'");
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function editProduct(Request $request)
    {
        $this->validate($request,
            [
                'name' => "required|unique:boutique_products,name,$request->id|max:128",
                'unit_price' => "required|numeric",
                'wholesaler_price' => "numeric",
                'nationality' => "required",
                'category' => "required",
                'barcode' => "nullable|unique:boutique_products,barcode,$request->id|max:20",
                'stock_alarm' => "numeric",
                'location_alarm' => "numeric",
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'unit_price.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre del producto ya existe',
                'nationality.required' => 'Debe seleccionar la nacionalidad del producto',
                'category.required' => 'Debe seleccionar la categoria del producto',
                'barcode.unique' => 'El código de barras ya está asignado a un producto',
                'stock_alarm' => "Debe ser un valor númerico",
                'location_alarm' => "Debe ser un valor númerico",
            ]
        );

        try {
            DB::beginTransaction();

            $product = BoutiqueProduct::find($request->id);
            $original_category_name = $product->boutiqueCategory->name;
            $original = (object)$product->getOriginal();

            $base_location = SettingLocation::where('base', 1)->first();
            $base_location_id = $base_location->id;

            $image = $request->file('image');

            if($image) {
                $product->image = $this->uploadFile($image, 'boutique');
            }

            if($request->category == 'other') {
                $category = new BoutiqueCategory();
                $category->name = $request->new_category;
                $category->save();
            }

            $product->name = $request->name;
            $product->unit_price = $request->unit_price;
            $product->wholesaler_price = $request->wholesaler_price;
            $product->nationality = $request->nationality;
            $product->boutique_category_id = ($request->category == 'other' ? $category->id : $request->category);
            $product->barcode = $request->barcode;
            $product->stock_alarm = $request->stock_alarm;
            $product->location_alarm = $request->location_alarm;
            $success = $product->save();

            if ($success) {
                $changes = $product->getChanges();

                foreach ($changes AS $key => $change) {
                    if($key == 'created_at' || $key == 'updated_at') {
                        continue;
                    }

                    if($key == 'image') {
                        $request->$key = $product->image;
                    }

                    if($key == 'boutique_category_id') {
                        $original_category = BoutiqueCategory::select('name')->where('id', $product->boutique_category_id)->first();
                        $original->$key = $original_category_name;
                        $request->$key = $original_category->name;
                    }

                    $this->saveProductLog(
                        'edit',
                        $product->id,
                        "Modificado " . $this->logs_keys[$key] . ": '" . $original->$key . "' por '" . $request->$key . "'"
                    );
                }
            }

            if($request->quantity > 0) {
                $inventory = BoutiqueInventory::firstOrCreate(
                    ['boutique_product_id' => $request->id, 'setting_location_id' => $base_location_id],
                    ['boutique_product_id' => $request->id, 'setting_location_id' => $base_location_id, 'quantity' => $request->quantity]
                );
                $original_inventory_quantity = $inventory->quantity;

                $inventory->quantity = $request->quantity;
                $inventory->save();

                $this->saveProductLog(
                    'edit',
                    $product->id,
                    "Modificada cantidad en $base_location->name",
                    $original_inventory_quantity,
                    $request->quantity
                );
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function editCategory(Request $request)
    {
        $this->validate($request,
            [
                'name' => "required|unique:boutique_categories,name,$request->id|max:128",
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre de la categoría ya existe',
            ]
        );

        try {
            DB::beginTransaction();

            $category = BoutiqueCategory::find($request->id);
            $original_name = $category->name;

            $category->name = $request->name;
            $success = $category->save();

            if ($success) {
                $this->saveLog('category', $category->id, "Modificada Categoría: '$original_name' por '$request->name'");
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function deleteCategory(Request $request)
    {
        try {
            DB::beginTransaction();

            $category = BoutiqueCategory::find($request->id);
            $original_name = $category->name;

            if($category->products->count() > 0) {
                return response()->json(['success' => false, 'msg' => 'No se puede eliminar ya que hay productos registrados en la categoría.'], 500);
            }

            $success = $category->delete();

            if ($success) {
                $this->saveLog('category', $category->id, "Eliminada Categoría: '$original_name'");
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al eliminar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function deleteProduct(Request $request)
    {
        try {
            DB::beginTransaction();

            $product = BoutiqueProduct::find($request->id);
            $product->active = 0;
            $success = $product->save();

            if ($success) {
                $this->saveProductLog(
                    'delete',
                    $request->id,
                    "Producto Inactivado"
                );
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al eliminar el producto. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function deleteSale(Request $request)
    {
        try {
            DB::beginTransaction();

            $deduction = null;

            if(isset($request->is_satellite)) {
                $sale = BoutiqueSatelliteSale::find($request->id);
                $sale_product_id = $sale->boutique_product_id;
                $sale_quantity = $sale->quantity;
                $sale_location_id = $sale->setting_location_id;
                $buyer_owner_id = $sale->buyer_owner_id;

                $owner = SatelliteOwner::find($buyer_owner_id);

                $deduction = SatellitePaymentDeduction::where('type', 3)->where('type_foreign_id', $request->id)->where('owner_id', $owner->id)->first();

                // Check if there is a satellite deduction and it has no payment
                if(!is_null($deduction) && $deduction->times_paid > 0) {
                    DB::rollback();
                    return response()->json(['success' => false, 'msg' => 'No se puede eliminar. Existe un abono asignado a esta venta.', 'code' => 1]);
                }
            }
            else
            {
                $sale = BoutiqueSale::find($request->id);
                $sale_product_id = $sale->boutique_product_id;
                $sale_quantity = $sale->quantity;
                $sale_location_id = $sale->setting_location_id;
                $buyer_user_id = $sale->buyer_user_id;

                $buyer = User::find($buyer_user_id);

                if ($buyer->setting_role_id == 14) {
                    // Check if there is a satellite deduction and it has no payment
                    $deduction = SatellitePaymentDeduction::where('type', 3)->where('type_foreign_id', $request->id)->first();

                    if(!is_null($deduction) && $deduction->times_paid > 0) {
                        DB::rollback();
                        return response()->json(['success' => false, 'msg' => 'No se puede eliminar. Existe un abono asignado a esta venta.', 'code' => 1]);
                    }
                } else {
                    $has_installment = PayrollBoutique::where('boutique_sale_id', $request->id)->with('instalemnts')->first();
                    if($has_installment->instalemnts->count() > 0) {
                        DB::rollback();
                        return response()->json(['success' => false, 'msg' => 'No se puede eliminar. Existe un abono asignado a esta venta.', 'code' => 1]);
                    }
                }
            }

            $inventory_product_location = BoutiqueInventory::where('boutique_product_id', $sale_product_id)->where('setting_location_id', $sale_location_id)->first();
            $inventory_product_quantity = $inventory_product_location->quantity;
            $inventory_product_location->quantity = $inventory_product_quantity + $sale_quantity;
            $success = $inventory_product_location->save();

            if ($success) {
                PayrollBoutique::where('boutique_sale_id', $request->id)->delete();
                $sale->delete();

                if(!is_null($deduction)) {
                    $deduction->delete();
                }

                $this->saveProductLog(
                    'deleted_sell',
                    $sale_product_id,
                    "Retornados $sale_quantity unidad(es) a " . $sale->location->name . " por venta " . (isset($request->is_satellite) ? "a satélite " : "") . "eliminada (ID: $request->id)",
                    $inventory_product_quantity,
                    $inventory_product_location->quantity
                );
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al eliminar la venta. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function deleteUserBlock(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = BoutiqueBlockedUser::find($request->id);
            $success = $user->delete();

            if ($success) {
                $this->saveLog('unblocked_user', $user->blockedUser->id, "Eliminado Bloqueo a Usuario: " . $user->blockedUser->roleUserShortName());
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al eliminar el usuario. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function sellProduct(Request $request)
    {
        $this->validate($request,
            [
                'location_id' => "required",
                'quantity' => "required|gt:0",
                'user_id' => "required",
                'product_id' => "required"
            ]
        );

        try {
            DB::beginTransaction();

            $user_is_blocked = BoutiqueBlockedUser::where('user_id', $request->user_id)->first();

            $product = BoutiqueProduct::find($request->product_id);
            $total_sell = $product->unit_price * $request->quantity;

            if(!is_null($user_is_blocked)) {
                $blocked_value_model = BoutiqueBlockedValue::first();

                if($blocked_value_model) {
                    $blocked_value = $blocked_value_model->value;
                    $blocked_value_formatted = number_format($blocked_value_model->value, 0, ',', '.');

                    if($total_sell >= $blocked_value) {
                        DB::rollback();
                        return response()->json(['success' => false, 'msg' => "No se pudo registrar la venta. El usuario seleccionado se encuentra bloqueado para realizar compras mayores o iguales a $$blocked_value_formatted.", 'code' => 2]);
                    }
                }
            }

            $check_location_inventory = BoutiqueInventory::where('boutique_product_id', $request->product_id)->where('setting_location_id', $request->location_id)->first();

            if($request->quantity > $check_location_inventory->quantity) {
                DB::rollback();
                return response()->json(['success' => false, 'msg' => "No se pudo registrar la venta. No existe la cantidad suficiente de productos para vender en la locación seleccionada.", 'code' => 3]);
            }

            $user_buyer = User::find($request->user_id);

            if($user_buyer->setting_role_id == 14)
            {
                $owner = SatelliteOwner::where('user_id', $request->user_id)->first();

                if(is_null($owner)) {
                    DB::rollback();
                    return response()->json(['success' => false, 'msg' => 'No se pudo registrar la venta. No existe un propietario asignado a la modelo.', 'code' => 1]);
                }
            }

            $sale = new BoutiqueSale();
            $sale->boutique_product_id = $request->product_id;
            $sale->quantity = $request->quantity;
            $sale->total = $total_sell;
            $sale->setting_location_id = $request->location_id;
            $sale->buyer_user_id = $request->user_id;
            $sale->seller_user_id = Auth::user()->id;
            $success = $sale->save();

            if ($success) {
                $location_inventory = BoutiqueInventory::where('boutique_product_id', $request->product_id)->where('setting_location_id', $request->location_id)->first();
                $original_location_quantity = $location_inventory->quantity;
                $inventory_quantity = $location_inventory->quantity - $request->quantity;

                $location_inventory->quantity = $inventory_quantity;
                $update_inventory = $location_inventory->save();

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

                if($user_buyer->setting_role_id != 14)
                { // If NOT a model
                    $payroll_boutique = new PayrollBoutique();
                    $payroll_boutique->user_id = $request->user_id;
                    $payroll_boutique->boutique_sale_id = $sale->id;
                    $payroll_boutique->amount = $total_sell;
                    $payroll_boutique->installment = $total_sell <= 30000 ? $total_sell : 30000;
                    $payroll_boutique->status = 0;
                    $payroll_boutique->comment = "Compra de $product->name (Cantidad: $request->quantity)";
                    $payroll_boutique->save();
                }
                else
                {
                    $owner_id = $owner->id;
                    $owner_last_payment_date = SatellitePaymentAccount::where('owner_id', $owner_id)->max('payment_date');

                    $deducction = new SatellitePaymentDeduction();
                    $deducction->payment_date = $owner_last_payment_date;
                    $deducction->owner_id = $owner_id;
                    $deducction->deduction_to = 2; // Valor pago
                    $deducction->total = $total_sell;
                    $deducction->amount = $total_sell;
                    $deducction->description = "Compra de $product->name (Cantidad: $request->quantity)";
                    $deducction->type = 3;
                    $deducction->type_foreign_id = $sale->id;
                    $deducction->created_by = Auth::user()->id;
                    $deducction->save();
                }

                $this->saveProductLog(
                    'sell',
                    $product->id,
                    "Venta en " . $sale->location->name . " a " . $sale->buyer->roleUserShortName() . " (Cantidad: $request->quantity)",
                    $original_location_quantity,
                    $location_inventory->quantity
                );
            }

            DB::commit();
            return response()->json(['success' => $success, 'code' => 0]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function blockUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $exists = BoutiqueBlockedUser::where('user_id', $request->user_id)->first();

            if ($exists) {
                return response()->json(['success' => false, 'msg' => 'El usuario o modelo ya se encuentra bloqueado']);
            }

            $user = new BoutiqueBlockedUser();

            $user->user_id = $request->user_id;
            $user->blocked_by_user_id = Auth::user()->id;
            $success = $user->save();

            if ($success) {
                $this->saveLog('blocked_user', $request->user_id, "Bloqueado Usuario: " . $user->blockedUser->roleUserShortName());
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function returnProduct(Request $request)
    {
        if(is_null($this->getBaseLocation()->base_location_id)) {
            return response()->json(['success' => false, 'code' => 1, 'msg' => 'No hay una locación base configurada.']);
        }

        try {
            DB::beginTransaction();

            $success = true;

            $from_location_inventory = BoutiqueInventory::where('boutique_product_id', $request->product_id)
                ->where('setting_location_id', $request->from_location_id)
                ->first();

            $from_quantity = $from_location_inventory->quantity;

            // Subtract from selected location
            $from_location_inventory->quantity = $from_quantity - $request->quantity;
            $subtract = $from_location_inventory->save();

            $base_location_inventory = BoutiqueInventory::firstOrCreate(
                ['boutique_product_id' => $request->product_id, 'setting_location_id' => $this->getBaseLocation()->base_location_id],
                ['boutique_product_id' => $request->product_id, 'setting_location_id' => $this->getBaseLocation()->base_location_id]
            );

            // Add to base location
            $base_quantity = $base_location_inventory->quantity;
            $base_location_inventory->quantity = $base_quantity + $request->quantity;
            $add = $base_location_inventory->save();

            if(!$subtract && !$add) {
                DB::rollback();
                $success = false;
            }

            if ($success) {
                $this->saveProductLog(
                    'return',
                    $request->product_id,
                    "Regresadas $request->quantity unidad(es) desde " . $from_location_inventory->settingLocation->name,
                    $from_quantity,
                    $from_location_inventory->quantity
                );
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al eliminar el usuario. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function transferProduct(Request $request)
    {
        if(is_null($this->getBaseLocation()->base_location_id)) {
            return response()->json(['success' => false, 'code' => 1, 'msg' => 'No hay una locación base configurada.']);
        }

        try {
            DB::beginTransaction();

            $success = true;

            $base_location_inventory = BoutiqueInventory::where('boutique_product_id', $request->product_id)
                ->where('setting_location_id', $this->getBaseLocation()->base_location_id)
                ->first();

            // Subtract from base location
            $base_quantity = $base_location_inventory->quantity;
            $base_location_inventory->quantity = $base_quantity - $request->quantity;
            $subtract = $base_location_inventory->save();

            $to_location_inventory = BoutiqueInventory::firstOrCreate(
                ['boutique_product_id' => $request->product_id, 'setting_location_id' => $request->to_location_id],
                ['boutique_product_id' => $request->product_id, 'setting_location_id' => $request->to_location_id]
            );
            $to_quantity = $to_location_inventory->quantity;

            // Add to selected location
            $to_location_inventory->quantity = $to_quantity + $request->quantity;
            $add = $to_location_inventory->save();

            if(!$subtract && !$add) {
                DB::rollback();
                $success = false;
            }

            if ($success) {
                $this->saveProductLog(
                    'transfer',
                    $request->product_id,
                    "Transferidas $request->quantity unidad(es) a " . $to_location_inventory->settingLocation->name,
                    $to_quantity,
                    $to_location_inventory->quantity
                );
            }

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al eliminar el usuario. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function massiveSell(Request $request)
    {
        $sell_products_names = "";

        try {
            DB::beginTransaction();

            $buyer_id         = $request->info['buyer_id'];
            $buyer_type       = $request->info['sale_to'];
            $wholesaler_price = $request->info['wholesaler_price'] == 'true' ? true : false;

            foreach ($request->shopping_cart AS $product) {
                $total_sell = 0;

                $product = (object)$product;
                $product_id = $product->id;
                $product_name = $product->name;
                $product_price = $product->price;
                $product_wholesaler_price = $product->wholesaler_price;
                $product_quantity = $product->quantity;
                $product_location_id = $product->location_id;

                $check_location_inventory = BoutiqueInventory::where('boutique_product_id', $product_id)->where('setting_location_id', $product_location_id)->first();

                if($product_quantity > $check_location_inventory->quantity) {
                    DB::rollback();
                    return response()->json(['success' => false, 'msg' => "No se pudo registrar la venta. No existe la cantidad suficiente del producto ($product_name) para vender en la locación seleccionada.", 'code' => 3]);
                    break;
                }

                if($buyer_type == 'satellite' && $wholesaler_price) {
                    $price = $product_wholesaler_price;
                } else {
                    $price = $product_price;
                }

                $total_sell = $total_sell + ($price * $product_quantity);

                if ($buyer_type == 'satellite')
                {
                    $sale = new BoutiqueSatelliteSale();
                    $sale->boutique_product_id = $product_id;
                    $sale->quantity = $product_quantity;
                    $sale->total = $total_sell;
                    $sale->setting_location_id = $product_location_id;
                    $sale->buyer_owner_id = $buyer_id;
                    $sale->seller_user_id = Auth::user()->id;
                    $success = $sale->save();

                    if($success) {
                        $sell_products_names .= "$product_name (Cantidad: $product_quantity)<br>";

                        $location_inventory = BoutiqueInventory::where('boutique_product_id', $product_id)->where('setting_location_id', $product_location_id)->first();
                        $original_location_quantity = $location_inventory->quantity;
                        $inventory_quantity = $location_inventory->quantity - $product_quantity;

                        $location_inventory->quantity = $inventory_quantity;
                        $update_inventory = $location_inventory->save();

                        $now = Carbon::now()->day;
                        $last_day_of_month = Carbon::now()->endOfMonth()->day;

                        if (($now >= 1 && $now <= 14) || ($now == $last_day_of_month)) {
                            $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-07";

                            if ($now == $last_day_of_month) {
                                $date = Carbon::now()->addDay();
                                $for_date = $date->year . "-" . $date->month . "-07";
                            }
                        } else {
                            $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-27";
                        }

                        $owner = SatelliteOwner::find($buyer_id);

                        $owner_id = $owner->id;
                        $owner_last_payment_date = SatellitePaymentAccount::where('owner_id', $owner_id)->max('payment_date');

                        $deducction = new SatellitePaymentDeduction();
                        $deducction->payment_date = $owner_last_payment_date;
                        $deducction->owner_id = $owner_id;
                        $deducction->deduction_to = 2; // Valor pago
                        $deducction->total = $total_sell;
                        $deducction->amount = $total_sell;
                        $deducction->description = "Compra de $product_name (Cantidad: $product_quantity)";
                        $deducction->type = 3;
                        $deducction->type_foreign_id = $sale->id;
                        $deducction->created_by = Auth::user()->id;
                        $deducction->save();

                        $this->saveProductLog(
                            'sell',
                            $product->id,
                            "Venta en " . $sale->location->name . " a propietario " . $sale->buyer->owner . " (Cantidad: $product_quantity)",
                            $original_location_quantity,
                            $location_inventory->quantity
                        );
                    }
                }
                else
                {
                    $user_is_blocked = BoutiqueBlockedUser::where('user_id', $buyer_id)->first();

                    if(!is_null($user_is_blocked)) {
                        $blocked_value_model = BoutiqueBlockedValue::first();

                        if($blocked_value_model) {
                            $blocked_value = $blocked_value_model->value;
                            $blocked_value_formatted = number_format($blocked_value_model->value, 0, ',', '.');

                            if($total_sell >= $blocked_value) {
                                DB::rollback();
                                return response()->json(['success' => false, 'msg' => "No se pudo registrar la venta. El usuario seleccionado se encuentra bloqueado para realizar compras mayores o iguales a $$blocked_value_formatted.", 'code' => 2]);
                                break;
                            }
                        }
                    }

                    $sale = new BoutiqueSale();
                    $sale->boutique_product_id = $product_id;
                    $sale->quantity = $product_quantity;
                    $sale->total = $total_sell;
                    $sale->setting_location_id = $product_location_id;
                    $sale->buyer_user_id = $buyer_id;
                    $sale->seller_user_id = Auth::user()->id;
                    $success = $sale->save();

                    if($success) {
                        $sell_products_names .= "$product_name (Cantidad: $product_quantity)<br>";

                        $location_inventory = BoutiqueInventory::where('boutique_product_id', $product_id)->where('setting_location_id', $product_location_id)->first();
                        $original_location_quantity = $location_inventory->quantity;
                        $inventory_quantity = $location_inventory->quantity - $product_quantity;

                        $location_inventory->quantity = $inventory_quantity;
                        $update_inventory = $location_inventory->save();

                        $now = Carbon::now()->day;
                        $last_day_of_month = Carbon::now()->endOfMonth()->day;

                        if (($now >= 1 && $now <= 14) || ($now == $last_day_of_month)) {
                            $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-07";

                            if ($now == $last_day_of_month) {
                                $date = Carbon::now()->addDay();
                                $for_date = $date->year . "-" . $date->month . "-07";
                            }
                        } else {
                            $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-27";
                        }

                        $user_buyer = User::find($buyer_id);

                        if ($user_buyer->setting_role_id != 14) { // If NOT a model
                            $payroll_boutique = new PayrollBoutique();
                            $payroll_boutique->user_id = $buyer_id;
                            $payroll_boutique->boutique_sale_id = $sale->id;
                            $payroll_boutique->amount = $total_sell;
                            $payroll_boutique->installment = $total_sell <= 30000 ? $total_sell : 30000;
                            $payroll_boutique->status = 0;
                            $payroll_boutique->comment = "Compra de $product_name (Cantidad: $product_quantity)";
                            $payroll_boutique->save();
                        } else {
                            if ($buyer_type == 'models') {
                                $owner = SatelliteOwner::where('user_id', $buyer_id)->first();

                                if (is_null($owner)) {
                                    DB::rollback();
                                    return response()->json(['success' => false, 'msg' => 'No se pudo registrar la venta. No existe un propietario asignado a la modelo.', 'code' => 1]);
                                    break;
                                }
                            } else {
                                $owner = SatelliteOwner::find($buyer_id);
                            }

                            $owner_id = $owner->id;
                            $owner_last_payment_date = SatellitePaymentAccount::where('owner_id', $owner_id)->max('payment_date');

                            $deducction = new SatellitePaymentDeduction();
                            $deducction->payment_date = $owner_last_payment_date;
                            $deducction->owner_id = $owner_id;
                            $deducction->deduction_to = 2; // Valor pago
                            $deducction->total = $total_sell;
                            $deducction->amount = $total_sell;
                            $deducction->description = "Compra de $product_name (Cantidad: $product_quantity)";
                            $deducction->type = 3;
                            $deducction->type_foreign_id = $sale->id;
                            $deducction->created_by = Auth::user()->id;
                            $deducction->save();
                        }

                        $this->saveProductLog(
                            'sell',
                            $product->id,
                            "Venta en " . $sale->location->name . " a " . $sale->buyer->roleUserShortName() . " (Cantidad: $product_quantity)",
                            $original_location_quantity,
                            $location_inventory->quantity
                        );
                    }
                }
            }

            // Create task

            $create_task = false;
            $user = null;

            if($buyer_type != 'satellite') {
                $user = User::find($buyer_id);
                $user_role_id = $user->setting_role_id;

                if($user_role_id == 1 || $user_role_id == 37) { // If is Gerente or Tesorero
                    $create_task = true;
                }
            }

            if($buyer_type == 'satellite' || $create_task) {
                // Create task
                $task_controller = new TaskController();

                if($buyer_type == 'satellite') {
                    $owner = SatelliteOwner::find($buyer_id);
                    $show_user = $owner->owner;
                } else {
                    $show_user = $user->roleUserShortName();
                }

                $task = new Task();
                $task->created_by_type = 1; // User
                $task->created_by = Auth::user()->id;
                $task->title = "Productos vendidos a $show_user";
                $task->status = 0;
                $task->should_finish = $should_finish = Carbon::now()->addDay();
                $task->terminated_by = 0;
                $task->code = $task_controller->generateCode();
                $created = $task->save();

                //Gerente, Asistente Administrativa, Soporte, Adminitradora, Programador, Auxiliar Boutique
                $receivers = [
                    'to_roles' => [
                        ['id' => 1, 'name' => 'Gerente'],
                        ['id' => 2, 'name' => 'Asistente Administrativo'],
                        ['id' => 3, 'name' => 'Administrador/a'],
                        ['id' => 4, 'name' => 'Soporte'],
                        ['id' => 11, 'name' => 'Programador/a'],
                        ['id' => 21, 'name' => 'Auxiliar Boutique'],
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
                        "<p>Se han vendido a <b>$show_user</b> los productos:</p>
                         $sell_products_names";
                    $task_comment->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'code' => 0]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function insertInventory(Request $request)
    {
        try {
            DB::beginTransaction();

            $products = $request->producto;

            $A1 = $request->A1;
            $A2 = $request->A2;
            $A3 = $request->A3;
            $A4 = $request->A4;
            $A5 = $request->A5;
            $A6 = $request->A6;
            $A7 = $request->A7;

            $count = 1;

            foreach ($products as $product) {
                $product_id = $product;

                $id = $count . "_";

                $value_B1 = $id . "B1";
                $value_B2 = $id . "B2";
                $value_B3 = $id . "B3";
                $value_B4 = $id . "B4";
                $value_B5 = $id . "B5";
                $value_B6 = $id . "B6";
                $value_B7 = $id . "B7";
                $value_B8 = $id . "B8";
                $value_B9 = $id . "B9";
                $value_B10 = $id . "B10";
                $value_B11 = $id . "B11";
                $value_B12 = $id . "B12";
                $value_B13 = $id . "B13";
                $value_B14 = $id . "B14";

                $B1 = $request->$value_B1;
                $B2 = $request->$value_B2;
                $B3 = $request->$value_B3;
                $B4 = $request->$value_B4;
                $B5 = $request->$value_B5;
                $B6 = $request->$value_B6;
                $B7 = $request->$value_B7;
                $B8 = $request->$value_B8;
                $B9 = $request->$value_B9;
                $B10 = $request->$value_B10;
                $B11 = $request->$value_B11;
                $B12 = $request->$value_B12;
                $B13 = $request->$value_B13;
                $B14 = $request->$value_B14;

                $count++;

                $inventory_ingress = new BoutiqueInventoryIngress();
                $inventory_ingress->boutique_product_id = $product_id;
                $inventory_ingress->user_id = Auth::user()->id;
                $inventory_ingress->A1 = $A1;
                $inventory_ingress->A2 = $A2;
                $inventory_ingress->A3 = $A3;
                $inventory_ingress->A4 = $A4;
                $inventory_ingress->A5 = $A5;
                $inventory_ingress->A6 = $A6;
                $inventory_ingress->A7 = $A7;
                $inventory_ingress->B1 = $B1;
                $inventory_ingress->B2 = $B2;
                $inventory_ingress->B3 = $B3;
                $inventory_ingress->B4 = $B4;
                $inventory_ingress->B5 = $B5;
                $inventory_ingress->B6 = $B6;
                $inventory_ingress->B7 = $B7;
                $inventory_ingress->B8 = $B8;
                $inventory_ingress->B9 = $B9;
                $inventory_ingress->B10 = $B10;
                $inventory_ingress->B11 = $B11;
                $inventory_ingress->B12 = $B12;
                $inventory_ingress->B13 = $B13;
                $inventory_ingress->B14 = $B14;
                $success = $inventory_ingress->save();

                if($success) {
                    $edit_unit_price = false;
                    $edit_wholesaler_price = false;

                    $quantity = $B1;
                    $new_unit_price = $B12;
                    $new_wholesaler_price = $B14;

                    $boutique_product = BoutiqueProduct::find($product_id);
                    $product_unit_price = $boutique_product->unit_price;
                    $product_wholesaler_price = $boutique_product->wholesaler_price;

                    if($product_unit_price < $new_unit_price) {
                        $edit_unit_price = true;
                        $boutique_product->unit_price = $new_unit_price;
                    }

                    if($product_wholesaler_price != $new_wholesaler_price) {
                        $edit_wholesaler_price = true;
                        $boutique_product->wholesaler_price = $new_wholesaler_price;
                    }

                    $success_price = $boutique_product->save();

                    if($success_price) {
                        if ($edit_unit_price) {
                            $this->saveProductLog(
                                'edit',
                                $product_id,
                                "Modificado Precio Unitario: '$product_unit_price' por '$new_unit_price' desde ingreso de inventario"
                            );
                        }

                        if ($edit_wholesaler_price) {
                            $this->saveProductLog(
                                'edit',
                                $product_id,
                                "Modificado Precio Mayorista: '$product_wholesaler_price' por '$new_wholesaler_price' desde ingreso de inventario"
                            );
                        }
                    }

                    $inventory = BoutiqueInventory::firstOrCreate(
                        ['boutique_product_id' => $product_id, 'setting_location_id' => $this->getBaseLocation()->base_location_id],
                        ['boutique_product_id' => $product_id, 'setting_location_id' => $this->getBaseLocation()->base_location_id, 'quantity' => $quantity]
                    );

                    $original_inventory_quantity = $inventory->quantity;
                    $new_inventory_quantity = $original_inventory_quantity + $quantity;
                    $inventory->quantity = $new_inventory_quantity;
                    $success = $inventory->save();

                    if($success) {
                        $this->saveProductLog(
                            'inventory_ingress',
                            $product_id,
                            "Añadidos $quantity unidad(es) a " . $this->getBaseLocation()->base_location_name . " desde ingreso de inventario",
                            $original_inventory_quantity,
                            $new_inventory_quantity
                        );
                    }

                    $this->saveLog('inventory_ingress', $inventory_ingress->id, "Agregado inventario: $quantity unidad(es) del producto $boutique_product->name");
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'code' => 0]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al registrar la el inventario. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function exportWeekSales($start_date, $end_date)
    {
        $sales_data = [];

        $week = "$start_date - $end_date";

        $filename = "Listado de Ventas Boutique ($week).xlsx";

        $sales = BoutiqueSale::with('product')
            ->orderBy('created_at', 'DESC')
            ->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date])
            ->get();

        foreach ($sales AS $sale) {
            $sales_data[] = [
                'product_name' => $sale->product->name,
                'quantity' => $sale->quantity,
                'total' => "$sale->total",
                'buyer' => $sale->buyer->roleUserShortName(),
                'seller' => $sale->seller->roleUserShortName(),
                'date' => Carbon::parse($sale->created_at)->toDateString(),
            ];
        }

        return Excel::download(new Sales($sales_data, $week), $filename);
    }

    public function exportSatelliteWeekSales($start_date, $end_date)
    {
        $sales_data = [];

        $week = "$start_date - $end_date";

        $filename = "Listado de Ventas Boutique Satélites ($week).xlsx";

        $sales = BoutiqueSatelliteSale::with('product')
            ->orderBy('created_at', 'DESC')
            ->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date])
            ->get();

        foreach ($sales AS $sale) {
            $sales_data[] = [
                'product_name' => $sale->product->name,
                'quantity' => $sale->quantity,
                'total' => "$sale->total",
                'buyer' => $sale->buyer->owner,
                'seller' => $sale->seller->roleUserShortName(),
                'date' => Carbon::parse($sale->created_at)->toDateString(),
            ];
        }

        return Excel::download(new Sales($sales_data, $week), $filename);
    }

    public function exportInventory(Request $request)
    {
        $date = Carbon::now()->toDateString();
        $products_data = [];

        $filename = "Inventario Boutique ($date).xlsx";

        $products = BoutiqueProduct::where('active', 1)->with('boutiqueCategory')->with('boutiqueInventory')->orderBy('name')->get();
        $locations = SettingLocation::where('id', '!=', 1)->get();

        foreach ($products AS $product) {
            $locations_stock = [];

            $products_data[$product->id] = [
                'product_name' => $product->name
            ];

            foreach ($product->boutiqueInventory AS $item) {
                $locations_stock[$item->settingLocation->id] = $item->quantity;
            }

            foreach ($locations AS $location) {
                if(isset($locations_stock[$location->id])) {
                    $quantity = $locations_stock[$location->id] > 0 ? $locations_stock[$location->id] : "0";
                    array_push($products_data[$product->id], $quantity);
                } else {
                    array_push($products_data[$product->id], "0");
                }
            }
        }

        return Excel::download(new Inventory($products_data, $locations, $date), $filename);
    }

    public function productsExecute()
    {
        try {
            $min_id = 1;
            $max_id = 100;

            if (!Schema::hasColumn('boutique_categories', 'old_category_id'))
            {
                Schema::table('boutique_categories', function (Blueprint $table) {
                    $table->integer('old_category_id')->nullable();
                });
            }

            if (!Schema::hasColumn('boutique_products', 'old_product_id'))
            {
                Schema::table('boutique_products', function (Blueprint $table) {
                    $table->integer('old_product_id')->nullable();
                });
            }

            if (!Schema::hasColumn('boutique_inventories', 'old_inventory_id'))
            {
                Schema::table('boutique_inventories', function (Blueprint $table) {
                    $table->integer('old_inventory_id')->nullable();
                });
            }

            $all_locations = [];

            $locations = DB::connection('gbmedia')->table('locacion')->get();
            foreach ($locations AS $location)
            {
                $new_location = SettingLocation::where('name', $location->nombre)->first();

                if(is_null($new_location)) { continue; }

                $all_locations[$location->id] = [
                    'old_id' => $location->id,
                    'old_name' => $location->nombre,
                    'new_id' => $new_location->id,
                    'new_name' => $new_location->name,
                ];
            }

            $categories = DB::connection('gbmedia')->table('productos_categoria')->whereBetween('id', [$min_id, $max_id])->get();

            DB::beginTransaction();

            foreach ($categories AS $category) {
                $boutique_category = BoutiqueCategory::firstOrCreate(
                    [
                        'name' => $category->nombre_categoria
                    ],
                    [
                        'name' => $category->nombre_categoria,
                        'old_category_id' => $category->id,
                    ]
                );

                $boutique_category->name = $category->nombre_categoria;
                $boutique_category->old_category_id = $category->id;
                $boutique_category->save();
            }

            $min_id = 1;
            $max_id = 1239; // Migrado hasta el 2021-01-07 16:00

            $products = DB::connection('gbmedia')->table('productos')->whereBetween('id', [$min_id, $max_id])->get();

            foreach ($products AS $product) {
                $product_category = BoutiqueCategory::select('id')->where('name', $product->categoria)->first();

                if(!is_null($product_category)) {
                    $category_id = $product_category->id;
                } else {
                    $category_id = 1;
                }

                $name = utf8_decode(trim($product->nombre));
                $boutique_category_id = $category_id;
                $image = $product->foto;
                $unit_price = !empty($product->precio_unidad) ? $product->precio_unidad : 0;
                $wholesaler_price = !empty($product->precio_mayorista) ? $product->precio_mayorista : 0;
                $nationality = $product->nacionalidad;
                $active = ($product->estado == 'activo' || empty($product->estado) ? 1 : 0);
                $barcode = $product->codigo;
                $stock_alarm = !is_null($product->alarma_inventario) ? $product->alarma_inventario : 0;
                $location_alarm = !is_null($product->alarma_sede) ? $product->alarma_sede : 0;

                $created_product = BoutiqueProduct::firstOrCreate(
                    [
                        'old_product_id' => $product->id,
                    ],
                    [
                        'name' => $name,
                        'boutique_category_id' => $boutique_category_id,
                        'unit_price' => $unit_price,
                        'wholesaler_price' => $wholesaler_price,
                        'nationality' => $nationality,
                        'active' => $active,
                        'stock_alarm' => $stock_alarm,
                        'location_alarm' => $location_alarm,
                        'old_product_id' => $product->id,
                    ]
                );

                $created_product->name = $name;
                $created_product->boutique_category_id = $boutique_category_id;
                $created_product->image = $image;
                $created_product->unit_price = $unit_price;
                $created_product->wholesaler_price = $wholesaler_price;
                $created_product->nationality = $nationality;
                $created_product->active = $active;
                $created_product->barcode = $barcode;
                $created_product->stock_alarm = $stock_alarm;
                $created_product->location_alarm = $location_alarm;
                $created_product->save();

                // Stock
                $product_stock = DB::connection('gbmedia')->table('productos_stock')->where('producto_id', $created_product->old_product_id)->get();

                foreach ($product_stock AS $stock) {
                    if(!isset($all_locations[$stock->locacion_id])) {
                        continue;
                    }

                    $location_id = $all_locations[$stock->locacion_id]['new_id'];

                    $created_product_inventory = BoutiqueInventory::firstOrCreate(
                        [
                            'boutique_product_id' => $created_product->id,
                            'setting_location_id' => $location_id,
                        ],
                        [
                            'boutique_product_id' => $created_product->id,
                            'setting_location_id' => $location_id,
                            'quantity' => $stock->cantidad,
                            'created_at' => $stock->created_at,
                            'updated_at' => $stock->updated_at,
                            'old_inventory_id' => $product->id,
                        ]
                    );

                    $created_product_inventory->boutique_product_id = $created_product->id;
                    $created_product_inventory->setting_location_id = $location_id;
                    $created_product_inventory->quantity = $stock->cantidad;
                    $created_product_inventory->created_at = $stock->created_at;
                    $created_product_inventory->updated_at = $stock->updated_at;
                    $created_product_inventory->old_inventory_id = $stock->id;
                    $created_product_inventory->save();
                }
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function salesExecute()
    {
        try {
            $min_id = 5000;
            $max_id = 10046; // Migrado hasta el 2021-01-07 16:00

            if (!Schema::hasColumn('boutique_sales', 'old_sale_id'))
            {
                Schema::table('boutique_sales', function (Blueprint $table) {
                    $table->integer('old_sale_id')->nullable();
                });
            }

            $all_locations = [];
            $all_locations_names = [];

            $locations = DB::connection('gbmedia')->table('locacion')->get();
            foreach ($locations AS $location)
            {
                $new_location = SettingLocation::where('name', $location->nombre)->first();

                if(is_null($new_location)) { continue; }

                $all_locations[$location->id] = [
                    'old_id' => $location->id,
                    'old_name' => $location->nombre,
                    'new_id' => $new_location->id,
                    'new_name' => $new_location->name,
                ];

                $all_locations_names[$location->nombre] = [
                    'old_id' => $location->id,
                    'old_name' => $location->nombre,
                    'new_id' => $new_location->id,
                    'new_name' => $new_location->name,
                ];
            }

            $sales = DB::connection('gbmedia')->table('productos_venta')->whereBetween('id', [$min_id, $max_id])->get();
            //dd($sales);

            DB::beginTransaction();

            foreach ($sales AS $sale) {
                $product = BoutiqueProduct::where('old_product_id', $sale->id_producto)->first();
                if(is_null($product)) {
                    continue;
                }

                $buyer = $sale->vendido_a_fk_u_id;
                $buyer_by = null;

                if ($buyer == 0)
                {
                    $user_name = trim($sale->vendido_a);
                    $exploded = explode(' ', $user_name);

                    if(count($exploded) > 1) {
                        $user_buyer = User::where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();
                    } else {
                        $user_buyer = User::where('nick', $exploded[0])->first();
                    }

                    if (!is_object($user_buyer)) { continue; }

                    $buyer_by = $user_buyer->id;
                }
                else
                {
                    $buyer_user = User::select('id')->where('old_user_id', $buyer)->first();
                    $buyer_by = $buyer_user->id;
                }

                $seller = $sale->vendido_por_fk_u_id;

                if ($seller == 0)
                {
                    $user_name = trim($sale->vendido_por);
                    $exploded = explode(' ', $user_name);

                    $user_seller = User::where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();

                    if (is_object($user_seller)) {
                        $seller_by = $user_seller->id;
                    } else {
                        $seller_by = 3;
                    }
                }
                else
                {
                    $seller_user = User::select('id')->where('old_user_id', $seller)->first();
                    $seller_by = $seller_user->id;
                }

                $quantity = is_numeric($sale->cantidad) ? $sale->cantidad : 1;
                $total = $sale->precio_producto * $quantity;
                $created_at = $sale->fecha;

                $location_name = !empty($sale->locacion_producto) ? trim($sale->locacion_producto) : 'Bodega';

                if($location_name == 'Bodega' || $location_name == 'Miraflores') {
                    $location_name = 'Tequendama';
                }

                $setting_location_id = $all_locations_names[$location_name]['new_id'];

                $created_sale = BoutiqueSale::firstOrCreate(
                    [
                        'old_sale_id' => $sale->id,
                    ],
                    [
                        'boutique_product_id' => $product->id,
                        'quantity' => $quantity,
                        'total' => $total,
                        'setting_location_id' => $setting_location_id,
                        'buyer_user_id' => $buyer_by,
                        'seller_user_id' => $seller_by,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                        'old_sale_id' => $sale->id,
                    ]
                );

                $created_sale->boutique_product_id = $product->id;
                $created_sale->quantity = $quantity;
                $created_sale->total = $total;
                $created_sale->setting_location_id = $setting_location_id;
                $created_sale->buyer_user_id = $buyer_by;
                $created_sale->seller_user_id = $seller_by;
                $created_sale->created_at = $created_at;
                $created_sale->updated_at = $created_at;
                $created_sale->old_sale_id = $sale->id;
                $created_sale->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function satelliteSalesExecute()
    {
        try {
            $min_id = 1;
            $max_id = 33; // Migrado hasta el 2021-01-07 16:00

            if (!Schema::hasColumn('boutique_satellite_sales', 'old_sale_id'))
            {
                Schema::table('boutique_satellite_sales', function (Blueprint $table) {
                    $table->integer('old_sale_id')->nullable();
                });
            }

            $all_locations = [];
            $all_locations_names = [];

            $locations = DB::connection('gbmedia')->table('locacion')->get();
            foreach ($locations AS $location)
            {
                $new_location = SettingLocation::where('name', $location->nombre)->first();

                if(is_null($new_location)) { continue; }

                $all_locations[$location->id] = [
                    'old_id' => $location->id,
                    'old_name' => $location->nombre,
                    'new_id' => $new_location->id,
                    'new_name' => $new_location->name,
                ];

                $all_locations_names[$location->nombre] = [
                    'old_id' => $location->id,
                    'old_name' => $location->nombre,
                    'new_id' => $new_location->id,
                    'new_name' => $new_location->name,
                ];
            }

            $sales = DB::connection('gbmedia')->table('productos_venta_studio')->whereBetween('id', [$min_id, $max_id])->get();
            //dd($sales);

            DB::beginTransaction();

            foreach ($sales AS $sale) {
                $product = BoutiqueProduct::where('old_product_id', $sale->id_producto)->first();
                if(is_null($product)) {
                    continue;
                }

                $owner_buyer = SatelliteOwner::where('old_id', $sale->vendido_a_fk_pro_id)->first();
                $buyer_by = $owner_buyer->id;

                $seller_user = User::select('id')->where('old_user_id', $sale->vendido_por_fk_u_id)->first();
                $seller_by = $seller_user->id;

                $quantity = is_numeric($sale->cantidad) ? $sale->cantidad : 1;
                $total = $sale->precio_producto * $quantity;
                $created_at = $sale->fecha;

                $location_name = !empty($sale->locacion_producto) ? trim($sale->locacion_producto) : 'Bodega';

                if($location_name == 'Bodega' || $location_name == 'Miraflores') {
                    $location_name = 'Tequendama';
                }

                $setting_location_id = $all_locations_names[$location_name]['new_id'];

                $created_satellite_sale = BoutiqueSatelliteSale::firstOrCreate(
                    [
                        'old_sale_id' => $sale->id,
                    ],
                    [
                        'boutique_product_id' => $product->id,
                        'quantity' => $quantity,
                        'total' => $total,
                        'setting_location_id' => $setting_location_id,
                        'buyer_owner_id' => $buyer_by,
                        'seller_user_id' => $seller_by,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                        'old_sale_id' => $sale->id,
                    ]
                );

                $created_satellite_sale->boutique_product_id = $product->id;
                $created_satellite_sale->quantity = $quantity;
                $created_satellite_sale->total = $total;
                $created_satellite_sale->setting_location_id = $setting_location_id;
                $created_satellite_sale->buyer_owner_id = $buyer_by;
                $created_satellite_sale->seller_user_id = $seller_by;
                $created_satellite_sale->created_at = $created_at;
                $created_satellite_sale->updated_at = $created_at;
                $created_satellite_sale->old_sale_id = $sale->id;
                $created_satellite_sale->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function blockedUsersExecute()
    {
        try {
            $block_value = DB::connection('gbmedia')->table('productos_bloqueo_max')->where('id', 1)->first();

            $created_blocker_value = BoutiqueBlockedValue::firstOrCreate(
                [
                    'id' => 1,
                ],
                [
                    'value' => $block_value->valor_max,
                ]
            );

            $created_blocker_value->value = $block_value->valor_max;
            $created_blocker_value->save();

            $min_id = 1;
            $max_id = 10; // Migrado hasta el 2021-01-07 16:00

            $blocked_users = DB::connection('gbmedia')->table('productos_bloqueo')->whereBetween('id', [$min_id, $max_id])->get();

            foreach ($blocked_users AS $blocked_user) {
                DB::beginTransaction();

                $user = User::select('id')->where('old_user_id', $blocked_user->id_usuario)->first();
                $user_id = $user->id;

                $user_name = trim($blocked_user->usuario_bloquea);
                $exploded = explode(' ', $user_name);
                $user_blocker = User::where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();
                $user_blocker_id = $user_blocker->id;

                $created_at = $blocked_user->fecha_bloqueo;

                $created_blocker_user = BoutiqueBlockedUser::firstOrCreate(
                    [
                        'user_id' => $user_id,
                    ],
                    [
                        'user_id' => $user_id,
                        'blocked_by_user_id' => $user_blocker_id,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                    ]
                );

                $created_blocker_user->user_id = $user_id;
                $created_blocker_user->blocked_by_user_id = $user_blocker_id;
                $created_blocker_user->created_at = $created_at;
                $created_blocker_user->updated_at = $created_at;
                $created_blocker_user->save();

                DB::commit();
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function inventoriesExecute()
    {
        try {
            if (!Schema::hasColumn('boutique_inventory_ingresses', 'old_inventory_id'))
            {
                Schema::table('boutique_inventory_ingresses', function (Blueprint $table) {
                    $table->integer('old_inventory_id')->nullable();
                });
            }

            $min_id = 1;
            $max_id = 1392; // Migrado hasta el 2021-01-07 16:00

            $inventories = DB::connection('gbmedia')->table('inventario')->whereBetween('id', [$min_id, $max_id])->get();

            DB::beginTransaction();

            foreach ($inventories AS $inventory) {
                $product = BoutiqueProduct::select('id')->where('old_product_id', $inventory->id_producto)->first();

                if(is_null($product)) {
                    continue;
                }

                $product_id = $product->id;

                if(!is_null($inventory->usuario_ingresa)) {
                    $user_name = trim($inventory->usuario_ingresa);
                    $exploded = explode(' ', $user_name);

                    if (count($exploded) > 1) {
                        $user_creator = User::select('id')->where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();

                        if(is_object($user_creator)) {
                            $user_id = $user_creator->id;
                        } else {
                            $user_id = 3;
                        }
                    } else {
                        $user_id = 3;
                    }
                } else {
                    $user_id = 3;
                }

                $A1 = $inventory->A1;
                $A2 = $inventory->A2;
                $A3 = $inventory->A3;
                $A4 = $inventory->A4;
                $A5 = $inventory->A5;
                $A6 = $inventory->A6;
                $A7 = $inventory->A7;
                $B1 = $inventory->B1;
                $B2 = $inventory->B2;
                $B3 = $inventory->B3;
                $B4 = $inventory->B4;
                $B5 = $inventory->B5;
                $B6 = $inventory->B6;
                $B7 = $inventory->B7;
                $B8 = $inventory->B8;
                $B9 = $inventory->B9;
                $B10 = $inventory->B10;
                $B11 = $inventory->B11;
                $B12 = $inventory->B12;
                $B13 = $inventory->B13;
                $B14 = $inventory->B14;
                $created_at = $inventory->fecha;

                $created_inventory_ingress = BoutiqueInventoryIngress::firstOrCreate(
                    [
                        'old_inventory_id' => $inventory->id,
                    ],
                    [
                        'boutique_product_id' => $product_id,
                        'user_id' => $user_id,
                        'A1' => $A1,
                        'A2' => $A2,
                        'A3' => $A3,
                        'A4' => $A4,
                        'A5' => $A5,
                        'A6' => $A6,
                        'A7' => $A7,
                        'B1' => $B1,
                        'B2' => $B2,
                        'B3' => $B3,
                        'B4' => $B4,
                        'B5' => $B5,
                        'B6' => $B6,
                        'B7' => $B7,
                        'B8' => $B8,
                        'B9' => $B9,
                        'B10' => $B10,
                        'B11' => $B11,
                        'B12' => $B12,
                        'B13' => $B13,
                        'B14' => $B14,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                        'old_inventory_id' => $inventory->id,
                    ]
                );

                $created_inventory_ingress->boutique_product_id = $product_id;
                $created_inventory_ingress->user_id = $user_id;
                $created_inventory_ingress->A1 = $A1;
                $created_inventory_ingress->A2 = $A2;
                $created_inventory_ingress->A3 = $A3;
                $created_inventory_ingress->A4 = $A4;
                $created_inventory_ingress->A5 = $A5;
                $created_inventory_ingress->A6 = $A6;
                $created_inventory_ingress->A7 = $A7;
                $created_inventory_ingress->B1 = $B1;
                $created_inventory_ingress->B2 = $B2;
                $created_inventory_ingress->B3 = $B3;
                $created_inventory_ingress->B4 = $B4;
                $created_inventory_ingress->B5 = $B5;
                $created_inventory_ingress->B6 = $B6;
                $created_inventory_ingress->B7 = $B7;
                $created_inventory_ingress->B8 = $B8;
                $created_inventory_ingress->B9 = $B9;
                $created_inventory_ingress->B10 = $B10;
                $created_inventory_ingress->B11 = $B11;
                $created_inventory_ingress->B12 = $B12;
                $created_inventory_ingress->B13 = $B13;
                $created_inventory_ingress->B14 = $B14;
                $created_inventory_ingress->created_at = $created_at;
                $created_inventory_ingress->updated_at = $created_at;
                $created_inventory_ingress->old_inventory_id = $inventory->id;
                $created_inventory_ingress->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function productsLogsExecute()
    {
        try {
            if (!Schema::hasColumn('boutique_products_logs', 'old_log_id'))
            {
                Schema::table('boutique_products_logs', function (Blueprint $table) {
                    $table->integer('old_log_id')->nullable();
                });
            }

            $min_id = 1;
            $max_id = 1465; // Migrado hasta el 2021-01-07 16:00

            $logs = DB::connection('gbmedia')->table('producto_historial')->whereBetween('id', [$min_id, $max_id])->get();

            DB::beginTransaction();

            foreach ($logs AS $log) {
                $product = BoutiqueProduct::select('id')->where('old_product_id', $log->id_producto)->first();

                if(is_null($product)) {
                    continue;
                }

                $type = '-';
                $type_description = '-';
                $old_inventory_quantity = null;
                $new_inventory_quantity = null;

                $product_id = $product->id;
                $action = trim($log->accion);
                $created_at = $log->fecha;

                if(!is_null($log->usuario_accion))
                {
                    $user_name = trim($log->usuario_accion);
                    $exploded = explode(' ', $user_name);

                    if (count($exploded) > 1) {
                        $user_creator = User::select('id')->where('first_name', $exploded[0])->where('last_name', $exploded[1])->first();

                        if(is_object($user_creator)) {
                            $user_id = $user_creator->id;
                        } else {
                            $user_id = 3;
                        }
                    } else {
                        $user_id = 3;
                    }
                }
                else
                {
                    $user_id = 3;
                }

                if(strpos($action, 'edita') != false)
                {
                    $type = 'edit';
                    $type_description = 'ROOT - Modificada cantidad en Tequendama';

                    $pieces = explode(' ', $action);
                    $last_word = array_pop($pieces);

                    $old_inventory_quantity = $last_word;
                    $new_inventory_quantity = $log->cantidad;
                }
                else if (strpos($action, 'traslado') !== false)
                {
                    $type = 'transfer';
                    $action_description = $action;

                    $pieces = explode(' ', $action_description);
                    $last_word = array_pop($pieces);

                    if($last_word == 'Bodega') {
                        $last_word = 'Tequendama';
                    }

                    $type_description = "ROOT - Traslado de $last_word";
                    $old_inventory_quantity = null;
                    $new_inventory_quantity = $log->cantidad;
                }
                else if (strpos($action, 'elimina') !== false)
                {
                    $type = 'delete';
                    $type_description = "ROOT - Eliminado producto del stock";
                    $old_inventory_quantity = null;
                    $new_inventory_quantity = 0;
                }

                $created_product_log = BoutiqueProductsLog::firstOrCreate(
                    [
                        'old_log_id' => $log->id,
                    ],
                    [
                        'type' => $type,
                        'boutique_product_id' => $product_id,
                        'created_by' => $user_id,
                        'action' => $type_description,
                        'old_inventory_quantity' => $old_inventory_quantity,
                        'new_inventory_quantity' => $new_inventory_quantity,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                        'old_log_id' => $log->id,
                    ]
                );

                $created_product_log->type = $type;
                $created_product_log->boutique_product_id = $product_id;
                $created_product_log->created_by = $user_id;
                $created_product_log->action = $type_description;
                $created_product_log->old_inventory_quantity = $old_inventory_quantity;
                $created_product_log->new_inventory_quantity = $new_inventory_quantity;
                $created_product_log->created_at = $created_at;
                $created_product_log->updated_at = $created_at;
                $created_product_log->old_log_id = $log->id;
                $created_product_log->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

}
