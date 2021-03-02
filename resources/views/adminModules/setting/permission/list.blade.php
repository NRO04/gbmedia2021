@extends('layouts.app')

@section('pageTitle', 'Permisos')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Tareas</li>
    <li class="breadcrumb-item active"><a href="">Permisos</a></li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header">
                <span class="span-title">Permisos @if(!empty($module_name)) <span class="text-danger">({{ $module_name }})</span> @endif</span>
                @can('create-permission-view')
                    <button class="btn btn-success btn-sm float-right mx-1" data-target="#modal-create" data-toggle="modal" id="btn-open-modal">
                        <i class="fa fa-plus"></i>
                        Crear
                    </button>
                @endcan
            </div>
            <div class="card-body">
                <table class="table table-hover table-striped" id="tasks-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Slug</th>
                            <th>Descripción</th>
                            <th>Roles</th>
                            <th>Permisos</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @can('create-permission-view')
    <!-- Modal Create -->
    <div aria-hidden="true" aria-labelledby="modalCreateTaskLabel" class="modal fade" id="modal-create" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Tarea / Permiso @if(!empty($module_name)) (Módulo {{ $module_name }}) @endif</h5>
                </div>
                <div class="modal-body">
                    <form id="form-create">
                        @csrf
                        <div class="form-group">
                            <label for="display_name" class="required">Nombre</label>
                            <input class="form-control" id="display_name" name="display_name" placeholder="Listado de Usuarios" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="slug" class="required">Slug</label>
                            <input class="form-control" id="slug" name="slug" placeholder="users-export" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="description" class="required">Descripción</label>
                            <textarea class="form-control" cols="30" id="description" name="description" placeholder="Gestionar la lista de los usuarios registrados..." rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="container-roles" class="required">Botones de Permisos</label>
                            <div class="row">
                                <div class='col text-center'>
                                    <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-md mt-2'>
                                        <input type='checkbox' class='c-switch-input' name="checkbox_view" checked />Ver
                                        <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                    </label>
                                </div>
                                <div class='col text-center'>
                                    <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-md mt-2'>
                                        <input type='checkbox' class='c-switch-input' name="checkbox_create"/>Crear
                                        <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                    </label>
                                </div>
                                <div class='col text-center'>
                                    <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-md mt-2'>
                                        <input type='checkbox' class='c-switch-input' name="checkbox_edit"/>Editar
                                        <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                    </label>
                                </div>
                                <div class='col text-center'>
                                    <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-md mt-2'>
                                        <input type='checkbox' class='c-switch-input' name="checkbox_delete"/>Eliminar
                                        <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="module-id" name="module_id" value="{{ $module_id }}">
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

    @can('assign-permissions-view')
    <!-- Modal Asign Permissions -->
    <div class="modal fade" id="modal-assign" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="modalEditTaskLabel">
        <div class="modal-dialog modal-lg modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Permisos (<span id="span-permission-name"></span>)</h5>
                </div>
                <div class="modal-body">
                    <form id="form-edit">
                        @csrf
                        <div class="form-group">
                            <label for="container-roles"><small class="text-muted">Slug: <i id="span-permission-slug"></i></small></label>
                            <table class="table table-striped table-hover table-bordered- table-sm" id="table-roles-permissions">
                                <thead>
                                    <tr>
                                        <th>Rol</th>
                                        <th class="text-center" id="th-permission-slug-view">Ver</th>
                                        <th class="text-center" id="th-permission-slug-create">Crear</th>
                                        <th class="text-center" id="th-permission-slug-edit">Editar</th>
                                        <th class="text-center" id="th-permission-slug-delete">Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($roles AS $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td class="text-center">
                                            <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm label-checkbox-view mb-0'>
                                                <input type='checkbox' class='c-switch-input checkbox-view checkbox-permission' data-type="view" data-roleid="{{ $role->id }}" name="roles[{{ $role->id }}][view]" id="view_{{ $role->id }}" />
                                                <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                                <span id="loader-view-{{ $role->id }}" class="d-none"><i class="fas fa-spinner fa-pulse"></i></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm label-checkbox-create mb-0'>
                                                <input type='checkbox' class='c-switch-input checkbox-create checkbox-permission' data-type="create" data-roleid="{{ $role->id }}" name="roles[{{ $role->id }}][create]" id="create_{{ $role->id }}" disabled/>
                                                <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                                <span id="loader-create-{{ $role->id }}" class="d-none"><i class="fas fa-spinner fa-pulse"></i></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm label-checkbox-edit mb-0'>
                                                <input type='checkbox' class='c-switch-input checkbox-edit checkbox-permission' data-type="edit" data-roleid="{{ $role->id }}" name="roles[{{ $role->id }}][edit]" id="edit_{{ $role->id }}" disabled/>
                                                <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                                <span id="loader-edit-{{ $role->id }}" class="d-none"><i class="fas fa-spinner fa-pulse"></i></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class='checkbox-inline c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm label-checkbox-delete mb-0'>
                                                <input type='checkbox' class='c-switch-input checkbox-delete checkbox-permission' data-type="delete" data-roleid="{{ $role->id }}" name="roles[{{ $role->id }}][delete]" id="delete_{{ $role->id }}" disabled/>
                                                <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                                <span id="loader-delete-{{ $role->id }}" class="d-none"><i class="fas fa-spinner fa-pulse"></i></span>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" id="edit-id" name="task_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <!-- Modal Edit Description-->
    <div class="modal fade" id="modal-assign-description" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar Descripción</h5>
                </div>
                <div class="modal-body">
                    <form id="form-edit-description">
                        @csrf
                        <div class="form-group">
                            <label for="textarea-edit-description" class="required">Descripción</label>
                            <textarea class="form-control" name="description" id="textarea-edit-description" cols="30" rows="5"></textarea>
                        </div>
                        <input type="hidden" id="input-edit-description-permission-id" name="permission_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cerrar</button>
                    <button type="button" id="btn-edit-description" class="btn btn-sm btn-warning">Modificar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let table = null;

        $(document).ready(function () {
            let path = window.location.pathname;
            let module_id = SplitURL(path).pop();

            let url = '';

            if(isNaN(module_id)) {
                url = '{{ route('permission.get_permissions') }}';
            } else {
                url = '{{ route('permission.get_permissions', ":id") }}';
            }

            url = url.replace(':id', module_id);

            //let url = isNaN(module_id) ? '/permission/getPermissions' : '/permission/getPermissions/' + module_id;

            if ($("#tasks-table").length > 0) {
                new Vue({
                    el: "#tasks-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#tasks-table").DataTable({
                                processing: true,
                                serverSide: true,
                                lengthMenu: [[100, 200, 300, -1], [100, 200, 300, 'Todos']],
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: url,
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    { data: "display_name" },
                                    { data: "name" },
                                    { data: "description" },
                                    { data: "roles" },
                                    { data: "actions" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [1, 2, 3],
                                        orderable: false,
                                    },
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

        function assignPermissions(task_id)
        {
            ResetValidations();
            ResetModalForm("#form-edit");

            $('.checkbox-create').prop('disabled', true);
            $('.checkbox-edit').prop('disabled', true);
            $('.checkbox-delete').prop('disabled', true);

            $.ajax({
                url: "{{ route('permission.get_permission') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    task_id
                },
            })
            .done(function (res) {
                let task = res.task;
                let permissions = res.permissions;

                let id = task.id;
                let name = task.name;
                let display_name = task.display_name;
                let description = task.description;

                $.each(permissions, function (id, role_permissions) {
                    let role_id = role_permissions.id;
                    let options = role_permissions.options;

                    $.each(options, function (key, option) {
                        let slug = key;
                        let permission = key.split('-');
                        key = permission.pop();

                        let element = '#' + key + '_' + role_id;
                        $(element).data('slug', slug);
                        $(element).prop('disabled', false);
                        $(element).prop('checked', option);
                    });

                });

                $('#edit-id').val(id);
                $('#span-permission-name').text(display_name);
                $('#th-permission-slug-view').prop('title', name + '-view');
                $('#th-permission-slug-create').prop('title', name + '-create');
                $('#th-permission-slug-edit').prop('title', name + '-edit');
                $('#th-permission-slug-delete').prop('title', name + '-delete');
                $('#span-permission-slug').text(name);
                $('#edit-description').val(description);

                $("#modal-assign").modal("toggle");
            })
            .fail(function (res) {
                CallBackErrors(res);
            });
        }

        function editDescription(permission_id)
        {
            let description = $('#btn-description-' + permission_id).attr('data-original-title');
            $('#textarea-edit-description').val(description);
            $('#input-edit-description-permission-id').val(permission_id);

            $('#modal-assign-description').modal('show');
        }

        // CONTROL ACTIONS
        $("#btn-create").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = $("#form-create").serialize();

            $.ajax({
                url: "{{ route('permission.save_permission') }}",
                type: "POST",
                data: form_data,
            })
            .done(function (res, status, xhr) {
                Toast.fire({
                    icon: "success",
                    title: "La tarea fue agregada exitosamente",
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

        $("#btn-open-modal").on('click', function(){
            ResetValidations();
            //ResetModalForm("#form-create");
        });

        $('.checkbox-permission').on('click', function () {
            let role_id = $(this).data('roleid');
            let type = $(this).data('type');
            let slug = $(this).data('slug');
            let assign = $(this).is(':checked');
            let parent_permission_id = $('#edit-id').val();

            $('#loader-' + type + '-' + role_id).removeClass('d-none');

            $.ajax({
                url: '{{ route('permission.assign_permission') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    parent_permission_id,
                    slug,
                    type,
                    role_id,
                    assign: assign ? 1 : 0,
                },
            }).done(function (res) {
                $('#loader-' + type + '-' + role_id).addClass('d-none');
            }).fail(function (res) {
                $('#loader-' + type + '-' + role_id).addClass('d-none');
                $('#loader-' + type + '-' + role_id).text('<b>ERROR</b>');
                if(!assign) {
                    $('#' + type + '_' + role_id).prop('checked', true);
                } else {
                    $('#' + type + '_' + role_id).prop('checked', false);
                }
            });
        });

        $("#btn-edit-description").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = $("#form-edit-description").serialize();

            $.ajax({
                url: "{{ route('permission.edit_permission_description') }}",
                type: "POST",
                data: form_data,
            })
            .done(function (res, status, xhr) {
                Toast.fire({
                    icon: "success",
                    title: "La descripción fue modificada exitosamente",
                });

                $("#modal-assign-description").modal("hide");
                ResetModalForm("#form-edit-description");
                table.draw();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-assign-description').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });
    </script>
@endpush
