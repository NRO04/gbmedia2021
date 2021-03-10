@extends('layouts.app')
@section('pageTitle', 'Trabajos')
@section('content')

<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header">
				<span class="span-title">Listado Trabajos </span>
				<button class="btn btn-m btn-success float-right btn-sm" data-toggle="modal" data-target="#CreateTask" id="create-task"><i class="fa fa-plus"></i> Crear</button>
			</div>
			<div class="card-body">
				<div class="row px-4">

					<div id="myFolders" class="col-lg-2 pr-0 pl-0"></div>
					<div id="div_schedule" class="col-lg-10">
						<table class="table tableTask table-p025 table-responsive-sm table-hover table-outline mb-0" id="task-table" style="width: 100%">
							<thead class="thead-light">
							<tr style="display: none">
								<th class="text-center"></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
							</thead>
							<tbody style="cursor: pointer">
							</tbody>
						</table>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<!--Create Task-->
<div class="modal fade" id="CreateTask" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel3" aria-hidden="true">
  <div class="modal-dialog modal-dialog-slideout modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Crear Trabajo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        </button>
      </div>
      <div class="modal-body">
      		<div class="container">
      			<div class="row">
      				<div class="col-12">
      					<div class="card">
      						<form action="{{ route('tasks.store')}}" method="post" id="form-create-task" enctype="multipart/form-data">
							@csrf
      						<div class="card-body">
      							<div class="form-group row">
									<div class="col-lg-8 pt-1">
										<label>Titulo</label>
										<input type="text" class="form-control" name="title" id="title" />
									</div>
								</div>

								<div class="form-group row">
									<div class="col-lg-11">
										<label>Descripci�n</label>
										<textarea name="comment" id="comment" class="description"></textarea>
									</div>
								</div>

								<div class="form-group row">
									<div class="col-lg-12">
										<div class="form-group row">
											<div class="col-lg-5">
												<label>Enviar a </label>
												<i title="Elija los remitentes que puedan visualizar o deban recibir esta tarea o informaci�n, debe dejar presionada la tecla Ctrl( Control ) para elegir varias Areas o Usuarios" class="fa fa-question-circle"></i>
												<div class="form-group row">
													<div class="col-md-9 col-form-label">
														<div class="form-check form-check-inline mr-1">
															<input class="form-check-input send-to" id="checkbox_roles" type="checkbox" name="inline-radios">
															<label class="form-check-label">Areas</label>
														</div>
														<div class="form-check form-check-inline mr-1">
															<input class="form-check-input send-to" id="checkbox_users" type="checkbox" name="inline-radios">
															<label class="form-check-label">Usuarios</label>
														</div>
														<div class="form-check form-check-inline mr-1">
															<input class="form-check-input send-to" id="checkbox_models" type="checkbox" name="inline-radios">
															<label class="form-check-label">Modelos</label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-lg-4">
												<label>Tiempo Estimado </label>
												<i title="Escoja el tiempo que calcula esta tarea debe tomar en obtener una soluci�n. Recuerde que la idea es que las tareas se realicen en el menor tiempo posible" class="fa fa-question-circle"></i>
												<div class="form-group row">
												<div class="col-md-9 col-form-label">
												<div class="form-check form-check-inline mr-1">
												<input class="form-check-input" id="radio-hours" type="radio" value="option-hours" name="time_aprox" checked="">
												<label class="form-check-label" for="radio-hours">Horas</label>
												</div>
												<div class="form-check form-check-inline mr-1">
												<input class="form-check-input" id="radio-days" type="radio" value="option-days" name="time_aprox">
												<label class="form-check-label" for="radio-days">Dias</label>
												</div>
												</div>
												</div>
											</div>
											<div class="col-lg-3">
												<label>Adjuntar Archivos</label><i title="Si desea subir varios archivos � im�genes, mantenga presionada la tecla CTRL (Control) del teclado y seleccione los archivos." class="fa fa-question-circle ml-1"></i>
												<div class="col-sm-7 col-lg-6 inputFile pl-0">
									                <span class="btnFile btn-dark btn-sm">
									                    <span id="spanFile">Seleccionar Archivos</span>
									                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
									                </span>
									                <input id="inputfile" name="inputfile[]" type="file" multiple="">
									            </div>
											</div>
										</div>
									</div>

									<div class="col-lg-12 row">
										<div class="col-lg-5 bg-secondary">
											<span id="div_receivers"></span>
										</div>
										<div class="col-lg-4">
											<div id="div_hours">
												<select name="select_hours" class="form-control">
													@for($i = 1; $i <= 24; $i++)
														<option>{{ $i }}</option>
													@endfor
												</select>
											</div>
											<div id="div_days" style="display: none">
												<select name="select_days" class="form-control">
													@for($i = 2; $i <= 15; $i++)
														<option>{{ $i }}</option>
													@endfor
												</select>
											</div>
										</div>
										<div class="col-lg-2">

										</div>
									</div>
								</div>
							</div>
							</form>
							<div class="card-footer">
								<button type="button" class="btn btn-danger  pull-right btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
        						<button type="button" class="btn btn-success  pull-rigth btn-sm" id="btn-create"><i class="fa fa-check"></i> Aceptar</button>
							</div>
						</div>
      				</div>
      			</div>
      		</div>
      </div>
      <!-- <div class="modal-footer ml-4">
        <button type="button" class="btn btn-danger  pull-right btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
        <button type="button" class="btn btn-success  pull-rigth btn-sm" id="btn-create"><i class="fa fa-check"></i> Aceptar</button>
      </div> -->
    </div>
  </div>
</div>

<!-- Add Receivers -->
<div id="modal-receivers" class="modal fade" role="dialog">
	<div class="modal-dialog modal-xl modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Agregar Recipiente</h4>
			</div>
			<div class="modal-body">
				<div class=" col-lg-12 row" id="modal-body-receiviers">

				</div>
			</div>
		</div>
	</div>
</div>

