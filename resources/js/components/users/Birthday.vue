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
                <div class="card table-responsive">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-8"><span class="span-title">Cumpleaños </span></div>
                        <div class="col-lg-2">
                            <div class='btn-toolbar' role='toolbar'>
                                <div class='btn-group'>
                                    <button class='btn btn-secondary btn-sm dropdown-toggle' type='button' data-toggle='dropdown'
                                            aria-haspopup='true' aria-expanded='false'>Configuración</button>
                                    <div class='dropdown-menu'>
                                        <a class='dropdown-item' href='#' data-toggle="modal" data-target="#modal-maintenance">Fondo Personalizado</a>
                                        <a class='dropdown-item' href='#' data-toggle="modal" data-target="#modal-maintenance">Mensajes Predeterminados</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <a class='btn btn-info btn-sm' href='#' data-toggle="modal" data-target="#modal-information">Información</a>
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
                        <b-col lg="4" class="my-1">
                            <b-row>
                                <label for="" class="col-lg-2 form-label">Mes</label>
                                <select v-model="month" @change="getUsers" class="form-control form-control-sm col-lg-5">
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </b-row>
                        </b-col>
                    </b-row>
                    <!-- Main table element -->
                    <b-table show-empty small stacked="md" :items="users" :fields="fields" :current-page="currentPage" :per-page="perPage"
                             :filter="filter" :filter-included-fields="filterOn"
                             @filtered="onFiltered" :busy ="isBusy">

                        <template #cell(avatar)="row">
                            <div class='c-avatar'><img class='c-avatar-img' :src='row.item.avatar'></div>
                        </template>

                        <template #cell(birth_date)="row">
                            <span v-if="row.item.birthday == 1" class='badge badge-pill badge-danger p-2'>{{
                                moment(row.item.birth_date).format("DD MMMM")
                                }}</span>
                            <span v-else class='badge badge-pill badge-dark p-2'>{{ moment(row.item.birth_date).format("DD MMMM") }}</span>
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

        <!-- Modal -->
        <div id="modal-maintenance" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Vista en Manteniento <span class="text-danger" v-text="owner_name"></span></h4>
                    </div>
                    <div class="modal-body">
                        <platform-in-maintenance></platform-in-maintenance>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-information" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Vista en Manteniento <span class="text-danger" v-text="owner_name"></span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <span>La foto de perfil de usuario debe tener el mismo tamaño de ancho y de alto, para generar la imagen en proporción al marco de cumpleaños.</span>
                        </div>
                        <b-img center src="../../../../images/birthday/information.png" height="400" width="400px" fluid
                               alt="Fluid image"></b-img>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PlatformInMaintenance from "../PlatformInMaintenance";
    export default {
        name: "birthday",
        data() {
            return{
                users: [],
                month: moment().format("M"),
                isBusy: false,
                show: true,
                moment : moment,
                fields: [
                    { key: 'avatar', label: '', sortable: true },
                    { key: 'birth_date', label: 'Cumpleaños', sortable: true },
                    { key: 'full_name', label: 'Nombre', sortable: true },
                    { key: 'role', label: 'Rol', sortable: true },
                    { key: 'location', label: 'Locacion', sortable: true },

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
            this.getUsers();
        },
        methods:{
            getUsers(){
                this.show = true;
                axios.get(route('user.users_birthday', { month: this.month })).then(response => {
                    console.log(response.data);
                    this.users = response.data;
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
