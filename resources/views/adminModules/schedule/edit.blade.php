@extends('layouts.app')
@section('pageTitle', 'Horario')
@push('styles')
<style>
.c-dark-theme .creados{
  background: #0a0b18;
  border: none;
}

.btn-danger{
	color: white !important;
}
</style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold"><a href="{{route('schedule.list')}}">Horarios</a></li>
<li class="breadcrumb-item active">Editar Horario</li>
@endsection
@section('content')
<div class="row">
	<div class="card col-lg-12">
		<div class="card-header">
			<span class="span-title">Editar Horario</span>
			<span class="float-right"><span class="text-danger">{{ $schedule_sessions[0]->location->name }}</span><span class="text-danger"> | {{ $schedule_sessions[0]->type->name }}</span><span class="text-danger"> | Nro Equipos : {{ $schedule_sessions[0]->available }}</span></span>
		</div>
		<div class="card-body">
			<form method="post" action="" id="myForm">
				@csrf
	            <input type="hidden" name="session" id="session" value="{{ $ses }}">
	            <input type="hidden" name="location" id="location" value="{{ $location }}">
	            <input type="hidden" name="available" id="available" value="{{ $schedule_sessions[0]->available }}">

	            <div id="new_table"></div>
	        </form>

		</div>
	</div>
	<div class="col-lg-12">
		<button type="button" class="btn btn-m btn-success float-right btn-sm ml-3 mb-5" id="btn-create" onclick="SaveForm()"><i class="fa fa-check"></i> Guardar</button>
		<a type="button" href="{{route('schedule.list')}}" class="btn btn-m btn-danger float-right btn-sm mb-5" ><i class="fa fa-check"></i> Cancelar</a>
		<button type="button" class="btn btn-m btn-primary float-left btn-sm ml-4 mb-5" id="btn-insert-row" onclick="InsertRow()"><i class="fa fa-plus"></i> Crear Fila</button>
	</div>