<!-- Folder Administrator -->
<div id="modal-admin-folder" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Administrar Carpeta</h4>
			</div>
			<div class="modal-body">
				<div class="container">
					<div class="row">
						<div class="col-lg-12">
							<div class="row">
								<div class="col-lg-2">
									<label> Nombre</label>
								</div>
								<div class="col-lg-7">
									<form action="{{ route('tasks.store_folder')}}" method="post" id="form-create-folder">
									@csrf
										<input type="text" class="form-control" name="name_folder" id="name_folder" placeholder="Please, enter a location name"/>
									</form>
								</div>
								<div class="col-lg-3">
									<button class="btn btn-success btn-sm" id="btn-create-folder">Crear</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class=" col-lg-12 pt-2" id="modal-body-folders">

				</div>
			</div>
		</div>
	</div>
</div>

<!--View Comments-->
<div class="modal fade" id="modal-view-comments" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel3" aria-hidden="true">
  <div class="modal-dialog modal-dialog-slideout modal-xl" role="document" >
    <div class="modal-content">
      <div id="modal-header-comment" class="modal-header d-flex">
        <div>
        	<h5 class="modal-title">Comentarios Trabajo</h5>
        </div>
        <button type="button" class="close float-right" data-dismiss="modal" aria-label="Close">
        </button>

        <button class="btn btn-m btn-dark float-right btn-sm mr-4" onclick="markAsUnreadTask()" title="Marcar como no leido"><i class="fas fa-bolt"></i></button>

        <button class="btn btn-m btn-dark float-right btn-sm mr-2" onclick="signOutTask()" title="Retirarme del Trabajo"><i class="fas fa-sign-out-alt"></i></button>

        <button id="extend-time" class="btn btn-m btn-dark float-right btn-sm mr-2" data-toggle="modal" data-target="#modal-extend-time"
                title="Alargar Tiempo"><i
                class="far fa-clock"></i></button>

        <button id="remove_receivers" class="btn btn-m btn-dark float-right btn-sm mr-2" data-toggle="modal" data-target="#modal-receivers_remove"
                
                 title="Eliminar Recipiente"><i class="fas fa-user-minus"></i></button>

        <button id="add-receivers" class="btn btn-m btn-dark float-right btn-sm mr-2" data-toggle="modal" data-target="#modal-receivers_add" id="add-receivers"
                 title="Agregar Recipiente"><i class="fas fa-user-plus"></i></button>

        <button class="btn btn-m btn-dark float-right btn-sm mr-4" id="goDown" title="Ir Abajo"><i class="fas fa-arrow-down"></i></button>

        <button class="btn btn-m btn-dark float-right btn-sm mr-2" id="goUp" title="Ir Arriba"><i class="fas fa-arrow-up"></i></button>

      </div>
      <div class="modal-body" id="modal-body-comment" >
      		<div class="container">
      			<div class="row">
      				<div class="col-lg-12">
      					<div class="card mb-0"><div class="card-body row p-2" id="title-comments"></div></div>
      					<div class="card"> <div class="card-body p-2" id="receivers-comments"></div></div>
      					<div class="col-lg-12" id="content-comments"></div>

      				</div>
      				<div class="col-lg-12" id="div-text-area">
      					<div class="card">
      						<form action="{{ route('tasks.create_comments')}}" method="post" id="form-create-comment" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="task_id" id="task_id">
      						<div class="card-body">
								<div class="form-group row">
									<div class="col-lg-12">
										<textarea name="replaycomment" id="replaycomment" class="description"></textarea>
									</div>
								</div>

								<div class="form-group row">
									<div class="col-lg-12">
										<div class="form-group row">
											<div class="col-lg-9">
												<label>Adjuntar Archivos</label><i title="Si desea subir varios archivos � im�genes, mantenga presionada la tecla CTRL (Control) del teclado y seleccione los archivos." class="fa fa-question-circle ml-1"></i>
												<div class="col-sm-7 col-lg-6 inputFile pl-0">
									                <span class="btnFile btn-dark btn-sm">
									                    <span id="spanFile">Seleccionar Archivos</span>
									                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
									                </span>
									                <input id="inputfileComment" name="inputfileComment[]" type="file" multiple="">
									            </div>
											</div>
                                            <div class="col-lg-3">
                                                <button type="button" class="btn btn-success btn-sm" id="btn-replaycomment"><i class="fa fa-check"></i> Responder</button>
                                                <button type="button" class="btn btn-danger btn-sm float-right" id="btn-finish_task"><i class="fa fa-check"></i> Finalizar</button>
                                            </div>


										</div>
									</div>
								</div>
							</div>
							</form>
							<div class="card-footer">

							</div>
						</div>
      				</div>
      				<div id="div-task-terminated" class="col-lg-12">
      					<div class="alert alert-danger text-center" role="alert"><h5 id="text_terminated_task">El trabajo se encuentra finalizado</h5></div>
      				</div>
      			</div>
      		</div>
      </div>
      <!-- <div class="modal-footer ml-4">
        <button type="button" class="btn btn-danger  pull-right btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
        <button type="button" class="btn btn-success  pull-rigth btn-sm" id="btn-create"><i class="fa fa-check"></i> Aceptar</button>
      </div> -->
    </div>
  </div>
</div>

<!-- Add Receivers -->
<div id="modal-receivers_add" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Agregar Recipiente</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-12">
						<div class="col-lg-12">
							<div class="form-group row">
								<div class="col-md-9 col-form-label">
									<div class="form-check form-check-inline mr-1">
										<input class="form-check-input send-to_add" id="checkbox_roles_add" type="checkbox" name="inline-radios">
										<label class="form-check-label">Areas</label>
									</div>
									<div class="form-check form-check-inline mr-1">
										<input class="form-check-input send-to_add" id="checkbox_users_add" type="checkbox" name="inline-radios">
										<label class="form-check-label">Usuarios</label>
									</div>
									<div class="form-check form-check-inline mr-1">
										<input class="form-check-input send-to_add" id="checkbox_models_add" type="checkbox" name="inline-radios">
										<label class="form-check-label">Modelos</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class=" col-lg-12 row" id="modal-body-receiviers_add">

				</div>
				<div class=" col-lg-12 row bg-dark p-3 mt-3 ml-1 text-info" id="div_receivers_add">

				</div>
				<button class="btn btn-sm btn-success float-right m-3" id="btn_add_receivers"><i class="fa fa-plus"></i> Agregar</button>
			</div>
		</div>
	</div>
