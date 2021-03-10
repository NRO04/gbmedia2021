<template>
    <div class="row">
        <div class="col-lg-12">
            <b-overlay :show="show" rounded="sm" variant="dark" opacity="0.8">
                <template #overlay>
                    <div class="text-center">
                        <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
                        <p id="cancel-label">Espere por favor...</p>
                    </div>
                </template>
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
                            <b-col lg="3" class="my-1">
                                <b-form-group class="mb-0">
                                    <select class="form-control form-control-sm" v-model="status_filter" @change="getOwners">
                                        <option value="0">All</option>
                                        <option value="1">Activos</option>
                                        <option value="2">Vetados</option>
                                        <option value="3">Inactivos</option>
                                    </select>
                                </b-form-group>
                            </b-col>
                        </b-row>

                        <!-- Main table element -->
                        <b-table show-empty small stacked="md" :items="owners" :fields="fields" :current-page="currentPage" :per-page="perPage"
                                 :filter="filter" :filter-included-fields="filterOn"
                                 @filtered="onFiltered" :busy ="isBusy">

                            <template #cell(owner)="row">
                                <div>
                                    <span>{{ row.item.owner }}</span>
                                    <span class='small text-muted'> | {{ row.item.full_name }}</span>
                                </div>
                                <div class='small text-muted'>
                                    <span>{{ row.item.email }}</span>
                                </div>
                            </template>

                            <template #cell(accounts)="row">
                                <span class='badge badge-pill badge-info'>{{ row.item.accounts }}</span>
                            </template>

                            <template #cell(status)="row">
                                <span v-if="row.item.status == 1"><i  class='fa fa-check text-success' title='activo'> </i> activo</span>
                                <span v-else-if="row.item.status == 2"><i class='fa fa-times text-danger' title='vetado'> </i> vetado</span>
                                <span v-else><i class='fa fa-times text-danger' title='inactivo'> </i> inactivo</span>
                            </template>

                            <template #cell(actions)="row">
                                <a type='' class='btn btn-success btn-sm' title='Pagos' target='_blank'
                                   :href="'payment/payroll/owner/'+ row.item.id"><i
                                    class='fa fa-dollar-sign'></i></a>
                                <button  v-if="can('satellite-owners-view')" data-target="#modal-accounts" data-toggle="modal"
                                         class='btn btn-info btn-sm'
                                        title='Cuentas' @click="getAccounts(row.item.id, row.item.owner)"><i class='fa fa-eye'></i></button>
                                <a v-if="can('satellite-owners-edit')" :href="'owner/edit/'+ row.item.id" class='btn btn-warning btn-sm'
                                   target='_blanck' title='Modificar'><i
                                    class='fa fa-edit'></i></a>
                                <button data-target="#modal-resume" data-toggle="modal" class='btn btn-info btn-sm'
                                        title='Resumen de Pagos'
                                        @click="getOwnerPayrolls(row.item.id, row.item.owner)"><i class='fa fa-list'></i></button>
                            </template>

                            <template #cell(purchase_limit)="row">
                                <i v-if="can('satellite-owner-gb-max-purchase')" data-target="#modal-limit" data-toggle="modal" style="cursor: pointer"
                                   @click="settingLimit(row.item.id,row.item.owner,row.item.purchase_limit,row.index
                                     )"
                                   class="fa fa-edit text-warning" ></i>
                                <span>{{ row.item.purchase_limit }}</span>
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
            </b-overlay>
        </div>

        <!-- Modal -->
        <div id="modal-accounts" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-xl">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Cuentas del Propietario <span class="text-danger" v-text="owner_name"></span></h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nick/Página</th>
                                    <th>Nombre</th>
                                    <th>Accesos</th>
                                    <th>Parejas</th>
                                    <th>Ultimo Cambio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, index) in accounts">
                                    <td>
                                        <div>
                                            <span>{{ data.nick }}</span><br>
                                            <span class='small text-muted'> {{ data.page }} </span>
                                        </div>
                                    </td>
                                    <td>{{ data.full_name }}</td>
                                    <td>
                                        <div>
                                            <span>Email: {{ data.access }}</span><br>
                                            <span class='small text-muted'> Clave: {{ data.password }} </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span v-if="data.partners > 0" class='badge badge-pill badge-info'>{{ data.partners }}</span>
                                        <span v-else class='badge badge-pill badge-secondary'>{{ data.partners }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <span>{{ data.updated_by }}</span><br>
                                            <span class='small text-muted'>{{ data.updated_at }} </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class='badge badge-pill'
                                                  :style="'background:'+ data.status_background + ';color:'+ data.status_color">{{
                                                data.status_name

                                                }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-resume" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Resumen de pago del Propietario <span class="text-danger" v-text="owner_name"></span></h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Propietario</th>
                                <th>Fecha Pago</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data, index) in payrolls">
                                <td v-text="owner_name"></td>
                                <td>{{ data.payment_date }}</td>
                                <td>{{ data.total }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-limit" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-md">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Limite de Compra de <span class="text-danger">{{ limit.owner_name }}</span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-12">
                            <div class="row justify-content-between">
                                <input type="text" class="col-lg-6 form-control form-control-sm" v-model="limit.purchase_limit" name="purchase_limit">
                                <button class="btn btn-success btn-sm float-right" @click="storeLimit">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import * as helper from '../../../../../public/js/vue-helper.js'
    export default {
        name: "owner-model",
        props: ["permissions", "months"],
        data() {
            return{
                owners: [],
                accounts: [],
                payrolls: [],
                limit: {},
                owner_name: "",
                isBusy: false,
                show: true,
                status_filter: 1,
                fields: [
                    { key: 'owner', label: 'Propietario', sortable: true },
                    { key: 'phone', label: 'Telefono', sortable: true },
                    { key: 'accounts', label: '# Cuentas', sortable: true },
                    { key: 'status', label: 'Estado', sortable: true },
                    { key: 'percent', label: '% Pred', sortable: true },
                    { key: 'actions', label: 'Acciones'},
                    { key: 'purchase_limit', label: 'Limite de Compra'},

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
            this.getOwners();
        },
        watch: {
            months() {
                this.getOwners();
            },
        },
        methods:{
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getOwners(){
                this.show = true;
                axios.get(route('satellite.owners_models', {status_filter : this.status_filter, months: this.months})).then(response => {
                    this.owners = response.data;
                    this.show = false;
                })
            },
            getAccounts(owner_id,owner){
                this.show = true;
                this.owner_name = owner;
                axios.get(route('satellite.get_owner_accounts', {owner_id : owner_id})).then(response => {
                    console.log(response.data);
                    this.accounts = response.data;
                    this.show = false;
                })
            },
            getOwnerPayrolls(owner_id,owner){
                this.show = true;
                this.owner_name = owner;
                axios.get(route('satellite.payment.get_owner_payment_payrolls', {owner_id : owner_id})).then(response => {
                    console.log(response.data);
                    this.payrolls = response.data.payrolls;
                    this.show = false;
                })
            },
            settingLimit(id,owner,purchase_limit,index){
                this.limit = {};
                this.limit.id = id;
                this.limit.owner_name = owner;
                this.limit.purchase_limit = purchase_limit;
                this.limit.index = index;
            },
            storeLimit(){
                helper.VUE_ResetValidations();
                axios.post(route('satellite.store_limit', this.limit)).then(response => {
                    if (response.data.success)
                    {
                        let index = this.limit.index;
                        this.owners[index].purchase_limit = this.limit.purchase_limit;
                        Toast.fire({
                            icon: "success",
                            title: "Se ha modificado la información exitosamente",
                        });
                    }
                    else
                    {
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
        }
    }
</script>
