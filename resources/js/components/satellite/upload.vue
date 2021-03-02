<template>
	<div class="row">
		<div class="col-lg-12">
			<div class="card table-responsive">
				<div class="card-header">
					<div class="row">
						<div class="col-lg-4">
						    <span class="span-title">Subir Archivos de Pago</span>
						</div>
						<!-- <div class="form-horizontal col-lg-5">
							<div class="row">
								<label class="mr-2 mt-1">Fecha de Pago</label>
								<input class="form-control col-lg-6 mr-2" type="date" id='payment_date' name='payment_date' v-model="payment_date" @change="getPages" :class="(payment_date == null)? 'is-invalid' : ''">
							</div>
						</div> -->
<!--						<div class="col-lg-2">
							<button class="btn btn-success" @click="createCommisionForReceiver">Comision</button>
						</div>-->

						<div class="col-lg-3">
							<VueCtkDateTimePicker
								onlyDate
								dark
								noHeader
								noLabel
								noButton
								v-model="payment_date"
								inputSize='sm'
								format="YYYY-MM-DD"
								formatted="ll"
                                autoClose
								label="Seleccione Fecha de Pago"
								:no-value-to-custom-elem="(true)"}>
								<input class="form-control" type="text" id='payment_date' name='payment_date' v-model="payment_date" :class="(payment_date == null)? 'is-invalid' : ''" placeholder="Seleccione Fecha de Pago">
							</VueCtkDateTimePicker>
						</div>
		            </div>
				</div>
				<div class="card-body">
					<form action="upload_submit" method="post" id="myForm" accept-charset="utf-8">
						<table class="table table-striped">
							<thead>
								<tr>
									<th style="width: 15%">Pagina</th>
									<th style="width: 10%">Euro</th>
									<th>Cargar</th>
									<th>Tipo</th>
									<th>Desc</th>
									<th style="width: 10%">Fecha1</th>
									<th style="width: 10%">Fecha2</th>
									<th style="width: 20%">Proceso</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for='(page, index) in pages' :key="index" :id='"tr-"+index' :class="(page.has_payment == 1)? 'bg-success' : '' ">
									<td>{{ page.name }}</td>
									<td><input type="text" class="form-control col-lg-12" :name="'euro-'+index" :id="'euro-'+index" v-show="page.has_euro == 1"></td>
									<td>
										<!-- <button type="button" class="btn btn-info btn-sm"><i class="fa fa-upload"></i></button> -->

										<div class="form-group m-0 divFile p-0">
											<div class="inputFile" :id='"div-file-"+index' v-if="page.has_payment == 0">
								                <span class="btnFile btn-dark btn-sm p-1">
								                    <i class="fa fa-upload pl-1" aria-hidden="true"></i>
								                </span>
								                <input class="form-control" style="left: 0" type="file" :name="index" :id="'file-'+index" @change='onFileChange'>
								            </div>
										</div>
									</td>
									<td>{{ page.type }}</td>
									<td>
										<div v-if="page.description != ''" style='cursor:pointer' title="Nick y Valor">
			                                <button type="button" class="btn btn-dark btn-sm"><i class="fa fa-question"></i></button>
			                            </div>
										<div v-else style='cursor:pointer' title='Click Me'>
			                                <button type="button" class="btn btn-dark btn-sm" data-toggle='modal' data-target='#modalImage' @click="modalImage(page.image)"><i class="fa fa-question"></i></button>
			                            </div>
									</td>
									<td>
										<input type="date" :name="'start_date-'+index" :id="'start_date-'+index" class="form-control" :value="page.start_date">
									</td>
									<td>
										<input type="date" :name="'end_date-'+index" :id="'end_date-'+index" class="form-control" :value="page.end_date">
									</td>
									<td>
										<span :id='"proccess-"+index' class="proccess" v-text="(page.has_payment == 1)? 'Completado!!!' : '' "></span>
										<div class="progress d-none" :id='"myProgress-"+index' >
											<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%" :id='"myBar-"+index'></div>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
				<div class="card-footer text-right">
						<button class="btn btn-success btn-sm" id="btn_accept" @click.prevent="uploadPayment"><i class="fa fa-check"></i> Aceptar
                        </button>
				</div>
			</div>
		</div>

		<!-- Modal -->
		<div id="modalImage" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark modal-xl">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Estructura del Archivo</h4>
		            </div>
		            <div class="modal-body">
		                <div class="container">
		                	<img :src="img_description" alt="" class="img-fluid">
		                </div>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- Modal -->
		<div id="modal-commission" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Creando Comisiones de Propietarios</h4>
		            </div>
		            <div class="modal-body">
		                <div class="container">
		                	<div class="progress">
								<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%" id="myBar-commission"></div>
							</div>
							<div class="row text-center" v-if="process_finish">
								<div class="col-lg-12">
									<p class="text-center">Proceso Finalizado Correctamente</p>
								</div>
							</div>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>

	</div>
</template>