</div>

<!-- remove Receivers -->
<div id="modal-receivers_remove" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Eliminar Recipiente</h4>
			</div>
			<div class="modal-body">
				<div class="alert alert-danger" role="alert">Marque los que desea eliminar</div>
				<div class=" col-lg-12 row" id="modal-body-receiviers_remove">

				</div>
				<button class="btn btn-sm btn-danger float-right m-2" id="btn_remove_receivers"><i class="fa fa-trash"></i> Remover</button>
			</div>
		</div>
	</div>
</div>

<!-- extend time -->
<div id="modal-extend-time" class="modal fade" role="dialog">
	<div class="modal-dialog modal-md modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Alargar Tiempo</h4>
			</div>
			<div class="modal-body">
				<form action="{{ route('tasks.extend_time')}}" method="post" id="form-extend_time" >
				<div class="col-lg-12">
					<div class="form-group row">
					<div class="col-md-9 col-form-label">
					<div class="form-check form-check-inline mr-1">
					<input class="form-check-input" id="radio-hours_extend" type="radio" value="option-hours_extend" name="time_aprox_extend" checked="">
					<label class="form-check-label" for="radio-hours_extend">Horas</label>
					</div>
					<div class="form-check form-check-inline mr-1">
					<input class="form-check-input" id="radio-days_extend" type="radio" value="option-days_extend" name="time_aprox_extend">
					<label class="form-check-label" for="radio-days_extend">Dias</label>
					</div>
					</div>
					</div>
				</div>
				<div class="col-lg-4">

						@csrf
						<div id="div_hours_extend">
							<select name="select_hours_extend" class="form-control">
								@for($i = 1; $i <= 24; $i++)
									<option>{{ $i }}</option>
								@endfor
							</select>
						</div>
						<div id="div_days_extend" style="display: none">
							<select name="select_days_extend" class="form-control">
								@for($i = 2; $i <= 15; $i++)
									<option>{{ $i }}</option>
								@endfor
							</select>
						</div>

				</div>
				</form>
				<button class="btn btn-sm btn-success float-right m-2" id="btn_extend_time"><i class="fa fa-plus"></i> Agregar</button>
			</div>
		</div>
	</div>
</div>


<!-- embed documents -->
<div id="modal-embed-documents" class="modal fade" role="dialog">
	<div class="modal-dialog modal-xl modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Documento</h4>
			</div>
			<div class="modal-body" >
				<embed id="embed-documents" src="" type="" style="height: 600px; width: 100%"></embed>
				{{-- <iframe id="iframe-documents" src="" type="" style="height: 500px; width: 100%"></iframe> --}}
			</div>
		</div>
	</div>
</div>

@endsection
@push('scripts')
<script>

let Mytable = null;
created_by = null;

var receivers = {
	'to_roles' : [],
	'to_users' : [],
	'to_models' : [],
	};

var receivers_add = {
	'to_roles' : [],
	'to_users' : [],
	'to_models' : [],
	};

var receivers_remove = {
	'to_roles' : [],
	'to_users' : [],
	};

var seeing = {
	'status' : 0,
	'folder'  : 0,
	'customDisplay': 0,
}

$('.send-to').click(function(){
    if($(this).prop("checked") == true){
    	if ($(this).prop("id") == "checkbox_roles")
		{
			roles = @json($roles);
			result = "";
			for (var i = 0; i < roles.length; i++) {
				role = roles[i]['name'];
				result = result + "<div class='form-check col-lg-4'><input class='form-check-input to_roles' onclick='AssignRole()' id='to_roles-"+roles[i]['id']+"' type='checkbox' name='inline-radios' value='"+role+"'><label class='form-check-label'>"+role+"</label></div>";
			}
			$("#modal-body-receiviers").html(result);
		}
		if ($(this).prop("id") == "checkbox_users")
		{
			users = @json($users);
			result = "<select class='form-control' name='select_users' id='select_users' multiple='' size='15' onchange='AssignUser()' >";
			for (var i = 0; i < users.length; i++) {
				name = users[i]['first_name']+ ' ' + users[i]['middle_name']+ ' ' + users[i]['last_name']+ ' ' + users[i]['second_last_name'];
				result = result + "<option value='to_users-"+users[i]['id']+"'>"+name+"</option>";
			}
			result = result + "</select>";

			$("#modal-body-receiviers").html(result);

		}
		if ($(this).prop("id") == "checkbox_models")
		{
			models = @json($models);
			result = "<select class='form-control' name='select_models' id='select_models' multiple='' size='15' onchange='AssignModel()' >";
			for (var i = 0; i < models.length; i++) {
				nick = models[i]['nick'];
				result = result + "<option value='to_models-"+models[i]['id']+"'>"+nick+"</option>";
			}
			result = result + "</select>";

			$("#modal-body-receiviers").html(result);

		}

        $("#modal-receivers").modal('show');
    }
    else if($(this).prop("checked") == false){
        if ($(this).prop("id") == "checkbox_roles")
		{
			receivers['to_roles'] = [];
		}
		if ($(this).prop("id") == "checkbox_users")
		{
			receivers['to_users'] = [];
		}
		if ($(this).prop("id") == "checkbox_models")
		{
			receivers['to_models'] = [];
		}
		ShowReceivers();
    }
});

function AssignRole(){
	receivers['to_roles'] = [];
	cont = 0;
    $('.to_roles').each(function(){
	    if ($(this).prop("checked") == true)
	    {
	    	id = $(this).prop('id');
	    	id = id.split('-');
	    	id = id[1];
	    	name = $(this).val();
	    	receivers['to_roles'][cont] = {'id' : id , 'name' : name};
	    	cont++;
	    }
	});
	ShowReceivers();
}

