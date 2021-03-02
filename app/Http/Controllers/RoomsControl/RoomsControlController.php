<?php

namespace App\Http\Controllers\RoomsControl;

use App\Http\Controllers\Controller;
use App\Models\RoomsControl\RoomsControl;
use App\Models\RoomsControl\RoomsControlInventory;
use App\Models\Settings\SettingLocation;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Traits\TraitGlobal;

class RoomsControlController extends Controller
{
    use TraitGlobal;

    public function __construct()
    {
        $this->middleware('auth');

        // Access to only certain methods
        $this->middleware('permission:rooms-control')->only('history');
        $this->middleware('permission:rooms-control')->only('roomHistory');
        $this->middleware('permission:rooms-control-status')->only('status');
        $this->middleware('permission:rooms-control-app-configuration')->only('info');
        $this->middleware('permission:rooms-control-inventories')->only('inventory');
    }

    public function inventory()
    {
        return view('adminModules.rooms_control.inventory')->with(['locations' => $this->userLocationAccess()]);
    }

    public function history()
    {
        return view('adminModules.rooms_control.history')->with(['locations' => $this->userLocationAccess()]);
    }

    public function roomHistory($location_id, $room_number)
    {
        $location = SettingLocation::find($location_id);

        if (is_null($location)) {
            return redirect()->route('home.dashboard');
        }

        return view('adminModules.rooms_control.room_history')->with(['location_id' => $location->id, 'location_name' => $location->name, 'room_number' => $room_number]);
    }

    public function info()
    {
        return view('adminModules.rooms_control.info')->with([]);
    }

    public function inventoryLocation()
    {
        return view('adminModules.rooms_control.inventory_location')->with(['locations' => $this->userLocationAccess()]);
    }

    public function historyLocation()
    {
        return view('adminModules.rooms_control.history_location')->with(['locations' => $this->userLocationAccess()]);
    }

    public function statusLocation()
    {
        return view('adminModules.rooms_control.status_location')->with(['locations' => $this->userLocationAccess()]);
    }

    public function status()
    {
        return view('adminModules.rooms_control.status')->with(['locations' => $this->userLocationAccess()]);
    }

    public function getRoomInventory(RoomsControlInventory $roomsControlInventory, Request $request)
    {
        if ($request->ajax()) {
            if (!is_null($request->room_number)) {
                $room_inventory = $roomsControlInventory::where('setting_location_id', $request->location_id)->where('room_number', $request->room_number)->orderBy('position')->get();

                return DataTables::of($room_inventory)
                    ->addIndexColumn()
                    ->addColumn('image', function ($row) {
                        if (!is_null($row->image)) {
                            $image = "<img src='" . asset("storage/" . tenant('studio_slug') . "/rooms_inventories/$row->image") . "' alt='$row->name' class='img-avatar zoom-img'>";
                        } else {
                            $image = "<img src='" . asset("images/svg/no-photo.svg") . "' title='Sin imagen' class='img-avatar'>";
                        }

                        return $image;
                    })
                    ->addColumn('actions', function ($row) {
                        $edit      = "";
                        $delete    = "";
                        $duplicate = "";

                        $user = Auth::user();

                        if ($user->can('rooms-control-inventories-edit')) {
                            $edit = "<button type='button' title='Editar' class='btn btn-sm btn-warning' onclick='edit($row->id)'><i class='fa fa-edit'></i></button>";
                        }

                        if ($user->can('rooms-control-inventories-delete')) {
                            $delete = "<button type='button' title='Eliminar' class='btn btn-sm btn-danger' onclick='remove($row->id)'><i class='fa fa-trash'></i></button>";
                        }

                        if ($user->can('rooms-control-inventories-duplicate-view')) {
                            $duplicate = "<button type='button' title='Duplicar' class='btn btn-sm btn-info' onclick='duplicate($row->id)'><i class='fa fa-clone'></i></button>";
                        }

                        return "$edit $delete $duplicate";
                    })
                    ->addColumn('created_at', function ($row) {
                        return Carbon::parse($row->created_at)->format('d / M / Y');
                    })
                    ->addColumn('position', function ($row) use ($room_inventory) {
                        $user = Auth::user();

                        $disabled = $user->can('rooms-control-inventories-edit');

                        $max = $room_inventory->max('position');
                        $item = "<select " . (!$disabled ? 'disabled' : '') . " class='form-control form-control-sm select-50 px-1' data-position='$row->position' id='item-position-$row->id' onchange='changePosition($row->id)'>";

                        for ($i = 1; $i <= $max; $i++) {
                            $item .= "<option value='$i' " . ($i == $row->position ? 'selected' : '') . ">$i</option>";
                        }

                        $item .= '</select>';

                        return $item;
                    })
                    ->rawColumns(['position', 'actions', 'image', 'created_at'])
                    ->make(true);
            } else {
                return DataTables::of([])
                    ->addIndexColumn()
                    ->make(true);
            }
        }
    }

