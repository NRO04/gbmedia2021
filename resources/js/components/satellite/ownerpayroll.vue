<template>
	<div class="row">
		<div class="col-lg-12">
			<div class="card table-responsive">
				<div class="card-header">
					<div class="row">
						<div class="col-lg-4">
                            <span class="span-title">Resumen de Pago
                                <span class="text-danger">{{ owner.owner }}
                                    <span v-if="can('admin')">| old_id {{ owner.old_id }} <button class="btn btn-sm btn-outline-dark" @click="Recalculate">
                                        Recalculate</button></span>
                                </span>
                            </span>
						</div>
						<div class="form-horizontal col-lg-3">
							<div class="row">
								<label class="mr-2 mt-1">Fecha de Pago</label>
								<select v-model="payment_date" class="col-lg-5 form-control" name="payment_date" @change="changeDate">
									<option value=""></option>
									<option v-for="(date, index) in payment_dates" :key="index">{{ date.payment_date }}</option>
								</select>
							</div>
						</div>
                        <div class="col-lg-5" v-if="owner.status != 2">
                            <button v-if="can('satellite-payroll-commission-create')" class="btn btn-sm btn-success" data-target="#create-commission"
                                    data-toggle="modal" type="button">
                                <i class="fa fa-plus"></i> Suma
                            </button>
                            <button v-if="can('satellite-payroll-deduction-create')" class="btn btn-sm btn-secondary" data-target="#create-deduction"
                                    data-toggle="modal" type="button">
                                <i class="fa fa-plus"></i> Deducción
                            </button>
                            <button v-if="can('satellite-payment-generate-edit')" v-show="accumulated > 0 ||
                                        payment_date == ''" class="btn btn-sm btn-secondary" type="button" @click="createPayroll">
                                <i class="fa fa-dollar-sign"></i> Generar Pago
                            </button>
                            <button v-if="can('satellite-payment-earnings')" class="btn btn-sm btn-secondary" data-target="#details"
                                    data-toggle='modal' type="button"
                                    @click="getOwnerDetails">
                                <i class="fa fa-list-alt"></i> Resumen
                            </button>
                            <div class="btn-group float-right" v-if="payment_date != ''">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" id="dropdownMenuButton" type="button" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">Acciones</button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a :href="'/public/satellite/payment/statistic/owner/export?payment_date='+ payment_date+ '&owner_id=' +
                                        owner.id"
                                           class="dropdown-item">
                                            <i class="fa fa-file-excel text-success"></i>
                                            <span class="ml-2">Exportar</span>
                                        </a>
                                        <button class="dropdown-item" @click="buildAndSend" :disabled="disableBtn">
                                            <i class="fa fa-envelope"></i>
                                            <span class="ml-2">Enviar Email</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
