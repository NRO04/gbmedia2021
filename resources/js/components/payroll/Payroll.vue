<template>
    <div class="row">
        <div class="alert alert-danger" v-if="user.setting_role_id === 11">
            <ul>
                <li>falta solicitado en comisiones</li>
                <li>Docuemnto Soporte</li>
                <li>Docuemnto Soporte TI</li>
            </ul>
        </div>

        <div class="col-lg-12">
            <div class="card table-responsive">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <span class="span-title">Nomina
                                <span v-if="filter_payroll.quarter != 0" class="text-danger">{{ filter_payroll.quarter }}{{ (filter_payroll.quarter
                                    == 1)? "ra" :
                                    "da" }}
                                    quincena {{
                                    moment(filter_payroll.month + '-01-'+ filter_payroll.year).format('MMMM') }} {{ filter_payroll.year
                                    }}</span></span>
                        </div>
                        <div class="col-lg-3">
                            <select v-model="range" class="form-control form-control-sm" @change="getPayrolls">
                                <option :value="data.quarter+'-'+data.month+'-'+data.year" v-for="(data, index) in ranges">{{ data.range }}</option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <div class="btn-group float-right">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" id="dropdownMenuButton" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Exportar</button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" :href="projectRoute('payroll.increases_export', {quarter: filter_payroll.quarter, month:  filter_payroll.month, year: filter_payroll.year})" v-if="can('payroll-export-excel')">
                                            <i class="fa fa-file-excel text-success"></i>
                                            <span class="ml-2">Salarios y Aumentos</span>
                                        </a>
                                        <a class="dropdown-item" :href="projectRoute('payroll.pab_export', {quarter: filter_payroll.quarter, month:  filter_payroll.month, year: filter_payroll.year})" v-if="can('payroll-export-pab-format')">
                                            <i class="fa fa-file-excel text-success"></i>
                                            <span class="ml-2">Formato PAB</span>
                                        </a>
                                        <a class="dropdown-item" :href="projectRoute('payroll.pab_export_inactive', {quarter: filter_payroll.quarter,
                                        month:  filter_payroll.month, year: filter_payroll.year})" v-if="can('payroll-export-pab-format')">
                                            <i class="fa fa-file-excel text-success"></i>
                                            <span class="ml-2">Formato PAB con Inactivos</span>
                                        </a>
                                        <a class="dropdown-item" :href="projectRoute('payroll.boutique.payroll_boutique')" v-if="can('payroll-export-pab-format')">Descuentos Boutique</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <b-overlay :show="show" rounded="sm" variant="dark" opacity="0.8">
                    <template #overlay>
                        <div class="text-center">
                            <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
                            <p id="cancel-label">Espere por favor...</p>
                        </div>
                    </template>
                    <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <b-row class="mb-5 justify-content-between">
                                <b-col sm="2" md="2" class="my-1">
                                    <b-form-group class="mb-0">
                                        <b-form-select v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
                                    </b-form-group>
                                </b-col>
                                <b-col lg="4" class="my-1">
                                    <b-form-group class="mb-0">
                                        <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Type to Search" size="sm"></b-form-input>
                                    </b-form-group>
                                </b-col>
                            </b-row>

                            <!-- Main table element -->
                            <b-table show-empty small stacked="md" :items="payrolls" :fields="fields" :current-page="currentPage"
                                     :per-page="perPage"
                                     :filter="filter" :filter-included-fields="filterOn"
                                     :sort-by.sync="sortBy"
                                     @filtered="onFiltered" :busy ="isBusy">

                                <template #cell(icon)="row">
                                    <i title='click me' data-toggle='modal' data-target='#modal-payroll' style='cursor:pointer; color:#14A414;'
                                       class='fas fa-file-invoice-dollar' @click="setPayroll(row.item.payroll)"></i>
                                </template>

                                <template #cell(avatar)="row">
                                    <div class='c-avatar'><img class='c-avatar-img' :src='row.item.avatar'></div>
                                </template>

                                <template #cell(user)="row">
                                    <div>
                                        <span>{{ row.item.user }}</span><br>
                                        <span class='small text-muted'> {{ row.item.role }}</span>
                                    </div>
                                </template>

                                <template #cell(salary)="row">
                                    <span class='text-muted'>{{ row.item.salary | pesos }}</span>
                                </template>

                                <template #cell(salary_half)="row">
                                    <span class='text-muted'>{{ row.item.salary_half | pesos }}</span>
                                </template>

                                <template #cell(sums_amount)="row">
                                    <span class='text-success'>{{ row.item.sums_amount | pesos }}</span>
                                </template>

                                <template #cell(deductions_amount)="row">
                                    <span class='text-danger'>{{ row.item.deductions_amount | pesos }}</span>
                                </template>

                                <template #cell(amount)="row">
                                    <span>{{ row.item.amount | pesos }}</span>
                                </template>

                                <template #cell(bonus_extra)="row">
                                    <span>{{ row.item.bonus_extra | pesos }}</span>
                                </template>

                                <template #cell(last_increase)="row">
                                    <span class='text-muted'>{{ row.item.last_increase }}</span>
                                </template>

                            </b-table>
                            <b-row class="justify-content-end">
                                <b-col sm="12" md="12" lg="12" class="my-1">
                                    <b-pagination
                                        v-model="currentPage"
                                        :total-rows="totalRows"
                                        :per-page="perPage"
                                        size="sm"
                                        class="my-0 float-right"
                                    ></b-pagination>
                                </b-col>
                            </b-row>
                        </div>
                    </div>
                </div>
                </b-overlay>
            </div>
        </div>

        <!--Modal-->
        <div id="modal-payroll" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark  modal-lg mt-0">
                <!-- Modal content-->
                <div class="modal-content table-responsive">
                    <div class="modal-header">
                        <h4 class="modal-title">Recibo Nomina <span class="text-danger" v-text="payroll.user_fullname"></span></h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="" id="modal_user_id">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="card col-lg-6">
                                        <div class="row p-2">
                                            <div class="col-lg-6"><span>Cantidad Dias</span></div>
                                            <div class="col-lg-4">
                                                <input type="number" name="worked_days" class="form-control form-control-sm"
                                                       v-model="payroll.worked_days">
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" @click="saveDays"><i class="fa fa-check"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card col-lg-6">
                                        <div class="row p-2">
                                            <div class="col-lg-6"><span>Basico Quincenal</span></div>
                                            <div class="col-lg-6"><span class="text-success">{{ payroll.basic_quarter | pesos}}</span></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!--Devengado-->
                            <div class="card col-lg-6  mt-2">
                                <div class="row">
                                    <div class="col-lg-12  mt-2 mb-2"><h5 >Devengado</h5></div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Horas Extras</span></div>
                                            <div class="col-lg-4"><span class="text-success">{{ payroll.extra_hours | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Horas Extras',14)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Recargo Nocturno</span></div>
                                            <div class="col-lg-4"><span class="text-success">{{ payroll.night_surcharge | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Recargo Nocturno',1)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Recargo Nocturno',1)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Comisiones</span></div>
                                            <div class="col-lg-4"><span class="text-success">{{ payroll.commissions | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Comisiones',2)"><i
                                                    class="fa fa-list"></i></button></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Comisiones',2)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Auxilio Movilizacion</span></div>
                                            <div class="col-lg-4"><span class="text-success">{{ payroll.movilization_help | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Auxilio Movilizacion',3)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Auxilio Movilizacion',3)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Re-Record</span></div>
                                            <div class="col-lg-4"><span class="text-success">{{ payroll.record | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Re-Record',4)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Re-Record',4)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Bonificacion</span></div>
                                            <div
                                                class="col-lg-4"><span class="text-success">{{ payroll.bonus |
                                                pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Bonificacion',5)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Bonificacion',5)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Auxilio Transporte</span></div>
                                            <div
                                                class="col-lg-4"><span class="text-success">{{
                                                payroll.transportation_help | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Auxilio Transporte',6)"><i
                                                    class="fa fa-list"></i></button></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Auxilio Transporte',6)"><i
                                                    class="fa fa-plus"></i></button></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-2 mt-2 pt-2 pb-2 bg-success">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Total Devengado</span></div>
                                            <div class="col-lg-4"><span>{{ payroll.total_accrued | pesos }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--/Devengado-->

                            <!--Descuentos-->
                            <div class="card col-lg-6  mt-2">
                                <div class="row">
                                    <div class="col-lg-12 mt-2 mb-2"><h5 >Descuentos</h5></div>

                                    <div class="col-lg-12" style="margin-bottom: 17px;">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Seguridad Social</span></div>
                                            <div class="col-lg-4"><span class="text-danger">{{payroll.social_security | pesos}}</span></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Préstamo</span></div>
                                            <div class="col-lg-4"><span class="text-danger">{{payroll.loan | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Préstamo',15)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Cafeteria</span></div>
                                            <div class="col-lg-4"><span class="text-danger">{{payroll.food | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Cafeteria',7)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Cafeteria',7)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Nevera</span></div>
                                            <div class="col-lg-4"><span class="text-danger">{{payroll.fridge | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Nevera',8)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Nevera',8)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Boutique</span></div>
                                            <div class="col-lg-4"><span class="text-danger">{{payroll.boutique | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Boutique',11)"><i
                                                    class="fa fa-list"></i>
                                                </button></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Boutique',11)"><i
                                                    class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Otros</span></div>
                                            <div class="col-lg-4"><span class="text-danger">{{payroll.others | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Otros',9)"><i
                                                    class="fa fa-list"></i>
                                                </button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Otros',9)"><i
                                                    class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-2">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Llegada Tarde</span></div>
                                            <div
                                                class="col-lg-4"><span class="text-danger">{{ payroll.late_arrival | pesos}}</span></div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-info-movements"
                                                        @click="infoMovements('Llegada Tarde',10)"><i
                                                    class="fa fa-list"></i></button>
                                            </div>
                                            <div class="col-lg-1">
                                                <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                                        data-target="#modal-create-movements" @click="setSaveMovements('Llegada Tarde',10)"><i
                                                    class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-2 mt-2 pt-2 pb-2 bg-danger">
                                        <div class="row">
                                            <div class="col-lg-5"><span>Total Deducciones</span></div>
                                            <div class="col-lg-4"><span>{{ payroll.deductions_amount | pesos }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--/Descuentos-->

                            <!--Neto a pagar-->
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="card col-lg-6">
                                        <div class="row p-2">
                                            <div class="col-lg-5"><span>Neto a Pagar</span></div>
                                            <div class="col-lg-6"><span class="text-success">{{ payroll.amount | pesos }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <small class="text-info">El bono extra es un valor adicional, no suma la nomina total.</small>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    <div class="card col-lg-6">
                                        <div class="row p-2">
                                            <div class="col-lg-5"><span>Bonificación Extra</span></div>
                                            <div class="col-lg-6"><span class="text-info">{{ payroll.bonus_extra | pesos }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--/Neto a pagar-->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-info-movements" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" >
                            <span v-text="modal_info.title"></span>
                            <span class="text-danger" v-text="modal_info.user_fullname"></span>
                        </h4>
                    </div>
                    <div class="modal-body" v-html="modal_info.movements">

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-create-movements" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" >
                            <span v-text="modal_save.title"></span>
                            <span class="text-danger" v-text="modal_save.user_fullname"></span>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Valor</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="movements_amount" v-model="modal_save.movements_amount" placeholder="50000"/>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Observación</label>
                            <div class="col-sm-7">
                                <textarea name="comment" class="form-control" v-model="modal_save.comment"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-success" @click="saveMovements"><i class="fa fa-plus"></i> Crear</button>
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
    export default {
        name: "Payroll",
        props: [
            'user',
            'permissions'
        ],
        data() {
            return{
                payrolls: [],
                payroll: {},
                isBusy: false,
                show: true,
                ranges: [],
                range: "0-0-0",
                sortBy: 'user',
                filter_payroll: {
                    quarter: 0,
                    month: 0,
                    year : 0,
                },
                modal_info: {
                    title : "",
                    movements : "",
                    payroll_type_id : 0,
                    quarter : 0,
                    month : 0,
                    year : 0,
                    user_id : 0,
                    user_fullname : "",
                },
                modal_save: {
                    title : "",
                    movements_amount : 0,
                    comment : "",
                    payroll_type_id : 0,
                    quarter : 0,
                    month : 0,
                    year : 0,
                    user_id : 0,
                    user_fullname : "",
                },
                moment: moment,
                fields: [
                    { key: 'icon', label: '', sortable: false },
                    { key: 'avatar', label: '', sortable: false },
                    { key: 'user', label: 'Usuario', sortable: true },
                    { key: 'salary', label: 'Salario Mensual', sortable: true },
                    { key: 'salary_half', label: 'Salario Quincenal', sortable: true },
                    { key: 'worked_days', label: 'Dias Trabajados', sortable: true },
                    { key: 'sums_amount', label: 'Devengado'},
                    { key: 'deductions_amount', label: 'Deducciones'},
                    { key: 'amount', label: 'Neto a Pagar'},
                    { key: 'bonus_extra', label: 'Bono Extra'},
                    { key: 'last_increase', label: 'Ultimo Aumento'},

                ],
                totalRows: 1,
                currentPage: 1,
                perPage: 100,
                pageOptions: [100, 200, 300, { value: 400, text: "Show a lot" }],
                filter: null,
                filterOn: [],
            }
        },
        computed: {
        },
        created() {
            this.getPayrolls();
            this.getPayrollDates();
        },
        methods:{
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getPayrolls(){
                this.show = true;
                let range_split = this.range.split('-');
                this.filter_payroll.quarter = range_split[0];
                this.filter_payroll.month = range_split[1];
                this.filter_payroll.year = range_split[2];

                let url = route('payroll.get_payrolls', {quarter: this.filter_payroll.quarter, month: this.filter_payroll.month, year: this.filter_payroll.year});

                axios.get(url).then(response => {
                    console.log(response.data);
                    this.payrolls = response.data.payrolls;
                    this.filter_payroll = response.data.filter_payroll;
                    this.range = response.data.range;
                    this.show = false;
                })
            },
            getPayrollDates(){
                let url = route('payroll.get_ranges');

                axios.get(url).then(response => {
                    console.log(response.data);
                    this.ranges = response.data;
                })
            },
            setPayroll(payroll){
                this.payroll = payroll;
            },
            infoMovements(title, type){
                this.modal_info.title = title;
                this.modal_info.payroll_type_id = type;
                this.modal_info.quarter = this.payroll.quarter;
                this.modal_info.month = this.payroll.month;
                this.modal_info.year = this.payroll.year;
                this.modal_info.user_id = this.payroll.user_id;
                this.modal_info.user_fullname = this.payroll.user_fullname;

                let url = route('payroll.info_movements');

                axios.post(url, this.modal_info).then(response => {
                    console.log(response.data);
                    this.modal_info.movements = response.data;
                })

            },
            setSaveMovements(title, type){
                this.modal_save.title = title;
                this.modal_save.payroll_type_id = type;
                this.modal_save.quarter = this.payroll.quarter;
                this.modal_save.month = this.payroll.month;
                this.modal_save.year = this.payroll.year;
                this.modal_save.user_id = this.payroll.user_id;
                this.modal_save.user_fullname = this.payroll.user_fullname;
            },
            saveMovements()
            {
                helper.VUE_ResetValidations();
                let url = route('payroll.save_movements');

                axios.post(url, this.modal_save).then(response =>{
                    if (response.data.success){
                        Toast.fire({
                            icon: "success",
                            title: "Se han guardado los cambios exitosamente",
                        });
                        this.getPayrolls();
                        this.payroll = response.data.payroll;
                        this.modal_save.movements_amount = "";
                        this.modal_save.comment = "";
                    }
                    else {
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
            saveDays(){
                helper.VUE_ResetValidations();
                let url = route('payroll.update_worked_days');

                axios.post(url, this.payroll).then(response =>{
                    if (response.data.success){
                        Toast.fire({
                            icon: "success",
                            title: "Se han modificado los dias trabajados exitosamente",
                        });
                        this.payroll = response.data.payroll;
                    }
                    else {
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
            sortOptions() {
                return this.fields
                    .filter(f => f.sortable)
                    .map(f => {
                        return {text: f.label, value: f.key}
                    })
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length
                this.currentPage = 1
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
        }
    }
</script>
