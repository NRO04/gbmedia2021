@extends("layouts.app")
@section('pageTitle', 'Satelite Usuarios')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card table-responsive">
			<div class="card-header">
				<div class="row">
					<div class="col-lg-12">
						<span class="span-title">Listado de Usuarios Satélite</span>
                        @can('satellite-user-create')
						<a href="{{ route('satellite.create_user') }}" target="_blank" type="button" class="btn btn-m btn-success float-right btn-sm">
                            <i class="fa fa-plus"></i> Crear</a>
                        @endcan
					</div>
				</div>
			</div>
			<div class="card-body">
				<table class="table table-hover table-striped" id="owners-table" style="width:100%">
					<thead>
					<tr>
						<th>Nombre Usuario</th>
						<th>Documento</th>
						<th>Pais</th>
						<th>Acciones</th>
						<th style="width: 130px">
							<div class="col-lg-12">
								<div class="row">
									<div class="col-lg-12">Imagenes</div>
									<div class="col-lg-12">
										<div class="row">
											<div class="col-lg-3">F</div>
											<div class="col-lg-3">R</div>
											<div class="col-lg-3">S</div>
											<div class="col-lg-3">P</div>
										</div>
									</div>
								</div>
							</div>
						</th>
						<th>Ultimo Cambio</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
				</table>

			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div id="modal-update" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark modal-lg">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<div class="col-lg-12">
				    <h4 class="modal-title">Modificar Usuario Satélite</h4>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
	                <div class="col-lg-12">
	            		<div class="card border-secondary">
	            			<div class="card-body">
	            			<form action="{{ route('satellite.update_user')}}" method="post" id="form-update-user" enctype="multipart/form-data">
								@csrf
	            				<input type="hidden" name="user_id" id="user_id">
		                        <div class="form-group row">
	                                <div class="col-md-6 pr-2">
	                                    <label for="owner">Tipo Documento</label>
	                                    <select class="form-control" id="document_type" name="document_type" onchange="verifyCountry()">
	                                        <option value="">Seleccione el tipo de documento...</option>
	                                        @foreach ($documents as $document)
	                                            <option value="{{ $document->id}}">{{ $document->name }}</option>
	                                        @endforeach
	                                    </select>
	                                </div>


	                                <div class="col-md-6 pl-1">
	                                    <label for="document_number">Número de Documento </label>

	                                    <input type="text" class="form-control" name="document_number" id="document_number" placeholder="11243254785" onfocusout="existsUser()">
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
	                            </div>

	                            <div class="form-row" id="div-info-user">
	                                <div class="form-group col-md-6">
	                                    <label for="first_name">Nombre</label>
	                                    <input type="text" class="form-control" name="first_name" id="first_name" placeholder="Angel">
	                                </div>

	                                <div class="form-group col-md-6">
	                                    <label for="second_name">Segundo Nombre</label>
	                                    <input type="text" class="form-control" name="second_name" id="second_name" placeholder="Alberto">
	                                </div>

	                                <div class="form-group col-md-6">
	                                    <label for="last_name">Primer Apellido</label>
	                                    <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Perez">
	                                </div>

	                                <div class="form-group col-md-6">
	                                    <label for="second_last_name">Segundo Apellido</label>
	                                    <input type="text" class="form-control" name="second_last_name" id="second_last_name" placeholder="Pelaez">
	                                </div>

	                                <div class="form-group col-md-6">
	                                    <label for="birth_date">Fecha Nacimiento</label>
	                                    <input type="date" class="form-control" name="birth_date" id="birth_date" >
	                                </div>

	                                <div class="col-md-6"></div>

	                                <div class="form-group col-md-6">
	                                    <div class="row ">
	                                        <label class="col-lg-12">Frente de Documento</label>
	                                        <small class="text-muted text-bold col-lg-12" style="padding-left: 15px;">Formatos permitidos: .jpg y .png | Máximo 5 MB</small>
	                                        <div class="col-lg-12" id="front_image"></div>
	                                    </div>
	                                </div>
	                                <div class="form-group col-md-6">
	                                    <div class="row ">
	                                        <label class="col-lg-12">Reverso de Documento</label>
	                                        <small class="text-muted text-bold col-lg-12" style="padding-left: 15px;">Formatos permitidos: .jpg y .png | Máximo 5 MB</small>
	                                        <div class="col-lg-12" id="back_image"></div>
	                                    </div>
	                                </div>
	                                <div class="form-group col-md-6">
	                                    <div class="row ">
	                                        <label class="col-lg-12">Sosteniendo Documento</label>
	                                        <small class="text-muted text-bold col-lg-12" style="padding-left: 15px;">Formatos permitidos: .jpg y .png | Máximo 5 MB</small>
	                                        <div class="col-lg-12" id="holding_image"></div>
	                                    </div>
	                                </div>
	                                <div class="form-group col-md-6">
	                                    <div class="row ">
	                                        <label class="col-lg-12">Foto de Perfil</label>
	                                        <small class="text-muted text-bold col-lg-12" style="padding-left: 15px;">Formatos permitidos: .jpg y .png | Máximo 5 MB</small>
	                                        <div class="col-lg-12" id="profile_image"></div>
	                                    </div>
	                                </div>
	                            </div>

	                        </form>
	                		</div>
	                	</div>
	                </div>
				</div>
			</div>
			<!--/modal-body-->
			<div class="modal-footer">
				<button id="btn-update" type="button" class="btn btn-m btn-success float-right btn-sm d-none"><i class="fa fa-edit"></i> Modificar</button>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    collapseMenu();
	Mytable = $("#owners-table").DataTable({
		processing: true,
		serverSide: true,
		ordering: false,
		"lengthMenu": [[100, 200, 300, 400], [100, 200, 300, 400]],
		"language": {
		url: '{{ asset('DataTables/Spanish.json') }}',
		},
		ajax: {
			url: "{{ route('satellite.get_satellite_users') }}",
			dataSrc: "data",
			type: "GET",
			dataType: 'json',
		},
		columns: [
			{ data: "name"},
			{ data: "document" },
			{ data: "country" },
			{ data: "actions" },
			{ data: "images" },
			{ data: "modified" },
		],
		columnDefs: [{ targets: [0], orderable: false,}],
	});
});

