@extends('layouts.app')

@section('pageTitle', 'Bloquear Compras Boutique')

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Bloquear Compras Boutique</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    @can('boutique-blocked-user-create')
                        <a class="btn btn-success btn-sm" data-target="#modal-block-user" data-toggle="modal" id="btn-open-block-user-modal">
                            <i class="fa fa-user-plus"></i>&nbsp; Bloquear Usuario
                        </a>
                    @endcan
                    @can('boutique-blocked-max-value')
                        <a class="btn btn-info btn-sm" data-target="#modal-max-value" data-toggle="modal" id="btn-open-max-value-modal">
                            <i class="fa fa-cog"></i> Valor de Compra
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="col-12 row">
                    <h6><i class="fa fa-info-circle text-warning"></i> Al bloquear un usuario, este solo podrá realizar compras de productos de valor menor a <span id="label-max-value" class="text-success">${{ $blocked_value_formatted }}</span></h6>
                </div>
                <hr>
                <table class="table table-hover" id="blocked-users-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Usuario / Modelo</th>
                            <th>Bloqueado desde</th>
                            <th>Bloqueado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @can('boutique-blocked-user-create')
    <!-- Modal Block User -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-block-user" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Bloquear Usuario / Modelo</h4>
                </div>
                <div class="modal-body">
                    <form id="form-block-user">
                        @csrf
                        <div class="form-group">
                            <div class="col-12 row">
                                <div class="col-6">
                                    <label>
                                        <input type="radio" name="selected_user" id="for-model" value="models" class="radio-for">
                                        <span class="label-text">Modelos</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label>
                                        <input type="radio" name="selected_user" id="for-user" value="users" class="radio-for">
                                        <span class="label-text">Otro cargo</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group d-none" id="container-for-models">
                                <label for="select-for-model">Modelos</label>
                                <select class="col-12 form-control" name="model" id="select-for-model">
                                    <option value="">Seleccione...</option>
                                    @foreach($models AS $model)
                                        <option value="{{ $model->id }}">{{ $model->nick }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group d-none" id="container-for-users">
                                <label for="select-for-user">Usuarios</label>
                                <select class="col-12 form-control" name="users" id="select-for-user">
                                    <option value="">Seleccione...</option>
                                    @foreach($users AS $user)
                                        <option value="{{ $user->id }}">{{ $user->roleUserShortName() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-block-user" type="button">Bloquear</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('boutique-blocked-max-value')
    <!-- Modal Value -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-max-value" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Valor Máximo de Compra</h4>
                </div>
                <div class="modal-body">
                    <form id="form-max-value">
                        @csrf
                        <div class="form-group">
                            <label for="input-max-value">Valor máximo de compra</label>
                            <input class="form-control" type="text" name="max_value" id="input-max-value" value="{{ $blocked_value }}">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-max-value" type="button">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection @push('scripts')
    <script>
        let table = null;

        $(document).ready(function () {
            if ($("#blocked-users-table").length > 0) {
                new Vue({
                    el: "#blocked-users-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#blocked-users-table").DataTable({
                                processing: true,
                                serverSide: false,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('boutique.get_blocked_users') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    { data: "image" },
                                    { data: "user" },
                                    { data: "date" },
                                    { data: "blocked_by" },
                                    { data: "actions" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [0, 4],
                                        orderable: false,
                                    },
                                ],
                                fnDrawCallback: function() {
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        },
                    },
                });
            }
        });

        // CONTROL FUNCTIONS
        $('.radio-for').on('click', function () {
            $('#container-for-models').addClass('d-none');
            $('#container-for-users').addClass('d-none');

            let selected = $(this).val();

            if(selected === 'users') {
                $('#container-for-users').removeClass('d-none');
            }
            else if (selected === 'models')
            {
                $('#container-for-models').removeClass('d-none');
            }
        });

        $('#btn-block-user').on('click', function () {
            let user_id = 0;

            let checked = $('input[name=selected_user]:checked').val();

            if(checked === undefined) {
                Toast.fire({
                    icon: "warning",
                    title: "Debe seleccionar el cargo",
                });

                return;
            }

            if(checked === 'users') {
                user_id = $('#select-for-user').val();

                if(user_id === "") {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar el usuario",
                    });

                    return;
                }
            }

            if(checked === 'models') {
                user_id = $('#select-for-model').val();

                if(user_id === "") {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar el/la modelo",
                    });

                    return;
                }
            }

            DisableModalActionButtons();

            $.ajax({
                url: '{{ route('boutique.block_user') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id
                },
            }).done(function (res) {
                if (!res.success) {
                    Toast.fire({
                        icon: "warning",
                        title: res.msg,
                        timer: 10000,
                    });

                    return;
                }

                Toast.fire({
                    icon: "success",
                    title: "Usuario bloqueado correctamente",
                });

                ResetModalForm('#form-block-user');

                $('#container-for-models').addClass('d-none');
                $('#container-for-users').addClass('d-none');

                $('#modal-block-user').modal('hide');

                table.ajax.reload();
            }).fail(function (res) {
                EnableModalActionButtons();

                let json = res.responseJSON;
                if (!json.success) {
                    Toast.fire({
                        icon: "error",
                        title: json.msg,
                        timer: 10000,
                    });
                }
            }).always(function () {
                EnableModalActionButtons();
            });
        });

        $('#btn-max-value').on('click', function () {
            let value = $('#input-max-value').val();

            if(value === "" || value == 0) {
                Toast.fire({
                    icon: "warning",
                    title: "El valor no puede estar vacío y debe ser mayor a cero",
                });

                return;
            }

            DisableModalActionButtons();

            $.ajax({
                url: '{{ route('boutique.save_block_value') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    value
                },
            }).done(function (res) {
                if (!res.success) {
                    Toast.fire({
                        icon: "warning",
                        title: res.msg,
                        timer: 10000,
                    });

                    return;
                }

                $('#label-max-value').text('');

                Toast.fire({
                    icon: "success",
                    title: "Valor modificado correctamente",
                });

                ResetModalForm('#form-max-value');

                $('#label-max-value').text("$" + new Intl.NumberFormat("de-DE").format(value));
                $('#input-max-value').val(value);
                $('#modal-max-value').modal('hide');

                table.ajax.reload();
            }).fail(function (res) {
                EnableModalActionButtons();

                let json = res.responseJSON;
                if (!json.success) {
                    Toast.fire({
                        icon: "error",
                        title: json.msg,
                        timer: 10000,
                    });
                }
            }).always(function () {
                EnableModalActionButtons();
            });
        });

        function deleteUserBlocked(id)
        {
            SwalGB.fire({
                title: '¿Está seguro que desea eliminar el bloquedo del usuario seleccionado?',
                icon: 'question',
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('boutique.delete_user_block') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        },
                    }).done(function (res) {
                        Toast.fire({
                            icon: "success",
                            title: "Usuario eliminado correctamente",
                            timer: 5000,
                        });

                        table.ajax.reload();
                    }).fail(function (res) {
                        let json = res.responseJSON;
                        if (!json.success) {
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
    </script>
@endpush
