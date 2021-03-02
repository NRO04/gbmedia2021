@extends("layouts.app")
@section('content')
@section('pageTitle', 'Crear cuenta')

<div class="row">
	<div class="card col-lg-12 table-responsive">
		<div class="card-header">
			<div class="row">
				<div class="col-lg-12">
				    <span class="span-title">Crear Cuentas Satélite</span>
                    <br>
                    <small class="text-muted text-bold">Ingrese el número de documento y haga click fuera del campo para verificar</small>
				</div>
         </div>
		</div>

		<div class="card-body">
			<div class="row">
				<div class="col-lg-12 alert">
                    <ul class="list-unstyled">
                        <li> <i class="fa fa-info-circle text-info"></i> Seleccione la página para cargar los campos requeridos para el envío del correo de creación.</li>
                        <li><i class="fa fa-info-circle text-info"></i> Los campos marcados en <span style="color: rgb(245 90 0)">NARANJA</span> son los requeridos para enviar el correo de creación de la cuenta al propietario.</li>
                        <li> <i class="fa fa-info-circle text-info"></i> Si alguna de las opciones requeridas está vacía al momento de crear la cuenta, el correo NO se le enviará al propietario de la cuenta.</li>
                    </ul>
                </div>
                <div class="col-lg-10">
            		<div class="card border-secondary">
            			<div class="card-body">
            			<form action="{{ route('satellite.store_account')}}" method="post" id="form-create-account" enctype="multipart/form-data">
							@csrf
            				<input type="hidden" name="satellite_user_id" id="satellite_user_id">
	                        <div class="form-group row">

                                <div class="col-md-12">
                                    <input type="radio" name="account_type" value="with_user" checked>
                                    <label for="owner">Cuenta con Usuario</label>
                                </div>

                                <div class="col-md-12 mb-2">
                                    <input type="radio" name="account_type" value="without_user" >
                                    <label for="owner"> Esta es una cuenta creada por el estudio, no contamos con sus documentos</label>
                                </div>

                                <div class="form-row col-lg-12" id="div-with-user">
                                    <div class="col-lg-6 pr-2">
                                        <label for="owner">Tipo Documento</label>
                                        <select class="form-control" id="document_type" name="document_type">
                                            <option value="">Seleccione el tipo de documento...</option>
                                            @foreach ($documents as $document)
                                                <option value="{{ $document->id}}">{{ $document->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-6 pl-1">
                                        <label for="document_number">Número de Documento</label>

                                        <input type="text" class="form-control" name="document_number" id="document_number" placeholder="11243254785" onfocusout="existsUser()">
                                        <small class="text-muted">Ingrese el número de documento y haga click fuera del campo para verificar</small>
                                    </div>

                                    <div class="col-md-12 mt-2 d-none" id="div-country">
                                        <div class="form-group row">
                                            <div class="col-md-6 pr-2">
                                                <label for="country_id">Pais de Origen</label>
                                                <select class="form-control" id="country_id" name="country_id">
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country->id}}">{{ $country->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-2 d-none" id="div_new_user">
                                        <p class="text-info">Para crear un nuevo usuario, haga click <a href="{{ route('satellite.create_user') }}" target="_blank">Aquí</a> </p>
                                    </div>

                                </div>
                                <!--/div-cuenta con usuario-->

                            </div>

                            <div class="form-row d-none" id="div-info-account">
                                <div class="form-group col-md-6">
                                    <label for="nick">Nick</label>
                                    <input type="text" class="form-control" name="nick" id="nick" placeholder="KerryJones">
                                </div>
                                <input type="hidden" name="owner_id" id="owner_id">
                                <div class="form-group col-md-6">
                                    <label for="owner_id">Propietario</label>
                                    <input type="text" class="form-control" id="owner_placeholder" name="owner_placeholder" placeholder="Grupo Bedoya" data-target='#modal-select-owner' data-toggle='modal' onfocus="this.blur()" style="cursor: pointer">
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
                                    <input type="text" class="form-control" name="second_last_name" id="second_last_name" placeholder="Cepeda">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="birth_date">Fecha Nacimiento</label>
                                    <input type="date" class="form-control" name="birth_date" id="birth_date">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="page">Página</label>
                                    <select class="form-control" id="page" name="page">
                                        <option value>Seleccione una página...</option>
                                        @foreach ($pages as $page)
                                            <option value="{{ $page->id}}">{{ $page->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6 d-none" id="div-live-id">
                                    <label for="live_id">Live ID</label>
                                    <input type="text" class="form-control" name="live_id" id="live_id" placeholder="Live:.cid.5cd83b5cc2eae0e4">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="access" id="label-access">Email</label>
                                    <input type="text" class="form-control" name="access" id="access" placeholder="carmen@gmail.com">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="password">Clave</label>
                                    <input type="text" class="form-control" name="password" id="password" placeholder="carmencita">
                                </div>
                                <div class="form-group col-lg-6">
                                    <button id="btn-partner" type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target='#modal-assign-partner'><i class="fa fa-plus"></i> Agregar Pareja</button>
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
                        <div>
                        	<button id="btn-create" type="button" class="btn btn-m btn-success float-right btn-sm d-none"><i class="fa fa-plus"></i> Crear</button>
                        </div>
                		</div>
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

@endsection

@push('scripts')
<script>

partner = [];

function tablePartner()
{
    result = "";
    for (var i = 0; i < partner.length; i++) {
        result = result + "<tr id='tr-"+i+"'>";
        result = result + "<td>"+ partner[i] +"</td>";
        result = result + "<td><i class='fa fa-trash text-danger' onclick='deleteRow("+i+")'></i></td>";
        result = result + "</tr>";
    }
    $("#body_table_partner").html(result);
}

function deleteRow(id)
{
    partner.splice(id, 1);
    tablePartner();
}

function assignPartner()
{
    $("#modal-assign-partner").modal("hide");
    partner[partner.length] = $("#partner").val();
    tablePartner();
}

function OwnerSelected(array)
{
    $("#owner_id").val(array.id);
    $("#owner_placeholder").val(array.owner);
    $("#modal-select-owner").modal("hide");
}

$("#btn-create").on("click", function () {
    $("#btn-create").prop('disabled', true);
    ResetValidations();

    formData = new FormData(document.getElementById('form-create-account'));
    formData.append('partner', partner);
    formData.append('_token', '{{ csrf_token() }}');
    $.ajax({
        url: '{{ route('satellite.store_account')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    }).done(function (res) {
        if (res.success) {
            SwalGB.fire({
                title: "Proceso exitoso",
                text: "Se ha agregado la cuenta exitosamente",
                icon: "success",
                showCancelButton: false,
            }).then(result => {
                let title = '';
                let text = '';
                let icon = '';

                if (res.email_sent) {
                    title = 'Accesos enviados';
                    text = 'Se ha enviado el correo de creación de cuenta al propietario (' + res.owner_email + ')';
                    icon = 'success';

                    SwalGB.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        showCancelButton: false,
                    }).then(done => {
                        location.reload();
                    });
                } else {
                    if (res.email_send_status === 1) { // data with only dots (.)
                        title = "¡Accesos no enviados: Datos de la cuenta con puntos (.)!";
                        text = "No se han enviado los accesos porque se han ingresado solo puntos (.) el nombre o apellido de la persona.";
                        icon = "warning";

                        SwalGB.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: false,
                        }).then(done => {
                            location.reload();
                        });
                    } else if (res.email_send_status === 2) { // No owner email
                        title = '¡Accesos no enviados: Propietario sin correo!';
                        text = 'No se han enviado los accesos porque el propetario no tiene correo principal registrado.';
                        icon = 'warning';

                        SwalGB.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: false,
                        }).then(done => {
                            location.reload();
                        });
                    } else if (res.email_send_status === 3) { // No template
                        title = '¡Accesos no enviados: Página sin plantilla!';
                        text = 'No se han enviado los accesos porque la página seleccionada no tiene una plantilla registrada.';
                        icon = 'warning';

                        SwalGB.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: false,
                        }).then(done => {
                            location.reload();
                        });
                    } else if (res.email_send_status === 4) { // Required files missing
                        title = '¡Accesos no enviados: Campos requeridos faltantes!';
                        text = 'No se han enviado los accesos porque no se ingresaron todos los campos requeridos para la página.';
                        icon = 'warning';

                        SwalGB.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: false,
                        }).then(done => {
                            location.reload();
                        });
                    }
                }
            });
        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
            $("#btn-create").prop('disabled', false);
        }
    })
        .fail(function (res) {
            CallBackErrors(res);
            Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
            $("#btn-create").prop('disabled', false);
        });
});

