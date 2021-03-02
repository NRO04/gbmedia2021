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
                    <b-col lg="4" class="my-1">
                        <select v-model="user_status" @change="getUsers" class="form-control form-control-sm">
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </b-col>
                </b-row>

                <!-- Main table element -->
                <b-table show-empty small stacked="md" :items="users" :fields="fields" :current-page="currentPage" :per-page="perPage"
                         :filter="filter" :filter-included-fields="filterOn"
                         @filtered="onFiltered" :busy ="isBusy" :sort-by.sync="sortBy">

                    <template #cell(avatar)="row">
                        <div class='c-avatar'><img class='c-avatar-img zoom-img' :src='row.item.avatar'></div>
                    </template>

                    <template #cell(actions)="row">
                        <button class="btn btn-info btn-sm" data-target="#modal-access" data-toggle="modal" title="Accessos" @click="getAccess(row.item)" v-if="can('users-models-pages-access-edit')">
                            <i class="fa fa-link"></i>
                        </button>
                    </template>

                </b-table>
                <b-row class="justify-content-end">
                    <b-col cols="6" class="my-1">
                        Total <b>{{ users.length }}</b> registros
                    </b-col>
                    <b-col cols="6" class="my-1">
                        <b-pagination
                            v-model="currentPage"
                            :total-rows="users.length"
                            :per-page="perPage"
                            size="sm"
                            class="my-0 float-right"
                        ></b-pagination>
                    </b-col>
                </b-row>
            </b-overlay>
        </div>

        <!-- Modal -->
        <div id="modal-access" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-xl">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Accesos de la Modelo <span class="text-danger">{{ model.nick }}</span></h4>
                        <button class="btn btn-success btn-sm" data-target="#modal-access-create" data-toggle="modal" @click="prepareCreate" v-if="can('users-models-pages-access-create')">
                            <i class="fa fa-plus"></i> Crear
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-6 col-lg-3">
                                    <div class="card">
                                        <div class="card-body p-3 d-flex align-items-center">
                                            <div class="bg-gradient-info p-2 mfe-3">
                                                    <i class="fa fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="text-value text-info">Usuario de la Modelo</div>
                                                <div class="text-muted font-weight-bold small" v-text="model.nick"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-lg-3">
                                    <div class="card">
                                        <div class="card-body p-3 d-flex align-items-center">
                                            <div class="bg-gradient-info p-2 mfe-3">
                                                <i class="fa fa-envelope"></i>
                                            </div>
                                            <div>
                                                <div class="text-value text-info">Email o Hangouts</div>
                                                <div class="text-muted font-weight-bold small" v-text="model.email"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-lg-3">
                                    <div class="card">
                                        <div class="card-body p-3 d-flex align-items-center">
                                            <div class="bg-gradient-info p-2 mfe-3">
                                                <i class="fa fa-key"></i>
                                            </div>
                                            <div>
                                                <div class="text-value text-info">Clave</div>
                                                <div class="text-muted font-weight-bold small" v-text="model.hangout_password"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-lg-3">
                                    <div class="card">
                                        <div class="card-body p-3 d-flex align-items-center">
                                            <div class="bg-gradient-info p-2 mfe-3">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <div>
                                                <div class="text-value text-info">Locación</div>
                                                <div class="text-muted font-weight-bold small" v-text="model.location"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Pagina</th>
                                    <th>Login</th>
                                    <th>Nick</th>
                                    <th>Email</th>
                                    <th>Clave</th>
                                    <th>Tareas</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, index) in accounts">
                                    <td>{{ data.page }}</td>
                                    <td>
                                        <a :href="data.login" class="btn btn-dark btn-sm" target="_blank"><i class="fas fa-sign-in-alt"></i></a>
                                    </td>
                                    <td>{{ data.nick }}</td>
                                    <td>{{ data.access }}</td>
                                    <td>{{ data.password }}</td>
                                    <td>
                                        <button class="btn btn-info btn-sm" data-target="#modal-mantenance"
                                                data-toggle="modal"><i class="fa fa-eye"></i></button>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-target="#modal-access-edit" data-toggle="modal" @click="setAccessEdit(data,index)" v-if="can('users-models-pages-access-edit')">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-access-create" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Crear Accesos de la Modelo <span class="text-danger">{{ model.nick }}</span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label>Pagina</label>
                                    <select class="form-control form-control-sm" v-model="account_create.page" name="page">
                                        <option :value="data.id" v-for="(data, index) in pages"> {{ data.name }}</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label>Nick</label>
                                    <input type="text" class="form-control form-control-sm" name="nick" v-model="account_create.nick">
                                </div>
                                <div class="col-lg-6 mt-2">
                                    <label>Email</label>
                                    <input type="text" class="form-control form-control-sm" v-model="account_create.access">
                                </div>
                                <div class="col-lg-6 mt-2">
                                    <label>Clave</label>
                                    <input type="text" class="form-control form-control-sm" v-model="account_create.password">
                                </div>
                                <div class="col-lg-12 mt-2">
                                    <button class="btn btn-success btn-sm float-right" @click="storeAccount"> Crear</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-access-edit" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Editar Accesos de la Modelo <span class="text-danger">{{ model.nick }}</span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label>Pagina</label>
                                    <input type="text" class="form-control form-control-sm text-info" title="Deshabilitado"
                                           :value="account_edit.page" disabled>
                                </div>
                                <div class="col-lg-6">
                                    <label>Nick</label>
                                    <input type="text" class="form-control form-control-sm" v-model="account_edit.nick">
                                </div>
                                <div class="col-lg-6 mt-2">
                                    <label>Email</label>
                                    <input type="text" class="form-control form-control-sm" v-model="account_edit.access">
                                </div>
                                <div class="col-lg-6 mt-2">
                                    <label>Clave</label>
                                    <input type="text" class="form-control form-control-sm" v-model="account_edit.password">
                                </div>
                                <div class="col-lg-12 mt-2">
                                    <button class="btn btn-warning btn-sm float-right" @click="updateAccount"> Editar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-mantenance" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Tareas de pagina de la Modelo <span class="text-danger" v-text="model.nick"></span></h4>
                    </div>
                    <div class="modal-body">
                        <platform-in-maintenance></platform-in-maintenance>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import * as helper from '../../../../public/js/vue-helper.js'

    export default {
        name: "user-models",
        props: [
            'permissions',
        ],
        data() {
            return{
                users: [],
                pages: [],
                user_status: 1,
                isBusy: false,
                sortBy: 'nick',
                show: true,
                model: {},
                accounts: [],
                account_edit: {},
                account_create: {},
                fields: [
                    { key: 'nick', label: 'Usuario de Modelo', sortable: true },
                    { key: 'avatar', label: 'Imagen', sortable: true },
                    { key: 'email', label: 'Email', sortable: true },
                    { key: 'location', label: 'Locacion', sortable: true },
                    { key: 'actions', label: 'Accesos', sortable: false},

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
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getUsers(){
                this.show = true;
                //'/user/models?user_status='+ this.user_status
                axios.get(route('user.models', { user_status: this.user_status })).then(response => {
                    console.log(response.data);
                    this.users = response.data.model;
                    this.pages = response.data.pages;
                    this.show = false;
                    this.totalRows = response.data.models.length;
                })
            },
            getAccess(item){
                this.model = item;
                axios.get(route('user.model_accounts', {id: item.id})).then(response => {
                    this.accounts = response.data;
                })
            },
            setAccessEdit(data,index){
                this.account_edit = data;
                this.account_edit.index = index;
            },
            updateAccount(){
                helper.VUE_ResetValidations();
                axios.post(route('user.update_accounts'), this.account_edit)
                    .then(response => {
                        if (response.data.success) {
                            Toast.fire({
                                icon: "success",
                                title: "La información ha sido modificada exitosamente",
                            });
                            this.accounts[this.account_edit.index].nick = this.account_edit.nick;
                            this.accounts[this.account_edit.index].access = this.account_edit.access;
                            this.accounts[this.account_edit.index].password = this.account_edit.password;
                            $('#modal-access-edit').modal('hide');
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
                            title: "Verifique la información de los campos",
                        });
                    });
            },
            prepareCreate(){
                this.account_create.user_id = this.model.id;
            },
            storeAccount(){
                helper.VUE_ResetValidations();
                axios.post(route('user.store_accounts'), this.account_create)
                    .then(response => {
                        if (response.data.success) {
                            Toast.fire({
                                icon: "success",
                                title: "La información ha sido guardada exitosamente",
                            });
                            this.accounts.push(response.data.result);
                            $('#modal-access-create').modal('hide');
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
                            title: "Verifique la información de los campos",
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
