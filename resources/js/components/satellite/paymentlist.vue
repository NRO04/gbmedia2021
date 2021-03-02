<template>
	<div class="row">
		<div class="col-lg-8">
            <b-overlay :show="show" rounded="sm" variant="dark" opacity="0.8">
                <template #overlay>
                    <div class="text-center">
                        <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
                        <p id="cancel-label">Espere por favor...</p>
                    </div>
                </template>
			    <div class="card table-responsive">
				<div class="card-header">
					<div class="row">
						<div class="col-lg-5">
						    <span class="span-title">Resumen de Archivos Subidos</span>
						</div>
						<div class="form-horizontal col-lg-5">
							<div class="row">
								<label class="mr-2 mt-1">Fecha de Pago</label>
								<select name="payment_date" v-model="payment_date" class="col-lg-5 form-control" @change="getPayments">
									<option value=""></option>
									<option v-for="(date, index) in payment_dates" :key="index">{{ date.payment_date }}</option>
								</select>
							</div>
						</div>
						<div class="col-lg-2">
							TRM:
							<span class="text-success" v-if="trm !== null">{{ trm }}</span>

							<span data-toggle="modal" data-target="#modal-trm" style="cursor: pointer"
                                  v-if="trm === null && accounts.length > 0 && can('satellite-export-excel-edit')"><i
                                class="text-success fa fa-plus"></i> Asignar</span>
						</div>
		         </div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-lg-12">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>A quien</th>
										<th>Página</th>
										<th>Nick</th>
										<th>Valor</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for='(account, index) in accounts' :key="index" :id="'tr-'+account.id" :class="(account.owner == null)? 'text-danger' : '' ">
										<td :id="'td-owner-'+account.id">{{ account.owner }}</td>
										<td>{{ account.name }}</td>
										<td>{{ account.nick }}</td>
										<td>{{ account.amount }}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
            </b-overlay>
		</div>

		<div class="col-lg-4">
			<div class="card table-responsive">
				<div class="card-header">
					<div class="row">
						<div class="col-lg-12">
						    <span class="span-title">Propietarios</span>
						</div>
		         </div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-lg-12">
							<table class="table">
								<thead>
									<tr>
										<th>Propietario</th>
										<th>Total</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<tr v-for='(owner, index) in owners' :id="'owner-'+ owner.owner_id">
										<td>{{ owner.owner }}</td>
										<td :class="(owner.owner == null)? 'text-danger' : ''">{{ owner.total | round(2) }}</td>
										<td>
                                            <a v-if="owner.owner_id != null" type="button" class="btn btn-sm btn-success"
                                               :href="projectRoute('satellite.payment.view_payroll_owner',  {id : owner.owner_id})"
                                               target="_blank"><i class="fa fa-dollar-sign"></i></a>
                                        </td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal -->
		<div id="modal-trm" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Asignar TRM</h4>
		            </div>
		            <div class="modal-body">
		            	<div v-if="not_matching == 0">
			            	<div class="container">
			                	<input type="text" class="form-control" id="trm_value" name="trm_value" v-model='trm_value'>
			                </div>
			                <button class="btn btn-success btn-sm float-right m-3" v-show="btn_trm" id='btn-trm' @click="assignTRM">Aceptar</button>
		            	</div>
		            	<div v-else>
		            		<p class="text-danger">Todos las cuentas deben tener un propietario</p>
		            	</div>

		            </div>
		        </div>
		    </div>
		</div>

	</div>
</template>
<script>
	export default{
		name: "paymentlist",
        props: [
            'permissions',
        ],
		data(){
			return{
				trm: null,
				not_matching: 0,
				trm_value: 0,
				accounts: [],
				owners: [],
				file: null,
				payment_date: null,
				btn_trm: true,
				img_description: "",
				payment_dates: {},
                show: false,
			}
		},
		mounted(){
			Echo.private('payment_account').listen('.payment_account', (e) => {
					console.log(e);
					$("#tr-"+e.id).removeClass('text-danger');
					$("#tr-"+e.id).addClass('bg-success');
					$("#td-owner-"+e.id).text(e.owner_name);
					/*this.$refs['tr0'][0].classList.value = 'text-success';*/
					this.getOwnerPayments();
				});
			this.getPaymentsDate();
		},
		methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
			getPaymentsDate(){
				axios.get(route('satellite.payment.get_payment_dates')).then(response => {
					this.payment_dates = response.data.payment_dates;
				})
			},
			getPayments(){
                this.show = true;
				let payment_date = this.payment_date;
				if (payment_date != null) {
					axios.get(route('satellite.payment.get_payments', {payment_date : payment_date})).then(response => {

						this.accounts = response.data.payment_accounts;
                        console.log(this.accounts);
                        console.log(response.data);
						this.trm = response.data.trm;
						this.not_matching = response.data.not_matching;
                        this.show = false;
					})

					this.getOwnerPayments();
				}
			},
			getOwnerPayments(){
				let payment_date = this.payment_date;
				if (payment_date != null) {
					console.log("get owner payment");
					axios.get(route('satellite.payment.get_owner_payments', {payment_date : payment_date})).then(response => {
						this.owners = response.data;
					})
				}
			},
			assignTRM(){
				let payment_date = this.payment_date;
				let trm_value = this.trm_value;

				if (payment_date != null && trm_value > 0) {
					this.btn_trm = false;
					axios.post(route('satellite.payment.change_trm', {trm_value : trm_value, payment_date : payment_date})).then(response => {
						console.log(response.data);
						if (response.data.success)
							{
								Toast.fire({
							        icon: "success",
							        title: "Se ha asignado la TRM exitosamente",
							    });
							    this.trm = this.trm_value;
							    $("#modal-trm").modal('hide');
							}
							else{
								this.btn_trm = true;
								Toast.fire({
							        icon: "error",
							        title: "Ha ocurrido un error, comuniquese con el ADMIN",
							    });
							}
					})
					.catch(error => {
						this.btn_trm = true;
							Toast.fire({
							        icon: "error",
							        title: "Ha ocurrido un error, comuniquese con el ADMIN",
							    });
						});
				}
				else{
					Toast.fire({
				        icon: "error",
				        title: "Verifique el valor de la TRM",
				    });
				}
			}
		}
	}
</script>
