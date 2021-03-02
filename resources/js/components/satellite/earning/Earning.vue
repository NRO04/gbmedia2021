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
                    <b-row class="my-3 mb-5 justify-content-between">
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
                        <div class="col-lg-3">
                            <div class="row">
                                <label class="mt-1  mr-2 ml-2">Tipo</label>
                                <select v-model="is_user" class="col-lg-8 form-control form-control-sm" @change="getEarnigns">
                                    <option value="0">Satelite</option>
                                    <option value="1">Modelos GB</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="row">
                                <label class="mt-1  mr-2 ml-2">Años</label>
                                <select v-model="year" class="col-lg-8 form-control form-control-sm" @change="getEarnigns">
                                    <option :value="data" v-for="(data, index) in years">{{ data }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <a :href="'/satellite/earnings/export?is_user='+ is_user + '&year=' + year"
                               class="btn btn-sm btn-success" v-if="btnExport"><i class="fas fa-download"></i> Exportar</a>
                        </div>
                    </b-row>

                    <!-- Main table element -->
                    <b-table show-empty small stacked="md" :items="earnings" :fields="fields" :current-page="currentPage" :per-page="perPage"
                             :filter="filter" :filter-included-fields="filterOn"
                        @filtered="onFiltered" :busy ="isBusy" :sort-by.sync="sortBy" :sort-desc.sync="sortDesc">

                        <template #cell(amount)="row">
                                <span class="text-success">{{ row.item.amount | dolares }}</span>
                        </template>

                        <template #cell(average)="row">
                            <span class="text-success">{{ row.item.average | dolares }}</span>
                        </template>

                        <template #cell(earnings_gb)="row">
                            <span class="text-success">{{ row.item.earnings_gb | pesos }}</span>
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
    import moment from "moment";

    export default {
        name: "Earning",
        data() {
            return{
                earnings: [],
                years: [],
                is_user : 0,
                year : "",
                isBusy: false,
                show: true,
                sortBy: 'amount',
                sortDesc: true,
                fields: [
                    { key: 'owner', label: 'Propietario', sortable: true},
                    { key: 'amount', label: 'Ganancias del Estudio', sortable: true },
                    { key: 'count_payrolls', label: 'Cantidad Pagos', sortable: true },
                    { key: 'average', label: 'Promedio', sortable: true },
                    { key: 'percent_gb', label: 'Actual % GB', sortable: true },
                    { key: 'earnings_gb', label: 'Ganancias GB', sortable: true },
                    { key: 'last_payroll', label: 'Ultimo Pago', sortable: true },
                    { key: 'city', label: 'Ciudad', sortable: true },

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
            btnExport(){
                return this.year;
            },
        },
        created() {
            this.getEarnigns();
            this.buildYears();
        },
        methods:{
            getEarnigns(){
                this.show = true;
                axios.get(route('satellite.get_earnings', {is_user : this.is_user, year: this.year})).then((response) => {
                    console.log(response.data);
                    this.earnings = response.data;
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
            buildYears(){
                let start = moment().format('YYYY');
                let end = 2019;
                this.years.push("Todos");
                while(start >= end)
                {
                    this.years.push(start);
                    start = start - 1;
                }

            }
        }
    }
</script>
