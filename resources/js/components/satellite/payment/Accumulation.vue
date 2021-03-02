<template>
    <div class="row">
        <div class="col-lg-12">
            <b-overlay :show="show" rounded="sm" variant="dark" opacity="0.8">
                <template #overlay>
                    <div class="text-center">
                        <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
                        <!--<p id="cancel-label">Espere por favor...</p>-->
                    </div>
                </template>
            <div class="card table-responsive">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <span class="span-title">Propietarios con pagos acumulados</span>
                        </div>
                        <div class="col-lg-3">
                            <div class="row">
                                <label class="mt-1  mr-2 ml-2">Tipo</label>
                                <select v-model="is_user" class="col-lg-8 form-control" @change="getAccumulations">
                                    <option value="0">Satelite</option>
                                    <option value="1">Modelos GB</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
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
                    <b-table show-empty small stacked="md" :items="accumulations" :fields="fields" :current-page="currentPage" :per-page="perPage"
                             :filter="filter" :filter-included-fields="filterOn"
                        @filtered="onFiltered" :busy ="isBusy">

                        <template #cell(amount)="row">
                                <span class="text-success">{{ row.item.amount | dolares }}</span>
                        </template>

                        <template #cell(pago)="row">
                            <span><a type='' class='btn btn-success btn-sm' title='Pagos' target='_blank'
                                     :href="'/public/satellite/payment/payroll/owner/' + row.item.owner_id"><i class='fa fa-dollar-sign'></i></a>
                            </span>
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
            </b-overlay>
        </div>
    </div>
</template>

<script>

    export default {
        name: "Accumulation",
        data() {
            return{
                accumulations: [],
                is_user : 0,
                isBusy: false,
                show: true,
                fields: [
                    { key: 'owner', label: 'Propietario', sortable: true },
                    { key: 'amount', label: 'Valor Acumulado', sortable: true },
                    { key: 'pago', label: 'Estadisticas', sortable: true },

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
            this.getAccumulations();
        },
        methods:{
            getAccumulations(){
                this.show = true;
                axios.get(route('satellite.get_accumulations', {is_user : this.is_user})).then(response => {
                    console.log(response.data);
                    this.accumulations = response.data;
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
