@extends('layouts.app')

@section('pageTitle', 'Módulos')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Configuraciones</li>
    <li class="breadcrumb-item active"><a href="">Módulos/Tareas</a></li>
@endsection

@section('content')

    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm">
                        <span class="span-title">Módulos</span>
                    </div>
                    <div class="col-sm">
                        @can('module-create')
                            <button id="create-module" class="btn btn-success btn-sm float-right mx-1">
                                <i class="fa fa-plus"></i> Crear
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover" id="tasks-table" style="width: 100%;">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Permisos</th>
                        <th>Admin</th>
                        <th>Editar</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div
        class="modal fade"
        id="exampleModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="cil-clipboard"></i>
                        Detalle Módulo
                    </h5>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form
                    class="form-horizontal"
                    id="myForm"
                    action="{{ route('module.update', 0) }}"
                    method="post"
                >
                    {!! method_field('PUT') !!} {{ csrf_field() }}

                    <div class="modal-body">
                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <label class="col-md-3 col-lg-2">Nombre*:</label>
                            <div class="col-md-8 col-lg-6">
                                <input
                                    class="inputForm"
                                    type="text"
                                    name="name"
                                    id="edit_name"
                                    required="required"
                                />
                            </div>

                            <div class="mb-3 form-check">
                                <input
                                    type="checkbox"
                                    name="administrator"
                                    class="form-check-input"
                                    id="edit_admin"
                                    value="1"
                                />
                                <label class="form-check-label" for="exampleCheck1">
                                    Administrador
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-danger"
                            data-dismiss="modal"
                        >
                            <i class="cil-x"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="cil-check-alt"></i>
                            Aceptar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-create-module" class="modal fade" role="dialog">
        <div class="modal-dialog modal-primary">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Crear Módulo</h4>
                </div>
                <div class="modal-body">

                    <form id="form-create" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input class="form-control" id="name" name="name" placeholder="Crear módulo" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control" cols="30" id="description" name="description" placeholder="Descripción del módulo" rows="4"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn_create">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-update-module" class="modal fade" role="dialog">
        <div class="modal-dialog modal-warning">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modificar Módulo</h4>
                </div>
                <div class="modal-body">
                    <form id="form-update" method="post">
                        @csrf
                        <input id="id_update" name="id_update" type="hidden">
                        <input id="old_name_update" name="old_name_update" type="hidden">
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input class="form-control" id="name_update" name="name_update" placeholder="Crear módulo" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control" cols="30" id="description_update" name="description_update" placeholder="Descripción del módulo" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="description">Administrador</label>
                            <div class='col text-left'>
                                <label
                                    class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-md mt-2'>
                                    <input type='checkbox' class='c-switch-input' id="is_admin_update"
                                           name="is_admin_update">
                                    <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn_update">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

@endsection @push('scripts')
    <script>
        let table = null;

        $(document).ready(function () {
            table = $('#tasks-table').DataTable({
                language: {
                    url: '{{ asset("DataTables/Spanish.json") }}',
                },
                processing: true,
                serverSide: true,
                lengthMenu: [[100, 200, 300, -1], [100, 200, 300, 'Todos']],
                ajax: {
                    url: "{{ route('module.getModules') }}",
                    dataSrc: 'data',
                    type: 'GET'
                },
                columns: [
                    {data: "name"},
                    {data: "description"},
                    {data: "permissions"},
                    {data: "admin"},
                    {data: "edit"},
                ],
                columnDefs: [
                    {
                        targets: [1, 2, 3, 4],
                        orderable: false,
                    },
                ],
                fnDrawCallback: function( oSettings ) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        });

        $("#create-module").on('click', function () {
            $('#modal-create-module').modal('show');
            $("#name").val('');
            $("#description").val('');
        });

        function update_module(id)
        {
            $.ajax({
                url: "{{ route('module.getModule') }}",
                type: "GET",
                dataType: 'JSON',
                data: {
                    _token: "{{ csrf_token() }}",
                    "id": id
                },
            })
            .done(function (res) {

                $("#id_update").val(res['id']);
                $("#name_update").val(res['name']);
                $("#old_name_update").val(res['name']);
                $("#description_update").val(res['description']);

                if (res['is_admin'] == 1) {
                    $("#is_admin_update").attr('checked', true);
                } else {
                    $("#is_admin_update").attr('checked', false);
                }

                $('#modal-update-module').modal('show');

            }).fail(function (res) {
                CallBackErrors(res);
            });
        }

        $("#btn_create").on('click', function () {

            if (($("#name").val() == '') || $("#description").val() == '') {
                toastr.error("Los campos son obligatorios.");
            } else {

                $.ajax({
                    url: "{{ route('module.create') }}",
                    type: "POST",
                    dataType: 'JSON',
                    data: $('#form-create').serialize(),
                })
                .done(function (res) {
                    if (res.message == true) {
                        toastr.success("El registro se realizo exitosamente");
                        $('#modal-create-module').modal('hide');
                        table.ajax.reload();
                    } else {
                        toastr.error("No se puede gestionar la solicitud, existe una solicitud pendiente en proceso.");
                        $('#modal-create-module').modal('hide');
                        table.ajax.reload();
                    }

                })
                .fail(function (res) {
                    toastr.error("Error comuniquese con el administrador");
                    table.ajax.reload();
                });
            }
        });

        $("#btn_update").on('click', function () {

            if (($("#name_update").val() == '') || $("#description_update").val() == '') {
                toastr.error("Los campos son obligatorios.");
            } else {

                $.ajax({
                    url: "{{ route('module.update') }}",
                    type: "POST",
                    dataType: 'JSON',
                    data: $('#form-update').serialize(),
                })
                .done(function (res) {
                    if (res.message == true) {
                        toastr.success("El registro se realizo exitosamente");
                        $('#modal-update-module').modal('hide');
                        table.ajax.reload();
                    } else {
                        toastr.error("No se puede modificar el modulo porque el nombre ya se encuentra registrado.");
                        $('#modal-update-module').modal('hide');
                        table.ajax.reload();
                    }

                })
                .fail(function (res) {
                    toastr.error("Error comuniquese con el administrador");
                    table.ajax.reload();
                });
            }
        });

    </script>
@endpush