function existsUser(){
    ResetValidations();
    document_type =  $("#document_type").val();
    document_number =  $("#document_number").val();
    country_id =  $("#country_id").val();
    $.ajax({
        url: '{{ route('satellite.exists_user')}}',
            type: 'GET',
            data: {'document_type' : document_type, 'document_number' : document_number, 'country_id' : country_id},
        })
        .done(function(res) {
            if (res.exists == false) {
                Toast.fire({
                    icon: "error",
                    title: "No existe un usuario con el documento ingresado",
                });
                $("#div-info-account").addClass('d-none');
                $("#satellite_user_id").val("null");
                $("#btn-create").addClass('d-none');
                $("#div_new_user").removeClass('d-none');
            } else {
                $("#div-info-account").removeClass('d-none');
                $("#first_name").val(res.user.first_name);
                $("#second_name").val(res.user.second_name);
                $("#last_name").val(res.user.last_name);
                $("#second_last_name").val(res.user.second_last_name);
                $("#birth_date").val(res.user.birth_date);
                $("#satellite_user_id").val(res.user.id);
                $("#btn-create").removeClass('d-none');
                $("#div_new_user").addClass('d-none');
            }
        })
        .fail(function(res) {
            CallBackErrors(res);
        });
};

$("#document_type").on("change", function(){
    existsUser();
    if (($(this).val() >= 1 && $(this).val() <= 3) || $(this).val() == "")
    {
        $("#div-country").addClass('d-none');
    }
    else
    {
        $("#div-country").removeClass('d-none');
    }
});

