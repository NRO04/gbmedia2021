@extends("layouts.app")
@section('pageTitle', 'Listado de cuentas')
@section('content')
    <div class="row">
        <!--        <div class="alert alert-primary" role="alert">
                    enviar email a correo real
                </div>-->
        <div class="col-lg-12 col-sm-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-3">
                            <span class="span-title">Listado de Cuentas</span>
                            <span class="text-danger">{{ $owner_name }}</span>
                        </div>
                        <div class="col-lg-3">
                            <div class="row">
                                <label class="col-lg-4">Estado</label>
                                <select class="col-lg-7 form-control form-control-sm" name="status" id="status" onchange="changeSelector()">
                                    <option value="0"></option>
                                    @foreach($status as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row">
                                <label class="col-lg-4">Página</label>
                                <select class="col-lg-7 form-control form-control-sm" name="page_select" id="page_select" onchange="changeSelector()">
                                    <option value="0"></option>
                                    @foreach($pages as $page)
                                        <option value="{{ $page->id }}">{{ $page->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @can('satellite-account-create')
                            <div class="col-lg-3">
                                <a href="{{ route('satellite.create_account') }}" target="_blank" type="button"
                                   class="btn btn-m btn-success float-right btn-sm">
                                    <i class="fa fa-plus"></i> Crear</a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-striped" id="acounts-table" style="width:100%">
                        <thead>
                        <tr>
                            <th>Propietario</th>
                            <th>Nick/Página</th>
                            <th>Nombre</th>
                            <th>Accesos</th>
                            <th>Parejas</th>
                            <th>Ultimo Cambio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

    </div>

    <!-- Modal -->
    <div id="modal-update" class="modal fade" role="dialog" style="overflow-y: scroll;">
        <div class="modal-dialog modal-dark modal-lg" id="modal-update-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="modal-title">Modificar Cuenta Satélite</h4>
                                <h5 id="title_vetada" class="text-dark font-weight-bold d-none">Esta cuenta ha sido vetada</h5>
                            </div>
                            <div class="col-lg-6">
                                @can('satellite-account-disable')
                                    <button class="btn btn-sm btn-success float-right mx-1 d-none" id="btn_activating"><i class="fa fa-check"></i> Activar
                                        Cuenta
                                    </button>
                                @endcan
                                <button class="btn btn-sm btn-success float-right mx-1" id="btn_send_email"><i class="fas fa-envelope"></i> Reenviar
                                    Email
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <ul class="list-unstyled">
                                <li><i class="fa fa-info-circle text-info"></i> Los campos marcados en <span style="color: rgb(245 90 0)">NARANJA
                                </span> son los requeridos para enviar el correo.
                                </li>
                                <li><i class="fa fa-info-circle text-info"></i> Si alguna de las opciones requeridas está vacía el correo NO se le
                                    enviará al propietario de la cuenta.
                                </li>
                            </ul>
                        </div>

                        <div class="col-lg-12" id="div_info_update">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <form action="{{ route('satellite.update_account')}}" method="post" id="form-update-account"
                                          enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="account_id" id="account_id">
                                        <div class="form-group row">
                                            <div class="form-group col-md-6">
                                                <label for="nick">Nick</label>
                                                <input type="text" class="form-control" name="nick" id="nick" placeholder="KerryJones">
                                            </div>

                                            <input type="hidden" name="owner_id" id="owner_id">
                                            <div class="form-group col-md-6">
                                                <label for="owner_id">Propietario</label>
                                                <input type="text" class="form-control" id="owner_placeholder" name="owner_placeholder"
                                                       placeholder="Grupo Bedoya" data-target='#modal-select-owner' data-toggle='modal'
                                                       onfocus="this.blur()" style="cursor: pointer">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="first_name">Nombre</label>
                                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="Carmen">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="second_name">Segundo Nombre</label>
                                                <input type="text" class="form-control" name="second_name" id="second_name" placeholder="Isabelle">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="last_name">Primer Apellido</label>
                                                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Martinez">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="second_last_name">Segundo Apellido</label>
                                                <input type="text" class="form-control" name="second_last_name" id="second_last_name"
                                                       placeholder="Cepeda">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="birth_date">Fecha Nacimiento</label>
                                                <input type="date" class="form-control" name="birth_date" id="birth_date">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Página</label>
                                                <select class="form-control" id="page_id" name="page_id" onchange="pageVerification()">
                                                    <option value>Seleccione una página...</option>
                                                    @foreach ($pages as $page)
                                                        <option value="{{ $page->id}}">{{ $page->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="access" id="label-access">Email</label>
                                                <input type="text" class="form-control" name="access" id="access" placeholder="carmen@gmail.com">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="password">Clave</label>
                                                <input type="text" class="form-control" name="password" id="password" placeholder="carmencita">
                                            </div>

                                            <div class="form-group col-md-12 d-none" id="div-live-id">
                                                <label for="live_id">Live ID</label>
                                                <input type="text" class="form-control" name="live_id" id="live_id"
                                                       placeholder="Live:.cid.5cd83b5cc2eae0e4">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Estado</label>
                                                <select class="form-control" name="status_id" id="status_id">
                                                    <option value="0"></option>
                                                    @foreach($status as $s)
                                                        @if($s->id != 5)
                                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                        @else
                                                            @can('satellite-account-disable')
                                                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                            @endcan
                                                        @endif

                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="comment">Comentario</label>
                                                <textarea class="form-control" name="comment" id="comment"></textarea>
                                            </div>

                                            <div class="form-group col-lg-6">
                                                <button id="btn-partner" type="button" class="btn btn-dark btn-sm" data-toggle="modal"
                                                        data-target='#modal-assign-partner'><i class="fa fa-plus"></i> Agregar Pareja
                                                </button>
                                            </div>
                                            <div class="form-group col-lg-6 " id='div_partner'>
                                                <table class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th>Nombre de Pareja</th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="body_table_partner">
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/modal-body-->
                <div class="modal-footer">
                    <div class="col-md-12 row">
                        <div class="col-lg-8">
                            <button id="btn-logs" type="button" class="btn btn-m btn-info float-left btn-sm mx-2"><i class="fas fa-list"></i>
                                Historial
                            </button>
                            <button id="btn-notes" type="button" class="btn btn-m btn-primary float-left btn-sm mx-2"><i
                                        class="fas fa-search-plus"></i> Notas
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button id="btn-update" type="button" class="btn btn-m btn-success float-right btn-sm"><i class="fa fa-edit"></i>
                                Modificar
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-select-owner" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dark modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Seleccionar Propietario</h4>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-lg-8 form-group">
                            <label>Email del Propietario</label>
                            <input type="text" class="form-control" name="search-email" id="search-email" placeholder="grupobedoya@gmail.com">
                        </div>

                        <div class="col-lg-12" id="body-select-owner">

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-assign-partner" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dark">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Asignar Pareja</h4>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-lg-8 form-group">
                            <label>Nombre de la Pareja</label>
                            <input type="text" class="form-control" name="partner" id="partner" placeholder="Liliam Cepeda Ramirez">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success btn-sm" onclick="assignPartner()"><i class="fa fa-plus"></i>Asignar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-account-logs" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dark modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Historial de la Cuenta</h4>
                </div>
                <div class="modal-body" id="div-body-account-logs">

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-account-notes" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dark modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Notas de la Cuenta</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="div-body-account-notes">

                        </div>
                        <div class="col-md-12 mt-2">
                            <textarea class="form-control" name="note" id="note"></textarea>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button class="btn btn-success btn-sm float-right" id="btn-create-note"><i class="fa fa-check"></i> Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-payment-account" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dark modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Resumen estadístico de la Cuenta</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12" id="body-payment-account">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>

        partner = [];

        function tablePartner() {
            result = "";
            if (partner.length > 0) {
                for (var i = 0; i < partner.length; i++) {

                    trash = "<i class='fa fa-trash text-danger' onclick='deleteRow(" + i + ")'></i>";
                    class_td = "";
                    if (partner[i]['deleted'] == 1) {
                        trash = "Se eliminará";
                        class_td = "bg-danger";
                    }
                    result = result + "<tr id='tr-" + i + "' class='" + class_td + "'>";
                    result = result + "<td>" + partner[i]['name'] + "</td>";
                    result = result + "<td>" + trash + "</td>";
                    result = result + "</tr>";

                }
                $("#body_table_partner").html(result);
            }
        }

        function deleteRow(id) {
            if ($("#status_id").val() != 5) {
                partner[id]['deleted'] = 1;
                tablePartner();
            }
        }

        function assignPartner() {
            $("#modal-assign-partner").modal("hide");
            partner.push({
                id: 0,
                name: $("#partner").val(),
                deleted: 0,
            });
            tablePartner();
        }

        $(document).ready(function () {

            collapseMenu();
            status = $("#status").val();
            page_select = $("#page_select").val();
            owner_id = @json($owner_id);
            Mytable = $("#acounts-table").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                "lengthMenu": [[50, 100, 300, 400], [50, 100, 300, 400]],
                "language": {
                    url: '{{ asset('DataTables/Spanish.json') }}',
                },
                ajax: {
                    url: "{{ route('satellite.get_accounts') }}",
                    dataSrc: "data",
                    type: "GET",
                    dataType: 'json',
                    data: function (data) {
                        data.status = status;
                        data.page_select = page_select;
                        data.owner_id = owner_id;
                    },
                },
                columns: [
                    {data: "owner"},
                    {data: "nick"},
                    {data: "name"},
                    {data: "access"},
                    {data: "partner"},
                    {data: "modified"},
                    {data: "status"},
                    {data: "actions"},

                ],
                columnDefs: [{targets: [0], orderable: false,}],
                fnDrawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        });

        function changeSelector() {
            status = $("#status").val();
            page_select = $("#page_select").val();
            Mytable.ajax.reload();
        }

        $("#search-email").on("keyup", function () {
            search_email = $(this).val();
            $.ajax({
                url: '{{ route('satellite.search_owner') }}',
                type: 'GET',
                data: {'search_email': search_email},
            })
                .done(function (res) {
                    $("#body-select-owner").html(res);
                });
        })

        function OwnerSelected(array) {
            $("#owner_id").val(array.id);
            $("#owner_placeholder").val(array.owner);
            $("#modal-select-owner").modal("toggle");
        }

        function modifyAccount(id) {
            $("#modal-update").modal("show");
            $.ajax({
                url: '{{ route('satellite.edit_account') }}',
                type: 'GET',
                dataType: 'json',
                data: {'id': id},
            })
                .done(function (res) {
                    $("#account_id").val(res.id);
                    $("#nick").val(res.nick);
                    $("#owner_id").val(res.owner_id);
                    $("#owner_placeholder").val(res.owner);
                    $("#first_name").val(res.first_name);
                    $("#second_name").val(res.second_name);
                    $("#last_name").val(res.last_name);
                    $("#second_last_name").val(res.second_last_name);
                    $("#birth_date").val(res.birth_date);
                    $("#page_id").val(res.page_id);
                    $("#access").val(res.access);
                    $("#password").val(res.password);
                    $("#live_id").val(res.live_id);
                    $("#status_id").val(res.status_id);
                    $("#comment").val(res.comment);
                    console.log(res.partners);
                    if (res.partners.length > 0) {
                        partner = res.partners;
                        tablePartner();
                    } else {
                        partner = [];
                        $("#body_table_partner").html("");
                    }
                    if (res.status_id == 5) {
                        $("#modal-update-dialog").removeClass("modal-dark");
                        $("#modal-update-dialog").addClass("modal-danger");
                        $("#title_vetada").removeClass("d-none");
                        $("#btn_activating").removeClass("d-none");
                        $("#btn_send_email").addClass("d-none");
                        $("#btn-update").addClass("d-none");
                        $("#div_info_update :input").attr("disabled", true);
                    } else {
                        $("#modal-update-dialog").addClass("modal-dark");
                        $("#modal-update-dialog").removeClass("modal-danger");
                        $("#title_vetada").addClass("d-none");
                        $("#btn_activating").addClass("d-none");
                        $("#btn_send_email").removeClass("d-none");
                        $("#btn-update").removeClass("d-none");
                        $("#div_info_update :input").attr("disabled", false);
                    }

                    pageVerification();
                });
        }

        function statisticSummary(id) {
            $.ajax({
                url: '{{ route('satellite.statistic_summary') }}',
                type: 'GET',
                data: {'id': id},
            })
                .done(function (res) {
                    console.log(res);
                    $("#body-payment-account").html(res);
                });
        }

        function pageVerification() {

            pages = @json($pages);
            if ($("#page_id").val() != 17) {
                $("#div-live-id").addClass('d-none');
                $("#live_id").val('');
            } else {
                $("#div-live-id").removeClass('d-none');
            }

            if ($("#page_id").val() != 9) {
                $("#label-access").html('Email');
            } else {
                $("#label-access").html('Enlace XLoveCam');
            }

            page = (pages[$("#page_id").val() - 1]);

            if (page.nick == 1) {
                $("#nick").css("border-color", "rgb(245 90 0)");
            } else {
                $("#nick").css("border-color", "rgba(255, 255, 255, 0.15)");
            }

            if (page.full_name == 1) {
                $("#first_name").css("border-color", "rgb(245 90 0)");
                $("#last_name").css("border-color", "rgb(245 90 0)");
            } else {
                $("#first_name").css("border-color", "rgba(255, 255, 255, 0.15)");
                $("#last_name").css("border-color", "rgba(255, 255, 255, 0.15)");
            }

            if (page.access == 1) {
                $("#access").css("border-color", "rgb(245 90 0)");
            } else {
                $("#access").css("border-color", "rgba(255, 255, 255, 0.15)");
            }

            if (page.password == 1) {
                $("#password").css("border-color", "rgb(245 90 0)");
            } else {
                $("#password").css("border-color", "rgba(255, 255, 255, 0.15)");
            }
        }

        $("#btn-update").on("click", function () {
            /*$("#btn-update").prop('disabled' , true);*/
            ResetValidations();

            formData = new FormData(document.getElementById('form-update-account'));
            console.log(partner);
            formData.append('partner', JSON.stringify(partner));
            formData.append('_token', '{{ csrf_token() }}');
            $.ajax({
                url: '{{ route('satellite.update_account')}}',
                type: 'POST',
                processData: false,
                contentType: false,
                data: formData,
            })
                .done(function (res) {
                    if (res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Se han modificado los valores exitosamente",
                        });
                        Mytable.draw();
                        modifyAccount($("#account_id").val());
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error, comuniquese con el ADMIN",
                        });

                    }
                    $("#btn-update").prop('disabled', false);

                })
                .fail(function (res) {
                    CallBackErrors(res);
                    Toast.fire({
                        icon: "error",
                        title: "Verifique la informacion de los campos",
                    });
                    $("#btn-update").prop('disabled', false);
                });
        });

        $("#btn-logs").on("click", function () {
            $("#modal-account-logs").modal("show");
            account_id = $("#account_id").val();
            $.ajax({
                url: '{{ route('satellite.get_logs') }}',
                type: 'GET',
                data: {'account_id': account_id},
            })
                .done(function (res) {
                    $("#div-body-account-logs").html(res);
                    $("#table-logs").DataTable({
                        "lengthMenu": [[100, 200, 300, 400], [100, 200, 300, 400]],
                        "ordering": false,
                    });
                });
        })

        $("#btn_activating").on("click", function () {
            account_id = $("#account_id").val();
            $.ajax({
                url: '{{ route('satellite.activating_account') }}',
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'account_id': account_id},
            })
                .done(function (res) {
                    modifyAccount(account_id);
                });
        })

        $("#btn_send_email").on("click", function () {
            account_id = $("#account_id").val();
            $.ajax({
                url: '{{ route('satellite.send_email_created_account') }}',
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'account_id': account_id},
            })
                .done(function (res) {
                    if (res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Se ha enviado el email exitosamente",
                        });
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error, comuniquese con el ADMIN",
                        });

                    }
                });
        })

        function accountNotes() {
            $("#note").val("");
            account_id = $("#account_id").val();
            $.ajax({
                url: '{{ route('satellite.get_notes') }}',
                type: 'GET',
                data: {'account_id': account_id},
            })
                .done(function (res) {
                    $("#div-body-account-notes").html(res);
                    /*$("#table-notes").DataTable({
                        "lengthMenu": [[100, 200, 300, 400], [100, 200, 300, 400]],
                        "ordering": false,
                    });*/
                });
        }

        $("#btn-notes").on("click", function () {
            $("#modal-account-notes").modal("show");
            accountNotes();
        })

        $("#btn-create-note").on("click", function () {

            $("#btn-create-note").prop('disabled', true);
            account_id = $("#account_id").val();
            note = $("#note").val();

            $.ajax({
                url: '{{ route('satellite.store_note') }}',
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'account_id': account_id, 'note': note},
            })
                .done(function (res) {
                    if (res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Se ha creado la nota exitosamente",
                        });
                        accountNotes();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error, comuniquese con el ADMIN",
                        });

                    }
                    $("#btn-create-note").prop('disabled', false);

                })
                .fail(function (res) {
                    CallBackErrors(res);
                    Toast.fire({
                        icon: "error",
                        title: "Verifique la informacion de los campos",
                    });
                    $("#btn-create-note").prop('disabled', false);
                });

        })
    </script>
@endpush