function modifyUser(id)
{
	$("#modal-update").modal("show");
	$.ajax({
		url: '{{ route('satellite.edit_user') }}',
		type: 'GET',
		dataType: 'json',
		data: {'id': id},
	})
	.done(function(res) {
		console.log(res);
		$("#document_number").val(res.document_number);
		$("#first_name").val(res.first_name);
		$("#second_name").val(res.second_name);
		$("#last_name").val(res.last_name);
		$("#second_last_name").val(res.second_last_name);
		$("#birth_date").val(res.birth_date);
		$("#country_id").val(res.country_id);
		$("#document_type").val(res.document_type);
		$("#user_id").val(res.id);
		$("#btn-update").removeClass("d-none");
		verifyCountry();

		$("#front_image").html("");
		$("#back_image").html("");
		$("#holding_image").html("");
		$("#profile_image").html("");

		$("#front_image").spartanMultiImagePicker({
		    fieldName: 'front_image',
		    maxCount: 1,
		    groupClassName: 'col-xs-12',
		    placeholderImage: {image: res.front_image , width: res.front_image_size},
		    maxFileSize: 5000000,
		    onExtensionErr : function(index, file){
		    },
		    onSizeErr : function(index, file){
		        console.log(index, file);
		        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
		    }
		});

		$("#back_image").spartanMultiImagePicker({
		    fieldName: 'back_image',
		    maxCount: 1,
		    groupClassName: 'col-xs-12',
		    placeholderImage: {image: res.back_image, width: res.back_image_size},
		    maxFileSize: 5000000,
		    onExtensionErr : function(index, file){
		    },
		    onSizeErr : function(index, file){
		        console.log(index, file);
		        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
		    }
		});

		$("#holding_image").spartanMultiImagePicker({
		    fieldName: 'holding_image',
		    maxCount: 1,
		    groupClassName: 'col-xs-12',
		    placeholderImage: {image: res.holding_image, width: res.holding_image_size},
		    maxFileSize: 5000000,
		    onExtensionErr : function(index, file){
		    },
		    onSizeErr : function(index, file){
		        console.log(index, file);
		        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
		    }
		});

		$("#profile_image").spartanMultiImagePicker({
		    fieldName: 'profile_image',
		    maxCount: 1,
		    groupClassName: 'col-xs-12',
		    placeholderImage: {image: res.profile_image, width: res.profile_image_size},
		    maxFileSize: 5000000,
		    onExtensionErr : function(index, file){
		    },
		    onSizeErr : function(index, file){
		        console.log(index, file);
		        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
		    }
		});

	});
}

$("#btn-update").on("click", function(){
    $("#btn-update").prop('disabled' , true);
    ResetValidations();

    formData = new FormData(document.getElementById('form-update-user'));
    formData.append('front_image' , $("#front_image"));
    formData.append('back_image' , $("#back_image"));
    formData.append('holding_image' , $("#holding_image"));
    formData.append('profile_image' , $("#profile_image"));
    $.ajax({
        url: '{{ route('satellite.update_user')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
    .done(function(res) {
        if (res.success)
        {
            Toast.fire({
                icon: "success",
                title: "Se han modificado los valores exitosamente",
            });
            Mytable.draw();
        }
        else
        {
        	if (res.exists)
        	{
        		Toast.fire({
	                icon: "error",
	                title: "Ya existe un usuario con este número de documento",
	            });
        	}
        	else
        	{
        		Toast.fire({
	                icon: "error",
	                title: "Ha ocurrido un error, comuniquese con el ADMIN",
	            });
        	}
        }
        $("#btn-update").prop('disabled' , false);
        modifyUser($("#user_id").val());
    })
    .fail(function(res) {
        CallBackErrors(res);
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
        $("#btn-update").prop('disabled' , false);
    });
});

function verifyCountry()
{
	if (($("#document_type").val() >= 1 && $("#document_type").val() <= 3) || $("#document_type").val() == "")
    {
        $("#div-country").addClass('d-none');
    }
    else
    {
        $("#div-country").removeClass('d-none');
    }
}

function removeUser(user_id)
{
	SwalGB.fire({
            title: "Está seguro ?",
            text: "Desea eliminar el usuario ?",
            icon: "warning",
            showCancelButton: true,
    }).then(result => {
        if (result.isConfirmed)
        {
            $.ajax({
				url: '{{route('satellite.remove_user')}}',
				type: 'POST',
				data: {'_token' : "{{ csrf_token() }}" , 'user_id' : user_id}
			})
			.done(function(res) {
				if (res)
		        {
		            Toast.fire({
		                icon: "success",
		                title: "Se ha eliminado el usuario exitosamente",
		            });
		            Mytable.draw();
		        }
		        else
		        {
		        	Toast.fire({
		                icon: "error",
		                title: "Ha ocurrido un error, comuniquese con el ADMIN",
		            });
		        }
			});
        }
    });


}
</script>
@endpush
