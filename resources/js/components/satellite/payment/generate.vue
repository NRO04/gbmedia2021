<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="card table-responsive">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-2">
                            <span class="span-title">Generar Pagos </span>
                        </div>
                        <div class="col-lg-2">
                            <div class="row">
                                <select v-model="is_user" class="col-lg-8 form-control" @change="getDatesPayroll">
                                    <option value="0">Satelite</option>
                                    <option value="1">Modelos GB</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="row">
                                <label class="mt-1  mr-2">Fecha de Pago</label>
                                <select v-model="payment_date" class="col-lg-8 form-control" @change="getPayrolls">
                                    <option value=""></option>
                                    <option v-for="(data, index) in payment_dates">
                                        {{ data.payment_date }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-12" v-if="payment_date">
                            <div class="row">
<!--                                <select v-model="export_type" class="col-lg-5 form-control">
                                    <option value="1">AV Villas Otro Bancos</option>
                                    <option value="2">Bancolombia Otro Bancos</option>
                                    <option value="3">Bancoomeva Otro Bancos</option>
                                </select>-->
                                <a :href="'/public/satellite/payment/payrolls/export?payment_date='+ payment_date + '&export_type=' + export_type+
                                '&is_user=' +
                                 is_user"
                                class="btn btn-sm btn-success ml-1 pt-2"><i class="fas fa-download"></i> Exportar</a>

                                <button class="btn btn-sm btn-secondary ml-3" data-target="#how-much-in-a-bank" data-toggle="modal" @click="perBank">Info
                                </button>
                                <button class="btn btn-sm btn-secondary ml-3" @click="sendStatisticEmails">Enviar Emails</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul>
                        <li><span class="text-muted">Cuando el propietario está en <span class="text-danger">ROJO</span> es porque tiene
                            deducciones o comisiones</span></li>
                        <li><span class="text-muted">Cuando la Informacion de pago está en <span class="text-danger">ROJO</span> es porque es la
                            primera vez o ha cambiado su informacion de pago</span></li>
                    </ul>


                    <b-row class="mb-5 justify-content-between">
                        <b-col sm="1" md="1" class="my-1">
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
                    <b-table
                        class="table-striped table-hover"
                        show-empty
                        small
                        stacked="md"
                        :items="items"
                        :fields="fields"
                        :current-page="currentPage"
                        :per-page="perPage"
                        :filter="filter"
                        :filter-included-fields="filterOn"
                        @filtered="onFiltered"
                        :busy ="isBusy"
                    >
                        <template #cell(owner.owner)="row">
                            <span :class="(row.item.has_payment_deduction == 1)? 'text-danger' : ''" >{{ row.item.owner.owner }}</span>
                        </template>
                        <template #cell(payment)="row">
                            <span class="text-success" v-if="(row.item.payment_methods.pay_with == 1)">{{ row.item.payment | pesos }}</span>
                            <span class="text-success" v-else>{{ row.item.percent_studio | dolares }}</span>
                        </template>

                        <template #cell(payment_methods)="row">
                            <!-- SIN FORMA DE PAGO -->
                            <span v-if="row.item.payment_methods_id == 1">
                                <p class="text-muted">Sin forma de Pago</p>
                            </span>
                            <!-- BANCO -->
                            <span v-else-if="row.item.payment_methods_id == 2 || row.item.payment_methods_id == 6 || row.item.payment_methods_id ==
                            7 ||
                            row.item.payment_methods_id == 9">
                                <div class="d-flex">
                                    <div>
                                        <span>Banco</span>
                                        <span class='small text-muted' v-if="row.item.payment_methods_id == 7"> : {{ row.item.bank_usa }}</span>
                                        <span class='small text-muted' v-else> : {{(row.item.global_bank == null)? "" : row.item.global_bank.name }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Titular</span>
                                        <span class='small text-muted'> : {{ row.item.holder }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Documentos</span>
                                        <span class='small text-muted'> : {{ (row.item.document_type !== null)?
                                        row.item.global_document.name_simplified +" / "+
                                        row.item.document_number : row.item.document_number
                                            }}</span>
                                    </div>
                                </div>

                                <div class="d-flex">
                                    <div>
                                        <span>Nro Cuenta</span>
                                        <span class='small text-muted'> : {{ row.item.account_number }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Tipo Cuenta</span>
                                        <span class='small text-muted'> : {{ (row.item.account_type == 1)? "Ahorros" : "Corriente" }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Ciudad</span>
                                        <span class='small text-muted'> : {{ row.item.city_id }}</span>
                                    </div>
                                </div>

                            </span>
                            <!-- EFECTY / CHEQUE SIN RETENCION-->
                            <span v-else-if="row.item.payment_methods_id == 3 || row.item.payment_methods_id == 5">
                                <div class="d-flex">
                                    <div>
                                        <span>Nombre</span>
                                        <span class='small text-muted'> : {{ row.item.holder }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Documentos</span>
                                        <span class='small text-muted'> : {{ (row.item.document_type !== null)?
                                            row.item.global_document.name_simplified +" / "+
                                            row.item.document_number : row.item.document_number
                                            }}</span>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div>
                                        <span>Dirección</span>
                                        <span class='small text-muted'> : {{ row.item.address }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Ciudad</span>
                                        <span class='small text-muted'> : {{ row.item.city_id }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Teléfono</span>
                                        <span class='small text-muted'> : {{ row.item.phone }}</span>
                                    </div>
                                </div>
                            </span>
                            <!-- PAXUM -->
                            <span v-if="row.item.payment_methods_id == 4">
                                <div>
                                    <span>Usuario</span>
                                    <span class='small text-muted'> : {{ row.item.holder }}</span>
                                </div>
                            </span>
                            <!-- WESTERN UNION -->
                            <span v-else-if="row.item.payment_methods_id == 8">
                                <div class="d-flex">
                                    <div>
                                        <span>Nombre</span>
                                        <span class='small text-muted'> : {{ row.item.holder }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Documentos</span>
                                        <span class='small text-muted'> : {{ (row.item.document_type !== null)?
                                            row.item.global_document.name_simplified +" / "+
                                            row.item.document_number : row.item.document_number
                                            }}</span>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div>
                                        <span>Dirección</span>
                                        <span class='small text-muted'> : {{ row.item.address }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Ciudad</span>
                                        <span class='small text-muted'> : {{ row.item.city_id }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Teléfono</span>
                                        <span class='small text-muted'> : {{ row.item.phone }}</span>
                                    </div>
                                    <div class="ml-2">
                                        <span>Pais</span>
                                        <span class='small text-muted'> : {{ row.item.country }}</span>
                                    </div>
                                </div>
                            </span>

                        </template>

                        <template #cell(pago)="row">
                            <span><a type='' class='btn btn-success btn-sm' title='Pagos' target='_blank'
                                     :href="'../payment/payroll/owner/' + row.item.owner.id"><i class='fa fa-dollar-sign'></i></a>
                            </span>
                        </template>

                        <template #cell(accumulate)="row">
                            <span v-if="last_payment_date"><button type="button" class="btn btn-sm btn-dark" @click="accumulate(row.item.owner_id,
                            row.item.id, row.index)" ><i class="fa fa-piggy-bank text-info"></i></button></span>
                        </template>

                        <!--<template #cell(last_payment)="row">
                            <span></span>
                        </template>-->

                        <template #cell(rut)="row">
                            <span v-if="row.item.rut == 1"><i class='fa fa-check text-success'></i></span>
                            <span v-else><i class='fa fa-times text-danger'></i></span>
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

        <!-- Modal Cuanto debe pagar en cada banco-->
        <div id="how-much-in-a-bank" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Cuanto debe pagar en cada Banco</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Forma de Pago</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, index) in per_bank">
                                    <td>{{ data.payment_method }}</td>
                                    <td>{{ data.type }}</td>
                                    <td class="text-success" v-if="data.type == 'pesos'">{{ data.amount | pesos }}</td>
                                    <td class="text-success" v-else>{{ data.amount | dolares }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        name: "generate",
        data() {
            return{
                payment_date: "",
                payment_dates: {},
                last_payment_date : false,
                export_type : 1,
                is_user : 0,
                items: [],
                per_bank: [],
                isBusy: false,
                fields: [
                    { key: 'owner.owner', label: 'Propietario', sortable: true },
                    { key: 'payment', label: 'Valor a Pagar', sortable: true },
                    { key: 'payment_methods.name', label: 'Forma de Pago', sortable: true },
                    { key: 'payment_methods', label: 'Información de Pago', sortable: true, tdClass(value, key, item){
                            if (item.first_time == 1){
                                return "text-danger";
                            }
                        } },
                    { key: 'pago', label: 'Pago'},
                    { key: 'accumulate', label: 'Acumular', class: 'text-center' },
                    { key: 'last_pay', label: 'Ultimo Abono', sortable: true},
                    { key: 'rut', label: 'RUT', class: 'text-center' },

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
            this.getDatesPayroll();
        },
        methods:{
            getDatesPayroll(){
                axios.get(route('satellite.payment.payroll_dates', {is_user : this.is_user})).then(response => {
                    console.log(response.data);
                    this.payment_dates = response.data;
                    this.payment_date = "";
                    this.getPayrolls();
                })
            },
            getPayrolls(){
                this.isBusy = true;
                axios.get(route('satellite.payment.payrolls', {payment_date : this.payment_date, is_user : this.is_user})).then(response => {
                    console.log(response.data);
                    this.items = response.data.payrolls;
                    this.totalRows = this.items.length
                    this.last_payment_date = response.data.last_payment_date;
                    this.isBusy = false;
                })
            },
            accumulate(owner_id, id, index){
                axios.post(route('satellite.payment.acumulate_payment'), {
                    payroll_id : id,
                    owner_id : owner_id,
                })
                .then(response => {
                    if (response.data.success) {
                        Toast.fire({
                            icon: "success",
                            title: "El propietario se ha puesto a acumular",
                        });
                        this.items.splice(index,1);
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
                    Toast.fire({
                        icon: "error",
                        title: "Se ha producido un error, comuniquese con el ADMIN",
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
            perBank(){
                this.per_bank = [
                    { payment_method : "Sin forma de Pago", amount : 0, type: 'pesos' },//0
                    { payment_method : "Bancolombia", amount : 0, type: 'pesos' },//1
                    { payment_method : "Bogota Grupo Aval", amount : 0, type: 'pesos' },//2
                    { payment_method : "Efecty", amount : 0, type: 'pesos' },//3
                    { payment_method : "Paxum", amount : 0, type: 'dolares' },//4
                    { payment_method : "Cheque sin Retencion", amount : 0, type: 'dolares' },//5
                    { payment_method : "Banco Usa sin Retencion", amount : 0, type: 'dolares' },//6
                    { payment_method : "Western Union", amount : 0, type: 'pesos' },//7
                    { payment_method : "Otros Bancos", amount : 0, type: 'pesos' },//8
                ];
                for (let i = 0; i < this.items.length; i++)
                {
                    if (this.items[i].payment_methods_id == 1){
                        this.per_bank[0].amount = parseFloat(this.per_bank[0].amount) + parseFloat(this.items[i].payment);
                    }
                    if (this.items[i].bank == 21 || this.items[i].bank == 36){
                        this.per_bank[1].amount = parseFloat(this.per_bank[1].amount) + parseFloat(this.items[i].payment);
                    }
                    if (this.items[i].bank == 2 || this.items[i].bank == 9 || this.items[i].bank == 11 || this.items[i].bank == 17){
                        this.per_bank[2].amount = parseFloat(this.per_bank[2].amount) + parseFloat(this.items[i].payment);
                    }
                    if (this.items[i].payment_methods_id == 3){
                        this.per_bank[3].amount = parseFloat(this.per_bank[3].amount) + parseFloat(this.items[i].payment);
                    }
                    if (this.items[i].payment_methods_id == 4){
                        this.per_bank[4].amount = parseFloat(this.per_bank[4].amount) + parseFloat(this.items[i].percent_studio);
                    }
                    if (this.items[i].payment_methods_id == 5){
                        this.per_bank[5].amount = parseFloat(this.per_bank[5].amount) + parseFloat(this.items[i].percent_studio);
                    }
                    if (this.items[i].payment_methods_id == 7){
                        this.per_bank[6].amount = parseFloat(this.per_bank[6].amount) + parseFloat(this.items[i].percent_studio);
                    }
                    if (this.items[i].payment_methods_id == 8){
                        this.per_bank[7].amount = parseFloat(this.per_bank[7].amount) + parseFloat(this.items[i].payment);
                    }
                    if (this.items[i].payment_methods_id == 2 && this.items[i].bank != 2 && this.items[i].bank != 9 && this.items[i].bank != 11 &&
                        this.items[i].bank != 17 && this.items[i].bank != 21 && this.items[i].bank != 36 ){
                        this.per_bank[8].amount = parseFloat(this.per_bank[8].amount) + parseFloat(this.items[i].payment);
                    }
                }
            },
            sendStatisticEmails(){
                axios.post(route('satellite.payment.send_statistic_emails'), {
                    payment_date : this.payment_date,
                    is_user : this.is_user,
                }).then(response => {
                    if (response.data.success)
                    {
                        SwalGB.fire({
                            title: "Atención",
                            text:
                                "Los correos de estadísticas se estarán enviando en segundo plano, " +
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
        }
    }
</script>

<style scoped>

</style>
