<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="card table-responsive">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <span class="span-title">Deducciones por Compra en Boutique</span>
                        </div>
                        <div class="col-lg-3">
                            <select v-model="view_type" class="form-control form-control-sm" @change="getPayrollsBoutique">
                                <option value="0">Activos</option>
                                <option value="1">Finalizados</option>
                            </select>
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
                                <b-table show-empty small stacked="md" :items="payrolls_boutique" :fields="fields" :current-page="currentPage"
                                         :per-page="perPage"
                                         :filter="filter" :filter-included-fields="filterOn"
                                         @filtered="onFiltered" :busy ="isBusy">

                                    <template #cell(avatar)="row">
                                        <div class='c-avatar'><img class='c-avatar-img' :src='row.item.avatar'></div>
                                    </template>

                                    <template #cell(amount)="row">
                                        <span class='text-muted'>{{ row.item.amount | pesos }}</span>
                                    </template>

                                    <template #cell(installment)="row">
                                        <span class='text-danger'>{{ row.item.installment | pesos }}</span>
                                    </template>

                                    <template #cell(should_pay)="row">
                                        <span class='text-danger'>{{ row.item.should_pay | pesos }}</span>
                                    </template>

                                    <template #cell(action)="row">
                                        <button class="btn btn-success btn-sm" data-toggle="modal"
                                                data-target="#modal-payroll-boutique"
                                                @click="setBoutiqueInstallment(row.item)"><i class="fa fa-dollar-sign"></i></button>
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
        <div class="modal fade" id="modal-payroll-boutique" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel3" aria-hidden="true">
            <div class="modal-dialog modal-dialog-slideout modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Resumen de Deducciones por Compra en Boutique</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div v-if="pay.status == 1" class="alert alert-danger text-center" id="alert_terminated_loan" role="alert">
                                        <h5>La deducción se encuentra finalizada</h5>
                                    </div>
                                </div>
                                <!--user info-->
                                <div class="col-lg-12">
                                    <div class="card  table-responsive">
                                        <div class="card-header">
                                            <div class="float-right" v-if="pay.status == 0">
                                                <button type="button" id="btn_create_installment" class="btn btn-success btn-sm"
                                                        data-toggle="modal" data-target="#modal-create-installment">
                                                    <i class="fa fa-dollar-sign"></i> Abonar</button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-hover table-striped">
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Solicitante</th>
                                                    <th>Fecha Compra</th>
                                                    <th>Valor Inicial</th>
                                                    <th>Debe</th>
                                                    <th>Desea Pagar</th>
                                                    <th>Detalle</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <div class='c-avatar'><img class='c-avatar-img' :src='pay.avatar'></div>
                                                        </td>
                                                        <td>{{ pay.user }}</td>
                                                        <td>{{ pay.created_at }}</td>
                                                        <td class="text-muted">{{ pay.amount | pesos }}</td>
                                                        <td class="text-danger">{{ pay.installment | pesos }}</td>
                                                        <td class="text-danger">{{ pay.should_pay | pesos }}</td>
                                                        <td>{{ pay.comment }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /user info-->
                                <div class="col-lg-12">
                                    <div class="card  table-responsive">
                                        <div class="card-body">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                <tr>
                                                    <th>Fechas</th>
                                                    <th>Abonos</th>
                                                    <th>Total</th>
                                                    <th>Creado Por</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(data, index) in installments" >
                                                        <td>{{ data.created_at }}</td>
                                                        <td>{{ data.installment | pesos }}</td>
                                                        <td class="text-success">{{ data.total | pesos }}</td>
                                                        <td>{{ data.created_by }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-create-installment" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Abonar</h4>
                    </div>
                    <div class="modal-body">
                        <small class="text-warning">Los abonos realizados el 15 o el último dia del mes se guardaran en la quincena siguiente de la
                            nomina</small>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Valor a Abonar</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" v-model="pay.amount_installment" name="amount_installment"
                                           placeholder="50000"/>
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-success" @click="storeInstallment"><i class="fa fa-plus"></i> Crear</button>
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
        data() {
            return{
                payrolls_boutique: [],
                installments: [],
                isBusy: false,
                show: true,
                view_type: 0,
                pay: {
                    id : "",
                    amount_installment : "",
                },
                moment: moment,
                fields: [
                    { key: 'avatar', label: '', sortable: true },
                    { key: 'user', label: 'Usuario', sortable: true },
                    { key: 'created_at', label: 'Fecha Compra'},
                    { key: 'amount', label: 'Valor Inicial', sortable: true },
                    { key: 'installment', label: 'Debe', sortable: true },
                    { key: 'should_pay', label: 'Desea Pagar', sortable: true },
                    { key: 'comment', label: 'Detalle'},
                    { key: 'action', label: 'Abonar'},


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
            this.getPayrollsBoutique();
        },
        methods:{
            getPayrollsBoutique(){
                this.show = true;
                axios.get(route('payroll.boutique.get_payrolls_boutique', {status : this.view_type})).then(response => {
                    console.log(response.data);
                    this.payrolls_boutique = response.data.payrolls;
                    this.show = false;
                })
            },
            setBoutiqueInstallment(item){
                this.pay = item;
                this.getBoutiqueInstallment();
            },
            getBoutiqueInstallment()
            {
                this.show = true;
                axios.post(route('payroll.boutique.get_boutique_installments'), this.pay).then(response => {
                    console.log(response.data);
                    this.installments = response.data.installments;
                    this.show = false;
                })
            },
            storeInstallment()
            {
                helper.VUE_ResetValidations();
                axios.post(route('payroll.boutique.store_payrolls_boutique'), this.pay).then(response =>{
                    if(response.data.terminated) {
                        Toast.fire({
                            icon: "success",
                            title: "El préstamo ha sido finalizado",
                        });
                        this.getPayrollsBoutique();
                        this.pay.installment = this.pay.installment - this.pay.amount_installment;
                        this.pay.amount_installment = "";
                        this.pay.status = 1;
                        this.getBoutiqueInstallment();
                    }
                    else if (response.data.success){
                        Toast.fire({
                            icon: "success",
                            title: "Se han guardado los cambios exitosamente",
                        });
                        console.log(response.data);
                        this.getPayrollsBoutique();
                        this.pay.installment = this.pay.installment - this.pay.amount_installment;
                        this.pay.amount_installment = "";
                        this.getBoutiqueInstallment();
                    }
                    else if(response.data.bigger) {
                        Toast.fire({
                            icon: "error",
                            title: "Upsss.. No puedes abonar esa cantidad",
                        });
                    }
                    else if(response.data.permission == false) {
                        Toast.fire({
                            icon: "error",
                            title: "Upsss.. No tienes permiso para esto",
                        });
                    }
                    else
                    {
                        Toast.fire({
                            icon: "error",
                            title: "Upsss.. Ha ocurrido un error, comuniquese con el ADMIN",
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
        }
    }
</script>
