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
                        <div class="col-lg-4">
                            <span class="span-title">Deudas Acumuladas </span>
                        </div>
                        <div class="col-lg-3">
                            <div class="row">
                                <label class="mt-1  mr-2 ml-2">Tipo</label>
                                <select v-model="is_user" class="col-lg-8 form-control" @change="getDebts">
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
                    <b-table show-empty small stacked="md" :items="debts" :fields="fields" :current-page="currentPage" :per-page="perPage" :filter="filter" :filter-included-fields="filterOn"
                        @filtered="onFiltered" :busy ="isBusy">

                        <template #cell(boutique)="row">
                            <span class="text-success">{{ row.item.boutique | pesos }}</span>
                        </template>

                        <template #cell(pesos)="row">
                                <span class="text-success">{{ row.item.pesos | pesos }}</span>
                        </template>

                        <template #cell(dolares)="row">
                                <span class="text-success">{{ row.item.dolares | dolares }}</span>
                        </template>

                        <template #cell(pago)="row">
                            <span><a type='' class='btn btn-success btn-sm' title='Pagos' target='_blank'
                                     :href="'/satellite/payment/payroll/owner/' + row.item.owner_id"><i class='fa fa-dollar-sign'></i></a>
                            </span>
                        </template>

                        <template #cell(blocked)="row" v-if="is_user == 1">
                            <label v-if="can('satellite-debts-edit')" class='c-switch c-switch-pill c-switch-label c-switch-danger c-switch-sm mt-2' >
                                <input :checked="row.item.blocked == 1" type='checkbox' class='c-switch-input' name='block'
                                        @click="blockBoutique(row.item.user_id, row.index)" :ref="'input-'+ row.index" />
                                <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                            </label>
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
        name: "Debts",
        props: ['permissions'],
        data() {
            return{
                debts: [],
                is_user : 0,
                isBusy: false,
                show: true,
                fields: [
                    { key: 'owner', label: 'Propietario', sortable: true },
                    { key: 'boutique', label: 'Deuda Boutique', sortable: true },
                    { key: 'pesos', label: 'Deuda Total Pesos', sortable: true },
                    { key: 'dolares', label: 'Deuda Total Dolares', sortable: true },
                    { key: 'pago', label: 'Estadisticas', sortable: true },
                    { key: 'blocked', label: 'Bloqueado'},

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
            this.getDebts();
        },
        methods:{
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getDebts(){
                this.show = true;
                axios.get(route('satellite.get_debts', {is_user : this.is_user})).then(response => {
                    console.log(response.data);
                    this.debts = response.data;
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
            blockBoutique(user_id, index){
                console.log(this.$refs['input-'+index]);
                status = (this.$refs['input-'+index].checked)? "checked" : "unchecked";
                axios.get(route('satellite.block_boutique', {user_id : user_id, status : status })).then(response => {
                    if (response.data.success)
                    {
                        Toast.fire({
                            icon: "success",
                            title: "Estado cambiado exitosamente",
                        });
                    }
                    else {
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
            }
        }
    }
</script>