</div>
@endsection
@push('scripts')
<script>
array_data = @json($schedules);
array_models = @json($models);
array_days = ["mon", "tue","wed","thu","fri","sat","sun"];

    function ExistModel(element)
    {
        var newValue = element.value;
        id = element.id;
        cont = 0;
        $(".models").each(function(){
            if($(this).val() == newValue && newValue != ""){ cont ++};
        });
        if (cont >= 2)
        {
        	Toast.fire({
                icon: "error",
                title: "La Modelo ya tiene horario asignado",
            });
            element.value = 0;
        }
        else
        {
            id = id.split("select-");
            for (var i = 0; i < array_models['models'].length; i++) {
                if (array_models['models'][i]['id'] == newValue)
                {
                    array_data[id[1]]['user_id'] = newValue;
                }
            }

        }
    }

	function CreatingTable()
    {
        result = "<table class= 'table table-striped table-hover' id='myTable'>";
		result = result+"<thead>";
		result = result+"<tr><th>Modelo</th>";
		result = result+"<th>Lunes</th>";
		result = result+"<th>Martes</th>";
		result = result+"<th>Miercoles</th>";
		result = result+"<th>Jueves</th>";
		result = result+"<th>Viernes</th>";
		result = result+"<th>Sábado</th>";
		result = result+"<th>Domingo</th>";
		result = result+"<th></th></tr>";
		result = result+"</thead>";
		result = result+"<tbody id='tbody-schedule'>";
        for (var i = 0; i < array_data.length; i++)
            {
            	console.log(array_data);
                datos_user_id = array_data[i].user_id;

                result = result + "<tr>";
                select = "<select class='models form-control' name='models[]' id='select-"+i+"' onchange='ExistModel(this)'>";
                select = select + "<option value='0'></option>";
                for (var s = 0; s < array_models['models'].length; s++)
                    {
                        selected = (array_models['models'][s]['id'] == datos_user_id)? "selected" : "";
                        user_id = array_models['models'][s]['id'];
                        user_id = parseInt(user_id);
                        select = select + "<option value='"+ user_id +"' "+selected+">"+ array_models['models'][s]['nick'] +"</option>";
                    }
                select = select  +  "</select>";
                result = result + "<td>"+ select +"</td>"
                select_dias = "";

                for (var d = 0; d < array_days.length; d++)
                    {

                        name = array_days[d];
                        select_dias = "<select class='"+name+"1 form-control' name='"+name+"[]' id='"+name+"-"+i+"' onchange='CalculateAvailables()'>";
                        option1 = ((array_data[i][name]) == 0)? "selected" : "";
                        option2 = ((array_data[i][name]) == 1)? "selected" : "";
                        option3 = ((array_data[i][name]) == 2)? "selected" : "";
                        option4 = ((array_data[i][name]) == 3)? "selected" : "";

                        select_dias = select_dias + "<option "+option1+" value='0'></option>";
                        select_dias = select_dias + "<option "+option2+" value='1'>Si</option>";
                        select_dias = select_dias + "<option "+option3+" value='2'>No</option>";
                        select_dias = select_dias + "<option "+option4+" value='3'>DESCANSO</option>";
                        select_dias = select_dias +  "</select>";

                        result = result + "<td>"+ select_dias +"</td>";
                    }
                result = result + "<td><i style='color:red' class='fa fa-trash-alt' onclick='EliminarFila("+i+")'></i></td>";
                result = result + "</tr>";
            }
            result = result+"<tr><th>DISPONIBLES</th>";
			result = result+"<td><span  class='text-danger ml-3 font-weight-bold' id='available_mon'></span></td>";
			result = result+"<td><span  class='text-danger ml-3 font-weight-bold' id='available_tue'></span></td>";
			result = result+"<td><span  class='text-danger ml-3 font-weight-bold' id='available_wed'></span></td>";
			result = result+"<td><span  class='text-danger ml-3 font-weight-bold' id='available_thu'></span></td>";
			result = result+"<td><span  class='text-danger ml-3 font-weight-bold' id='available_fri'></span></td>";
			result = result+"<td><span  class='text-danger ml-3 font-weight-bold' id='available_sat'></span></td>";
			result = result+"<td><span  class='text-danger ml-3 font-weight-bold' id='available_sun'></span></td>";
			result = result+"<td></td></tr>";
            result = result + "</table>";
            $("#new_table").html(result);
            $("select").on("change", function(){
                id = $(this).attr('id');
                value = $(this).val();
                res = id.split("-");
                if (res[0] != "select")
                {
                    array_data[res[1]][res[0]] = value;
                }
            });
            CalculateAvailables();
    }

	function InsertRow()
    {
        location_id = @json($location);
        session_id = @json($ses);

        fila = {
	        created_at: "",
			fri: 2,
			id: 0,
			mon: 2,
			sat: 2,
			session: session_id,
			setting_location_id: location_id,
			sun: 2,
			thu: 2,
			tue: 2,
			updated_at: "",
			user_id: 0,
			wed: 2,
		};

        array_data.push(fila);
        CreatingTable();
    }

	function EliminarFila(index)
    {
        array_data = removeItemFromArr(array_data, index);
        console.log(array_data);
        CreatingTable();
    }

	function removeItemFromArr(arr,item)
    {
        return arr.filter( function( e ) {
            return e !== arr[item];
        } );
    };

	function CalculateAvailables()
    {
        schedule_sessions = @json($schedule_sessions);
        number = schedule_sessions[0]['available'];
        for (var i = 0; i < array_days.length; i++)
            {
                name = array_days[i];
                result_day = number;
                $("."+name+"1").each(function()
                {
                    if ($(this).val() == 1)
                    {
                        result_day = result_day - 1 ;
                    }
                });

                available = "available_"+name;
                $("#"+available).html(result_day);

            }
    }

	function SaveForm()
    {
        bandera = true;
        if ($("#locacion_nombre").val() == "" || $("#nro_equipos").val() <= 0 || $("#sesion").val() <= 0)
            bandera = false;

        $("select").each(function(){
            if ($(this).val() == 0)
            {
               bandera = false;
               return;
            }
        });

        if (bandera)
           	$.ajax({
           		url: '{{ route('schedule.update')}}',
           		type: 'POST',
           		data: $("#myForm").serialize(),
           	})
           	.done(function(res) {
    			if (res.success) {
    				Toast.fire({
		                icon: "success",
		                title: "El horario fue modificado exitosamente",
		            });

                } else {
                	Toast.fire({
		                icon: "error",
		                title: "Ha ocurrido un error, comuniquese con el ADMIN",
		            });
                }
    		})

        else
            {
            	Toast.fire({
	                icon: "error",
	                title: "Llene todos los campos para completar la accion",
	            });
            }
    }

window.onload = CreatingTable();
    </script>
@endpush
