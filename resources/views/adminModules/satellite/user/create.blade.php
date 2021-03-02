@extends("layouts.app")
@section('content')
<div class="row">
	<div class="card col-lg-12 table-responsive">
		<div class="card-header">
			<div class="row">
				<div class="col-lg-12">
				    <span class="span-title">Crear Usuario Satélite</span>
                    <br>
                    <small class="text-muted text-bold">Ingrese el número de documento y haga click fuera del campo para verificar</small>
				</div>
         </div>
		</div>
		<div class="card-body">
			<div class="row">
                <div class="col-lg-10">
            		<div class="card border-secondary">
            			<div class="card-body">
            			<form action="{{ route('satellite.store_user')}}" method="post" id="form-create-user" enctype="multipart/form-data">
							@csrf

	                        <div class="form-group row">
                                <div class="col-md-6 pr-2">
                                    <label for="owner">Tipo Documento</label>
                                    <select class="form-control" id="document_type" name="document_type">
                                        <option value="">Seleccione el tipo de documento...</option>
                                        @foreach ($documents as $document)
                                            <option value="{{ $document->id}}">{{ $document->name }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-6 pl-1">
                                    <label for="document_number">Número de Documento </label>

                                    <input type="text" class="form-control" name="document_number" id="document_number" placeholder="11243254785" onfocusout="existsUser()">
                                    <small class="text-muted">Ingrese el número de documento y haga click fuera del campo para verificar</small>
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

                            <div class="form-row d-none" id="div-info-user">
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
                        <div>
                        	<button id="btn-create" type="button" class="btn btn-m btn-success float-right btn-sm d-none"><i class="fa fa-plus"></i> Crear</button>
                        </div>
                		</div>
                	</div>
                </div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>

$("#btn-create").on("click", function(){
    $("#btn-create").prop('disabled' , true);
    ResetValidations();

    formData = new FormData(document.getElementById('form-create-user'));
    formData.append('front_image' , $("#front_image"));
    formData.append('back_image' , $("#back_image"));
    formData.append('holding_image' , $("#holding_image"));
    formData.append('profile_image' , $("#profile_image"));
    $.ajax({
        url: '{{ route('satellite.store_user')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
    .done(function(res) {
        if (res.success) {
            SwalGB.fire({
                    title: "Proceso exitoso",
                    text: "Se ha agregado el usuario exitosamente",
                    icon: "success",
                    showCancelButton: false,
            }).then(result => {
                if (result.isConfirmed)
                {
                    document.location.href = '{{ route('satellite.list_users')}}';
                }
            });

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
                title: "Verifique la informacion de los campos",
            });
        $("#btn-create").prop('disabled' , false);
    });

});

$("#front_image").spartanMultiImagePicker({
    fieldName: 'front_image',
    maxCount: 1,
    groupClassName: 'col-xs-12',
    placeholderImage: {image: '../../images/default/placeholder_front.png', width: '50%'},
    maxFileSize: 5000000,
    onExtensionErr : function(index, file){
        // console.log(index, file);
        // alert('Please only input png or jpg type file');
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
    placeholderImage: {image: '../../images/default/placeholder_back.png', width: '50%'},
    maxFileSize: 5000000,
    onExtensionErr : function(index, file){
        // console.log(index, file);
        // alert('Please only input png or jpg type file');
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
    placeholderImage: {image: '../../images/default/placeholder_holding.png', width: '50%'},
    maxFileSize: 5000000,
    onExtensionErr : function(index, file){
        // console.log(index, file);
        // alert('Please only input png or jpg type file');
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
    placeholderImage: {image: '../../images/default/placeholder_profile.png', width: '50%'},
    maxFileSize: 5000000,
    onExtensionErr : function(index, file){
        // console.log(index, file);
        // alert('Please only input png or jpg type file');
    },
    onSizeErr : function(index, file){
        console.log(index, file);
        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
    }
});

function existsUser(){
    ResetValidations();
    document_type =  $("#document_type").val();
    document_number =  $("#document_number").val();
    country_id =  $("#country_id").val();
    $("#btn-verify").prop('disabled' , true);
    $.ajax({
        url: '{{ route('satellite.exists_user')}}',
            type: 'GET',
            data: {'document_type' : document_type, 'document_number' : document_number, 'country_id' : country_id},
            dataType : "json",
        })
        .done(function(res) {
            console.log(res);
            if (res.exists == false) {
                $("#btn-verify").prop('disabled' , false);
                $("#div-info-user").removeClass('d-none');
                $("#btn-create").removeClass('d-none');

            } else {
                Toast.fire({
                    icon: "error",
                    title: "Ya existe un usuario creado con el documento ingresado",
                });
                $("#btn-verify").prop('disabled' , false);
                $("#div-info-user").addClass('d-none');
                $("#btn-create").addClass('d-none');
                $("#document_number").val('');
                return false;
            }
        })
        .fail(function(res) {
            CallBackErrors(res);
            $("#btn-verify").prop('disabled' , false);
            $("#div-info-user").addClass('d-none');
            $("#btn-create").addClass('d-none');
        });

};

$("#document_type").on("change", function(){
    existsUser();
    if (($(this).val() >= 1 && $(this).val() <= 3) || $(this).val() == "")
    {
        $("#div-country").addClass('d-none');
    }
    else
    {
        $("#div-country").removeClass('d-none');
    }
});
</script>
@endpush