function AssignUser(){
	receivers['to_users'] = [];
	cont = 0;
	$("#select_users option:selected").each(function () {
        id = $(this).val();
        id = id.split('-');
	    id = id[1];
        name = $(this).text();
        receivers['to_users'][cont] = {'id' : id , 'name' : name};
        cont++;
    });
	ShowReceivers();
}

function AssignModel(){
	receivers['to_models'] = [];
	cont = 0;
	$("#select_models option:selected").each(function () {
        id = $(this).val();
        id = id.split('-');
	    id = id[1];
        name = $(this).text();
        receivers['to_models'][cont] = {'id' : id , 'name' : name};
        cont++;
    });
	ShowReceivers();
}

function ShowReceivers()
{
	result = "";
	for (var i = 0; i < receivers['to_roles'].length; i++) {
		if (result == "")
			result = receivers['to_roles'][i]['name'];
		else
			result = result + ", " +receivers['to_roles'][i]['name'];
	}
	for (var i = 0; i < receivers['to_users'].length; i++) {
		if (result == "")
			result = receivers['to_users'][i]['name'];
		else
			result = result + ", " +receivers['to_users'][i]['name'];
	}
	for (var i = 0; i < receivers['to_models'].length; i++) {
		if (result == "")
			result = receivers['to_models'][i]['name'];
		else
			result = result + ", " +receivers['to_models'][i]['name'];
	}
	$("#div_receivers").html(result);
}

$("input[name=time_aprox]").on("click", function(){
	if($(this).val() == "option-hours"){
		$("#div_days").css('display' , 'none');
		$("#div_hours").css('display' , '');
	}
	else{
		$("#div_hours").css('display' , 'none');
		$("#div_days").css('display' , '');
	}
});

$("input[name=time_aprox_extend]").on("click", function(){
	if($(this).val() == "option-hours_extend"){
		$("#div_days_extend").css('display' , 'none');
		$("#div_hours_extend").css('display' , '');
	}
	else{
		$("#div_hours_extend").css('display' , 'none');
		$("#div_days_extend").css('display' , '');
	}
});

$("#btn-create").on("click" , function(){
	$("#btn-create").prop('disabled' , true);
	bandera = true;
	if (receivers['to_roles'].length == 0 && receivers['to_users'].length == 0 && receivers['to_models'].length == 0)
	{
		texto = "Debe elegir al menos un remitente";
		bandera = false;
	}
	if ($("#title").val() == "")
	{
		texto = "Debe escoger un titulo";
		bandera = false;
	}

	if (bandera)
	{
		receivers1 = JSON.stringify(receivers);
		formData = new FormData(document.getElementById('form-create-task'));
		formData.append('receivers' , receivers1);
		ResetValidations();
		$.ajax({
			url: '{{ route('tasks.store')}}',
			type: 'POST',
			processData: false,
    		contentType: false,
			data: formData,
		})
		.done(function(res) {
			if (res.success) {
				Toast.fire({
	                icon: "success",
	                title: "El trabajo fue creado exitosamente",
	            });
	            $("#btn-create").prop('disabled' , false);
                ResetModalForm("#form-create-task");
                $("#div_receivers").html("");
                Mytable.draw();
                ShowFolders();
            } else {
            	Toast.fire({
	                icon: "error",
	                title: "Ha ocurrido un error, comuniquese con el ADMIN",
	            });
	            $("#btn-create").prop('disabled' , false);
            }
		})
		.fail(function(res) {
			CallBackErrors(res);
			Toast.fire({
	                icon: "error",
	                title: "Upss... Ha ocurrido un error, comuniquese con el ADMIN",
	            });
			$("#btn-create").prop('disabled' , false);
		});

	}
	else
	{
		Toast.fire({
            icon: "error",
            title: texto,
        });
        $("#btn-create").prop('disabled' , false);
	}
});

function ShowTable()
{
	Mytable = $('#task-table').DataTable({
        processing: true,
        ordering : false,
        serverSide: true,
        // bLengthChange: false,
        "language": {
                    url: '{{ asset('DataTables/Spanish.json') }}',
                },
        "lengthMenu": [[10, 20, 30, 40], [10, 20, 30, 40]],

        ajax: {
            url: "{{ route('tasks.get_tasks')}}",
            dataSrc: 'data',
            type: 'GET',
            data: function (d) {
	            	d.seeing = seeing;
	          	}
        },
        columns: [
            {data: 'bolt' , class: 'TdClass_task'},
            {data: 'img' , class: 'TdClass_task'},
            {data: 'info_task' , class: 'TdClass_task', "render": function ( data, type, row, meta ) {
                //let encode_title = encode_utf8(row.title);
                return "<div>" + checkUTF8(row.title) + "</div>" + row.info_task_extra;
                }},
            {data: 'time' , class: 'TdClass_task'},
            {data: 'move'},
        ],

        columnDefs: [
            { "orderable": false, }
        ],
        "order" : [[1 , "asc"]],
    });
}

function checkUTF8(text) {
    var utf8Text = text;
    try {
        // Try to convert to utf-8
        utf8Text = decodeURIComponent(escape(text));
        // If the conversion succeeds, text is not utf-8
    }catch(e) {
        // console.log(e.message); // URI malformed
        // This exception means text is utf-8
    }
    return utf8Text; // returned text is always utf-8
}

function encode_utf8(s) {
    return unescape(encodeURIComponent(s));
}

function decode_utf8(s) {
    return decodeURIComponent(escape(s));
}

function ShowFolders()
{

	

	$.ajax({
		url: '{{ route('tasks.get_folders')}}',
		type: 'GET',
		data: { seeing }
	})
	.done(function(res) {
		$("#myFolders").html(res);
		//show modal create folder
		$("#admin-folder").on("click" , function(){
			$("#modal-admin-folder").modal("show");
			$.ajax({
				url: '{{ route('tasks.admin_folders')}}',
				type: 'GET',
			})
			.done(function(res) {
				$("#modal-body-folders").html(res)
				
			});

		});
	});

	
}

