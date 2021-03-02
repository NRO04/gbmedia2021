@extends('layouts.app')

@section('pageTitle', 'Inventario de Cuartos')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Tareas</li>
    <li class="breadcrumb-item active"><a href="">Inventario de Cuartos</a></li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-12 col-md-6">
                    <span class="span-title">Inventario de Cuartos</span>
                </div>
                <div class="col-12 col-md-6 text-right row">
                    <label for="filter-locations" class="col-12 col-md-6 pt-2">Locación:</label>
                    <div class="col-12 col-md-6">
                        <select id="filter-locations" class="form-control">
                            <option value="">Seleccione...</option>
                            @foreach($locations AS $location)
                                <option @if($location->id == 1) selected @endif value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="container-rooms" class="d-none">
                    <div class="form-group row">
                        <div class="col-12 col-sm-5 col-md-4 col-lg-3 my-1">
                            <select class="form-control form-control-sm" id="room-number">
                                <option value="">Seleccione el cuarto</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">Cuarto {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 col-sm-5 col-md-4 col-lg-3 my-1">
                            @can('rooms-control-inventories-create')
                                <button class="btn btn-success btn-sm d-none" data-target="#modal-create" data-toggle="modal" id="btn-open-create-modal">
                                    <i class="fa fa-plus"></i>
                                    Crear ítem
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-12 text-center" id="inventory-table-no-data">
                    <h5>Por favor, seleccione un cuarto</h5>
                </div>
                <div id="container-table" class="d-none">
                    <table class="responsive table table-hover table-striped" id="inventory-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Creación</th>
                            <th>Posición <i data-toggle="tooltip" title="" data-original-title="Orden en que aparecerán los ítems en la APP" class="fa fa-question-circle"></i></th>
                            <th>Imagen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    @can('rooms-control-inventories-create')
    <!-- Modal Create -->
    <div aria-hidden="true" aria-labelledby="modalCreateTaskLabel" class="modal fade" id="modal-create" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear ítem</h5>
                </div>
                <div class="modal-body">
                    <form id="form-create">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="required">Nombre del ítem</label>
                            <input class="form-control" id="name" name="name" placeholder="Televisor LED" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="image">Imagen</label>
                            <input type="file" name="image" id="image" class="form-control-file" accept="image/*">
                        </div>
                        <input type="hidden" name="location_id" id="location_id">
                        <input type="hidden" name="room_number" id="room_number">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-create" type="button">Crear</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('rooms-control-inventories-edit')
    <!-- Modal Edit -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-edit" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar ítem del inventario</h4>
                </div>
                <div class="modal-body">
                    <form id="form-edit">
                        @csrf
                        <div class="form-group">
                            <label for="input-edit-name" class="required">Nombre</label>
                            <input class="form-control" id="input-edit-name" name="name" placeholder="Televisor LED" type="text" />
                        </div>
                        <div class="form-group row">
                            <div class="col-12 col-sm-8">
                                <label for="image">Imagen</label>
                                <input type="file" name="image" id="input-edit-image" class="form-control-file" accept="image/*">
                            </div>
                        </div>
                        <input type="hidden" id="input-edit-item-id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-warning" id="btn-edit" type="button">Editar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('rooms-control-inventories-duplicate-view')
    <!-- Modal Duplicate Item -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-duplicate" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Duplicar ítem del inventario</h4>
                </div>
                <div class="modal-body">
                    <small class="text-muted">Ítem: <i id="span-item-name"></i></small>
                    <hr>
                    <form id="form-duplicate">
                        @csrf
                        <div class="row">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="col-4 col-sm-3 text-center my-2">
                                    <div class="room-inner">
                                        <div class="col-12">
                                            <span class="d-none d-sm-inline">Cuarto</span> {{ $i }}
                                        </div>
                                        <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm label-checkbox-delete mb-0'>
                                            <input type='checkbox' class='c-switch-input checkbox-room' name="rooms[{{ $i }}]" id="room-{{ $i }}"/>
                                            <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                        </label>
                                    </div>
                                </div>
                            @endfor
                        </div>
                        <input type="hidden" name="id" id="input-duplicate-id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-info" id="btn-duplicate" type="button">Duplicar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('scripts')
    <script>
        let table = null;
        let room_number = null;
        let location_id = null;

        $(document).ready(function () {
            if ($("#inventory-table").length > 0) {
                new Vue({
                    el: "#inventory-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#inventory-table").DataTable({
                                responsive: true,
                                processing: true,
                                serverSide: false,
                                lengthMenu: [[100, 200, 300, -1], [100, 200, 300, 'Todos']],
                                ordering: false,
                                ajax: {
                                    url: '{{ route('roomscontrol.get_room_inventory') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                    data: function(data) {
                                        data.room_number = room_number;
                                        data.location_id = location_id;
                                    },
                                    beforeSend: function (xhr) {
                                        if(location_id == null || room_number == null) {
                                            xhr.abort();
                                        }
                                    },
                                },
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                columns: [
                                    { data: "name" },
                                    { data: "created_at" },
                                    { data: "position" },
                                    { data: "image" },
                                    { data: "actions" },
                                ],
                                fnDrawCallback: function( oSettings ) {
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        },
                    },
                });
            }
        });

        // CONTROL ACTIONS
        $("#btn-create").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = new FormData(document.getElementById('form-create'));

            $.ajax({
                url: "{{ route('roomscontrol.save_room_inventory') }}",
                type: "POST",
                data: form_data,
                processData: false,
                contentType: false,
            })
            .done(function (res) {
                if(res.success) {
                    Toast.fire({
                        icon: "success",
                        title: "El ítem fue agregado exitosamente",
                    });

                    $("#modal-create").modal("toggle");
                    ResetModalForm("#form-create");
                    table.ajax.reload();
                }
            })
            .fail(function (res) {
                $('#modal-create').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        $('#btn-open-create-modal').on('click', function () {
            ResetValidations();
        });

        $('#room-number').on('change', function () {
            let selected_room_number = $(this).val();

            if(selected_room_number == '') {
                table.clear().draw();
                $('#btn-open-create-modal').addClass('d-none');
                $('#container-table').addClass('d-none');
                $('#inventory-table-no-data').removeClass('d-none');
                return;
            }

            $('#inventory-table-no-data').addClass('d-none');
            $('#container-table').removeClass('d-none');

            $('#room_number').val(selected_room_number);
            $('#btn-open-create-modal').removeClass('d-none');

            room_number = selected_room_number;
            table.ajax.reload();
        });

        $('#filter-locations').on('change', function () {
            $('#container-table').addClass('d-none');
            $('#room-number').html('');
            table.clear().draw();

            let selected_location_id = $(this).val();
            let selected_room_number = $('#room-number').val();

            location_id = selected_location_id;
            $('#location_id').val(location_id);

            $('#room-number').val('');
            $('#btn-open-create-modal').addClass('d-none');

            if(selected_location_id != '') {
                $.ajax({
                    url: '{{ route('roomscontrol.get_location') }}',
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        location_id
                    },
                }).done(function (res) {
                    let options = '';
                    let rooms = res.rooms;

                    options += '<option value="">Seleccione el cuarto</option>';

                    for (let i = 1; i <= rooms; i++) {
                        options += '<option value="' + i + '">Cuarto ' + i + '</option>'
                    }

                    $('#room-number').append(options);
                }).fail(function (res) {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información",
                    });

                    return;
                });
            }

            if(selected_location_id != '' || (selected_location_id != '' && selected_room_number != '')) {
                $('#container-rooms').removeClass('d-none');
            }  else {
                $('#container-rooms').addClass('d-none');
                $('#container-table').addClass('d-none');
                $('#inventory-table-no-data').addClass('d-none');
            }

            if(selected_location_id != '' && selected_room_number != '') {
                table.ajax.reload();
            }
        });

        function edit(id)
        {
            ResetValidations();
            ResetModalForm("#form-edit");

            $.ajax({
                url: "{{ route('roomscontrol.get_inventory_item') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            })
            .done(function (res) {
                let item_name = res.name;

                $("#input-edit-item-id").val(id);
                $("#input-edit-name").val(item_name);

                $("#modal-edit").modal('show');
            })
            .fail(function (res) {
                $("#modal-edit").modal('hide');

                Toast.fire({
                    icon: "error",
                    title: 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.',
                    timer: 10000,
                });
            });
        }

        $("#btn-edit").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = new FormData(document.getElementById('form-edit'));

            $.ajax({
                url: "{{ route('roomscontrol.edit_inventory_item') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                if(res.success) {
                    Toast.fire({
                        icon: "success",
                        title: "El ítem fue actualizado exitosamente",
                    });

                    $("#modal-edit").modal("hide");
                    ResetModalForm("#form-edit");
                    table.ajax.reload();
                } else {
                    Toast.fire({
                        icon: "error",
                        title: 'Ha ocurrido un error al actualizar la información. Por favor, intente mas tarde.',
                        timer: 10000,
                    });
                }
            })
            .fail(function (res) {
                $('#modal-edit').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        function remove(id)
        {
            SwalGB.fire({
                title: '¿Está seguro que desea eliminar el ítem?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('roomscontrol.delete') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        },
                    }).done(function (res) {
                        // item deleted successfully
                        table.ajax.reload();

                        Toast.fire({
                            icon: "success",
                            title: "Ítem eliminado exitosamente",
                        });
                    }).fail(function (res) {
                        let json = res.responseJSON;

                        if(!json.success) {
                            Toast.fire({
                                icon: "error",
                                title: json.msg,
                                timer: 10000,
                            });
                        }
                    });
                }
            });
        }

        function duplicate(id)
        {
            $('.checkbox-room').prop('checked', false);
            $('#input-duplicate-id').val(id);

            $.ajax({
                url: '{{ route('roomscontrol.get_item_duplicated_in_rooms') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id,
                    location_id
                },
            }).done(function (res) {
                let item_name = res[0].name;

                $.each(res, function (i, room) {
                    let room_number = room.room_number;
                    $('#room-' + room_number).prop('checked', true);
                    $('#room-' + room_number).prop('disabled', true);
                });

                $('#span-item-name').text(item_name);
                $('#modal-duplicate').modal('show');
            }).fail(function (res) {
                $('#modal-duplicate').modal('hide');

                Toast.fire({
                    icon: "error",
                    title: 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.',
                    timer: 10000,
                });
            });
        }

        $('#btn-duplicate').on('click', function () {
            DisableModalActionButtons();

            let form_data = new FormData(document.getElementById('form-duplicate'));

            $.ajax({
                url: "{{ route('roomscontrol.duplicate_inventory_item') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                if(res.success) {
                    Toast.fire({
                        icon: "success",
                        title: "El ítem fue actualizado exitosamente",
                    });

                    $("#modal-duplicate").modal("hide");
                    ResetModalForm("#form-duplicate");
                    table.ajax.reload();
                } else {
                    Toast.fire({
                        icon: "error",
                        title: 'Ha ocurrido un error al actualizar la información. Por favor, intente mas tarde.',
                        timer: 10000,
                    });
                }
            })
            .fail(function (res) {
                $('#modal-edit').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        function changePosition(id)
        {
            let position = parseInt($('#item-position-' + id).val());

            $.ajax({
                url: '{{ route('roomscontrol.set_inventory_order') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id,
                    position
                },
            }).done(function (res) {
                table.ajax.reload();
            }).fail(function (res) {
                Toast.fire({
                    icon: "error",
                    title: 'Ha ocurrido un error al actualizar la información. Por favor, intente mas tarde.',
                    timer: 10000,
                });
            });
        }
    </script>
@endpush
