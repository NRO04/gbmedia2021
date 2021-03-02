@extends('layouts.app')
@section('pageTitle', 'Horario')
@push('styles')
<style>
.c-dark-theme .created{  
  background: #0a0b18; 
  border: none;
}
</style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold"><a href="{{route('schedule.list')}}">Horarios</a></li>
<li class="breadcrumb-item active">Crear Horario</li>
@endsection
@section('content')
<div class="row">
	<div class="col-lg-12">
		
	<form action="{{ route('schedule.store') }}" method="post" class="row" id="form-create">
	@csrf
	<div class="card col-lg-6">
		<div class="card-header">
			<span class="span-title">Crear Horario</span>
		</div>
		<div class="card-body">
				<div class="form-group col-lg-8">
					<label class="required">Locacion</label>
					<select name="location" class="form-control" id="location">
						<option value="0"></option>
						@foreach($locations as $location)
							<option value="{{ $location->id }}">{{ $location->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group col-lg-8">
					<label class="required">Sesión</label>
					<select name="sessions_type" id="sessions_type" class="form-control" disabled>
						<option value="0"></option>
					</select>
				</div>
				<div class="form-group col-lg-8">
					<label class="required">Nro Equipos</label>
					<input type="text" class="form-control only-numbers" name="available" id="available" disabled />
				</div>
		</div>
	</div>
	<div class="card col-lg-6 created">
		<div class="card-header bg-success">
			<span class="span-title" style='color:white'>Creados</span>
		</div>
		<div class="card-body">
			<div class="nav-tabs-boxed">
	            <ul class="nav nav-tabs" role="tablist">
	             	@foreach($locations as $key => $location)
						<li class="nav-item"><a class="nav-link @if($key == 0)active @endif" data-toggle="tab" href="#tab-{{$location->id}}" role="tab" aria-controls="home" >{{$location->name}}</a></li>
					@endforeach	
	            </ul>
	            <div class="tab-content">
	            	@foreach($locations as $key => $location)
						<div class="tab-pane @if($key == 0) active @endif" id="tab-{{$location->id}}" role="tabpanel">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Session</th>
										<th>Nro Cuartos</th>
										<th>Jornadas</th>
									</tr>
								</thead>
								<tbody>
									@foreach($sessions as $session)
									@if($session->setting_location_id == $location->id)
									@php
										if($session->session == 1)
											$class = "table-success";
										if($session->session == 2)
											$class = "table-warning";
										if($session->session == 3)
											$class = "table-info";
										if($session->session == 4)
											$class = "table-dark";
									@endphp
										
										<tr class="{{ $class }}">
											<td>{{ $session->type->name }}</td>
											<td>{{ $session->available }}</td>
											<td>{{ $session->shift_start." ".$session->shift_end }}</td>
										</tr>
									@endif	
									@endforeach
								</tbody>
							</table>
						</div>
					@endforeach	
	            </div>
            </div>
		</div>
	</div>
	<div class="card col-lg-12">
		<div class="card-body">
			<div id="table_schedule" class="contenedorTable table-responsive"></div>
		</div>
	</div>
	</form>
	<button style="display: none" type="button" class="btn btn-m btn-success float-right btn-sm mb-3" id="btn-create"><i class="fa fa-check"></i> Guardar</button>
	<button style="display: none" type="button" class="btn btn-m btn-primary float-left btn-sm mb-3" id="btn-insert-row"><i class="fa fa-plus"></i> Crear Fila</button>
	</div>

</div>

@endsection
@push('scripts')
<script type="text/javascript">
	
	array_selects = [];
	array_dias = ["lunes", "martes","miercoles","jueves","viernes","sabado","domingo"];

	$("#location").on("change", function(){
		$('#sessions_type').attr("disabled", true);
		$('#available').attr("disabled", true);
		$('#available').val("");
		$("#table_schedule").html("");
		$("#btn-create").css("display" , "none");
		$("#btn-insert-row").css("display" , "none");
		locacion = $(this).val();
		$('#sessions_type').html("");
		if(locacion == 0){
			Toast.fire({
                    icon: "error",
                    title: "Debe escoger una locacion",
                });
		}
		else{
			sessions = @json($sessions);
			sessions_type = @json($sessions_type);
			cont = 0;
			for (var i = sessions_type.length - 1; i >= 0  ; i--) 
			{
				session = sessions_type[i]['id'];
				bandera = false;
				for (var j = 0; j < sessions.length; j++) {
					if (sessions[j]['setting_location_id'] == locacion && sessions[j]['session'] == session) 
					{
						bandera = true;
						break;
					}
				}
				if (bandera == false) 
				{
					cont++;
					let $option = $('<option />', {
					    text: sessions_type[i]['name'],
					    value: sessions_type[i]['id'],
					});
					$('#sessions_type').prepend($option);

				}
			}
			let $option = $('<option />', {
			    text: '',
			    value: 0,
			});
			$('#sessions_type').prepend($option);
			$("#sessions_type option[value='0']").attr("selected",true);
			if(cont == 0)
			{
				Toast.fire({
                    icon: "error",
                    title: "No hay horarios disponibles para esta Locación",
                });
			}
			else
			{
				$('#sessions_type').attr("disabled", false);
			}

		}
	});

	$("#sessions_type").on("change" , function(event) {
		$('#available').attr("disabled", true);
		$('#available').val("");
		$("#table_schedule").html("");
		$("#btn-create").css("display" , "none");
		$("#btn-insert-row").css("display" , "none");
		session = $("#sessions_type option:selected").val();
		if (session == 0) 
		{
			Toast.fire({
                    icon: "error",
                    title: "Debe escoger una Sesión",
                });
		}
		else
		{
			$('#available').attr("disabled", false);
		}
	});

	$("#available").keyup(function(event) {
		available = $(this).val();
		locacion = $("#location").val();
		if (available > 0) 
		{
			$.ajax({
				url: "/schedule/getModelsLocation/"+locacion,
				type: 'GET',
				dataType: 'JSON',
			})
			.done(function(res) {
				console.log(res);
				models = res.models;
				console.log(res);
				if (models.length > 0) 
				{
					CreateTable(models);
				}
				else
				{
					$("#btn-insert-row").css("display" , "none");
					Toast.fire({
	                    icon: "error",
	                    title: "No hay Modelos en esta locación",
	                });
					$("#table_schedule").html("No hay Modelos en esta locación");			
				}
			});
			
		}
		else
		{
			$("#table_schedule").html("");
			$("#btn-create").css("display" , "none");
			$("#btn-insert-row").css("display" , "none");
			Toast.fire({
                    icon: "error",
                    title: "Debe escoger un Nro equipos válido",
                });
		}
	});
	
	function CreateTable(modelos)
	{
		$("#btn-create").css("display" , "block");
		$("#btn-insert-row").css("display" , "block");
		available = $("#available").val();
		tabla = "<table class= 'table table-striped table-hover' id='mitabla'>";
		tabla = tabla+"<thead>";
		tabla = tabla+"<tr><th>Modelo</th>";
		tabla = tabla+"<th>Lunes</th>";
		tabla = tabla+"<th>Martes</th>";
		tabla = tabla+"<th>Miercoles</th>";
		tabla = tabla+"<th>Jueves</th>";
		tabla = tabla+"<th>Viernes</th>";
		tabla = tabla+"<th>Sábado</th>";
		tabla = tabla+"<th>Domingo</th>";
		tabla = tabla+"<th>Eliminar</th></tr>";
		tabla = tabla+"</thead>";
		tabla = tabla+"<tbody id='tbody-schedule'>";
		for (var i = 0; i < available; i++)
			{

				tabla = tabla + "<tr id='tr-"+i+"'>";
                select = "<select class='modelo1 form-control' name='modelos[]' id='select-"+i+"' onchange='ExistModel(this)'>";
                select = select + "<option value='0'></option>";
                for (var j = 0; j < modelos.length; j++)
				{
					select = select+"<option value='"+modelos[j]['id']+"'>"+modelos[j]['nick']+"</option>";
				}
                select = select  +  "</select>";                         
                tabla = tabla + "<td>"+ select +"</td>"
                select_dias = "";

                for (var d = 0; d < array_dias.length; d++) 
                    {
                        
                        name = array_dias[d];
                        select_dias = "<select class='"+name+"1 form-control' name='"+name+"[]' id='"+name+"-"+i+"' onchange='CalculateAvailable()'>";

                        select_dias = select_dias + "<option value='0'></option>";
                        select_dias = select_dias + "<option value='1'>Si</option>";
                        select_dias = select_dias + "<option value='2'>No</option>";
                        select_dias = select_dias + "<option value='3'>DESCANSO</option>";
                        select_dias = select_dias +  "</select>";

                        tabla = tabla + "<td>"+ select_dias +"</td>";
                    } 
                tabla = tabla + "<td><i style='color:red' class='fa fa-trash-alt' onclick='DeleteRow("+i+")'></i></td>";     
                tabla = tabla + "</tr>";

			  

			}
			tabla = tabla + "<tr id='tr-available'><td>DISPONIBLES</td><td id='dispo1_lunes' style='color: red'></td><td id='dispo1_martes' style='color: red'></td><td id='dispo1_miercoles' style='color: red'></td><td id='dispo1_jueves' style='color: red'></td><td id='dispo1_viernes' style='color: red'></td><td id='dispo1_sabado' style='color: red'></td><td id='dispo1_domingo' style='color: red'></td></tr>";
			tabla = tabla+"</tbody>";
            tabla = tabla + "</table>";
		$("#table_schedule").html(tabla);

		CalculateAvailable();
	}

	function CalculateAvailable()
    {
        available = $("#available").val();
        for (var i = 0; i < array_dias.length; i++) 
            {
                name = array_dias[i];
                result_dia = available;
                $("."+name+"1").each(function()
                {
                    if ($(this).val() == 1) 
                    {
                        result_dia = result_dia - 1 ;
                    }
                });

                id_dispo = "dispo1_"+name;
                $("#"+id_dispo).html(result_dia);
                       
            }
    }

    function DeleteRow(index)
	{
		$("#tr-" + index).remove();
		Toast.fire({
                icon: "info",
                title: "Se ha eliminado la fila exitosamente",
            });

		if ($('#tbody-schedule tr:first').attr('id') == "tr-available") 
		{
			$("#table_schedule").html("");
			$("#btn-create").css("display" , "none");
			$("#btn-insert-row").css("display" , "none");
			$('#available').val("");
			Toast.fire({
                    icon: "info",
                    title: "Seleccione un Nro Equipos",
                });
		}
		CalculateAvailable();
	}

	$("#btn-insert-row").on("click", function(){
		$("#tr-available").remove();
		ArrayElements();
		console.log(array_selects);
		locacion = $("#location").val();
		$.ajax({
				url: "/schedule/getModelsLocation/"+locacion,
				type: 'GET',
				dataType: 'JSON',
			})
		.done(function(res) {
			models = res.models;
			if (models.length > 0) 
			{
				id = $('#table_schedule tr:last').attr('id');
				id = id.split("-");
				i = parseInt(id[1]) + 1;
				tabla = "<tr id='tr-"+i+"'>";
                select = "<select class='modelo1 form-control' name='modelos[]' id='select-"+i+"' onchange='ExistModel(this)'>";
                select = select + "<option value='0'></option>";
                for (var j = 0; j < models.length; j++)
				{
					select = select+"<option value='"+models[j]['id']+"'>"+models[j]['nick']+"</option>";
				}
                select = select  +  "</select>";                         
                tabla = tabla + "<td>"+ select +"</td>"
                select_days = "";

                for (var d = 0; d < array_dias.length; d++) 
                    {
                        
                        name = array_dias[d];
                        select_days = "<select class='"+name+"1 form-control' name='"+name+"[]' id='"+name+"-"+i+"' onchange='CalculateAvailable()'>";

                        select_days = select_days + "<option value='0'></option>";
                        select_days = select_days + "<option value='1'>Si</option>";
                        select_days = select_days + "<option value='2'>No</option>";
                        select_days = select_days + "<option value='3'>DESCANSO</option>";
                        select_days = select_days +  "</select>";

                        tabla = tabla + "<td>"+ select_days +"</td>";
                    } 
                tabla = tabla + "<td><i style='color:red' class='fa fa-trash-alt' onclick='DeleteRow("+i+")'></i></td>";     
                tabla = tabla + "</tr>";
                tabla = tabla + "<tr id='tr-available'><td>DISPONIBLES</td><td id='dispo1_lunes' style='color: red'></td><td id='dispo1_martes' style='color: red'></td><td id='dispo1_miercoles' style='color: red'></td><td id='dispo1_jueves' style='color: red'></td><td id='dispo1_viernes' style='color: red'></td><td id='dispo1_sabado' style='color: red'></td><td id='dispo1_domingo' style='color: red'></td></tr>";
                table_schedule = $("#tbody-schedule").html();
                table_schedule = table_schedule + tabla;
                $("#tbody-schedule").html(table_schedule);
                SelectElements();
                CalculateAvailable();

			}
		});
	});

	function ExistModel(element)
    {
        var newValue = element.value;
        id = element.id;
        cont = 0;
        $(".modelo1").each(function(){
            if($(this).val() == newValue && newValue != ""){ cont ++};
        });
        if (cont >= 2) 
        {
        	Toast.fire({
                icon: "error",
                title: "La Modelo ya tiene horario asignado",
            });
            element.value = "";
        }
    } 

    function ArrayElements()
    {
    	elements = 0;
    	for (var i = 0; i < array_dias.length; i++) {
    		day = array_dias[i];
    		$("."+day+"1").each(function(){
				id = $(this).prop('id');
				value = $(this).val();
				array_selects[elements] = {'id' : id , 'value' : value };
				elements++;
			});
    	}
    	$(".modelo1").each(function(){
				id = $(this).prop('id');
				value = $(this).val();
				array_selects[elements] = {'id' : id , 'value' : value };
				elements++;
			});
    }

    function SelectElements()
    {
    	elements = 0;
    	for (var i = 0; i < array_selects.length; i++) {
    		id = array_selects[i]['id'];
    		value = array_selects[i]['value'];
    		$("#"+id+" > option[value="+value+"]").attr("selected",true);
    	}
    	
    }

    $("#btn-create").on("click" , function(){
    	if ($("#location").val() != 0 && $("#sessions_type").val() != 0 && $("#available").val() > 0) 
    	{
    		ResetValidations();
    		$.ajax({
    			url: '{{ route('schedule.store')}}',
    			type: 'POST',
    			data: $("#form-create").serialize(),
    		})
    		.done(function(res) {
    			if (res.success) {
    				Toast.fire({
		                icon: "success",
		                title: "El horario fue creado exitosamente",
		            }).then((result) => {
		            	location.reload();	
		            });
                    
                } else {
                	Toast.fire({
		                icon: "error",
		                title: "Ha ocurrido un error, comuniquese con el ADMIN",
		            });
                }
    		})
    		.fail(function(res) {
    			CallBackErrors(res);
    		});
    		
    	}
    });
</script>
@endpush