//create folder
$("#btn-create-folder").on("click" , function(){
	$("#btn-create-folder").prop("disabled" , true);
	ResetValidations();
	$.ajax({
		url: '{{ route('tasks.store_folder')}}',
		type: 'POST',
		dataType: 'json',
		data: $("#form-create-folder").serialize(),
	})
	.done(function(res) {
        if (res.success) {
            Toast.fire({
                icon: "success",
                title: "La Carpeta fue agregada exitosamente",
            });
            $("#modal-admin-folder").modal("hide");
            ResetModalForm("#form-create-folder");
            Mytable.data('seeing', seeing);
			Mytable.draw();
            ShowFolders();
            $("#btn-create-folder").prop("disabled" , false);
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
});


function displayCustomTask(folderId){
	
	$("#task-table_info").css("display","none");

	seeing.customDisplay = 1;

	console.log(seeing);
	
	if(folderId == 'midUrgencyFolder' ){
		console.log(folderId);
		seeing.customDisplay = 3;
		Mytable.data('seeing', seeing);
		Mytable.draw();
		ShowFolders();
		
	}else if(folderId == 'highUrgencyFolder'){
		console.log(folderId);
		seeing.customDisplay = 2;
		Mytable.data('seeing', seeing);
		Mytable.draw();
		ShowFolders();
		
	}else if(folderId == 'myTaskFolder'){
		console.log(folderId);
		seeing.customDisplay = 1;
		Mytable.data('seeing', seeing);
		Mytable.draw();
		ShowFolders();
		
	}else{
		console.log(folderId);
		seeing.customDisplay = 4;
		Mytable.data('seeing', seeing);
		Mytable.draw();
		ShowFolders();
	 	
	}	
	
}

function ShowTaskFolder(status,folder)
{
	// seeing = {
	// 	'status' : status,
	// 	'folder'  : folder,
	// }

	seeing.status = status;
	seeing.folder = folder;

	Mytable.data('seeing', seeing);
    Mytable.draw();
    ShowFolders();
}

function SendToFolder(task_status_id,folder)
{
	$.ajax({
		url: '{{ route('tasks.update_folder_destination')}}',
		type: 'GET',
		data: {task_status_id, folder},
	})
	.done(function() {
		Mytable.data('seeing', seeing);
	    Mytable.draw();
	    ShowFolders();
	});

}

function updateFolderName(id)
{
	if ($("#folder-"+id).val() != "")
	{
		$.ajax({
			url: '{{ route('tasks.update_folder_name') }}',
			type: 'GET',
			data: {'id': id, 'value' : $("#folder-"+id).val()},
		})
		.done(function() {
			Mytable.data('seeing', seeing);
		    Mytable.draw();
		    ShowFolders();
		});

	}
	else
	{
		Toast.fire({
            icon: "error",
            title: "El nombre de la carpeta es obligatorio",
        });
	}
}

function deleteFolder(id)
{
	$.ajax({
		url: '{{ route('tasks.delete_folder') }}',
		type: 'GET',
		data: {'id': id},
	})
	.done(function() {
		Toast.fire({
            icon: "success",
            title: "La carpeta ha sido eliminada exitosamente",
        });
		Mytable.data('seeing', seeing);
	    Mytable.draw();
	    ShowFolders();
	    $("#tr-folder-"+id).remove();
	});
}

function NuevoReloj()
{
    var msecPerMinute = 1000 * 60;
    var msecPerHour = msecPerMinute * 60;
    var msecPerDay = msecPerHour * 24;
    $("input[name='should_finish_date']").each(function(){

        should_finish_date = $(this).val();
        id = this.id;
        status_id = id.split("should_finish_date-");
        status_id = status_id[1];
        created_at_date = $("#created_at_date-"+status_id).val();

        var date_now = new Date();
        var should_finish_date = new Date(should_finish_date.replace(/\s/, 'T'));
        var created_at_date = new Date(created_at_date.replace(/\s/, 'T'));


        should_finish_date = should_finish_date.getTime();
        date_now = date_now.getTime();
        created_at_date = created_at_date.getTime();

        var interval = should_finish_date - date_now;
        var minutes1 = (date_now/msecPerMinute) - (created_at_date/msecPerMinute);
        var minutes2 = (should_finish_date/msecPerMinute) - (created_at_date/msecPerMinute);

        xc = (minutes1*100)/minutes2;
        xc = 100 - xc;

        var days = Math.floor(interval / msecPerDay );
        interval = interval - (days * msecPerDay );
        var hours = Math.floor(interval / msecPerHour );
        interval = interval - (hours * msecPerHour );
        if (hours <= 9)
        	hours = "0"+hours;


        var minutes = Math.floor(interval / msecPerMinute );
        interval = interval - (minutes * msecPerMinute );
        if (minutes <= 9)
        	minutes = "0"+minutes;


        var seconds = Math.floor(interval / 1000 );
        if (seconds <= 9)
        	seconds = "0"+seconds;


        // Display the result.
        if (should_finish_date < date_now)
        {
            time = "Caducado";
            var progress_status = -1;
        }
        else
        {
            time = days + "d " + hours + ":" + minutes + ":" + seconds;
            var progress_status = parseInt(days);
        }

        progress_status = parseInt(progress_status);

        if (progress_status >= 3)
        {
            $("#should_finish_progress-"+status_id).removeClass();
            $("#should_finish_progress-"+status_id).addClass("progress-bar bg-gradient-success");
        }
        if ( progress_status <= 2  && progress_status  >=1)
        {
            $("#should_finish_progress-"+status_id).removeClass();
            $("#should_finish_progress-"+status_id).addClass("progress-bar bg-gradient-warning");
        }
        if (progress_status == 0)
        {
            $("#should_finish_progress-"+status_id).removeClass();
            $("#should_finish_progress-"+status_id).addClass("progress-bar bg-gradient-danger");
        }
        if (progress_status == -1)
        {
            $("#should_finish_progress-"+status_id).removeClass();
            $("#should_finish_progress-"+status_id).addClass("progress-bar");
        }

        $("#should_finish_progress-"+status_id).css("width" , xc+"%");
        $("#should_finish_time-"+status_id).html(time);

    });
}


//click one task
$("#task-table").on("click", ".TdClass_task" , function(){
	id = $(this).parent().prop("id");
	id = id.split("_");
	id = id[1];
	$("#modal-view-comments").modal("show");
	$("#task_id").val(id);
	titleComment(id);
	receiversComments(id);
	contentComments(id);
	updatePulsing(id,0);
	pulsing_id = "pulsing-"+id;
	removePulsing(pulsing_id);
});

function removePulsing(id)
{
    $("#"+id).css("visibility", "visible");
    $("#"+id).removeClass("pulsing-active");
    $("#"+id).css("color", "#e1e1e1");
}

function titleComment(id)
{
    $("#title-comments").html('');
	$.ajax({
		url: "{{ route('tasks.title_comments')}}",
		type: 'GET',
		data: {'id': id},
	})
	.done(function(res) {
		$("#title-comments").html(res.result);

        if(res.permission.extend_time == 0) {
            $("#extend-time").css('display', 'none');
        } else {
            $("#extend-time").css('display', 'block');
        }

        if(res.permission.remove_receivers == 0) {
            $("#remove_receivers").css('display', 'none');
        } else {
            $("#remove_receivers").css('display', 'block');
        }

        if(res.permission.add_receivers == 0) {
            $("#add-receivers").css('display', 'none');
        } else {
            $("#add-receivers").css('display', 'block');
        }

        if(res.permission.finalized == 0) {
            $("#btn-finish_task").css('display', 'none');
        } else {
            $("#btn-finish_task").css('display', 'block');
        }

	});

}

function receiversComments(id)
{
	$.ajax({
		url: "{{ route('tasks.receivers_comments')}}",
		type: 'GET',
		data: {'id': id},
	})
	.done(function(res) {
		$("#receivers-comments").html(res);
	});

}

function contentComments(id)
{
    $("#content-comments").html("");
	$.ajax({
		url: "{{ route('tasks.content_comments')}}",
		datatype: 'json',
		type: 'GET',
		data: {'id': id},
	})
	.done(function(res) {

		if (res.terminated == 1 || res.signed_out == 1)
		{
			if (res.terminated == 1)
			{
				$("#text_terminated_task").html("El trabajo se encuentra finalizado");
			}
			else
			{
				$("#text_terminated_task").html("No puedes comentar en este trabajo");
			}
			$("#div-text-area").css("display", "none");
			$("#div-task-terminated").css("display", "");
		}
		else
		{
			$("#div-text-area").css("display", "");
			$("#div-task-terminated").css("display", "none");
		}
		$("#content-comments").html(res.comments + "<div id='div-goDown'></div>");
		zoomImageGB();
	});
}

function embedDocuments(url, type)
{

	if (type == "pdf")
	{
		$("#modal-embed-documents").modal("show");
		$("#embed-documents").attr("src" , "../../storage/app/public/GB/task/"+url);
	}
	else
	{
		window.location.href = "../../storage/app/public/GB/task/"+url;
	}

}

function markAsUnreadTask()
{
	task_id = $("#task_id").val();
	updatePulsing(task_id, 1);
	$("#pulsing-"+task_id).addClass("pulsing-active");
}

function updatePulsing(task_id, pulsing)
{
	$.ajax({
		url: "{{ route('tasks.update_pulsing')}}",
		type: 'POST',
		data: {"_token": "{{ csrf_token() }}",'task_id': task_id , "pulsing" : pulsing},
	})
	.done(function(res) {
		if (pulsing == 1)
		{
			Toast.fire({
	                icon: "info",
	                title: "Ha marcado este trabajo como no leido !!!",
	            });
		}
	});
}

function signOutTask()
{
	SwalGB.fire({
                title: "�Est� seguro?",
                text: "� Desea retirarse del trabajo ?",
                icon: "question",
    }).then(result => {
        if (result.value) {
            task_id = $("#task_id").val();
			$.ajax({
				url: "{{ route('tasks.sign_out_task')}}",
				type: 'POST',
				data: {"_token": "{{ csrf_token() }}",'task_id': task_id},
			})
			.done(function(res) {
				if (res.success)
				{
					Toast.fire({
				                icon: "success",
				                title: "Te has retirado del trabajo !!!",
				        });
					$("#modal-view-comments").modal("hide");
					Mytable.draw();

				}
				else
				{
					Toast.fire({
		                icon: "error",
		                title: "No te has podido retirar del trabajo !!!",
		            });
				}
			});
        }
    });


}

$("#btn-replaycomment").on("click" , function(){
	$("#btn-replaycomment").prop('disabled' , true);
	bandera = true;

	if ($("#replaycomment").val() == "")
	{
		texto = "Escriba un comentario";
		bandera = false;
	}

	if (bandera)
	{
		formData = new FormData(document.getElementById('form-create-comment'));
		ResetValidations();
		$.ajax({
			url: '{{ route('tasks.create_comments')}}',
			type: 'POST',
			processData: false,
    		contentType: false,
			data: formData,
		})
		.done(function(res) {
			if (res.success) {
				Toast.fire({
	                icon: "success",
	                title: "El comentario fue creado exitosamente",
	            });
	            
	            $("#form-create-comment")[0].reset();

                tinyMCE.activeEditor.setContent('');
                contentComments($("#task_id").val());
                $("#btn-replaycomment").prop('disabled' , false);
            } else {
            	Toast.fire({
	                icon: "error",
	                title: "Ha ocurrido un error, comuniquese con el ADMIN",
	            });
	            $("#btn-replaycomment").prop('disabled' , false);
	            contentComments($("#task_id").val());
	            Mytable.draw();
	            ShowFolders();
            }
		})
		.fail(function(res) {
			CallBackErrors(res);
			$("#btn-replaycomment").prop('disabled' , false);
			contentComments($("#task_id").val());
			ShowFolders();
		});

	}
	else
	{
		Toast.fire({
            icon: "error",
            title: texto,
        });
        $("#btn-replaycomment").prop('disabled' , false);
	}
});

$("#btn-finish_task").on("click" , function(){
	SwalGB.fire({
                title: "�Est� seguro?",
                text: "� Desea finalizar el trabajo ?. Tenga en cuenta que este proceso no es reversible !!!",
                icon: "question",
    }).then(result => {
        if (result.value) {
        	task_id = $("#task_id").val();
        	$.ajax({
        		url: '{{ route('tasks.finish_task')}}',
        		type: 'POST',
        		data: {"_token": "{{ csrf_token() }}", 'task_id' : task_id},
        	})
        	.done(function() {
        		Toast.fire({
		            icon: "success",
		            title: "Se ha finalizado el trabajo !!!",
		        });
		        contentComments(task_id);
		        Mytable.draw();
        	})
        	.fail(function() {
				Toast.fire({
		            icon: "error",
		            title: "Se ha producido un error, contactese con el ADMIN",
		        });
			});

        }
    });
});

$('.send-to_add').click(function(){
    if($(this).prop("checked") == true){
    	task_id = $("#task_id").val();
    	if ($(this).prop("id") == "checkbox_roles_add")
		{
			$.ajax({
				url: '{{ route('tasks.no_in_roles_receivers') }}',
				type: 'GET',
				dataType: 'json',
				data: {'task_id': task_id},
			})
			.done(function(res) {
				roles = res;
				result = "";
				for (var i = 0; i < roles.length; i++) {
					role = roles[i]['name'];
					result = result + "<div class='form-check col-lg-4'><input class='form-check-input to_roles_add' onclick='AssignRoleAdd()' id='to_roles-"+roles[i]['id']+"' type='checkbox' name='inline-radios' value='"+role+"'><label class='form-check-label'>"+role+"</label></div>";
				}
				$("#modal-body-receiviers_add").html(result);
			});


		}
		if ($(this).prop("id") == "checkbox_users_add")
		{
			$.ajax({
				url: '{{ route('tasks.no_in_users_receivers') }}',
				type: 'GET',
				dataType: 'json',
				data: {'task_id': task_id, 'type' : 0},
			})
			.done(function(res) {
				users = res;
				result = "<select class='form-control' name='select_users_add' id='select_users_add' multiple='' size='15' onchange='AssignUserAdd()' >";
				for (var i = 0; i < users.length; i++) {
					name = users[i]['fullname'];
					result = result + "<option value='to_users-"+users[i]['id']+"'>"+name+"</option>";
				}
				result = result + "</select>";
				$("#modal-body-receiviers_add").html(result);
			});
		}
		if ($(this).prop("id") == "checkbox_models_add")
		{
			$.ajax({
				url: '{{ route('tasks.no_in_users_receivers') }}',
				type: 'GET',
				dataType: 'json',
				data: {'task_id': task_id, 'type' : 1},
			})
			.done(function(res) {
				models = res;
				result = "<select class='form-control' name='select_models_add' id='select_models_add' multiple='' size='15' onchange='AssignModelAdd()' >";
				for (var i = 0; i < models.length; i++) {
					nick = models[i]['fullname'];
					result = result + "<option value='to_models-"+models[i]['id']+"'>"+nick+"</option>";
				}
				result = result + "</select>";
				$("#modal-body-receiviers_add").html(result);
			});
		}

    }
    else if($(this).prop("checked") == false){
        if ($(this).prop("id") == "checkbox_roles_add")
		{
			receivers_add['to_roles'] = [];
		}
		if ($(this).prop("id") == "checkbox_users_add")
		{
			receivers_add['to_users'] = [];
		}
		if ($(this).prop("id") == "checkbox_models_add")
		{
			receivers_add['to_models'] = [];
		}
		ShowReceiversAdd();
    }

    if ($("#checkbox_roles_add").prop("checked") == false && $("#checkbox_users_add").prop("checked") == false  && $("#checkbox_models_add").prop("checked") == false )
    {
    	$("#modal-body-receiviers_add").html("");
    }
});

function AssignRoleAdd(){
	receivers_add['to_roles'] = [];
	cont = 0;
    $('.to_roles_add').each(function(){
	    if ($(this).prop("checked") == true)
	    {
	    	id = $(this).prop('id');
	    	id = id.split('-');
	    	id = id[1];
	    	name = $(this).val();
	    	receivers_add['to_roles'][cont] = {'id' : id , 'name' : name};
	    	cont++;
	    }
	});
	ShowReceiversAdd();
}

function AssignUserAdd(){
	receivers_add['to_users'] = [];
	cont = 0;
	$("#select_users_add option:selected").each(function () {
        id = $(this).val();
        id = id.split('-');
	    id = id[1];
        name = $(this).text();
        receivers_add['to_users'][cont] = {'id' : id , 'name' : name};
        cont++;
    });
	ShowReceiversAdd();
}

function AssignModelAdd(){
	receivers_add['to_models'] = [];
	cont = 0;
	$("#select_models_add option:selected").each(function () {
        id = $(this).val();
        id = id.split('-');
	    id = id[1];
        name = $(this).text();
        receivers_add['to_models'][cont] = {'id' : id , 'name' : name};
        cont++;
    });
	ShowReceiversAdd();
}

function ShowReceiversAdd()
{
	console.log(receivers_add);
	result = "";
	for (var i = 0; i < receivers_add['to_roles'].length; i++) {
		if (result == "")
			result = receivers_add['to_roles'][i]['name'];
		else
			result = result + ", " +receivers_add['to_roles'][i]['name'];
	}
	for (var i = 0; i < receivers_add['to_users'].length; i++) {
		if (result == "")
			result = receivers_add['to_users'][i]['name'];
		else
			result = result + ", " +receivers_add['to_users'][i]['name'];
	}
	for (var i = 0; i < receivers_add['to_models'].length; i++) {
		if (result == "")
			result = receivers_add['to_models'][i]['name'];
		else
			result = result + ", " +receivers_add['to_models'][i]['name'];
	}
	$("#div_receivers_add").html(result);
}

$("#btn_add_receivers").on('click', function() {
	task_id = $("#task_id").val();
	receivers_add1 = JSON.stringify(receivers_add);
	$.ajax({
		url: '{{ route('tasks.add_receivers') }}',
		type: 'POST',
		dataType: 'json',
		data: {"_token": "{{ csrf_token() }}", 'receivers': receivers_add1, 'task_id' : task_id, 'from_add_receivers' : 1},
	})
	.done(function() {
		Toast.fire({
            icon: "success",
            title: "Los recipientes fueron agregados",
        });

        receiversComments(task_id);
        contentComments(task_id);
        $("#modal-body-receiviers_add").html("");
        $("#div_receivers_add").html("");
        $("#checkbox_roles_add").prop("checked", false);
        $("#checkbox_users_add").prop("checked", false);
        $("#checkbox_models_add").prop("checked", false);
        $("#modal-receivers_add").modal("hide");
	})
	.fail(function() {
		Toast.fire({
            icon: "error",
            title: "Se ha producido un error, contactese con el ADMIN",
        });
	});

});

$("#goDown").on("click" , function(){
	$("#modal-body-comment").animate({scrollTop: $('#div-goDown').offset().top}, 1300);
})

$("#goUp").on("click" , function(){
    $('.modal-body').scrollTop(0);
	/*$("#modal-body-comment").animate({scrollTop: $('#modal-view-comments').offset().top}, 1300);*/
})


$("#remove_receivers").on('click', function() {
	task_id = $("#task_id").val();
	$.ajax({
		url: '{{ route('tasks.get_receivers') }}',
		dataType: 'json',
		type: 'GET',
		data: {'task_id' : task_id},
	})
	.done(function(res) {
		result = "";
		for (var i = 0; i < res.to_roles.length; i++) {
			id = res.to_roles[i]['id'];
			role = res.to_roles[i]['name'];
			result = result + "<div class='form-check col-lg-4'><input class='form-check-input to_roles_remove' onclick='AssignRoleRemove()' type='checkbox' name='inline-radios' id='to_roles_remove-"+id+"' value='"+role+"'><label class='form-check-label'>"+role+"</label></div>";
		}
		for (var i = 0; i < res.to_users.length; i++) {
			id = res.to_users[i]['id'];
			role = res.to_users[i]['name'];
			result = result + "<div class='form-check col-lg-4'><input class='form-check-input to_users_remove' onclick='AssignUserRemove()' type='checkbox' name='inline-radios' id='to_users_remove-"+id+"' value='"+role+"'><label class='form-check-label'>"+role+"</label></div>";
		}
        $("#modal-body-receiviers_remove").html(result);
	});

});

function AssignRoleRemove(){
	receivers_remove['to_roles'] = [];
	cont = 0;
    $('.to_roles_remove').each(function(){
	    if ($(this).prop("checked") == true)
	    {
	    	id = $(this).prop('id');
	    	id = id.split('-');
	    	id = id[1];
	    	name = $(this).val();
	    	receivers_remove['to_roles'][cont] = {'id' : id , 'name' : name};
	    	cont++;
	    }
	});
}

function AssignUserRemove(){
	receivers_remove['to_users'] = [];
	cont = 0;
    $('.to_users_remove').each(function(){
	    if ($(this).prop("checked") == true)
	    {
	    	id = $(this).prop('id');
	    	id = id.split('-');
	    	id = id[1];
	    	name = $(this).val();
	    	receivers_remove['to_users'][cont] = {'id' : id , 'name' : name};
	    	cont++;
	    }
	});
}

$("#btn_remove_receivers").on('click', function() {
	task_id = $("#task_id").val();
	receivers_add1 = JSON.stringify(receivers_remove);
	$.ajax({
		url: '{{ route('tasks.remove_receivers') }}',
		type: 'POST',
		dataType: 'json',
		data: {"_token": "{{ csrf_token() }}", 'receivers': receivers_add1, 'task_id' : task_id},
	})
	.done(function() {
		Toast.fire({
            icon: "success",
            title: "Recipientes eliminados",
        });

        receiversComments(task_id);
        contentComments(task_id);
        $("#modal-receivers_remove").modal("hide");
	})
	.fail(function() {
		Toast.fire({
            icon: "error",
            title: "Se ha producido un error, contactese con el ADMIN",
        });
	});

});

$("#btn_extend_time").on('click', function() {
	task_id = $("#task_id").val();

	formData = new FormData(document.getElementById('form-extend_time'));
	formData.append('task_id' , task_id);
	console.log(formData);
	$.ajax({
		url: '{{ route('tasks.extend_time') }}',
		type: 'POST',
		processData: false,
		contentType: false,
		data: formData,
	})
	.done(function() {
		Toast.fire({
            icon: "success",
            title: "Se ha alargado el tiempo del trabajo",
        });
        contentComments(task_id);
        Mytable.draw();
        $("#modal-extend-time").modal("hide");
	})
	.fail(function() {
		Toast.fire({
            icon: "error",
            title: "Se ha producido un error, contactese con el ADMIN",
        });
	});

});


window.onload = NuevoReloj();
setInterval(NuevoReloj,1000);

/*function NotificacionPush()
{
	Push.Permission.request();
		Push.create('Hi there!', {
	    body: 'This is a notification.',
	    icon: 'icon.png',
	    timeout: 8000,               // Timeout before notification closes automatically.
	    vibrate: [100, 100, 100],    // An array of vibration pulses for mobile devices.
	    onClick: function() {
	        // Callback for when the notification is clicked.
	        console.log(this);
	    }
	});
}

window.onload = NotificacionPush();*/
window.onload = ShowTable();
window.onload = ShowFolders();

$(document).ready(function(){
    collapseMenu();
});
</script>
@endpush
