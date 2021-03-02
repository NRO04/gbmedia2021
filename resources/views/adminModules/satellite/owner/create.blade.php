@extends("layouts.app")
@section('content')
<div class="row">
	<div class="card col-lg-12 table-responsive">
		<div class="card-header">
			<div class="row">
				<div class="col-lg-12">
				    <span class="span-title">Crear Propietario </span>

				</div>
         </div>
		</div>
		<div class="card-body">
			<div class="row">

                <div class="col-lg-9">
            		<div class="card border-secondary">
            			<div class="card-body">
            			<form action="{{ route('satellite.store_owner')}}" class="form-row" method="post" id="form-create-owner" enctype="multipart/form-data">
							@csrf

	                        <div class="form-group col-md-6">
                                <label for="owner">Propietario</label>
                                <input type="text" class="form-control" name="owner" id="owner" placeholder="GrupoBedoya">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="email">Email Principal</label>
                                <input type="text" class="form-control" name="email" id="email" placeholder="email@gmail.com">
                            </div>

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
                                <label for="document_number">Nro Documento</label>
                                <input type="text" class="form-control" name="document_number" id="document_number" placeholder="11243254785">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="phone">Teléfono</label>
                                <input type="text" class="form-control" name="phone" id="phone" placeholder="3135025668">
                            </div>

                            <div class="form-group col-md-12">
                                <label>Otros correos Conocidos:</label>
                                <span class="small text-info">En caso que se haya comunicado desde otros correos</span>
                                <input type="text" class="form-control" name="others_emails" id="others_emails" placeholder="angel@mail.com, angel@hotmail.com">
                            </div>

                            <div class="form-group col-md-12">
                                <label>Correo para estadisticas:</label>
                                <span class="small text-info">Al enviar estadisticas solo llegaran a los correos que ingrese aqui <span class="smal text-danger">(solo si diferentes del email principal)</span></span>
                                <input type="text" class="form-control" name="statistics_emails" id="statistics_emails" placeholder="angel@mail.com, angel@hotmail.com">
                            </div>

                            <div class="form-group col-md-6">
                                <label>RUT</label>
                                <div class="col-sm-7 col-lg-6 inputFile pl-0">
					                <span class="btnFile btn-dark btn-sm">
					                    <span id="spanFile">Seleccionar Archivos</span>
					                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
					                </span>
					                <input id="rut" name="rut[]" type="file">
					            </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Cámara y Comercio</label>
                                <div class="col-sm-7 col-lg-6 inputFile pl-0">
					                <span class="btnFile btn-dark btn-sm">
					                    <span id="spanFile">Seleccionar Archivos</span>
					                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
					                </span>
					                <input id="chamber_commerce" name="chamber_commerce[]" type="file" multiple="">
					            </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Composición Accionaria</label>
                                <div class="col-sm-7 col-lg-6 inputFile pl-0">
					                <span class="btnFile btn-dark btn-sm">
					                    <span id="spanFile">Seleccionar Archivos</span>
					                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
					                </span>
                                    <input id="shareholder_structure" name="shareholder_structure[]" type="file" multiple="">
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Certificación Bancaria</label>
                                <div class="col-sm-7 col-lg-6 inputFile pl-0">
					                <span class="btnFile btn-dark btn-sm">
					                    <span id="spanFile">Seleccionar Archivos</span>
					                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
					                </span>
                                    <input id="bank_certification" name="bank_certification[]" type="file" multiple="">
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="first_name">Departamento</label>
                                <select class="form-control" id="department" name="department">
                                    <option value>Seleccione el departamento...</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id}}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="first_name">Ciudad</label>
                                <select class="form-control" name = "city" id ="city"></select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="address">Dirección</label>
                                <input type="text" class="form-control" name="address" id="address" placeholder="12345 Main St. Houston, TX 770083">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="neighborhood">Barrio</label>
                                <input type="text" class="form-control" name="neighborhood" id="neighborhood" placeholder="VillaGorgona">
                            </div>
                        </form>
                        <div>
                        	<button id="btn-create" type="button" class="btn btn-m btn-success float-right btn-sm"><i class="fa fa-plus"></i> Crear</button>
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
	$('#department').on('change', function () {
        let department = $(this).val();

        if(department != '')
        {
            $.ajax({
                url: "{{ route('satellite.get_cities') }}",
                type: 'GET',
                data: {department},
            })
            .done(function (res){
                $('#city').html("");
                let options = '';
                $.each(res, function(i, cities) {
                    let city_id = cities.id;
                    let city_name = cities.name;
                    options += '<option value="' + city_id + '">' + city_name + '</option>';
                });
                $('#city').append(options);
            });
        }
        else
        {
            $('#city').html("");
        }
    });

    $("#btn-create").on("click", function(){
        $("#btn-create").prop('disabled' , false);
        ResetValidations();
        formData = new FormData(document.getElementById('form-create-owner'));
        $.ajax({
            url: '{{ route('satellite.store_owner')}}',
            type: 'POST',
            processData: false,
            contentType: false,
            data: formData,
        })
        .done(function(res) {
            if (res.success) {
                SwalGB.fire({
                        title: "Se ha agregado el propietario exitosamente",
                        text: "Desea completar la otra información ?",
                        icon: "success",
                        showCancelButton: true,
                }).then(result => {
                    if (result.isConfirmed)
                    {
                        document.location.href= "/satellite/owner/edit/" + res.owner_id;
                    }
                    else
                    {
                        $("#btn-create").prop('disabled' , false);
                        ResetModalForm("#form-create-owner");
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
</script>
@endpush
