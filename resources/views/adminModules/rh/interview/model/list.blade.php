@extends('layouts.app')
@section('pageTitle', 'Prospectos')
@push('styles')
    <style>
        .btnFile {
            position: relative;
            z-index: 1;
            color: rgb(255, 255, 255);
            display: flex;
            -webkit-box-align: center;
            align-items: center;
            width: max-content;
            box-shadow: rgba(0, 0, 0, 0.4) 2px 2px 5px 0px, rgba(0, 0, 0, 0.3) 2px 2px 5px 0px;
            border-radius: 5px;
        }

        .inputFile input {
            position: absolute;
            top: 0px;
            left: 15px;
            width: 160px;
            height: 36px;
            z-index: 2;
            opacity: 0;
        }

        .inputFile i {
            color: #00a237;
        }
    </style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold">Recursos Humanos</li>
<li class="breadcrumb-item active"><a href="">Prospectos</a></li>
@endsection
@section('content')
<div class="card border-secondary">
  <div class="card-header">
	<div class="row">
		<div class="col-sm">
		<span class="span-title">Listado <span class="badge badge-primary">Modelos</span></span>
		</div>
		<div class="col-sm">
			<select class="form-control form-control-sm" id="changeProspectsView">
				<option value="1">Usuarios</option>
				<option selected>Modelos</option>
				<option value ="3">Modelos Referidos</option>
			</select>
		</div>
		<div class="col-sm">
			<a href="{{ route('rh.interview.storeInterview')}}"><button class="btn btn-success btn-sm float-right mx-1"><i class="fa fa-plus"></i>&nbsp;Entrevista</button></a>
		</div>
	</div>
  </div>
  <div class="card-body">
	<table class="table table-striped table-hover" id="table_interviews_model" style="width: 100%;">
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Fecha Entrevista</th>
                    <th>Referido</th>
                    <th>Acciones</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
  </div>
</div>
<!-- User Modal cite-->
<div id="modal-cite-user" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Citar</h4>
			</div>
			<div class="modal-body">
				<form id="form_cite">
                    @csrf
					<input type="hidden" id="id_model_cita" name="id" value="">
                    <input id="option_user" name="cite" type="hidden" value="">
                </form>
			</div>
			<div class="modal-footer">
				<div class="col-sm">
					<button type="button" class="btn btn-success btn-block" onclick="citeModal(1)"><i class="fas fa-check"></i> Citar</button>
				</div>
				<div class="col-sm">
					<button type="button" class="btn btn-danger btn-block" onclick="citeModal(0)"><i class="fas fa-times"></i> No Citar</button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- User Modal img update-->
<div id="modal-img-update" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Imagenes</h4>
			</div>
			<div class="modal-body">
				<form id="form-interviewer-img" method="POST" enctype="multipart/form-data">
					@csrf
					<input type="hidden" id="id_model" name="id_model">

					<div class="form-group row">
						<div class="col">Nombre</div>
						<div class="col" id='name_update'></div>
					</div>
					<div class="form-group row">
						<div class="col">Foto rostro</div>
						<div class="col">
							<div class="col-sm-7 col-lg-6 inputFile pl-0" id="img_face"></div>
						</div>
					</div>
					<div class="form-group row">
						<div class="col">Foto desnuda de frente</div>
						<div class="col">
							<div class="col-sm-7 col-lg-6 inputFile pl-0" id="img_front"></div>
						</div>
					</div>
					<div class="form-group row">
						<div class="col">Foto desnuda de lado</div>
						<div class="col">
							<div class="col-sm-7 col-lg-6 inputFile pl-0" id="img_side"></div>
						</div>
					</div>
					<div class="form-group row">
						<div class="col">Foto desnuda de espalda</div>
						<div class="col">
							<div class="col-sm-7 col-lg-6 inputFile pl-0" id="img_back"></div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<a onclick="updateInterviewsIMG()" class="btn btn-sm btn-success">Aceptar</a>
			</div>
		</div>
	</div>
</div>

