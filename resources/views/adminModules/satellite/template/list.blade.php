@extends("layouts.app")
@section('content')
<div class="row">
	<div class="card col-lg-8 table-responsive">
		<div class="card-header">
			<div class="row">
				<div class="col-lg-8">
				    <span class="span-title">Listado de Plantillas</span><br>
				    <small class="text-danger">Estas plantillas seran enviadas de acuerdo al estado de la cuenta</small>
				</div>
         </div>
		</div>
		<div class="card-body">
			<table class="table table-hover table-striped" id="owners-table" style="width:100%">
				<thead>
					<tr>
					<th>Plantilla</th>
					<th>Configuracion</th>
					</tr>
				</thead>
				<tbody>
					@foreach($templates as $template)
						<tr>
							<td>{{ $template->name }}</td>
							<td><a href="{{ route("satellite.config_template", ['id' =>  $template->id]) }}" target="_blank" class="btn btn-warning btn-sm"><i class="fas fa-cogs"></i></a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
			
		</div>
	</div>
</div>

@endsection