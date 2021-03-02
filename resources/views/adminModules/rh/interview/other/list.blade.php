@extends('layouts.app')
@section('pageTitle', 'Prospectos')
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold">Recursos Humanos</li>
<li class="breadcrumb-item active"><a href="">Prospectos</a></li>
@endsection
@section('content')
<div class="card border-secondary">
  <div class="card-header">
	<div class="row">
		<div class="col-sm">
			<span class="span-title">Listado <span class="badge badge-primary">Usuarios</span></span>
		</div>
		<div class="col-sm">
			<select class="form-control form-control-sm" id="changeProspectsView">
				<option selected>Usuarios</option>
				<option value="2">Modelos</option>
				<option value="3">Modelos Referidos</option>
			</select>
		</div>
		<div class="col-sm">
            @can('human-resources-prospect-create')
			<a href="{{ route('rh.interview.storeInterview')}}"><button class="btn btn-success btn-sm float-right mx-1"><i class="fa fa-plus"></i>&nbsp;Entrevista</button></a>
            @endcan
		</div>
	</div>
  </div>
  <div class="card-body">
	<table class="table table-striped table-hover" id="table_interviews_other" style="width: 100%;">
		<thead>
			<tr>
				<th>Nombre</th>
				<th>Rol</th>
				<th>F.Entrevista</th>
				<th>Adapta</th>
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
					<input id="id_user" name="id" type="hidden" value="">
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
	table = $('#table_interviews_other').DataTable({
		language: {
			url: '{{ asset("DataTables/Spanish.json") }}',
		},
		processing: true,
		serverSide: true,
		ordering: false,
        pageLength: 50,
        destroy: true,
		ajax: {
			method: "GET",
			url   : "{{ route('rh.interview.listInterviewsOther') }}",
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
			{ data: "role"},
			{ data: "created_at"},
			{ data: "adapts"},
			{ data: "action"},
		],
		fnDrawCallback: function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
	});
});

$('#changeProspectsView').on('change', function (e) {
    let showValue = $(this).val();
    if(parseInt(showValue) === 2) {
        window.location.replace("../model/list");
    }
    if(parseInt(showValue) === 3) {
        window.location.replace("../../referred");
    }
});

function ModalCite(id)
{
	$('#modal-cite-user').modal('show');
	document.getElementById("id_user").value = id;
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

		$('#email').val('');
		$('#setting_location_id').val('');
		$('#contract_id').val('');
		$('#password').val('');

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(errors);
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
            title: "La entrevista fue creada exitosamente"
        });

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(errors);
    });
}

function deleteInterview(id){

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
			}).fail(function (res, textStatus, xhr) {
				let errors = res.responseJSON.errors;
				CallBackErrors(res);
			});
		}
	});

}

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
            title: "Se ha convertido el prospecto a usuarion exitosamente",
        });
    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(res);
    });
}


</script>
@endpush