<!--                            <button class="btn btn-sm btn-secondary ml-3" @click="buildAndSend" :disabled="disableBtn">Enviar Email</button>-->

                            <!--              <button class="btn btn-sm btn-secondary ml-3" @click="sendStatisticEmails">Enviar Email</button>-->
                        </div>
                        <div v-if="owner.status == 2" class="col-lg-12 bg-danger text-center p-3 mt-2">
                            <h3>Este propietario esta vetado</h3>
                        </div>
                    </div>
                </div>
				<div class="card-body">
					<div class="row">

						<!--Resumen del pago-->
						<div v-if="payroll" v-show="payment_date != ''" class="col-lg-12">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="bg-dark text-success">
										<th>Fecha Pago</th>
										<th>Periodo Rango</th>
										<th>Total $</th>
										<th>% GB</th>
										<th>% Pago</th>
										<th v-if='show_payment'>TRM</th>
										<th v-if='show_payment && show_transaction'>Transacción</th>
										<th v-if='show_payment && show_retention'>Retención</th>
										<th v-if='show_payment '>Valor Pago</th>
										<th>Acumular</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>{{ payment_payroll.payment_date }}</td>
										<td>{{ payment_payroll.payment_range }}</td>
										<td>{{ payment_payroll.total | dolares }}</td>
										<td>{{ payment_payroll.percent_gb | dolares}}</td>
										<td>{{ payment_payroll.percent_studio | dolares}}</td>
										<td v-if='show_payment '>{{ payment_payroll.trm | pesos}}</td>
										<td v-if='show_payment && show_transaction '>{{ payment_payroll.transaction | pesos}}</td>
										<td v-if='show_payment && show_retention '>{{ payment_payroll.retention | pesos}}</td>
										<td v-if='show_payment ' class="text-success">{{ payment_payroll.payment | pesos}}</td>
										<td><button v-if="last_payment_date && can('satellite-payment-generate-edit')"  class="btn btn-sm btn-dark"
                                                    type="button" @click="accumulate"><i
                                            class="fa fa-piggy-bank text-info"></i></button></td>
									</tr>
								</tbody>
							</table>
							<div class="accordion" role="tablist">
								<b-card class="mb-1" no-body>
									<b-card-header class="p-1" header-tag="header" role="tab">

										<b-button v-b-toggle.accordion-1 block variant="dark"><i class="fas fa-arrow-down mx-3"></i> Info de Pago <span class="text-danger"> - {{ payment_payroll.payment_methods.name }} </span></b-button>
									</b-card-header>
									<b-collapse id="accordion-1" accordion="my-accordion" role="tabpanel">
										<b-card-body>
											<!-- SIN FORMA DE PAGO -->
											<div v-if="payment_payroll.payment_methods_id == 1">
												<h5>No tiene forma de pago para esta fecha</h5>
											</div>
											<!-- BANCO -->
											<table v-else-if="payment_payroll.payment_methods_id == 2 || payment_payroll.payment_methods_id == 6 || payment_payroll.payment_methods_id == 7 || payment_payroll.payment_methods_id == 9" class="table table-striped">
											 	<thead>
											 		<tr>
											 			<th>Banco</th>
													 	<th>Titular</th>
													 	<th>Identificación</th>
													 	<th>Tipo Cuenta</th>
													 	<th>Nro Cuenta</th>
													 	<th>Ciudad</th>
											 		</tr>
											 	</thead>
											 	<tbody>
											 		<tr>
											 			<td v-if="payment_payroll.payment_methods_id == 7">{{ payment_payroll.bank_usa }}</td>
											 			<td v-else>{{(payment_payroll.global_bank == null)? "" : payment_payroll.global_bank.name }}</td>
											 			<td>{{ payment_payroll.holder }}</td>
											 			<td>{{ (payment_payroll.document_type !== null)?
                                                            payment_payroll.global_document.name_simplified +" / "+
                                                        payment_payroll.document_number : payment_payroll.document_number }}</td>
											 			<td>{{ (payment_payroll.account_type == 1)? "Ahorros" : "Corriente" }}</td>
											 			<td>{{ payment_payroll.account_number }}</td>
											 			<td>{{ payment_payroll.city_id }}</td>
											 		</tr>
											 	</tbody>
											</table>
											<!-- EFECTY / CHEQUE SIN RETENCION-->
											<table v-else-if="payment_payroll.payment_methods_id == 3 || payment_payroll.payment_methods_id == 5" class="table table-striped">
											 	<thead>
											 		<tr>
													 	<th>Nombre</th>
													 	<th>Identificación</th>
													 	<th>Dirección</th>
													 	<th>Banco</th>
													 	<th>Telefono</th>
											 		</tr>
											 	</thead>
											 	<tbody>
											 		<tr>
											 			<td>{{ payment_payroll.holder }}</td>
											 			<td>{{ payment_payroll.document_number  }}</td>
											 			<td>{{ (payment_payroll.account_type == 1)? "Ahorros" : "Corriente" }}</td>
											 			<td>{{ payment_payroll.address }}</td>
											 			<td>{{ payment_payroll.phone }}</td>
											 		</tr>
											 	</tbody>
											</table>
											<!-- PAXUM -->
											<div v-if="payment_payroll.payment_methods_id == 4">
												<h5>Usuario: {{ payment_payroll.holder }}</h5>
											</div>
											<!-- WESTERN UNION -->
											<table v-else-if="payment_payroll.payment_methods_id == 8" class="table table-striped">
											 	<thead>
											 		<tr>
													 	<th>Titular</th>
													 	<th>Identificación</th>
													 	<th>Ciudad</th>
													 	<th>Dirección</th>
													 	<th>Teléfono</th>
													 	<th>Pais</th>
											 		</tr>
											 	</thead>
											 	<tbody>
											 		<tr>
											 			<td>{{ payment_payroll.holder }}</td>
											 			<td>{{ (payment_payroll.document_type !== null)?
                                                            payment_payroll.global_document.name_simplified +" / "+
                                                            payment_payroll.document_number : payment_payroll.document_number  }}</td>
											 			<td>{{ payment_payroll.city_id }}</td>
											 			<td>{{ payment_payroll.address }}</td>
											 			<td>{{ payment_payroll.phone }}</td>
											 			<td>{{ payment_payroll.country }}</td>
											 		</tr>
											 	</tbody>
											</table>
										</b-card-body>
									</b-collapse>
								</b-card>
							</div>

						</div>

						<div v-else v-show="payment_date != ''" class="col-lg-12">
							<div class="alert alert-warning text-danger" role="alert">Existe un acumulado de: {{ accumulated }} </div>
						</div>

						<!--Cuentas asociadas-->
						<div v-for="(res, index) in payment_accounts" v-show="payment_date != ''" class="col-lg-12 mt-2">
							<div class="bg-dark p-2"><span class="text-success">Fecha Pago: {{ res.payment_date }}</span></div>
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="bg-secondary">
										<th style="width: 15%">Pagina</th>
										<th style="width: 15%">Nick</th>
										<th style="width: 10%">Valor(USD)</th>
										<th>Descripcion</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(account, index) in res.resume">
										<td>{{ account.page }}</td>
										<td>{{ account.nick }}</td>
										<td>{{ account.amount }}</td>
										<td v-if="account.page != 'XLoveCam'">{{ account.description }}</td>
                                        <td v-else>{{ account.description }} <i class='text-info fa fa-eye ml-2'
                                                                                data-target='#modalXloveDescription' data-toggle='modal'
                                                                                @click="convertDescription(account.description_xlove)"></i></td>

									</tr>
								</tbody>
							</table>
						</div>

						<!--Sumas Asignadas-->
						<div v-if="assigned_commissions.length > 0" v-show="payment_date != ''" class="col-lg-12 mt-2">
							<div class="bg-dark p-2"><span class="text-success">Sumas asignadas a esta fecha de pago</span></div>
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="bg-secondary">
										<th>Fecha Asignación</th>
										<th>Total</th>
										<th>Tipo</th>
										<th>Sumar a</th>
										<th>Descripcion</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(data, index) in assigned_commissions">
										<td>{{ moment(data.updated_at).format("YYYY-MM-DD") }}</td>
										<td>{{ data.amount }}</td>
										<td>
											<p v-if="data.assign_to == 0 || data.assign_to == 1">Dolares</p>
											<p v-else>Pesos</p>
										</td>
										<td>
											<p v-if="data.assign_to == 0">Total $</p>
											<p v-else-if="data.assign_to == 1">% Pago</p>
											<p v-else>Valor Pago</p>
										</td>
										<td>{{ data.description }}</td>
									</tr>

								</tbody>
							</table>
						</div>

						<!--Deducciones y abonos-->
						<div v-if="deductions.length > 0" class="col-lg-12 mt-2">
							<div class="bg-dark p-2"><span class="text-success">Deducciones y Abonos</span></div>
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="bg-secondary">
										<th>Fecha Creacion</th>
										<th style="width: 40%;">Comentario</th>
										<th>Total</th>
										<th>Abonos</th>
										<th>Debe</th>
										<th>Ultimo Abono</th>
										<th>Deduce de</th>
										<th>Abonar</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(row, index) in deductions">
										<td>{{ moment(row.created_at).format("YYYY-MM-DD") }}</td>
										<td style="width: 40%;">{{ row.description }}</td>
										<td>
											<p v-if="row.deduction_to == 0 || row.deduction_to == 1">{{ row.total | dolares}}</p>
											<p v-else>{{ row.total | pesos}}</p>
										</td>
										<td class="text-center">{{ (row.times_paid == 0)? "-----" : row.times_paid }}</td>
										<td class="text-danger">
											<p v-if="row.deduction_to == 0">{{ row.amount | dolares}}</p>
											<p v-else-if="row.deduction_to == 1">{{ row.amount | dolares}}</p>
											<p v-else>{{ row.amount | pesos}}</p>
										</td>
										<td class="text-center">{{ (row.last_pay == null)? "-----" : row.last_pay }}</td>
										<td>
											<p v-if="row.deduction_to == 0">Total $</p>
											<p v-else-if="row.deduction_to == 1">% Pago</p>
											<p v-else>Valor Pago</p>
										</td>
										<td>
											<button v-if="can('satellite-payroll-deduction-edit')" :class="(last_payment_date)?
											'btn btn-sm btn-success' : 'btn btn-sm btn-danger'" data-target="#create-paydeduction"
                                                    data-toggle="modal"  type="button" @click="payDeduction(row)"><i
                                                class="fas fa-hand-holding-usd"></i></button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<!--Sumas por asignar-->
						<div v-show="not_assigned_commissions.length > 0" class="col-lg-12 mt-2">
							<div class="bg-dark p-2">
                                <div class="row">
                                    <div class="col-6">
                                        <span class="text-success">Sumas por asignar</span>
                                    </div>
                                    <div class="col-6">
                                        <button v-if="last_payment_date && payroll && can('satellite-payroll-commission-edit')"
                                                class="btn btn-sm btn-outline-success float-right" data-target="#assign-massive-commission"
                                                data-toggle="modal">Asignacion Masiva</button>
                                    </div>
                                </div>
                            </div>
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr class="bg-secondary">
										<th>Fecha Creacion</th>
										<th>Total</th>
										<th>Tipo</th>
										<th>Sumar a</th>
										<th>Descripcion</th>
										<th>Asignar</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(data, index) in not_assigned_commissions">
										<td>{{ moment(data.created_at).format("YYYY-MM-DD") }}</td>
										<td class="text-success">
											<p v-if="data.assign_to == 0">{{ data.amount | dolares}}</p>
											<p v-else-if="data.assign_to == 1">{{ data.amount | dolares}}</p>
											<p v-else>{{ data.amount | pesos}}</p>
										</td>
										<td>
											<p v-if="data.assign_to == 0 || data.assign_to == 1">Dolares</p>
											<p v-else>Pesos</p>
										</td>
										<td>
											<p v-if="data.assign_to == 0">Total $</p>
											<p v-else-if="data.assign_to == 1">% Pago</p>
											<p v-else>Valor Pago</p>
										</td>
										<td>{{ data.description }}</td>
										<td><button v-if="last_payment_date && payroll && can('satellite-payroll-commission-edit')"
                                                    class="btn btn-sm btn-success" type="button" :disabled="btn_assign_commission"
                                                    @click="assignCommission(data.id, index)"><i class="fas fa-plus"></i></button></td>
									</tr>
								</tbody>
							</table>
						</div>

					</div>
				</div>
			</div>
		</div>

		<!-- Modal Commission-->
		<div id="create-commission" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark modal-lg">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Crear Suma para <span class="text-danger">{{ owner.owner }}</span></h4>
		            </div>
		            <div class="modal-body">
		            	<form>

		            		<div class="row">
				            	<div class="form-group col-md-4">
                                    <label for="amount">Valor</label>
                                    <input id="c_amount" v-model="commission.c_amount" class="form-control" name="c_amount" placeholder="100000" type="text">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="second_name">Tipo</label>
                                    <select v-model="commission.type" class="form-control" name="" @change="changeTypeCommission">
                                    	<option :value="0">Dolares</option>
                                    	<option :value="1">Pesos</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="second_name">Sumar a</label>
                                    <select v-if="commission.type == 0" v-model="commission.c_assign_to" class="form-control" name="">
                                    	<option :value="0">Total $</option>
                                    	<option :value="1">% Pago</option>
                                    </select>
                                    <input v-else class="form-control" readonly type="" value="Valor Pago">
                                </div>

                                 <div class="form-group col-md-12">
                                    <label for="second_name">Comentario</label>
                                    <textarea v-model="commission.c_description" class="form-control" name="c_description"></textarea>
                                </div>
			            	</div>

		            	</form>
		            </div>
		            <div class="modal-footer">
		            	<button v-if="btnCommission" id='btn-trm' class="btn btn-success btn-sm float-right m-1" @click="createCommission">Aceptar</button>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- Modal Deduction-->
		<div id="create-deduction" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark modal-lg">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Crear Deducción para <span class="text-danger">{{ owner.owner }}</span></h4>
		            </div>
		            <div class="modal-body">
		            	<form>

		            		<div class="row">
				            	<div class="form-group col-md-4">
                                    <label for="amount">Valor</label>
                                    <input id="d_amount" v-model="deduction.d_amount" class="form-control" name="d_amount" placeholder="100000" type="text">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="second_name">Tipo</label>
                                    <select v-model="deduction.type" class="form-control" name="" @change="changeTypeDeduction">
                                    	<option :value="0">Dolares</option>
                                    	<option :value="1">Pesos</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="second_name">Deducir a</label>
                                    <select v-if="deduction.type == 0" v-model="deduction.d_deduction_to" class="form-control" name="">
                                    	<option :value="0">Total $</option>
                                    	<option :value="1">% Pago</option>
                                    </select>
                                    <input v-else class="form-control" name="" readonly type="" value="Valor Pago">
                                </div>

                                 <div class="form-group col-md-12">
                                    <label for="second_name">Comentario</label>
                                    <textarea v-model="deduction.d_description" class="form-control" name="d_description"></textarea>
                                </div>
			            	</div>

		            	</form>
		            </div>
		            <div class="modal-footer">
		            	<button v-if="btnDeduction" id='btn-trm' class="btn btn-success btn-sm float-right m-1" @click="createDeduction">Aceptar</button>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- Modal PayDeduction-->
		<div id="create-paydeduction" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark modal-xl">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Abonar a deducción de <span class="text-danger">{{ owner.owner }}</span></h4>
		            </div>
		            <div class="modal-body">

		            	<div v-if="deduction_info.status == 1" class="alert alert-info text-center">
		            		<h5>Deducción Finalizada!!!.</h5>
		            	</div>
		            	<div v-else-if="last_payment_date == false" class="alert alert-info text-center">
		            		<h5>Solo podrá hacer abonos en la última fecha de pago.</h5>
		            	</div>
		            	<div v-else-if="payroll == false" class="alert alert-info text-center">
		            		<h5>El propietario esta acumulando en esta fecha, no se puede abonar!!!.</h5>
		            	</div>
	            		<div v-else class="row col-lg-7 pt-3 mb-2 ml-0 border" >
			            	<div class="form-group col-md-10">
			            		<div class="row">
			            			<label class="col-lg-4" for="amount">Valor a Abonar</label>
                                	<div class="col-lg-6">
                                		<input id="pd_amount" v-model="paydeduction.pd_amount" class="form-control" name="pd_amount" placeholder="100000" type="text">
                                	</div>
			            		</div>
                            </div>
                            <div class="col-lg-2">
                            	<button v-if="btnPayDeduction" id='btn-trm' class="btn btn-success btn-sm m-1" @click="createPayDeduction">Aceptar</button>
                            </div>
		            	</div>

	            		<table v-if="deduction_info.id" class="table table-striped table-bordered">
							<thead>
								<tr class="bg-secondary">
									<th style="width: 40%;">Comentario</th>
									<th>Total</th>
									<th>Abonos</th>
									<th>Debe</th>
									<th>Ultimo Abono</th>
									<th>Deduce de</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="width: 40%;">{{ deduction_info.description }}</td>
									<td>
										<p v-if="deduction_info.deduction_to == 0 || deduction_info.deduction_to == 1">{{ deduction_info.total | dolares}}</p>
										<p v-else>{{ deduction_info.total | pesos}}</p>
									</td>
									<td>{{ (deduction_info.times_paid == 0)? "-----" : deduction_info.times_paid }}</td>
									<td class="text-danger">
										<p v-if="deduction_info.deduction_to == 0">{{ deduction_info.amount | dolares}}</p>
										<p v-else-if="deduction_info.deduction_to == 1">{{ deduction_info.amount | dolares}}</p>
										<p v-else>{{ deduction_info.amount == null ? "" : deduction_info.amount | pesos}}</p>
									</td>
									<td>{{ (deduction_info.last_pay == null)? "-----" : deduction_info.last_pay }}</td>
									<td>
										<p v-if="deduction_info.deduction_to == 0">Total $</p>
										<p v-else-if="deduction_info.deduction_to == 1">% Pago</p>
										<p v-else>Valor Pago</p>
									</td>
								</tr>
							</tbody>
						</table>

						<table v-if="paydeductions.length" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="bg-secondary">
									<th>Fecha Creación</th>
									<th>Valor Abonado</th>
									<th>Abonado por</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(data, index) in paydeductions">
									<td>{{ moment(data.created_at).format("YYYY-MM-DD") }}</td>
									<td class="text-success">
										<p v-if="deduction_info.deduction_to == 0 || deduction_info.deduction_to == 1">{{ data.amount | dolares}}</p>
										<p v-else>{{ data.amount | pesos}}</p>
									</td>
									<td>{{ data.user}}</td>
								</tr>
							</tbody>
						</table>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- Modal Details-->
		<div id="details" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark modal-xl">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Detalles de <span class="text-danger">{{ owner.owner }}</span></h4>
		            </div>
		            <div class="modal-body">
	            		<table class="table table-striped table-bordered">
							<thead>
								<tr class="bg-secondary">
									<th>Ganancias GB</th>
									<th>Retención</th>
									<th>Deudas Boutique</th>
									<th>Status</th>
									<th>Deudas Cafeteria</th>
									<th>Deudas Préstamo</th>
									<th>Total Deudas Dolares</th>
									<th>Total Deudas Pesos</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-success">{{ details.earnings | pesos}}</td>
									<td class="text-danger">{{ details.retention | pesos}}</td>
									<td class="text-danger">{{ details.boutique | pesos}}</td>
									<td>{{ details.status_boutique }}</td>
									<td class="text-danger">{{ details.cafeteria | pesos}}</td>
									<td class="text-danger">{{ details.loans }}</td>
									<td class="text-danger">{{ details.total_dollar | dolares}}</td>
									<td class="text-danger">{{ details.total_pesos | pesos}}</td>
								</tr>
							</tbody>
						</table>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- Modal XloveDescription-->
		<div id="modalXloveDescription" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark modal-lg">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Detalles de Xlovecam</h4>
		            </div>
		            <div class="modal-body">
	            		<div class="text-center" v-html="xloveDescription"></div>
		            </div>
		        </div>
		    </div>
		</div>

        <!-- Modal Asignacion masiva de comisiones-->
		<div id="assign-massive-commission" class="modal fade" role="dialog">
		    <div class="modal-dialog modal-dark modal-lg">
		        <!-- Modal content-->
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title"> Asignacion masiva de comisiones</h4>
                        <button class="btn btn-sm btn-dark" @click="checkAllCommission"> Marcar Todas</button>
		            </div>
		            <div class="modal-body">
                        <div class="row justify-content-between">
                            <div class="col-lg-12" v-for="(data, index) in not_assigned_commissions" v-if="data.payment_date != null">
                                    <input type="checkbox" v-model="data.massive" class="ckeckbox-commission">
                                    <span>{{ data.description }}</span>
                            </div>
                        </div>

		            </div>
                    <div class="modal-footer">
                        <button v-if="btn_massive_commission" class="btn btn-success btn-sm float-right m-1" @click="assignMassiveCommission">
                            Aceptar</button>
                    </div>
		        </div>
		    </div>
		</div>

	</div>