<!-- User Modal convertUser-->
<div id="modal-img-convert-user" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content border-secondary">
			<div class="modal-header">
				<h4 class="modal-title">Convertir a usuario</h4>
			</div>
			<div class="modal-body">

				<form id="form-interviewer-convert-user" method="POST">
					@csrf
					<input id="id_interviewer" name="id_interviewer" type="hidden" value="">
					<input id="setting_role_id" name="setting_role_id" type="hidden" value="">

					<div class="form-group row">
						<label for="colFormLabel" class="col-sm-4 col-form-label">Nombre</label>
						<div class="col-sm-8">
						<span class="badge badge-primary" style="font-size: 12px;" id="user_name_interview"></span>
						</div>
					</div>

					<div class="form-group row">
						<label for="nick" class="col-sm-4 col-form-label">Nick de la modelo</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="nick" name="nick" placeholder="Ejemplo: PrettyGirl">
						</div>
					</div>
					<div class="form-group row">
						<label for="email" class="col-sm-4 col-form-label">Email o Hangouts</label>
						<div class="col-sm-8">
							<input type="email" class="form-control" id="email" name="email" placeholder="Ejemplo:luisavalenciagbmediagroup@gmail.com">
						</div>
					</div>
					<div class="form-group row">
						<label for="setting_location_id" class="col-sm-4 col-form-label">Locación</label>
						<div class="col-sm-8">
							<select id="setting_location_id" name='setting_location_id' class="form-control form-control-sm">
								<option value='' selected="selected">-- Seleccionar locación --</option>
								@foreach($SettingLocation as $location)
									<option value='{{ $location->id }}'>{{ $location->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label for="contract_id" class="col-sm-4 col-form-label">Tipo Contrato</label>
						<div class="col-sm-8">
							<select id="contract_id" name='contract_id' class="form-control form-control-sm">
								<option value='' selected="selected">-- Seleccionar tipo contrato --</option>
								@foreach($GlobalTypeContract as $TypeContract)
									<option value='{{ $TypeContract->id }}'>{{ $TypeContract->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label for="password" class="col-sm-4 col-form-label">Clave</label>
						<div class="col-sm-8">
							<input type="email" class="form-control" id="password" name="password" placeholder="Ejemplo: Xz1Luisa2klValencia">
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<a class="btn btn-sm btn-success" onclick="actionInterviewToUser()"><i class="fas fa-plus"></i> Crear</a>
			</div>
		</div>
	</div>
</div>
@endsection
@push('scripts')
<script>
let table = null;

$(document).ready(function() {
	table = $('#table_interviews_model').DataTable({
		language: {
			url: '{{ asset("DataTables/Spanish.json") }}',
		},
        ordering: false,
		processing: true,
		serverSide: true,
		pageLength: 50,
		destroy: true,
		ajax: {
			method: "GET",
			url   : "{{ route('rh.interview.listInterviewsModel') }}",
            beforeSend: function() {
                $('html,body').animate({
                    scrollTop: $('html').offset().top
                }, 200);

                $('#global-spinner').removeClass('d-none');
            },
            complete: function () {
                $('html,body').animate({
                    scrollTop: $('html').offset().top
                }, 1);

                $('#global-spinner').addClass('d-none');
            }
		},
		columns: [
			{ data: "first_name" },
			{ data: "created_at"},
            { data: "refer"},
            { data: "action"},
		],
		fnDrawCallback: function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
	});
});

function actionInterviewToUser(){

	ResetValidations();
    DisableModalActionButtons();
	formData = new FormData(document.getElementById('form-interviewer-convert-user'));

	$.ajax({
        url: "{{ route('rh.interview.actionInterviewToUser') }}",
        type: "POST",
		processData: false,
		contentType: false,
		headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
        data: formData,
    }).done(function (res) {
		table.ajax.reload();
		$('#modal-img-convert-user').modal('hide');
		Toast.fire({
            icon: "success",
            title: "Se ha convertido el prospecto a usuarion exitosamente"
        });

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(res);
    });
}

function convertUserModal(id)
{
	ResetValidations();
	$('#modal-img-convert-user').modal('show');
	$.ajax({
        url: "{{ route('rh.interview.getInterviewFullName') }}",
        type: "POST",
		headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
        data: { id: id },
    }).done(function (res) {

		$('#id_interviewer').val(id);
		$('#user_name_interview').html(res.full_name);
		$('#setting_role_id').html(res.setting_role_id);

		$('#nick').val('');
		$('#email').val('');
		$('#setting_location_id').val('');
		$('#contract_id').val('');
		$('#password').val('');

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(errors);
    });
}

$('#changeProspectsView').on('change', function (e) {
    let showValue = $(this).val();
    if(parseInt(showValue) === 1) {
        window.location.replace("../other/list");
    }
    if(parseInt(showValue) === 3) {
        window.location.replace("../../referred");
    }
});

function updateInterviewsIMG()
{
	formData = new FormData(document.getElementById('form-interviewer-img'));
	$.ajax({
        url: "{{ route('rh.interviewImg.update') }}",
        type: "POST",
		processData: false,
		contentType: false,
		headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
        data: formData,
    }).done(function (res) {
		$('#modal-img-update').modal('hide');
        Toast.fire({
            icon: "success",
            title: "Imágenes subidas correctamente"
        });

		table.ajax.reload();

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(errors);
    });

}

function senInterviewsIMG()
{
	ResetValidations();
    DisableModalActionButtons();

	formData = new FormData(document.getElementById('form-interviewer-img-create'));
	$.ajax({
        url: "{{ route('rh.interviewImg.create') }}",
        type: "POST",
		processData: false,
		contentType: false,
		headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
        data: formData,
    }).done(function (res) {
		$('#modal-img-create').modal('hide');
        Toast.fire({
            icon: "success",
            title: "La entrevista fue creada exitosamente"
        });

		table.ajax.reload();
		$('#modal-img-convert-user').modal('hide');

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(res);
    });
}

function ModalCite(id)
{
	$('#modal-cite-user').modal('show');
	document.getElementById("id_model").value = id;
	document.getElementById("id_model_cita").value = id;
}

function ManageIMG(id)
{
	$.ajax({
        url: "{{ route('rh.interview.getInterviewimg') }}",
		type: "POST",
		headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	},
        data: { id: id },
    }).done(function (res) {

		let id = res['id']
		document.getElementById("id_model").value = id;

		document.getElementById("name_update").innerHTML = "<a class='badge bg-primary'>"+res['full_name']+"</a>";

		if((res['face'] == '') || (res['face'] == null))
		{
			document.getElementById("img_face").innerHTML = drawFieldEdit('face');
		}
		else
		{
			document.getElementById("img_face").innerHTML = "<i class='fas fa-check'></i> Existe";
		}

		if((res['front'] == '') || (res['front'] == null))
		{
			document.getElementById("img_front").innerHTML = drawFieldEdit('front');
		}
		else
		{
			document.getElementById("img_front").innerHTML = "<i class='fas fa-check'></i> Existe";
		}

		if((res['side'] == '') || (res['side'] == null))
		{
			document.getElementById("img_side").innerHTML = drawFieldEdit('side');
		}
		else
		{
			document.getElementById("img_side").innerHTML = "<i class='fas fa-check'></i> Existe";
		}

		if((res['back'] == '') || (res['back'] == null))
		{
			document.getElementById("img_back").innerHTML = drawFieldEdit('back');
		}
		else
		{
			document.getElementById("img_back").innerHTML = "<i class='fas fa-check'></i> Existe";
		}

		$('#modal-img-update').modal('show');

		document.getElementById("id_create").value = id;
		document.getElementById("name_create").innerHTML = "<a class='badge bg-primary'>"+res['full_name']+"</a>";




    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(errors);
	});
}

function drawFieldEdit(name)
{
	let field = "<span class='btnFile btn-dark btn-sm'>";
	field = field+"<span id='spanFile'>Seleccionar Archivos</span>";
	field = field+"<i class='fa fa-upload pl-1 pr-1' aria-hidden='true'></i>";
	field = field+"</span>";
	field = field+"<input id="+name+" name="+name+" type='file' multiple=''>";

	return field;
}

function confirmMessage()
{
	let field = ""
}

function citeModal(cite)
{
    document.getElementById("option_user").value = cite;
	let form_cite = $("#form_cite").serialize();
	$.ajax({
        url: "{{ route('rh.interview.updateCite') }}",
        type: "POST",
        data: form_cite,
    }).done(function (res) {

		$('#modal-cite-user').modal('hide');

		table.ajax.reload();

        Toast.fire({
            icon: "success",
            title: "El prospecto fue citado exitosamente"
        });

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(errors);
    });
}

function deleteInterview(id)
{
	Swal.fire({
		title: 'Prospecto',
		text: "Desea eliminar este prospecto ?",
		icon: 'warning',
		showCancelButton: true,
		cancelButtonColor: '#d33',
		confirmButtonText: 'Eliminar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {

		var data =
		{
			"_token": "{{ csrf_token() }}",
			"id":id,
		};

		if (result.value) {
			$.ajax({
				url: "{{ route('rh.interview.deleteInterview') }}",
				type: "POST",
				data: data,
			}).done(function (res) {
				table.ajax.reload();
				Toast.fire({
					icon: "success",
					title: "El prospecto se ha borrado con exito"
				});
				table.ajax.reload();
			}).fail(function (res, textStatus, xhr) {
				let errors = res.responseJSON.errors;
				CallBackErrors(errors);
			});
		}
	});

}

function referModelProspect(id)
{
    console.log(id);
    let status = $('#checkbox-' + id).is(':checked');
    console.log(status);

    $('#loader-' + id).css('display', 'inline-block');

    $.ajax({
        url: "{{ route('rh.refer_model_prospect') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id,
            status,
        },
    }).done(function (res) {
        if(res.success) {
            $('#loader-' + id).removeClass('fa-pulse fa-spinner');
            $('#loader-' + id).addClass('fa-check');
            $('#loader-' + id).css('color', 'green');
            $('#loader-' + id).fadeOut(2000);
        } else {
            $('#loader-' + id).removeClass('fa-pulse fa-spinner');
            $('#loader-' + id).addClass('fa-times');
            $('#loader-' + id).css('color', 'red');
            $('#loader-' + id).html('&nbsp;&nbsp;Error.');
            $('#checkbox-' + id).prop('checked', false);
        }

        Toast.fire({
            icon: "success",
            title: "Actualizado correctamente."
        });
    }).fail(function (res, textStatus, xhr) {
        $('#loader-' + id).removeClass('fa-pulse fa-spinner');
        $('#loader-' + id).addClass('fa-times');
        $('#loader-' + id).css('color', 'red');
        $('#loader-' + id).html('&nbsp;&nbsp;Error.');
        $('#checkbox-' + id).prop('checked', false);
    });

}
</script>
@endpush