    public function getHistory(RoomsControl $roomsControl, Request $request)
    {
        if ($request->ajax()) {
            $rooms_status = [];

            $location = SettingLocation::find($request->location_id);
            $rooms_count = $location->rooms;

            for ($i = 1; $i <= $rooms_count; $i++) {
                $last_status = $roomsControl::where('setting_location_id', $request->location_id)->where('room_number', $i)->latest('id')->first();

                if (is_null($last_status)) {
                    $rooms_status[$i] = (object)[
                        'room_number' => $i,
                        'setting_location_id' => null,
                        'status' => null,
                        'date' => null,
                        'model_id' => null,
                        'monitor_id' => null,
                    ];

                    continue;
                }

                $rooms_status[$i] = (object)[
                    'room_number' => $i,
                    'setting_location_id' => $last_status->setting_location_id,
                    'status' => $last_status->status,
                    'date' => $last_status->date,
                    'model_id' => $last_status->model_id,
                    'monitor_id' => $last_status->monitor_id,
                ];
            }

            return DataTables::of($rooms_status)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return (!is_null($row->status) ? $row->status == 1 ? '<b class="text-success">ENTREGADO</b>' : '<b class="text-info">RECIBIDO</b>' : '');
                })
                ->addColumn('date', function ($row) {
                    return !is_null($row->date) ? Carbon::parse($row->date)->format('d/M h:i a') : null;
                })
                ->addColumn('monitor', function ($row) {
                    return !is_null($row->monitor_id) ? User::find($row->monitor_id)->roleUserFullName() : null;
                })
                ->addColumn('model', function ($row) {
                    return !is_null($row->model_id) ? User::find($row->model_id)->roleUserFullName() : null;
                })
                ->addColumn('history', function ($row) {
                    return !is_null($row->date)
                        ?
                        '<a target="_blank" class="btn btn-outline-info btn-sm" href="' . route('roomscontrol.room_history', [$row->setting_location_id, $row->room_number]) . '">Historial <i class="fa fa-external-link-alt"></i></a>'
                        :
                        null;
                })
                ->rawColumns(['model', 'monitor', 'date', 'status', 'history'])
                ->make(true);
        }
    }

    public function saveRoomInventory(RoomsControlInventory $roomsControlInventory, Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => "required|unique:rooms_control_inventories,name|max:128",
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre del ítem ya existe',
            ]
        );

        try {
            DB::beginTransaction();

            $position = $roomsControlInventory::where('setting_location_id', $request->location_id)->where('room_number', $request->room_number)->max('position');
            $image = $request->file('image');

            if ($image) {
                $roomsControlInventory->image = $this->uploadFile($image, 'rooms_inventories');
            }

            $roomsControlInventory->name = $request->name;
            $roomsControlInventory->slug = strtolower(str_replace(' ', '_', trim($this->removeAccents($request->name))));
            $roomsControlInventory->setting_location_id = $request->location_id;
            $roomsControlInventory->room_number = $request->room_number;
            $roomsControlInventory->position = $position + 1;
            $success = $roomsControlInventory->save();

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function getInventoryItem(RoomsControlInventory $roomsControlInventory, Request $request)
    {
        $item_id = $request->id;
        $item = $roomsControlInventory::find($item_id);
        $item->name = str_replace('_', ' ', $item->name);;

        return response()->json($item);
    }

    public function editInventoryItem(RoomsControlInventory $roomsControlInventory, Request $request)
    {
        $item_id = $request->id;

        $this->validate(
            $request,
            [
                'name' => "required|max:128",
            ],
            [
                'name.required' => 'Este campo es obligatorio',
            ]
        );

        $image = $request->file('image');

        $item = $roomsControlInventory::find($item_id);

        try {
            DB::beginTransaction();

            $item->name = $request->name;
            $item->slug = strtolower(str_replace(' ', '_', trim($this->removeAccents($request->name))));

            if ($image) {
                $item->image = $this->uploadFile($image, 'rooms_inventories');
            }

            $success = $item->save();

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function delete(RoomsControlInventory $roomsControlInventory, Request $request)
    {
        try {
            DB::beginTransaction();

            $item_id = $request->id;
            $item = $roomsControlInventory::find($item_id);
            $success = $item->delete();

            DB::commit();
            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function getItemDuplicatedInRooms(RoomsControlInventory $roomsControlInventory, Request $request)
    {
        try {
            $item_id = $request->id;
            $location_id = $request->location_id;
            $item = $roomsControlInventory::find($item_id);

            return $roomsControlInventory::select('id', 'name', 'room_number')->where('name', $item->name)->where('image', $item->image)->where('setting_location_id', $location_id)->get();
        } catch (Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function duplicateItemInRooms(RoomsControlInventory $roomsControlInventory, Request $request)
    {
        try {
            DB::beginTransaction();

            $success = true;

            $item_id = $request->id;
            $item = $roomsControlInventory::find($item_id);

            if ($request->rooms) {
                foreach ($request->rooms as $room_number => $room) {
                    $position = $roomsControlInventory::where('setting_location_id', $item->setting_location_id)->where('room_number', $room_number)->max('position');

                    $success = $roomsControlInventory->updateOrCreate(
                        [
                            'name' => $item->name,
                            'slug' => strtolower(str_replace(' ', '_', trim($this->removeAccents($item->name)))),
                            'image' => $item->image,
                            'room_number' => $room_number,
                            'setting_location_id' => $item->setting_location_id,
                            'position' => $position + 1,
                        ],
                        [
                            'name' => $item->name,
                            'slug' => strtolower(str_replace(' ', '_', trim($this->removeAccents($item->name)))),
                            'image' => $item->image,
                            'room_number' => $room_number,
                            'setting_location_id' => $item->setting_location_id,
                            'position' => is_null($position) ? 1 : $position + 1,
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()]);
        }
    }

    public function setInventoryOrder(Request $request)
    {
        try {
            DB::beginTransaction();

            $item = RoomsControlInventory::find($request->id);

            RoomsControlInventory::where('setting_location_id', $item->setting_location_id)
                ->where('room_number', $item->room_number)
                ->where('position', $request->position)
                ->update(['position' => $item->position]); // Getting and change position to the old item

            $item->position = $request->position;
            $success = $item->save();

            DB::commit();

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.', 'code' => $e->getCode(), 'error' => $e->getMessage()], 500);
        }
    }

    public function getRoomHistory(RoomsControl $roomsControl, Request $request)
    {
        if ($request->ajax()) {
            if (!is_null($request->room_number)) {
                $room_history = $roomsControl::where('setting_location_id', $request->location_id)->where('room_number', $request->room_number)->orderBy('date', 'desc')->get();

                return DataTables::of($room_history)
                    ->addIndexColumn()
                    ->addColumn('monitor', function ($row) {
                        return User::find($row->monitor_id)->roleUserFullName();
                    })
                    ->addColumn('model', function ($row) {
                        return User::find($row->model_id)->roleUserFullName();
                    })
                    ->addColumn('date', function ($row) {
                        return Carbon::parse($row->date)->format('d/M h:i a');
                    })
                    ->addColumn('status', function ($row) {
                        return (!is_null($row->status) ? $row->status == 1 ? '<b class="text-success">ENTREGADO</b>' : '<b class="text-info">RECIBIDO</b>' : '');
                    })
                    ->addColumn('observations', function ($row) {
                        return "<button class='btn btn-outline-info btn-sm' onclick='openRoomHistory($row->id)'>Observaciones</a>";
                    })
                    ->rawColumns(['monitor', 'model', 'date', 'status', 'observations'])
                    ->make(true);
            } else {
                return DataTables::of([])
                    ->addIndexColumn()
                    ->make(true);
            }
        }
    }

    public function getRoomControl(RoomsControl $roomsControl, Request $request)
    {
        $room_control = $roomsControl::find($request->id);
        $control_observations = json_decode($room_control->observations);

        $observations = "";
        $have_observations = false;

        $extra_observations = "";
        $have_extra_observations = false;

        if ($control_observations) {
            $observations = "<table class='table table-hover table-striped'>";

            foreach ($control_observations as $observation) {
                if (empty($observation->observation)) {
                    continue;
                }

                $observations .= "<tr>";
                $observations .= "    <td>" . ucwords(str_replace('_', ' ', $observation->item)) . "</td>";
                $observations .= "    <td>$observation->observation</td>";
                $observations .= "</tr>";

                $have_observations = true;
            }

            $observations . "</table>";
        }

        if (is_null($room_control->image)) {
            $room_control->image = "<img src='" . asset("images/imagen-no-disponible.png") . "' title='Sin imagen' alt='sin imagen' class='img-fluid' />";
        } else {
            $room_control->image = "<a target='_blank' href='" . asset("storage/" . tenant('studio_slug') . "/rooms_control_models/$room_control->image") . "'><img src='" . asset("storage/" . tenant('studio_slug') . "/rooms_control_models/$room_control->image") . "' class='img-fluid'></a>";
        }

        if (!is_null($room_control->extra_image_description)) {
            $have_extra_observations = true;

            $extra_observations .= "<table class='table table-hover table-striped'>";
            $extra_observations .= "    <tr>";
            $extra_observations .= "        <td class='text-center'><a target='_blank' href='" . asset("storage/" . tenant('studio_slug') . "/task/$room_control->extra_image") . "'>$room_control->extra_image <i class='fa fa-external-link-alt'></i></a></td>";
            $extra_observations .= "        <td class='text-center'>$room_control->extra_image_description</td>";
            $extra_observations .= "    </tr>";
            $extra_observations .= "</table>";
        }

        $room_control->observations = $observations;
        $room_control->have_observations = $have_observations;

        $room_control->extra_observations = $extra_observations;
        $room_control->have_extra_observations = $have_extra_observations;

        return $room_control;
    }

    public function getLocationRoomsStatus(RoomsControl $roomsControl, Request $request)
    {
        // $location = SettingLocation::find($request->location_id);



        $location =
            DB::connection('gbmedia')->table('locacion')->find($request->location_id);
        $rooms_count =   $location->cantidad_cuartos;


        for ($i = 1; $i <= $rooms_count; $i++) {
            //     //     //     //     $last_status = $roomsControl::where('setting_location_id', $request->location_id)->where('room_number', $i)->latest('id')->first();
            $rooms_status =  DB::connection('gbmedia')->table('rooms_control')->where('id_location', '=', $request->location_id)->where('id_room', '=', $i)->latest('id')->first();

            //     // if (is_null($rooms_status)) {
            //     //     $rooms_status[$i] = (object)[
            //     //         'room_number' => $i,
            //     //         'status' => 2,
            //     //     ];

            //     //     continue;
            //     // }
            $result[] = [

                "room_number" => $i,
                "status" => $rooms_status->status,
                "last_updated" => null

            ];

            //     $rooms_status[$i] = (object)[
            //         'id_room' => $i,
            //         'status' => $rooms_status->status,
            //     ];
        }

        $result['last_updated'] = Carbon::now()->format('h:i:s a');

        return  response()->json($result);
    }

    public function getLocation(RoomsControl $roomsControl, Request $request)
    {
        // $location = SettingLocation::find($request->location_id);

        $location =  DB::connection('gbmedia')->table('locacion')->find($request->location_id);
        return response()->json($location);
    }
}
