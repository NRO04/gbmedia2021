@extends('layouts.app')

@section('pageTitle', 'Alarmas Finalizadas')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Alarmas</li>
    <li class="breadcrumb-item active">Finalizadas</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Alarmas Finalizadas</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-md-right">
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
                    @can('alarms-created')
                        <a class="btn btn-warning btn-sm" href="{{ route('alarms.list') }}">
                            <i class="fa fa-list"></i> Creadas
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
                            <th>Finalizada Por</th>
                            <th>Finalizada el</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
                                serverSide: true,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('alarms.get_finished_alarms') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    { data: "name" },
                                    { data: "cycle" },
                                    { data: "finished_by" },
                                    { data: "finished_date" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [],
                                        orderable: false,
                                    },
                                    {
                                        targets: [],
                                        searchable: false,
                                    },
                                ],
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
                let alarm = res;
                let roles = alarm.roles;
                let users = alarm.users;
                let showing_roles = alarm.showing_roles;
                let showing_users = alarm.showing_users;

                let roles_ids = [];
                let users_ids = [];

                $('#name').val(alarm.name);
                $('#date').val(alarm.showing_date);
                $('#timer').val(alarm.cycle_count);
                $('#cycle').val(alarm.cycle);
                $('#container-receivers-text').append(showing_roles);
                $('#container-receivers-text').append(showing_users);

                if(alarm.is_fixed_date) {
                    $('#fixed-date').prop('checked', true);
                } else {
                    $('#normal-cycle-date').prop('checked', true);
                }

                $.each(roles, function (id, role) {
                    roles_ids.push(role.id);
                });

                console.log(roles_ids);
                $('.to_roles').val(roles_ids);

                $.each(users, function (id, user) {
                    users_ids.push(user.id);
                });

                $("#modal-edit").modal("toggle");
            })
            .fail(function (res) {
                CallBackErrors(res);
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

        $("#btn-create").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = $("#form-create").serialize();

            $.ajax({
                url: "{{ route('alarms.create') }}",
                type: "POST",
                data: form_data,
            })
            .done(function (res) {
                // alarm created successfully
                location.reload();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-create').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        $("#btn-open-modal").on('click', function(){
            ResetValidations();
            ResetModalForm("#form-create");
            $('#container-users').css('display', 'none');
            $('#container-roles').css('display', 'none');
        });
    </script>
@endpush
