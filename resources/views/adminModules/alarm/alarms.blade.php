@extends('layouts.app')

@section('pageTitle', 'Alarmas')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Alarmas</li>
    <li class="breadcrumb-item active">Pendientes</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Alarmas</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-md-right">
                    @can('alarms-create')
                        <a class="btn btn-success btn-sm" data-target="#modal-create" data-toggle="modal" id="btn-open-modal">
                            <i class="fa fa-plus"></i> Crear
                        </a>
                    @endcan
                    @can('alarms-created')
                        <a class="btn btn-info btn-sm" href="{{ route('alarms.list') }}">
                            <i class="fa fa-list"></i> Creadas
                        </a>
                    @endcan
                    @can('alarms-finished')
                        <a class="btn btn-warning btn-sm" href="{{ route('alarms.finished') }}">
                            <i class="fa fa-check"></i> Finalizadas
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body px-0">
                <div class="col-">
                    <div class="nav-tabs-boxed">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link nav-link-gb active" data-toggle="tab" href="#tab-pending" role="tab" aria-controls="tab-pending" aria-selected="true" id="tab-pending-tab">
                                    Pendientes
                                    @if($alarms->count() > 0)
                                        <span class="badge badge-pill badge-success">{{ $alarms->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            @can('alarms-contract-renovation')
                            <li class="nav-item">
                                <a class="nav-link nav-link-gb" id="pills-contracts-renewals-tab" data-toggle="pill" href="#tab-signing-contract" role="tab" aria-controls="tab-signing-contract" aria-selected="true">
                                    Renovación de Contratos
                                    @if($renewals->count() > 0)
                                        <span class="badge badge-pill badge-success">{{ $renewals->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            @endcan
                            @can('alarms-document-expiration')
                            <li class="nav-item">
                                <a class="nav-link nav-link-gb" id="pills-documents-expiry-tab" data-toggle="pill" href="#tab-documents-expiry" role="tab" aria-controls="tab-documents-expiry" aria-selected="false">
                                    Expiración de Documentos
                                    @if($expiry_documents->count() > 0)
                                        <span class="badge badge-pill badge-success">{{ $expiry_documents->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            @endcan
                            @can('alarms-boutique-products')
                            <li class="nav-item">
                                <a class="nav-link nav-link-gb" id="pills-products-tab" data-toggle="pill" href="#tab-products" role="tab" aria-controls="tab-products" aria-selected="false">
                                    Boutique
                                    @if(collect($products['stocks_alarms'])->count() > 0 || collect($products['locations_alarms'])->count() > 0)
                                        <span class="badge badge-pill badge-success">{{ collect($products['stocks_alarms'])->count() + collect($products['locations_alarms'])->count() }}</span>
                                    @endif
                                </a>
                            </li>
                            @endcan
                        </ul>
                        <div class="tab-content tab-content-gb">
                            <div class="tab-pane fade active show" id="tab-pending" role="tabpanel">
                                @if(count($alarms) > 0)
                                    <table class="table table-hover table-responsive-sm mt-4">
                                    <thead>
                                        <tr>
                                            <td>Alarma</td>
                                            <td class="d-none d-md-table-cell">Cada</td>
                                            <td class="d-none d-md-table-cell text-center">Roles</td>
                                            <td class="d-none d-md-table-cell text-center">Usuarios</td>
                                            <td class="d-none d-md-table-cell">Desde</td>
                                            <td class="text-center">Finalizar</td>
                                        </tr>
                                    </thead>
                                    @foreach ($alarms AS $alarm)
                                        <tr>
                                            <td>{{ $alarm->name }}</td>
                                            <td class="d-none d-md-table-cell">{{ $alarm->cycle_count }} @if($alarm->cycle == 'weekly') semanas @elseif($alarm->cycle == 'monthly') meses @elseif($alarm->cycle == 'yearly') años @endif</td>
                                            <td class="d-none d-md-table-cell text-center">
                                                <span class="badge badge-info" title="" data-toggle="tooltip" data-html="true" data-original-title="{{ $alarm->roles_receivers->implode(', ') }}">
                                                     @if($alarm->roles_receivers_count > 0) {{ $alarm->roles_receivers_count }} @endif
                                                </span>
                                            </td>
                                            <td class="d-none d-md-table-cell text-center">
                                                <span class="badge badge-info" title="" data-toggle="tooltip" data-html="true" data-original-title="{{ $alarm->users_receivers->implode(', ') }}">
                                                     @if($alarm->users_receivers_count > 0) {{ $alarm->users_receivers_count }} @endif
                                                </span>
                                            </td>
                                            <td class="d-none d-md-table-cell">{{ Carbon\Carbon::parse($alarm->showing_date)->format('d / M / Y') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-success btn-sm finish-alarm" data-id="{{ $alarm->id }}">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                                @else
                                    <div class="my-4">No hay registros</div>
                                @endif
                            </div>
                            @can('alarms-contract-renovation')
                            <div class="tab-pane fade" id="tab-signing-contract" role="tabpanel">
                                @if($renewals->count() > 0)
                                    <table class="table table-hover table-striped table-responsive-sm mt-4">
                                        <thead>
                                            <tr>
                                                <td>Nombre</td>
                                                <td>Firma de Contrato</td>
                                                <td>Fecha de Vencimiento</td>
                                            </tr>
                                        </thead>
                                        @foreach ($renewals AS $renewal)
                                            <tr>
                                                <td>{{ $renewal->roleUserShortName() }}</td>
                                                <td>{{ Carbon\Carbon::parse($renewal->contract_date)->format('d / M / Y') }}</td>
                                                <td>{{ Carbon\Carbon::parse($renewal->contract_singning_date)->format('d / M / Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <div class="my-4">No hay registros</div>
                                @endif
                            </div>
                            @endcan
                            @can('alarms-document-expiration')
                            <div class="tab-pane fade" id="tab-documents-expiry" role="tabpanel">
                                @if($expiry_documents->count() > 0)
                                    <table class="table table-hover table-striped table-responsive-sm mt-4">
                                        <thead>
                                            <tr>
                                                <td>Nombre</td>
                                                <td>Rol</td>
                                                <td>Locación</td>
                                                <td>Fecha Expiración</td>
                                                <td>Vencimiento</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expiry_documents AS $document)
                                                <tr>
                                                    <td>{{ $document->user_name }}</td>
                                                    <td>{{ $document->role_name }}</td>
                                                    <td>{{ $document->location_name }}</td>
                                                    <td>{{ $document->expiration_date }}</td>
                                                    <td>{{ $document->expiration_in }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="my-4">No hay registros</div>
                                @endif
                            </div>
                            @endcan
                            @can('alarms-boutique-products')
                            <div class="tab-pane fade" id="tab-products" role="tabpanel">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link nav-link-gb active" id="tab-location-inventories-tab" data-toggle="pill" href="#tab-general-inventories" role="tab" aria-controls="tab-general-inventories" aria-selected="false">
                                            Stock
                                            @if(collect($products['stocks_alarms'])->count() > 0)
                                                <span class="badge badge-pill badge-success">{{ collect($products['stocks_alarms'])->count() }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link nav-link-gb" id=tab-location-inventories-tab" data-toggle="pill" href="#tab-location-inventories" role="pill" aria-controls="tab-location-inventories" aria-selected="true">
                                            Inventario en Locaciones
                                            @if(collect($products['locations_alarms'])->count() > 0)
                                                <span class="badge badge-pill badge-success">{{ collect($products['locations_alarms'])->count() }}</span>
                                            @endif
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content tab-content-gb">
                                    <div class="tab-pane fade active show" id="tab-general-inventories" role="tabpanel">
                                        @if(collect($products['stocks_alarms'])->count() > 0)
                                            <table class="table table-hover table-striped table-responsive-sm mt-4">
                                                <thead>
                                                <tr>
                                                    <td>Nombre</td>
                                                    <td>En stock</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($products['stocks_alarms'] AS $product)
                                                    <tr>
                                                        <td>{{ $product->product_name }}</td>
                                                        <td>{{ $product->quantity }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="my-4">No hay registros</div>
                                        @endif
                                    </div>
                                    <div class="tab-pane fade" id="tab-location-inventories" role="tabpanel">
                                        @if(collect($products['locations_alarms'])->count() > 0)
                                            <table class="table table-hover table-striped table-responsive-sm mt-4">
                                                <thead>
                                                    <tr>
                                                        <td>Nombre</td>
                                                        <td>Locación</td>
                                                        <td>En stock</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($products['locations_alarms'] AS $product)
                                                        <tr>
                                                            <td>{{ $product->product_name }}</td>
                                                            <td>{{ $product->location_name }}</td>
                                                            <td>{{ $product->quantity }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="my-4">No hay registros</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('alarms-create')
    <!-- Modal Create -->
    <div aria-hidden="true" aria-labelledby="modalCreateAlarmLabel" class="modal fade overflow-auto" id="modal-create" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Crear Alarma</h4>
                </div>
                <div class="modal-body">
                    <form id="form-create">
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
                        <div id="container-receivers" class="d-none">
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
                                    <input class="form-check-input" id="fixed-date" type="radio" value="fixed-date" name="cycle_type">
                                    <label class="form-check-label mr-1" for="fixed-date">Fecha Fija</label>
                                </div>
                                <div class="form-check form-check-inline mr-1">
                                    <input class="form-check-input" id="normal-cycle-date" type="radio" value="normal-cycle-date" name="cycle_type">
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
        let receivers = {
            'to_roles' : [],
            'to_users' : [],
            'to_models' : [],
        };

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
                                '    <input class="form-check-input to_roles" onclick="assignRole(' + role.id + ')" id="checkbox-role-' + role.id + '" data-name="' + role.name + '"  data-id="' + role.id + '" type="checkbox" name="inline-radios">' +
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

                            items += '<option data-name="' + user_name + '" data-id="' + user.id + '">' + user_name + '</option>';
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
                            items += '<option data-name="' + model.nick + '" data-id="' + model.id + '">' + model.nick + '</option>';
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
                $('#modal-receivers').modal('show');
            } else {
                if (option === "input-roles") { receivers['to_roles'] = []; }
                if (option === "input-users") { receivers['to_users'] = []; }
                if (option === "input-models") { receivers['to_models'] = []; }
                showReceivers();
            }

            //$('#container-receivers').removeClass('d-none');
        });

        // CONTROL ACTIONS
        $('.finish-alarm').on('click', function () {
            let id = $(this).data('id');

            SwalGB.fire({
                title: '¿Está seguro que desea finalizar la alarma?',
                text: 'Asegúrese de haber completado la tarea correctamente antes de finalizar',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Finalizar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('alarms.finish') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        },
                    }).done(function (res) {
                        // alarm finished successfully
                        Toast.fire({
                            icon: "success",
                            title: "Alarma finalizada exitosamente",
                        });

                        setTimeout(function () {
                            location.reload();
                        }, 2500);
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
        });

        $("#btn-create").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            $('#checkbox-select-all').prop('checked', false);

            if (receivers['to_roles'].length == 0 && receivers['to_users'].length == 0 && receivers['to_models'].length == 0)
            {
                Toast.fire({
                    icon: "warning",
                    title: "Debe seleccionar al menos un recipiente",
                    timer: 4000,
                });
                return;
            }

            let all_receivers = JSON.stringify(receivers);
            let form_data = new FormData(document.getElementById('form-create'));
            form_data.append('receivers', all_receivers);

            $.ajax({
                url: "{{ route('alarms.create') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                $('#modal-create').modal('toggle');
                Toast.fire({
                    icon: "success",
                    title: "Agregado exitosamente",
                });

                setTimeout(function () {
                    location.replace("{{ route('alarms.alarms') }}");
                }, 2500);
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-create').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        $("#btn-open-modal").on('click', function() {
            ResetValidations();
            ResetModalForm("#form-create");

            // Reset array receivers
            receivers = {
                'to_roles': [],
                'to_users': [],
                'to_models': [],
            };

            $('#container-receivers').addClass('d-none');
            $('#container-users').css('display', 'none');
            $('#container-roles').css('display', 'none');
        });

        // EXTRA FUNCTIONS
        function showReceivers()
        {
            let all_receivers = [];

            // Roles
            $.each(receivers['to_roles'], function (id, role) {
                all_receivers.push(role.name);
            });

            // Users
            $.each(receivers['to_users'], function (id, user) {
                all_receivers.push(user.name);
            });

            // Models
            $.each(receivers['to_models'], function (id, user) {
                all_receivers.push(user.name);
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

        $(document).ready(function () {
            let url = new URL(window.location.href);

            if (url.searchParams.get('create') != null) {
                $('#modal-create').modal('show');
            }
        });
    </script>
@endpush