</template>
<script>
	import moment from "moment";
	import"moment/locale/es";
	import * as helper from '../../../../public/js/vue-helper.js'

	export default{
		name: "ownerpayroll",
		props : ['owner', 'permissions'],
		data(){
			return{
				payment_date: "",
				payment_dates: [],
				payroll : false,
				last_payment_date : false,
				accumulated : 0,
				payment_payroll : {},
                show_transaction : true,
                show_retention : true,
                show_payment : true,
				payment_accounts : [],
				assigned_commissions : [],
				not_assigned_commissions : [],
				deductions : [],
				moment : moment,
				commission : {
					c_amount : "",
					type : 0,
					c_assign_to : 0,
					c_description : "",
					owner_id : this.owner.id,
				},
                btn_assign_commission: false,
                btn_massive_commission: true,
				deduction : {
					d_amount : "",
					type : 0,
					d_deduction_to : 0,
					d_description : "",
					owner_id : this.owner.id,
				},
				paydeduction : {
					payment_date : "",
					owner_id : this.owner.id,
					deduction_id : 0,
					payroll_id : "",
					pd_amount : "",
				},
				deduction_info: {},
				paydeductions: {},
				details: {},
				xloveDescription: "",
        disableBtn: false
			}
		},
		computed:{
			btnCommission(){
				return this.commission.c_amount && this.commission.c_description;
			},
			btnDeduction(){
				return this.deduction.d_amount && this.deduction.d_description;
			},
			btnPayDeduction(){
				return this.paydeduction.pd_amount;
			},
		},
		mounted(){
			this.getPaymentDates();
			this.getNotAssignedCommissions();
			this.getDeductions();
		},
		methods: {
            can(permission_name) {
              return this.permissions.indexOf(permission_name) !== -1;
            },
			getPaymentDates(){
				axios.get(route('satellite.payment.get_owner_payment_dates', {owner_id : this.owner.id})).then(response => {
					this.payment_dates = response.data;
				})
			},
			changeDate(){
				if (this.payment_date != "") {
					this.getPaymentPayroll();
					this.getPaymentAccounts();
				}
				else{
					this.last_payment_date = false;
				}
				this.getDeductions();
				this.getNotAssignedCommissions();
			},
			getPaymentPayroll(){
				axios.get(route('satellite.payment.get_owner_payment_payroll'), {
					params: {
						owner_id : 	this.owner.id,
						payment_date : this.payment_date
					}
				}).then(response => {
					this.payment_payroll = response.data.payment_payroll[0];
					this.payroll = response.data.payroll;
					this.last_payment_date = response.data.last_payment_date;
					this.accumulated = response.data.accumulated;
					if (this.payroll) {
						this.getAssignedCommissions();
					}
					else{
						this.assigned_commissions = {};
					}

					this.show_payment = (this.payment_payroll.payment_methods_id == 4 || this.payment_payroll.payment_methods_id == 7)? false : true;
				})
			},
			getPaymentAccounts(){
				axios.get(route('satellite.payment.get_owner_payment_accounts'), {
					params: {
						owner_id : 	this.owner.id,
						payment_date : this.payment_date
					}
				}).then(response => {
					this.payment_accounts = response.data.payment_accounts;
				})
			},
			getNotAssignedCommissions(){
				axios.get(route('satellite.payment.get_not_assigned_commission'), {
					params: {
						owner_id : 	this.owner.id,
					}
				}).then(response => {
					this.not_assigned_commissions = response.data;
				})
			},
			getAssignedCommissions(){
				axios.get(route('satellite.payment.get_assigned_commission'), {
					params: {
						owner_id : this.owner.id,
						payroll_id : this.payment_payroll.id,
					}
				}).then(response => {
					this.assigned_commissions = response.data;
				})
			},
			getDeductions(){
				this.deductions = [];
				axios.get(route('satellite.payment.get_owner_deductions'), {
					params: {
						owner_id : this.owner.id,
						payment_date : this.payment_date,
					}
				}).then(response => {
					this.deductions = response.data;
				})
			},
			getOwnerDetails(){
				this.deductions = [];
				axios.get(route('satellite.payment.get_owner_details'), {
					params: {
						owner_id : this.owner.id,
					}
				}).then(response => {
					this.details = response.data[0];
				})
			},
			changeTypeCommission(){
				if (this.commission.type == 1) {
					this.commission.c_assign_to = 2;
				}
				else{
					this.commission.c_assign_to = 0;
				}
			},
			changeTypeDeduction(){
				if (this.deduction.type == 1) {
					this.deduction.d_deduction_to = 2;
				}
				else{
					this.deduction.d_deduction_to = 0;
				}
			},
			createCommission(){
				helper.VUE_ResetValidations();
				axios.post(route('satellite.payment.create_commission'), this.commission).then(response => {
					if (response.data.success) {
						Toast.fire({
					        icon: "success",
					        title: "La suma ha sido creada exitosamente",
					    });
					    this.not_assigned_commissions.push(response.data.commission);
					    this.commission = {
							c_amount : "",
							type : 0,
							c_assign_to : 0,
							c_description : "",
							owner_id : this.owner.id,
						};
					}
					else{
						Toast.fire({
					        icon: "error",
					        title: "Ha ocurrido un error, comuniquese con el ADMIN",
					    });
					}
				})
				.catch(error => {
					helper.VUE_CallBackErrors(error.response);
			        Toast.fire({
		                icon: "error",
		                title: "Verifique la informacion de los campos",
		            });
				});
			},
			createDeduction(){
				helper.VUE_ResetValidations();
				axios.post(route('satellite.payment.create_deduction'), this.deduction).then(response => {
					if (response.data.success) {
						Toast.fire({
					        icon: "success",
					        title: "La deducción ha sido creada exitosamente",
					    });
					    this.deductions.push(response.data.deduction);
					    this.deduction = {
							d_amount : "",
							type : 0,
							d_deduction_to : 0,
							d_description : "",
							owner_id : this.owner.id,
						};
					}
					else{
						Toast.fire({
					        icon: "error",
					        title: "Ha ocurrido un error, comuniquese con el ADMIN",
					    });
					}
				})
				.catch(error => {
					helper.VUE_CallBackErrors(error.response);
			        Toast.fire({
		                icon: "error",
		                title: "Verifique la informacion de los campos",
		            });
				});
			},
			payDeduction(data){
				this.deduction_info = data;
				axios.get(route('satellite.payment.get_paydeductions'), {
					params: {
						deduction_id : 	data.id,
					}
				}).then(response => {
					console.log(response.data);
					this.paydeductions = response.data.paydeductions;
					this.paydeduction.deduction_id = data.id;
					this.paydeduction.payroll_id = this.payment_payroll.id;
					this.paydeduction.payment_date = this.payment_date;
				})
			},
			createPayDeduction(){
				helper.VUE_ResetValidations();
				axios.post(route('satellite.payment.create_paydeduction'), this.paydeduction).then(response => {
					if (response.data.success) {
						Toast.fire({
					        icon: "success",
					        title: "Se ha abonado exitosamente",
					    });
					    console.log(response.data.paydeduction);
					    console.log(response.data.deduction);
					    this.paydeductions.unshift(response.data.paydeduction);
					    this.deduction_info = response.data.deduction;
					    this.payment_payroll = response.data.payment_payroll;
					    this.paydeduction.pd_amount = "";
						this.getDeductions();
					}
					else{
						if(response.data.can_pay == false){
							Toast.fire({
						        icon: "error",
						        title: "No se pudo abonar, el valor supera la cantidad que debe de la deducción",
						    });
					    }
					    else if(response.data.can_pay_payroll == false){
							Toast.fire({
						        icon: "error",
						        title: "No se pudo abonar esa cantidad porque el valor a pagar quedaría negativo",
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
				})
				.catch(error => {
					helper.VUE_CallBackErrors(error.response);
			        Toast.fire({
		                icon: "error",
		                title: "Verifique la informacion de los campos",
		            });
				});
			},
            checkAllCommission(){
                for(let i = 0; i < this.not_assigned_commissions.length; i++)
                {
                    if (this.not_assigned_commissions[i].payment_date != null)
                    {
                        this.not_assigned_commissions[i].massive = true;
                    }
                }
                $(".ckeckbox-commission").each(function(){
                    $(this).prop("checked", true);
                });
            },
			assignMassiveCommission(){
                this.btn_massive_commission = false;
                let flag = false;
                let massive_index = [];
                for(let i = 0; i < this.not_assigned_commissions.length; i++)
                {
                    if (this.not_assigned_commissions[i].hasOwnProperty('massive'))
                    {
                        if (this.not_assigned_commissions[i].massive)
                        {
                            massive_index.push(this.not_assigned_commissions[i].id);
                            flag = true;
                        }
                    }
                }
                if (flag)
                {
                    axios.post(route('satellite.payment.assign_massive_commission'), {
                        not_assigned_commissions: this.not_assigned_commissions,
                        payroll_id: this.payment_payroll.id,
                    })
                    .then(response => {
                        if (response.data.success) {
                            Toast.fire({
                                icon: "success",
                                title: "Se han asignado las comisiones exitosamente",
                            });

                            for(let i = 0; i < response.data.massive.length; i++)
                            {
                                this.assigned_commissions.push(response.data.massive[i]);
                            }
                            for(let i = 0; i < massive_index.length; i++)
                            {
                                for (let j = 0; j < this.not_assigned_commissions.length; j++)
                                {
                                    if (this.not_assigned_commissions[j].id == massive_index[i])
                                    {
                                        this.not_assigned_commissions.splice(j, 1);
                                    }
                                }

                            }

                            this.payment_payroll = response.data.payroll;
                            this.btn_assign_commission = false;
                            this.btn_massive_commission = true;
                        }
                        else
                        {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error, comuniquese con el ADMIN",
                            });
                            this.btn_massive_commission = true;
                        }
                    })
                    .catch(error => {
                        helper.VUE_CallBackErrors(error.response);
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error, comuniquese con el ADMIN",
                        });
                        this.btn_massive_commission = true;
                    });

                }
                else
                {
                    Toast.fire({
                        icon: "error",
                        title: "Debe elegir al menos una fecha",
                    });
                    this.btn_massive_commission = true;
                }

			},
            assignCommission(id, index){
                this.btn_assign_commission = true;
                axios.post(route('satellite.payment.assign_commission'), {
                    commission_id: id,
                    payroll_id: this.payment_payroll.id,
                })
                    .then(response => {
                        if (response.data.success) {
                            Toast.fire({
                                icon: "success",
                                title: "Se ha asignado la suma exitosamente",
                            });
                            this.assigned_commissions.push(response.data.commission);
                            this.payment_payroll = response.data.payroll;
                            this.not_assigned_commissions.splice( index, 1 );
                            this.btn_assign_commission = false;
                        }
                        else
                        {
                            Toast.fire({
                                icon: "error",
                                title: "Verifique la informacion de los campos",
                            });
                            this.btn_assign_commission = false;
                        }
                    })
                    .catch(error => {
                        helper.VUE_CallBackErrors(error.response);
                        Toast.fire({
                            icon: "error",
                            title: "Verifique la informacion de los campos",
                        });
                        this.btn_assign_commission = false;
                    });
            },
			accumulate(){
				axios.post(route('satellite.payment.acumulate_payment'), {
						payroll_id : this.payment_payroll.id,
						owner_id : this.owner.id,
				})
				.then(response => {
					if (response.data.success) {
						Toast.fire({
			                icon: "success",
			                title: "El propietario se ha puesto a acumular",
			            });
						this.getPaymentPayroll();
						this.assigned_commissions = {};
						this.getDeductions();
						this.getNotAssignedCommissions();
						this.getPaymentDates();
						this.payment_date = "";
					}
					else
					{
						Toast.fire({
			                icon: "error",
			                title: "Se ha producido un error, comuniquese con el ADMIN",
			            });
					}
				})
				.catch(error => {
					helper.VUE_CallBackErrors(error.response);
			        Toast.fire({
		                icon: "error",
		                title: "Verifique la informacion de los campos",
		            });
				});
			},
			createPayroll(){
				SwalGB.fire({
                        title: "Atención el pago se generará en la ultima fecha de pago",
                        text: "Hagámoslo !!!",
                        icon: "info",
                        showCancelButton: true,
                }).then(result => {
                    if (result.isConfirmed)
                    {
                        axios.post(route('satellite.payment.create_payroll'), {owner_id : this.owner.id})
						.then(response => {

							if (response.data.success) {
								Toast.fire({
					                icon: "success",
					                title: "Se ha generado el pago exitosamente",
					            });
					            console.log(response.data);
								this.payment_date = response.data.payment_date;
					            this.getPaymentDates();
					            this.getPaymentPayroll();
								this.getPaymentAccounts();
								this.getNotAssignedCommissions();
								this.getDeductions();
							}
							else if(response.data.exists)
							{
								Toast.fire({
					                icon: "error",
					                title: "El propietario ya tiene pago en la última fecha",
					            });
							}
							else if(response.data.trm_null)
							{
								Toast.fire({
					                icon: "error",
					                title: "Una ves que asigne la TRM a esta fecha podrá generar el pago",
					            });
							}
							else
							{
								Toast.fire({
					                icon: "error",
					                title: "Se ha producido un error, comuniquese con el ADMIN",
					            });
							}
						})
						.catch(error => {
							helper.VUE_CallBackErrors(error.response);
					        Toast.fire({
				                icon: "error",
				                title: "Verifique la informacion de los campos",
				            });
						});
                    }
                });
			},
			convertDescription(data_html){
			    console.log(data_html);
                this.xloveDescription = data_html;
			},
            sendStatisticEmails(){
        axios.post(route('satellite.payment.send_statistic_email'), {
            payment_date : this.payment_date,
            owner_id : this.owner.id,
        }).then(response => {
            if (response.data.success)
            {
                SwalGB.fire({
                    title: "Atención",
                    text:
                        "El email de estadísticas se estará enviando en segundo plano, " +
                        "el sistema intentará enviarlo al menos 3 veces en caso de fallar el envio, " +
                        "si el error persiste se le notificará.",
                    icon: "info",
                    showCancelButton: false,
                });
            }
            else{
                Toast.fire({
                    icon: "error",
                    title: "Se ha producido un error, comuniquese con el ADMIN",
                });
            }
        })
      },
            buildAndSend(){
                const url = route('satellite.buildAndSend');
                this.disableBtn = true
                axios.post(url, {
                    payment_date: this.payment_date,
                    owner_id: this.owner.id
                }).then((response) => {

                    if (response.data.code === 200) {
                        Toast.fire({
                            icon: "success",
                            title: "email has been sent",
                        });
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: response.data.msg,
                        });
                    }

                    this.disableBtn = false
                }).catch((error) => {
                    this.disableBtn = false
                })
            },
            Recalculate(){
                const url = route('satellite.payment.calculate_payroll');
                axios.post(url, {
                    payment_date: this.payment_date,
                    owner_id: this.owner.id
                }).then((response) => {
                    if (response.data.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Recalculated OK",
                        });
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Error",
                        });
                    }

                }).catch((error) => {
                    Toast.fire({
                        icon: "error",
                        title: "Error",
                    });
                })
            }
            }


	}
</script>
