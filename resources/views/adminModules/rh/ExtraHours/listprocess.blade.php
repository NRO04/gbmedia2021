@extends('layouts.app')
@section('pageTitle', 'Solicitudes Horas Extra')
@section('pageTitle', 'AQUIas')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Recursos Humanos</li>
    <li class="breadcrumb-item active"><a href="">Listado Horas Extras</a></li>
@endsection
@push('styles')
<style>
</style>
@endpush
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<input id="selected_user" type="hidden" value="">
<div class="row">
    <div class="card border-secondary" style="width: 100%;">
        <div class="card-header">
            <div class="row">
                <div class="col-sm">
                    <span class="span-title">Solicitudes de Horas Extras</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover" id="process-table" style="width: 100%;">
				<thead>
					<tr>
						<th>Nombre Solicitante</th>
						<th>Razón Horas Extras</th>
						<th>Solicitado</th>
						<th>Duración</th>
						<th>Resumen</th>
						<th>Total</th>
						<th>Estado</th>
					</tr>
				</thead>
				<tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!--modal-->
<div id="modal-update-disapproved" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Desaprobar solicitud de Hora Extra</h5>
			</div>
			<div class="modal-body">
				<form id="form-update-disapproved" method="post">
                    @csrf
                    <input id="request_id" name="request_id" type="hidden" value="">
                    <input id="state_id" name="state_id" type="hidden" value="3">
					<div class="form-group col-md-12">
						<label for="reason_deny" class="required">Razon por la cual no aprueba</label>
                        <textarea name="reason_deny" style="height: 142px;" class="form-control" id="reason_deny" rows="3"></textarea>
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
@endsection @push('scripts')
<script>

function approbe(id)
{

    Swal.fire({
		title: 'Hora Extra',
		text: "¿Está seguro que desa aceptar esta solicitud? Las horas aprobadas serán asignadas automáticamente a la nómina del usuario.",
		icon: 'question',
		showCancelButton: true,
		cancelButtonColor: '#d33',
		confirmButtonText: 'Aceptar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
        var data =
		{
			"_token"        : "{{ csrf_token() }}",
			"request_id"    : id,
			"state_id"      : 2,
		};

        if (result.value) {
			$.ajax({
				url : "{{ route('rh.extraHours.updateHourRequest') }}",
				type: "POST",
				data: data,
			}).done(function (res) {
				table.ajax.reload();
				Toast.fire({
					icon : "success",
					title: "La solicitud se ha gestionado exitosamente"
				});
			}).fail(function (res, textStatus, xhr) {
				let errors = res.responseJSON.errors;
				CallBackErrors(errors);
			});
		}
	});
}

function disapprove(id)
{
    $('#modal-update-disapproved').modal('show');
    $("#request_id").val(id);
    $("#reason_deny").val('');
}

$("#btn_create").on('click', function() {
    ResetValidations();
    $.ajax({
        url: "{{ route('rh.extraHours.updateHourRequest') }}",
        type: 'POST',
        dataType: 'JSON',
        data: $("#form-update-disapproved").serialize(),
    })
    .done(function(res) {
        table.ajax.reload();
        $('#modal-update-disapproved').modal('hide');
        Toast.fire({
            icon: "success",
            title: "La solicitud se ha gestionado exitosamente"
        });
    })
    .fail(function (res) {
        let errors = res.responseJSON.errors;
        CallBackErrors(res);
    });
});

let table = null;
$(document).ready(function() {
    table = $('#process-table').DataTable({
            language: {
                url: '{{ asset("DataTables/Spanish.json") }}',
            },
            processing  : true,
            serverSide  : true,
            ordering    : false,
            pageLength  : 100,
            destroy     : true,
            ajax: {
                method: "GET",
                url   : "{{ route('rh.extraHours.getExtraHourHistoryProcess') }}",
            },
            columns: [
                { data: "user" },
                { data: "extra_reason" },
                { data: "date_request"},
                { data: "duration" },
                { data: "resume" },
                { data: "total" },
                { data: "state_id" },
            ],
            fnDrawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
});

$(document).ready(function(){
    collapseMenu();
});
</script>
@endpush
