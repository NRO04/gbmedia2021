@extends('layouts.app')

@section('pageTitle', 'Roles')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Configuraciones</li>
    <li class="breadcrumb-item active"><a href="">Listado de Roles</a></li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header">
                <span class="span-title">Roles</span>
                @can('roles-create')
                    <button class="btn btn-success btn-sm float-right mx-1" data-target="#modal-create" data-toggle="modal" id="btn-open-modal">
                        <i class="fa fa-plus"></i>
                        Crear
                    </button>
                @endcan
            </div>
            <div class="card-body">
                <table class="table table-hover table-striped" id="roles-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Nombre Alternativo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div aria-hidden="true" aria-labelledby="modalCreateRolesLabel" class="modal fade" id="modal-create" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Crear Rol</h4>
                </div>
                <div class="modal-body">
                    <form id="form-create">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="required">Nombre</label>
                            <input class="form-control" id="name" name="name" placeholder="Soporte" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="alternative-name">Nombre Alternativo <small class="text-muted">(mostrado en el carnet y contratos)</small></label>
                            <input class="form-control" id="alternative-name" name="alternative_name" placeholder="Secretario/a Administrativo/a" type="text"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-create" type="button">Crear</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="modalEditRolesLabel">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modificar Rol</h4>
                </div>
                <div class="modal-body">
                    <form id="form-edit">
                        @csrf
                        <div class="form-group">
                            <label for="edit-name" class="required">Nombre</label>
                            <input class="form-control" id="edit-name" name="name" placeholder="Soporte" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="edit-alternative-name">Nombre Alternativo <small class="text-muted">(mostrado en el carnet y contratos)</small></label>
                            <input class="form-control" id="edit-alternative-name" name="alternative_name" placeholder="Secretario/a Administrativo/a" type="text"/>
                        </div>
                        <input type="hidden" id="edit-id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-warning" id="btn-edit">Modificar</button>
                </div>
            </div>
        </div>
    </div>

@endsection @push('scripts')
    <script>
        let table = null;

        $(document).ready(function () {
            if ($("#roles-table").length > 0) {
                new Vue({
                    el: "#roles-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#roles-table").DataTable({
                                processing: true,
                                serverSide: true,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('role.get_roles') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    { data: "name" },
                                    { data: "alternative_name" },
                                    { data: "actions" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [2],
                                        orderable: false,
                                    },
                                ],
                            });
                        },
                    },
                });
            }
        });

        function Edit(id)
        {
            ResetValidations();
            ResetModalForm("#form-edit");

            $.ajax({
                url: "{{ route('role.get_role') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            })
            .done(function (res) {
                let role = res;

                let id = role.id;
                let name = role.name;
                let alternative_name = role.alternative_name;

                $('#edit-id').val(id);
                $('#edit-name').val(name);
                $('#edit-alternative-name').val(alternative_name);

                $("#modal-edit").modal("toggle");
            })
            .fail(function (res) {
                CallBackErrors(res);
            });
        }

        function Delete(id)
        {
            Swal.fire({
                title: '¿Está seguro que desea eliminar el item seleccionado?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('role.delete_role') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        }
                    }).done(function (res) {
                        Toast.fire({
                            icon: "success",
                            title: "El rol fue eliminado exitosamente",
                        });

                        table.draw();
                    }).fail(function (res) {
                        let json = res.responseJSON;

                        if(!json.success) {
                            Swal.fire({
                                title: '¡Error!',
                                text: json.msg,
                                icon: 'error',
                            });
                        }
                    });
                }
            });
        }

        // CONTROL ACTIONS
        $("#btn-create").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = $("#form-create").serialize();

            $.ajax({
                url: "{{ route('role.save_role') }}",
                type: "POST",
                data: form_data,
            })
            .done(function (res) {
                Toast.fire({
                    icon: "success",
                    title: "El rol fue agregado exitosamente"
                });

                $("#modal-create").modal("toggle");
                ResetModalForm("#form-create");
                table.draw();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-create').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        $("#btn-edit").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = $('#form-edit').serialize();

            $.ajax({
                url: "{{ route('role.edit_role') }}",
                type: "POST",
                data: form_data,
            })
            .done(function (res, status, xhr) {
                Toast.fire({
                    icon: "success",
                    title: "El rol fue modificado exitosamente",
                });

                $("#modal-edit").modal("toggle");
                ResetModalForm("#form-edit");
                table.draw();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-edit').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        $("#btn-open-modal").on('click', function(){
            ResetValidations();
            ResetModalForm("#form-create");
        });
    </script>
@endpush
