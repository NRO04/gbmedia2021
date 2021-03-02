@extends('layouts.app')
@section('pageTitle', 'Editar Hora Extra')
@push('styles')
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
		<span class="span-title">Configuración Horas Extras</span>
		</div>
	</div>
  </div>
  <div class="card-body">



  <form id="form_extravalue">
	@csrf
	<div class="card" style="width: 100%;">
		<div class="card-header">
			Valor Horas Extras
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-lg-6">
					<label>Valor horas extras diurnas</label>
					<input type="text" class="form-control only-numbers" name="day_value" value="{{ $RHExtraValue->day_value }}" id="day_value"/>
				</div>
				<div class="col-lg-6">
					<label>Valor horas extras nocturnas</label>
					<input type="text" class="form-control only-numbers" name="night_value" value="{{ $RHExtraValue->night_value }}" id="night_value"/>
				</div>
			</div>
		</div>
	</div>
	<div class="card" style="width: 100%;">
		<div class="card-header">
			Valor Extra %
		</div>
		<div class="card-body">
			<label style="color: orange; font-size: 12px;">
				<i class="fas fa-info-circle"></i>&nbsp Estos Valores solo se aplican a usuarios que tiene contrato a termino indefinido.
			</label>
			<table style="width: 100%">
				<tr>
					<td colspan="2">
						<label class="required">Valor %</label>
					</td>
					<td colspan="2">
						<label class="required">Valor % Dominical o Festivo</label>
					</td>
				</tr>
				<tr>
					<td style="padding: 5px;">
						<label>Diurno</label>
					</td>
					<td style="padding: 5px;">
						<input type="text" class="form-control only-numbers" name="day_percent" value="{{ $RHExtraValue->day_percent }}" id="day_percent"/>
					</td>
					<td style="padding: 5px;">
						<label>Diurno</label>
					</td>
					<td style="padding: 5px;">
						<input type="text" class="form-control only-numbers" name="day_sunday_percent" value="{{ $RHExtraValue->day_sunday_percent }}" id="day_sunday_percent"/>
					</td>
				</tr>
				<tr>
					<td style="padding: 5px;">
						<label>Nocturno</label>
					</td>
					<td style="padding: 5px;">
						<input type="text" class="form-control only-numbers" name="night_percent" value="{{ $RHExtraValue->night_percent }}" id="night_percent"/>
					</td>
					<td style="padding: 5px;">
						<label>Nocturno</label>
					</td>
					<td style="padding: 5px;">
						<input type="text" class="form-control only-numbers" name="night_sunday_percent" value="{{ $RHExtraValue->night_sunday_percent }}" id="night_sunday_percent"/>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="card" style="width: 100%;">
		<div class="card-header">
			Auxilio de transporte
		</div>
		<div class="card-body">
			<label style="color: orange; font-size: 12px;">
				<i class="fas fa-info-circle"></i>&nbsp El auxilio de transporte solo aplica para usuarios que tiene contrato a termino indefinido, pero no aplica si su salario es mayor a 1.755.606.
			</label>
			<div class="col-md-12">
				<div class="row">
					<div class="col-lg-6">
						<label>Auxilio de transporte</label>
					</div>
					<div class="col-lg-6">
						<input type="text" class="form-control only-numbers" name="transportation_aid" value="{{ $RHExtraValue->transportation_aid }}" id="transportation_aid"/>
					</div>
				</div>
			</div>
		</div>
	</div>
  </form>
  </div>
  <div class="modal-footer">
      @can('human-resources-extra-hour-configuration-edit')
	    <button type="button" class="btn btn-success" id="send_extravalue">Aceptar</button>
      @endcan
  </div>
</div>
@endsection
@push('scripts')
<script>
$("#send_extravalue").on('click', function() {
		console.log('hola');
		$.ajax({
            url: "{{ route('rh.extraHours.UpdateExtraValue') }}",
            type: "POST",
            data: $('#form_extravalue').serialize(),
        })
        .done(function (res) {
			Toast.fire({
                icon: "success",
                title: "Los cambios se realizaron con exito !!",
            });
			$('#modal-value-extraHours').modal('hide');
        })
        .fail(function (res)
        {
			Toast.fire({
                icon: "danger",
                title: "Error comuniquese con el administrador",
            });
			$('#modal-value-extraHours').modal('hide');
        });
	});
</script>
@endpush
