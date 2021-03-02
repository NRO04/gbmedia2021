@extends('layouts.app')

@section('pageTitle', 'Alarmas Creadas')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Alarmas</li>
    <li class="breadcrumb-item active">Creadas</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Alarmas Creadas</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    @can('alarms-create')
                        <a class="btn btn-success btn-sm" href="{{ route('alarms.alarms', 'create') }}">
                            <i class="fa fa-plus"></i> Crear
                        </a>
                    @endcan
                    @can('alarms')
                        <a class="btn btn-info btn-sm" href="{{ route('alarms.alarms') }}">
                            <i class="fa fa-bolt"></i> Pendientes
                        </a>
                    @endcan
                    @can('alarms-finished')
                        <a class="btn btn-warning btn-sm" href="{{ route('alarms.finished') }}">
                            <i class="fa fa-check"></i> Finalizadas
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover" id="alarms-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cada</th>
                            <th>Roles</th>
                            <th>Usuarios</th>
                            <th>Se muestra el</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div aria-hidden="true" aria-labelledby="modalCreateAlarmLabel" class="modal fade overflow-auto" id="modal-edit" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Alarma</h4>
                </div>
                <div class="modal-body">
                    <form id="form-edit">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="required">Nombre</label>
                            <input class="form-control" id="name" name="name" placeholder="Formateo de Computadores" type="text"/>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label required">
                                Para <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="" data-original-title="Elija los remitentes que puedan visualizar o deban recibir esta alarma. Si desea seleccionar varias áreas o usuarios, mantenga presionado la tecla CRTL y haga click en la opción."></i>
                            </label>
                            <div class="col-md-9 col-form-label">
                                <div class="form-check form-check-inline mr-1">
                                    <input class="form-check-input checkbox-for" id="input-roles" type="checkbox" value="roles" name="for">
                                    <label class="form-check-label" for="input-roles">Roles</label>
                                </div>
                                <div class="form-check form-check-inline mr-1">
                                    <input class="form-check-input checkbox-for" id="input-users" type="checkbox" value="users" name="for">
                                    <label class="form-check-label" for="input-users">Usuarios</label>
                                </div>
                                <div class="form-check form-check-inline mr-1">
                                    <input class="form-check-input checkbox-for" id="input-models" type="checkbox" value="models" name="for">
                                    <label class="form-check-label" for="input-models">Modelos</label>
                                </div>
                            </div>
                        </div>
                        <div id="container-receivers" class="d-none-">
                            <hr>
                            <div class="form-group col-12" style="display: none" id="container-roles"></div>
                            <div class="form-group" style="display: none" id="container-users"></div>
                            <small class="text-muted">Recipientes:</small>
                            <div class="form-group bg-secondary p-2" id="container-receivers-text"></div>
                            <hr>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label required">
                                Ciclo <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="" data-original-title="<b>Fecha Fija</b>: La alarma se restablecerá basándose en la fecha indicada <br><br> <b>Ciclo</b>: La alarma se reestablecerá tomando el cuenta el día en que la misma se marque como finalizada"></i>
                            </label>
                            <div class="col-md-9 col-form-label">
                                <div class="form-check form-check-inline mr-1">
                                    <input class="form-check-input cycle_type" id="fixed-date" type="radio" value="fixed-date" name="cycle_type">
                                    <label class="form-check-label mr-1" for="fixed-date">Fecha Fija</label>
                                </div>
                                <div class="form-check form-check-inline mr-1">
                                    <input class="form-check-input cycle_type" id="normal-cycle-date" type="radio" value="normal-cycle-date" name="cycle_type">
                                    <label class="form-check-label mr-1" for="normal-cycle-date">Ciclo a partir de fecha</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label required" for="date">
                                Fecha <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="" data-original-title="La alarma empezará a contar a partir de esta fecha"></i>
                            </label>
                            <div class="col-md-9 col-form-label">
                                <input class="form-control" id="date" name="date" type="date" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="timer" class="required">
                                Temporizador <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="" data-original-title="Tiempo en el cual se disparará la alarma automáticamente"></i>
                            </label>
                            <div class="row">
                                <div class="col-4 col-md-3">
                                    <input class="form-control" id="timer" name="timer" type="number" max="24" min="1" value="1"/>
                                </div>
                                <div class="col-8 col-md-9">
                                    <select class="form-control" name="cycle" id="cycle">
                                        <option value="weekly">Semana</option>
                                        <option value="monthly">Mes</option>
                                        <option value="yearly">Año</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="id" id="alarm-id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-warning" id="btn-edit" type="button">Editar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Receivers -->
    <div class="modal fade" id="modal-receivers" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="modalReceiversLabel">
        <div class="modal-dialog modal-primary modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Agregar recipientes (<span id="label-to-receivers"></span>)</h4>
                </div>
                <div class="modal-body">
                    <div class="row-" id="container-select-all">
                        <div class="form-check col-12 mb-2">
                            <input class="form-check-input" id="checkbox-select-all" type="checkbox">
                            <label class="form-check-label" for="checkbox-select-all">Seleccionar todos</label>
                        </div>
                    </div>
                    <div id="modal-body-receivers" class="col-12 row"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection @push('scripts')
    <script>
        let table = null;

        let receivers = {
            'to_roles' : [],
            'to_users' : [],
            'to_models' : [],
        };

        $(document).ready(function () {
            if ($("#alarms-table").length > 0) {
                new Vue({
                    el: "#alarms-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#alarms-table").DataTable({
                                processing: true,
                                serverSide: false,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('alarms.get_alarms') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    { data: "name" },
                                    { data: "cycle" },
                                    { data: "for_roles" },
                                    { data: "for_users" },
                                    { data: "showing_date" },
                                    { data: "actions" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [2, 3, 5],
                                        orderable: false,
                                    },
                                    {
                                        targets: [2, 3, 5],
                                        searchable: false,
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

        // CONTROL ACTIONS
        function Delete(id)
        {
            Swal.fire({
                title: '¿Está seguro que desea eliminar la alarma?',
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
                        url: '{{ route('alarms.delete') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        },
                    }).done(function (res) {
                        // alarm deleted successfully
                        table.draw();

                        Toast.fire({
                            icon: "success",
                            title: "Alarma eliminada exitosamente",
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

        function Edit(id)
        {
            $('.checkbox-for').prop('checked', false);
            receivers['to_roles'] = [];
            receivers['to_users'] = [];
            receivers['to_models'] = [];
            ResetValidations();
            ResetModalForm("#form-edit");
            $('#container-receivers-text').html('');

            $.ajax({
                url: "{{ route('alarms.get_alarm') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            })
            .done(function (res) {
                let all_roles = @json($roles);
                let all_users = @json($users);
                let all_models = @json($models);

                let alarm = res;
                let roles = alarm.roles;
                let users = alarm.users;
                let models = alarm.models;
                let showing_roles = alarm.showing_roles;
                let showing_users = alarm.showing_users;
                let showing_receivers = alarm.showing_receivers;

                $('#alarm-id').val(alarm.id);
                $('#name').val(alarm.name);
                $('#date').val(alarm.showing_date);
                $('#timer').val(alarm.cycle_count);
                $('#cycle').val(alarm.cycle);
                $('#container-receivers-text').append(showing_receivers);

                if(alarm.is_fixed_date) {
                    $('#fixed-date').prop('checked', true);
                } else {
                    $('#normal-cycle-date').prop('checked', true);
                }

                $.each(roles, function (id, role) {
                    let selected_role = all_roles.find(function(item) {
                        if(item.id === role.setting_role_id) { return true }
                    });

                    receivers['to_roles'].push({id: role.setting_role_id, name: selected_role.name});
                });

                $.each(users, function (id, user) {
                    let selected_user = all_users.find(function(item) {
                        if(item.id === user.user_id) { return true }
                    });

                    if(selected_user !== undefined) {
                        receivers['to_users'].push({id: selected_user.id, name: selected_user.first_name + ' ' + selected_user.last_name});
                    }
                });

                $.each(models, function (id, model) {
                    let selected_model = all_models.find(function(item) {
                        if(item.id === model.id) { return true }
                    });

                    receivers['to_models'].push({id: selected_model.id, name: selected_model.nick});
                });

                showReceivers();

                $("#modal-edit").modal("toggle");
            })
            .fail(function (res) {
                $("#modal-edit").modal("toggle");

                Toast.fire({
                    icon: "error",
                    title: 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.',
                    timer: 10000,
                });
            });
        }

        $('.radio-for').on('change', function () {
            let selected = $(this).val();

            $('#container-users').css('display', 'none');
            $('#container-roles').css('display', 'none');

            if(selected === 'roles') {
                $('#container-roles').css('display', 'block');
            } else {
                $('#container-users').css('display', 'block');
            }
        });

        $("#btn-edit").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let all_receivers = JSON.stringify(receivers);
            let form_data = new FormData(document.getElementById('form-edit'));
            form_data.append('receivers', all_receivers);

            $.ajax({
                url: "{{ route('alarms.edit') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                // alarm edited successfully
                table.draw();

                Toast.fire({
                    icon: "success",
                    title: "Alarma modificada exitosamente",
                });

                $('#modal-edit').modal('toggle');
            })
            .fail(function (res) {
                $('#modal-edit').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        $("#btn-open-modal").on('click', function(){
            ResetValidations();
            ResetModalForm("#form-edit");
            $('#container-users').css('display', 'none');
            $('#container-roles').css('display', 'none');
        });

        // EXTRA ACTIONS
        $('#checkbox-select-all').change(function () {
            let checkboxes = $('#modal-body-receivers').find(':checkbox');
            checkboxes.prop('checked', $(this).is(':checked'));

            let select_all = $('#checkbox-select-all').is(':checked');

            if(select_all) {
                let count = 0;

                $('.to_roles').each(function () {
                    let role_checked = $(this).prop("checked");

                    if (role_checked) {
                        let role_id = $(this).data('id');
                        let role_name = $(this).data('name');

                        receivers['to_roles'][count] = {id: role_id, name: role_name};
                        count++;
                    }
                });
            } else {
                receivers['to_roles'] = [];
            }

            showReceivers();
        });

        $('.checkbox-for').on('click', function () {
            let checked = $(this).prop("checked");
            let option = $(this).prop("id");

            if(checked) {
                let items = '';

                switch (option) {
                    case 'input-roles':
                        let roles = @json($roles);

                        $.each(roles, function (id, role) {
                            items +=
                                '<div class="form-check col-12 col-sm-6 col-lg-4">' +
                                '    <input class="form-check-input to_roles" value="' + role.id + '" onclick="assignRole(' + role.id + ')" id="checkbox-role-' + role.id + '" data-name="' + role.name + '"  data-id="' + role.id + '" type="checkbox" name="inline-radios">' +
                                '    <label class="form-check-label" for="checkbox-role-' + role.id + '">' + role.name + '</label>' +
                                '</div>'
                        });

                        $('#container-select-all').removeClass('d-none');
                        $('#container-select-all').addClass('d-block');
                        $('#label-to-receivers').text('Roles');
                        $('#modal-receivers > .modal-dialog').addClass('modal-lg');
                        $('#modal-body-receivers').addClass('row');
                        break;

                    case 'input-users':
                        let users = @json($users);

                        items += '<select class="form-control to_users" id="select-users" multiple size="15" onchange="assignUser()">';

                        $.each(users, function (id, user) {
                            let user_name = user.first_name + ' ' + user.last_name;
                            items += '<option data-name="' + user_name + '" data-id="' + user.id + '" id="option-user-' + user.id + '">' + user_name + '</option>';
                        });

                        items += '</select>';

                        $('#container-select-all').removeClass('d-block');
                        $('#container-select-all').addClass('d-none');
                        $('#label-to-receivers').text('Usuarios');
                        $('#modal-receivers > .modal-dialog').removeClass('modal-lg');
                        $('#modal-body-receivers').removeClass('row');
                        break;

                    case 'input-models':
                        let models = @json($models);

                        items += '<select class="form-control" id="select-models" multiple size="15" onchange="assignModels()">';

                        $.each(models, function (id, model) {
                            items += '<option data-name="' + model.nick + '" data-id="' + model.id + '" id="option-model-' + model.id + '">' + model.nick + '</option>';
                        });

                        items += '</select>';

                        $('#container-select-all').removeClass('d-block');
                        $('#container-select-all').addClass('d-none');
                        $('#label-to-receivers').text('Modelos');
                        $('#modal-receivers > .modal-dialog').removeClass('modal-lg');
                        $('#modal-body-receivers').removeClass('row');
                        break;
                }

                $('#modal-body-receivers').html(items);
                showReceivers();

                $('#modal-receivers').modal('show');
            } else {
                if (option === "input-roles") { receivers['to_roles'] = []; }
                if (option === "input-users") { receivers['to_users'] = []; }
                if (option === "input-models") { receivers['to_models'] = []; }
                showReceivers();
            }

            //$('#container-receivers').removeClass('d-none');
        });

        // EXTRA FUNCTIONS
        function showReceivers()
        {
            let all_receivers = [];

            // Roles
            $.each(receivers['to_roles'], function (id, role) {
                all_receivers.push(role.name);
                $('#checkbox-role-' + role.id).prop('checked', true);
            });

            // Users
            $.each(receivers['to_users'], function (id, user) {
                all_receivers.push(user.name);
                $('#option-user-' + user.id).prop('selected', true);
            });

            // Models
            $.each(receivers['to_models'], function (id, model) {
                all_receivers.push(model.name);
                $('#option-model-' + model.id).prop('selected', true);
            });

            $('#container-receivers').removeClass('d-none');
            $('#container-receivers-text').html(all_receivers.join(', '));
        }

        function assignRole()
        {
            receivers['to_roles'] = [];

            let count = 0;

            $('.to_roles').each(function () {
                let role_checked = $(this).prop("checked");

                if (role_checked) {
                    let role_id = $(this).data('id');
                    let role_name = $(this).data('name');

                    receivers['to_roles'][count] = {id: role_id, name: role_name};
                    count++;
                }
            });

            showReceivers();
        }

        function assignUser()
        {
            receivers['to_users'] = [];

            let count = 0;

            $("#select-users option:selected").each(function () {
                let user_id = $(this).data('id');
                let user_name = $(this).data('name');

                receivers['to_users'][count] = {id: user_id, name: user_name};
                count++;
            });

            showReceivers();
        }

        function assignModels()
        {
            receivers['to_models'] = [];

            let count = 0;

            $("#select-models option:selected").each(function () {
                let model_id = $(this).data('id');
                let model_name = $(this).data('name');

                receivers['to_models'][count] = {id: model_id, name: model_name};
                count++;
            });

            showReceivers();
        }
    </script>
@endpush
