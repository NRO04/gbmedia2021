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
                            <b-col sm="1" md="1" class="my-1">
                                <b-form-group class="mb-0">
                                    <b-form-select v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
                                </b-form-group>
                            </b-col>
                            <b-col lg="5" class="my-1">
                                <b-form-group class="mb-0">
                                    <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Type to Search" size="sm"></b-form-input>
                                </b-form-group>
                            </b-col>
                            <div class="col-lg-3">
                                <div class="row">
                                    <label class="mt-1  mr-2 ml-2">En: </label>
                                    <select class="col-lg-8 form-control" v-model="last_date" @change="getOwners">
                                        <option value="1">Ultimo mes</option>
                                        <option value="2">Ultimos 2 meses</option>
                                        <option value="3">Ultimos 3 meses</option>
                                        <option value="4">Ultimos 4 meses</option>
                                        <option value="5">Ultimos 5 meses</option>
                                        <option value="6">Ultimos 6 meses</option>
                                        <option value="7">Ultimos 7 meses</option>
                                        <option value="8">Ultimos 8 meses</option>
                                        <option value="9">Ultimos 9 meses</option>
                                        <option value="10">Ultimos 10 meses</option>
                                        <option value="11">Ultimos 11 meses</option>
                                        <option value="12">Ultimo año</option>
                                    </select>
                                </div>
                            </div>
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
                                    <span>{{ row.item.email }}</span> | {{ row.item.phone }}
                                </div>
                            </template>

                            <template #cell(amount)="row">
                                <span class='text-success'>{{ row.item.amount | dolares }}</span>
                            </template>

                            <template #cell(accounts)="row">
                                <span class='badge badge-pill badge-info'>{{ row.item.accounts }}</span>
                            </template>

                            <template #cell(status)="row">
                                <i v-if="row.item.status == 1" class='fa fa-check text-success' title='activo'></i>
                                <i v-else class='fa fa-times text-danger' title='vetado'></i>
                            </template>

                            <template #cell(actions)="row">
                                <a type='' class='btn btn-success btn-sm' title='Pagos' target='_blank'
                                   :href="'payment/payroll/owner/'+ row.item.id"><i
                                    class='fa fa-dollar-sign'></i></a>
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
    </div>
</template>

<script>
    export default {
        name: "PayReceived",
        data() {
            return{
                owners: [],
                last_date: "",
                isBusy: false,
                show: false,
                fields: [
                    { key: 'owner', label: 'Propietario', sortable: true },
                    { key: 'amount', label: 'Suma % Pago', sortable: true },
                    { key: 'accounts', label: '# Cuentas', sortable: true },
                    { key: 'status', label: 'Estado', sortable: true },
                    { key: 'percent', label: '% Pred', sortable: true },
                    { key: 'actions', label: 'Acciones'},

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
            //this.getOwners();
        },
        methods:{
            getOwners(){
                this.show = true;
                axios.get(route('satellite.pay_received'), {option : this.last_date}).then(response => {
                    console.log(response.data);
                    this.owners = response.data;
                    this.show = false;
                })
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

<style scoped>

</style>