$("#page").on("change", function(){

    pages = @json($pages);

    if ($(this).val() != 17) {
        $("#div-live-id").addClass('d-none');
        $("#live_id").val('');
    }
    else{
        $("#div-live-id").removeClass('d-none');
    }

    if ($(this).val() != 9) {
        $("#label-access").html('Email');
    }
    else{
        $("#label-access").html('Enlace XLoveCam');
    }

    page = (pages[$(this).val() - 1]);

    if (page.nick == 1) {
        $("#nick").css("border-color" , "rgb(245 90 0)");
    }
    else{
        $("#nick").css("border-color" , "rgba(255, 255, 255, 0.15)");
    }

    if (page.full_name == 1){
        $("#first_name").css("border-color" , "rgb(245 90 0)");
        $("#last_name").css("border-color" , "rgb(245 90 0)");
    }
    else{
        $("#first_name").css("border-color" , "rgba(255, 255, 255, 0.15)");
        $("#last_name").css("border-color" , "rgba(255, 255, 255, 0.15)");
    }

    if (page.access == 1) {
        $("#access").css("border-color" , "rgb(245 90 0)");
    }
    else{
        $("#access").css("border-color" , "rgba(255, 255, 255, 0.15)");
    }

    if (page.password == 1) {
        $("#password").css("border-color" , "rgb(245 90 0)");
    }
    else{
        $("#password").css("border-color" , "rgba(255, 255, 255, 0.15)");
    }
});

$("input[name='account_type']").on("click", function(){
    if ($(this).val() == "with_user")
    {
        $("#div-info-account").addClass('d-none');
        $("#div-with-user").removeClass('d-none');
        $("#btn-create").addClass('d-none');
    }
    else
    {
        $("#div-info-account").removeClass('d-none');
        $("#div-with-user").addClass('d-none');
        $("#btn-create").removeClass('d-none');
    }
});

$("#search-email").on("keyup", function(){
    search_email = $(this).val();
    $.ajax({
        url: '{{ route('satellite.search_owner') }}',
        type: 'GET',
        data: {'search_email': search_email},
    })
    .done(function(res) {
        $("#body-select-owner").html(res);
    });
})

</script>
@endpush
