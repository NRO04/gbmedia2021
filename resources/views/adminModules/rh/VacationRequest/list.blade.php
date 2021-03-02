@extends('layouts.app')
@section('pageTitle', 'Vacaciones')
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold">Recursos Humanos</li>
<li class="breadcrumb-item active"><a href="">Vacaciones</a></li>
@endsection
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<input id="selected_user" type="hidden" value="">
<div class="row">
<!--    <div class="alert alert-danger" style="display: {{ Auth::user()->setting_role_id == 11 ? 'block' : 'none' }}">Falta el modulo de asistecia, en el caso de que una modelo peida vacaciones , se descuenten los dias de asistencia.</div>-->
    <div class="card border-secondary" style="width: 100%;">
    <div class="card-header">
                <div class="row">
                    <div class="col-sm">
                        <span class="span-title">Solicitudes de Vacaciones</span>
                    </div>
                    <div class="col-sm">
                        @if(Auth::user()->can('human-resources-vacation-historial'))
                            <select class="form-control form-control-sm" id="select_user">
                                <option value="0">Listar Todos</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="select_user" type="hidden" value="{{ Auth::user()->id }}">
                        @endif
                    </div>
                    <div class="col-sm">
                        <button data-target="#modal-create" data-toggle="modal" id="btn_solicit" class="btn btn-success btn-sm float-right mx-1"><i class="fa fa-plus"></i>&nbsp;Solicitar</button>
                        @can('human-resources-vacation-historial')
                        <a target="_blank" href="{{ route('rh.vacationRequest.staffVacations') }}" class="btn btn-warning btn-sm float-right mx-1"><i class="fas fa-sun"></i>&nbsp;Vacaciones de personal</a>
                        @endcan
                    </div>
                </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover" id="holidays-table" style="width: 100%;">
				<thead>
					<tr>
						<th>Nombre Solicitante</th>
						<th>Periodo</th>
						<th>Tiempo</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modal-create-vacation" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Solicitar Vacaciones</h4>
			</div>
			<div class="modal-body">
                <label style="color: orange; margin-bottom: 10px;"><i class="fas fa-info-circle"></i>&nbsp;La solicitud de vacaciones debe realizarse con 3 semanas de anticipación.&nbsp;El sistema no permite hacer reservas antes de dicho lapso.</label>
                <form id="form-create" method="post">
                    @csrf
                    <input name="user_id" type="hidden" value="{{ Auth::user()->id }}">
                    <div class="form-row text-center">
                        <div class="form-group col-md-12">
                            <input style="text-align: center; font-style: bold;" id="date" name ="date" class="form-control form-control-lg" type="text" value="{{$start_date}} - {{ $end_date }}">
                        </div>
                    </div>
                </form>
			</div>
			<div class="modal-footer">
                <button class="btn btn-danger" data-dismiss="modal" type="button">Cancelar</button>
				<button type="button" class="btn btn-success" id="btn_send">Aceptar</button>
			</div>
		</div>
	</div>
</div>
<!--------->

<!--modal-->
<div id="modal-update-disapproved" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Desaprobar solicitud de vacaciones</h4>
			</div>
			<div class="modal-body">
				<form id="form-update-disapproved" method="post">
                    @csrf
                    <input id="id" name="id" type="hidden" value="">
                    <input id="user_confirm_id" name="user_confirm_id" type="hidden" value="{{ Auth::user()->id }}">
                    <input name="rh_vacation_status_id" type="hidden" value="3">
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
    $('#date').dateDropper({
        roundtrip: true,
        format: 'd-m-Y',
        lang: 'es',
        largeOnly: true,
        fx: true,
        autoIncrease: true,
        autofill: true,
        modal: true,
        theme: 'leaf',
        defaultDate: "{{ date('m/d/Y', strtotime($start_date)) }}",
        minDate: "{{ date('m/d/Y', strtotime($start_date)) }}",
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    let table = null;
    $(document).ready(function() {
        table = $('#holidays-table').DataTable({
            language: {
                url: '{{ asset("DataTables/Spanish.json") }}',
            },
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 100,
            destroy: true,
            ajax: {
                method: "GET",
                url   : "{{ route('rh.vacationRequest.get_vacationRequest') }}",
                data  : function (d) {
                    return $.extend({}, d, {
                        "user_id": $('#select_user').val()
                    });
                }
            },
            columns: [
                { data: "name" },
                { data: "date" },
                { data: "days"},
                { data: "approve" },
            ],
            fnDrawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });

    $("#select_user").on('change', function() {
        table.ajax.reload();
    });

    function ActionApprove(id){
        Swal.fire({
            title: '¿Está seguro que desea Aprobar esta solicitud?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: 'var(--hex-exito, #2ecc71)',
            cancelButtonColor: 'var(--hex-peligro, #ff5252)',
            confirmButtonText: 'Aprobar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {

            var user_confirm_id = "{{ Auth::user()->id }}";
            var data =
            {
                "_token": "{{ csrf_token() }}",
                "id":id,
                "rh_vacation_status_id": 2,
                "user_confirm_id":user_confirm_id,
            };
            $.ajax({
                url: "{{ route('rh.vacationRequest.update')}}",
                type: 'POST',
                dataType: 'JSON',
                data: data
            })
            .done(function(res) {
                table.ajax.reload();
                Toast.fire({
                    icon: "success",
                    title: "La solicitud se ha gestionado exitosamente"
                });
            })
            .fail(function (res) {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error, comuniquese con el ADMIN"
                });
            });
        });
    }

    function ActionModalDisapproved(id){
        $("#reason_deny").val('');
        $("#id").val(id);
        $('#modal-update-disapproved').modal('show');
    }

    $("#btn_solicit").on('click',function(){
        document.getElementById("btn_send").disabled = false;
        $('#modal-create-vacation').modal('show');
        $('#date').val('{{$start_date}} - {{ $end_date }}');
        $('#date').dateDropper('set',{ defaultDate: "{{ date('m/d/Y', strtotime($start_date)) }} - {{ date('m/d/Y', strtotime($end_date)) }}" });
    });

    $("#btn_send").on('click', function() {
            document.getElementById("btn_send").disabled = true;
                $.ajax({
                    url: "{{ route('rh.vacationRequest.create') }}",
                    type: "POST",
                    dataType: 'JSON',
                    data: $('#form-create').serialize(),
                })
                .done(function (res) {
                   if(res.message == true)
                   {
                        Toast.fire({
                            icon: "success",
                            title: "La solicitud se ha enviado exitosamente"
                        });
                        $('#modal-create-vacation').modal('hide');
                        table.ajax.reload();
                   }
                   else
                   {
                        Toast.fire({
                            icon: "error",
                            title: "No se peude enviar la solicitud, existe una solicitud pendiente en proceso."
                        });
                        $('#modal-create-vacation').modal('hide');
                        table.ajax.reload();
                   }
                })
                .fail(function (res)
                {
                    toastr.error("Error comuniquese con el administrador");
                    table.ajax.reload();
                });

    });

    $("#btn_create").on('click', function() {
        ResetValidations();
        DisableModalActionButtons();
        $.ajax({
            url: "{{ route('rh.vacationRequest.update') }}",
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
            EnableModalActionButtons();
        })
        .fail(function (res) {
            let errors = res.responseJSON.errors;
            CallBackErrors(res);
        });
    });
</script>
@endpush