<script>
	import * as helper from '../../../../public/js/vue-helper.js'
	export default{
		name: "upload",
		data(){
			return{
				pages: [],
				file: null,
				payment_date: null,
				img_description: "",
				process_finish: false,
			}
		},
		created(){
			this.getPages();
		},
		mounted(){
			Echo.private('payment').listen('.payment', (e) => {
					console.log(e);
					let width = e.percent + "%";
					if (e.percent > 99.7)
					{
						$("#myProgress-"+e.file).addClass("d-none");
						$("#proccess-"+e.file).text("Completado!!!");
						$("#tr-"+e.file).removeClass("bg-info");
						$("#tr-"+e.file).addClass("bg-success");
						$("#div-file-"+e.file).addClass("d-none");
						$("#file-"+e.file).val("");
					}
					else
					{
						$("#myBar-"+e.file).css("width" , width);
					}

				});

			Echo.private('payment_commission').listen('.payment_commission', (e) => {
					console.log(e);
					let width = e.percent + "%";
					if (e.percent > 99.7)
					{
						this.process_finish = true;
						$("#myBar-commission").css("width" , width);
						this.hideModal();
					}
					else
					{
						$("#myBar-commission").css("width" , width);
					}

				});
		},
        watch: {
            payment_date: function (val) {
                this.getPages();
            },
        },
		methods: {
			getPages(){
				axios.get(route('satellite.payment.get_pages', {payment_date : this.payment_date})).then(response => {
					console.log(response.data);
					this.pages = response.data;
					$('[data-toggle="tooltip"]').tooltip();
				})
			},
			onFileChange(event){
				let position = event.target.name;
				this.file = event.target.files[0];

				if (this.file == null) {
					$("#page-"+position).text("");
					$("#tr-"+position).removeClass("bg-info");
					this.pages[position]['has_file'] = 0;
				}
				else{
					$("#page-"+position).text("Pendiente");
					$("#tr-"+position).addClass("bg-info");
					this.pages[position]['has_file'] = 1;
				}

			},
			uploadPayment(){
			    $("#btn_accept").prop('disabled' , true);
				let flag = false;
				$('.invalid-feedback').remove();
				$('input[type="file"]').each(function() {
					if ($(this).val() != "")
					{
						flag = true;
						let index = $(this)[0].name;
						$("#myProgress-"+index).removeClass("d-none");
						if ($("#start_date-"+index).val() == "")
						{
							flag = false;
							$("#start_date-"+index).addClass('is-invalid');
							return false;
						}
						else
						{
							$("#start_date-"+index).removeClass('is-invalid');
						}
						if ($("#end_date-"+index).val() == "")
						{
							flag = false;
							$("#end_date-"+index).addClass('is-invalid');
							return false;
						}
						else
						{
							$("#end_date-"+index).removeClass('is-invalid');
						}

						if (flag) {
							$("#proccess-"+index).text("Leyendo...");
						}

					}
				});
				const config = {
					headers: {
						'content-type': 'multipart/form-data'
						}
				}

				if (flag && this.payment_date != null)
				{
					let formData = new FormData(document.getElementById("myForm"));
					formData.append('payment_date', $("#payment_date").val());
					formData.append('pages', JSON.stringify(this.pages));

					axios.post(route('satellite.payment.upload_payment'), formData, config).then(response => {
						console.log(response);
							if (response.data.success)
							{
								SwalGB.fire({
				                        title: "Se han subido los archivos exitosamente, espere mientras se crean las comisiones",
				                        text: "Hagámoslo !!!",
				                        icon: "success",
				                        showCancelButton: false,
				                }).then(result => {
				                    if (result.isConfirmed)
				                    {
				                        this.createCommisionForReceiver();
				                    }
				                });
                                $("#btn_accept").prop('disabled' , false);
							}
							else if(response.data.trm_null)
							{

								SwalGB.fire({
				                        title: "Error",
				                        text: "Existe una fecha de pago que no tiene TRM asignada, no se podrán subir estos archivos !!!",
				                        icon: "danger",
				                        showCancelButton: false,
				                }).then(result => {
				                    if (result.isConfirmed)
				                    {
				                        $('input[type="file"]').each(function() {
											if ($(this).val() != "")
											{
												let index = $(this)[0].name;
												$("#proccess-"+index).text("");
												$("#myProgress-"+index).addClass("d-none");
											}
										});
				                    }
				                });
                                $("#btn_accept").prop('disabled' , false);
							}
							else{
								Toast.fire({
							        icon: "error",
							        title: "Ha ocurrido un error, comuniquese con el ADMIN",
							    });
                                $("#btn_accept").prop('disabled' , false);
							}
						})
						.catch(error => {
							helper.VUE_CallBackErrors(error.response);
					        Toast.fire({
				                icon: "error",
				                title: "Verifique la informacion de los campos",
				            });
				            $('input[type="file"]').each(function() {
								if ($(this).val() != "")
								{
									let index = $(this)[0].name;
									$("#proccess-"+index).text("");
									$("#myProgress-"+index).addClass("d-none");
								}
							});

                            $("#btn_accept").prop('disabled' , false);
						});
				}
				else
				{
					$('input[type="file"]').each(function() {
						if ($(this).val() != "")
						{
							let index = $(this)[0].name;
							$("#proccess-"+index).text("");
							$("#myProgress-"+index).addClass("d-none");
						}
					});


					Toast.fire({
		                icon: "error",
		                title: "Verifique la informacion de los campos",
		            });

                    $("#btn_accept").prop('disabled' , false);
				}

			},
			createCommisionForReceiver(){
				$("#myBar-commission").css("width" , "0%");
				this.process_finish = false;
				$("#modal-commission").modal("show");
				let payment_date = this.payment_date;
				axios.post(route('satellite.payment.create_all_commission'), {payment_date : payment_date}).then(response => {
					console.log(response);
					if (response.data.success){
					    $("#modal-commission").modal('hide');
					}
					else{
						Toast.fire({
					        icon: "error",
					        title: "Ha ocurrido un error, comuniquese con el ADMIN",
					    });
					}
				})
				.catch(error => {
					Toast.fire({
				        icon: "error",
				        title: "Ha ocurrido un error, comuniquese con el ADMIN",
				    });
				});
			},
			modalImage(image){
				this.img_description = "/storage/GB/satellite/payment/upload_img/"+image;
			},
			hideModal(){
				$("#modal-commission").modal("toggle");
			}
		}
	}
</script>
