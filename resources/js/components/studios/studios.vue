<template>
    <div id="container-studios" class="row">
        <div class="mb-2 col-12 alert-danger" v-if="user.setting_role_id === 11">
            FALTA:
            <ul>
                <li>Compartir capacitaciones, wikis, contratos, etc...</li>
                <li>Crearle las configuraciones del estudio.</li>
            </ul>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 mb-2">
                            <span class="span-title">Listado de Estudios</span>
                        </div>
                        <div class="col-xs-12 col-sm-6 text-sm-right" @click="modalCreateStudio()">
                            <a class="btn btn-success btn-sm" v-b-modal.modal-create
                               v-if="this.current_tenant_id === 1 && can('studio-create')">
                                <i class="fa fa-plus"></i> Crear
                            </a>
                            <a class="btn btn-warning btn-sm" v-if="can('studio-assign-user-view')"
                               :href="projectRoute('studio.assign_users')">
                                <i class="fa fa-user-plus"></i> &nbsp;Asignar Usuarios
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <b-form-select class="float-right" v-model="perPage" id="perPageSelect" size="sm"
                                           :options="pageOptions"></b-form-select>
                        </div>
                        <div class="col-md-3">
                            <b-form-select @change="getStudios" class="float-right" v-model="showStudiosStatus"
                                           id="perPageSelect" size="sm">
                                <b-form-select-option :value="1" :key="1">Activos</b-form-select-option>
                                <b-form-select-option :value="0" :key="0">Inactivos</b-form-select-option>
                            </b-form-select>
                        </div>
                        <div class="col-12 col-md-4 push-3">
                            <b-input-group size="sm">
                                <b-form-input debounce="500" v-model.trim="filter" type="text" id="filterInput"
                                              placeholder="Buscar estudio..."></b-form-input>
                            </b-input-group>
                        </div>
                    </div>
                    <hr>
                    <b-table show-empty small stacked="sm" :fields="fields"
                             :items="items" responsive="sm" class="table-striped"
                             :current-page="currentPage" :per-page="perPage" :filter="filter"
                             :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" ref="table"
                             selected-variant="info" @row-selected="onRowSelected"
                             :busy.sync="isBusy" empty-text="No hay estudios creados"
                    >
                        <template #cell(studio_url)="data">
                            <b-button class="btn btn-outline-info btn-sm" title="Copiar enlace" v-b-tooltip.hover
                                      v-clipboard:copy="data.item.studio_url + '/login'" v-clipboard:success="onCopy"
                                      v-clipboard:error="onError">
                                <i class="fa fa-copy"></i>
                            </b-button>
                        </template>

                        <template #cell(is_shared)="data">
                            <i :class="data.item.is_shared ? 'fa fa-check text-success' : 'fa fa-times text-danger'"></i>
                        </template>

                        <template #cell(assign)="data">
                            <div v-if="current_tenant_id === 1 && can('studio-assign-view')"
                                 @click="openAssignStudioModal(data.item)">
                                <b-button class="btn btn-warning btn-sm" v-b-modal.modal-assign-studios>
                                    <i class="fa fa-edit"></i>
                                </b-button>
                            </div>
                        </template>

                        <template #cell(studio_logo)="data">
                            <div @click="studioLogo(data.item)" v-if="can('studio-edit')">
                                <b-button class="btn btn-success btn-sm" v-b-modal.modal-logo>
                                    <i class="fa fa-image"></i>
                                </b-button>
                            </div>
                        </template>

                        <template #cell(studio_status)="data">
                            <div v-if="current_tenant_id === 1 && can('studio-delete')">
                                <b-button v-if="data.item.is_active === 1" :disabled="data.item.tenant_id === 1"
                                          class="btn btn-danger btn-sm" title="Desactivar Estudio" v-b-tooltip.hover
                                          @click="changeStudioStatus(data.item.tenant_id, 0)">
                                    <i class="fa fa-ban"></i>
                                </b-button>
                                <b-button v-else class="btn btn-success btn-sm" title="Activar Estudio"
                                          v-b-tooltip.hover @click="changeStudioStatus(data.item.tenant_id, 1)">
                                    <i class="fa fa-check"></i>
                                </b-button>
                            </div>
                        </template>

                        <template #cell(studio_last_login)="data">
                        <span :title="data.item.studio_last_login_datetime" v-b-tooltip.hover
                              v-if="data.item.studio_last_login_user != null">
                            {{ data.item.studio_last_login_user }} | {{ data.item.studio_last_login_diff }}
                        </span>
                        </template>

                        <template #cell(login_url)="data">
                            <div v-if="current_tenant_id === 1">
                                <a :href="'http://' + data.item.login_url" class="btn btn-secondary btn-sm"
                                   title="Ir al Estudio" target="_blank" v-b-tooltip.hover>
                                    <i class="fa fa-external-link-alt"></i>
                                </a>
                            </div>
                        </template>
                    </b-table>
                </div>
                <div class="card-footer">
                    <div class="float-left">
                        Total <b>{{ items.length }}</b> registros
                    </div>
                    <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm"
                                  class="float-right"></b-pagination>
                </div>
            </div>
        </div>

        <b-modal id="modal-create" title="Crear Estudio" scrollable size="md" header-bg-variant="primary" header-close-content="" ref="modal-create">
            <form id="form-create-studio">
                <div class="form-group">
                    <label for="name" class="required">Nombre:</label>
                    <div class="input-group">
                        <input class="form-control" id="name" name="name" type="text" placeholder="GB Media Group" v-model="formCreate.name" @focusout="checkIfStudioExists()" />
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i ref="icon-name-status"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div id="container-logo"></div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <input class="form-check form-check-inline" id="share" name="share" type="checkbox" v-model="formCreate.shared" />
                        <label for="share" class="col-md-10 col-form-label p-0">Compartir wikis, noticias, capacitaciones</label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <input class="form-check form-check-inline" id="create-user" name="create_user" type="checkbox" v-model="createUser" />
                        <label for="create-user" class="col-md-10 col-form-label p-0">Crear usuario predeterminado</label>
                    </div>
                </div>
                <div class="container-user" v-if="createUser">
                    <div class="form-group">
                        <label for="user-name" class="required">Nombre:</label>
                        <input class="form-control" id="user-name" name="name" type="text" placeholder="Pedro" v-model="formUser.name" />
                    </div>
                    <div class="form-group">
                        <label for="user-last-name" class="required">Apellido:</label>
                        <input class="form-control" id="user-last-name" name="last_name" type="text" placeholder="Perez" v-model="formUser.last_name" />
                    </div>
                    <div class="form-group">
                        <label for="user-email" class="required">Email:</label>
                        <input class="form-control" id="user-email" name="email" type="email" placeholder="pedro@gmail.com" v-model="formUser.email" />
                    </div>
                    <div class="form-group">
                        <label for="user-password" class="required">Contraseña:</label>
                        <input class="form-control" id="user-password" name="password" type="password" v-model="formUser.password" />
                    </div>
                    <div class="form-group">
                        <label for="user-role-id" class="required">Rol:</label>
                        <select name="" id="user-role-id" class="form-control" v-model="formUser.role_id">
                            <option v-for="role in roles" :key="role.id" :value="role.id" :selected="role.id === 1">{{ role.name }}</option>
                        </select>
                    </div>
                </div>
            </form>
            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="success" @click="createStudio" v-if="btnCreate">
                            <i class="fas fa-plus"></i> Crear
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>

        <b-modal id="modal-logo" title="Cambiar Logo Estudio" scrollable size="md" header-bg-variant="primary" header-close-content="" ref="modal-logo">
            <div class="form-group">
                <label for="logo">Logo</label>
                <div id="container-change-logo"></div>
            </div>
            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="warning" @click="changeStudioLogo" :disabled="false">
                            <i class="fa fa-edit"></i> Cambiar logo
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>

        <b-modal id="modal-assign-studios" :title="'Asignar Estudios a ' + selectedTenantName" scrollable size="md" header-bg-variant="primary" header-close-content="" ref="modal-assign-studios">
            <div class="form-group col-12">
                <div class="row">
                    <div class="form-check col-12 col-sm-6 col-md-4" v-for="studio in this.items" v-bind:key="studio.id" v-if="studio.tenant_id != selectedTenantID">
                        <input class="form-check-input" type="checkbox" v-model="assignToTenants" :id="studio.studio_slug" :value="studio.tenant_id">
                        <label class="form-check-label" :for="studio.studio_slug">{{ studio.studio_name }}</label>
                    </div>
                </div>
            </div>
            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="info" @click="assignStudio()" :disabled="false">
                            <i class="fa fa-edit"></i> Asignar
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import * as helper from '../../../../public/js/vue-helper.js'

    export default {
        components: {},
        props: [
            'roles',
            'current_tenant_id',
            'permissions',
            'user',
        ],
        data() {
            return {
                isBusy: false,
                sortBy: 'tenant_id',
                sortDesc: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 50,
                pageOptions: [50, 100, 200],
                filter: null,
                fields: [
                    {key: 'tenant_id', label: '#', sortable: true, class: (this.current_tenant_id !== 1 ? 'd-none' : '')},
                    {key: 'studio_name', label: 'Estudio', sortable: true},
                    {key: 'studio_url', label: 'URL', sortable: false},
                    {key: 'studio_api', label: 'API', sortable: true},
                    {key: 'is_shared', label: 'Compartido', sortable: true},
                    {key: 'assign', label: 'Asignar Estudios', sortable: false, class: (this.current_tenant_id !== 1 || !this.can('studio-assign-view') ? 'd-none' : '')},
                    {key: 'studio_logo', label: 'Logo', sortable: false, class: (!this.can('studio-edit') ? 'd-none' : '')},
                    {key: 'studio_last_login', label: 'Último Lógin', sortable: true},
                    {key: 'studio_created_date', label: 'Creación', sortable: true},
                    {key: 'studio_status', label: 'Estado', sortable: false, class: (this.current_tenant_id !== 1 || !this.can('studio-delete') ? 'd-none' : '')},
                    {key: 'login_url', label: 'Login', sortable: false, class: 'd-none'},
                ],
                items: [],
                selected: [],
                selectedTenantID: null,
                selectedTenantName: null,
                validationErrors: [],
                createUser: false,
                formCreate: new Form({
                    name: '',
                    logo: '',
                    shared: '',
                }),
                formUser: {
                    name: '',
                    last_name: '',
                    email: '',
                    role_id: 1,
                    password: '',
                },
                totalSteps: 3,
                activeStep: 1,
                changeLogoTenantID: null,
                showStudiosStatus: 1,
                isValid: false,
                isNameValid: false,
                assignTenantID: null,
                assignToTenants: [],
                studios: [],
            }
        },
        computed: {
            btnCreate() {
                return this.isValid
            }
        },
        mounted() {
            $('#global-spinner').removeClass('d-none');
            Echo.private('create-studio').listen('.create-studio', (e) => {
                let step = e.step;

                switch (step) {
                    case 1:
                        $('#step-directory i').removeClass('fa-circle-notch text-info fa-pulse');
                        $('#step-directory i').addClass('fa-check text-success');
                        $('#step-directory span').addClass('text-muted');
                        this.activeStep = 2;
                        break;

                    case 2:
                        $('#step-database i').removeClass('fa-clock fa-circle-notch text-info fa-pulse');
                        $('#step-database i').addClass('fa-check text-success');
                        $('#step-database span').addClass('text-muted');
                        this.activeStep = 3;
                        break;

                    case 3:
                        $('#step-seed i').removeClass('fa-clock fa-circle-notch text-info fa-pulse');
                        $('#step-seed i').addClass('fa-check text-success');
                        $('#step-seed span').addClass('text-muted');
                        this.activeStep = 4;
                        break;
                }

                $('.step-' + this.activeStep).removeClass('fa-clock');
                $('.step-' + this.activeStep).addClass('fa-circle-notch fa-pulse');

                if(this.activeStep > this.totalSteps) {
                    this.getStudios();
                    Swal.close();
                }
            });
        },
        created() {
            this.getStudios();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getStudios() {
                axios.get(route('studio.get_studios', {status: this.showStudiosStatus})).then(response => {
                    this.items = response.data;
                    this.totalRows = response.data.length;
                    $('#global-spinner').addClass('d-none');
                });
            },
            onRowSelected(items) {
                this.selected = items
            },
            selectAllRows() {
                this.$refs.table.selectAllRows()
            },
            clearSelected() {
                this.$refs.table.clearSelected()
            },
            openAssignStudioModal(tenant) {
                this.assignToTenants = [];
                this.assignTenantID = tenant.tenant_id;
                this.selectedTenantID = tenant.tenant_id;
                this.selectedTenantName = tenant.studio_name;

                axios.post(
                        route('studio.get_studio_assignments'),
                        {tenant_id: tenant.tenant_id}
                ).then((response) => {
                    let assignments = response.data;
                    let studios_assignments = [];

                    $.each(assignments, function (i, assign) {
                        studios_assignments.push(assign.has_tenant_id);
                    });

                    this.assignToTenants = studios_assignments;
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                });
            },
            assignStudio() {
                helper.VUE_DisableModalActionButtons();

                axios.post(
                    route('studio.assigns_studio'),
                    {tenant_id: this.assignTenantID, assign_to: this.assignToTenants}
                ).then((response) => {
                    let res = response.data;

                    if(res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Estudio asignado correctamente",
                        });

                        this.$refs['modal-assign-studios'].hide();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "No se pudo asignar el estudio. Por favor, intente mas tarde.",
                            timer: 8000
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });

                    helper.VUE_EnableModalActionButtons();
                });
            },
            studioLogo(item) {
                this.changeLogoTenantID = item.tenant_id;

                $("#container-change-logo").spartanMultiImagePicker({
                    fieldName: 'change-studio-logo',
                    maxCount: 1,
                    groupClassName: 'col-xs-12',
                    maxFileSize: 5000000,
                    placeholderImage: {image: item.studio_logo , width: '100%'},
                    onAddRow: function(index) {
                    },
                    onRenderedPreview : function(index){
                    },
                    onSizeErr: function(index, file){
                        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
                    }
                });
            },
            changeStudioStatus(id, status) {
                SwalGB.fire({
                    title: '¡Confirmar!',
                    text: '¿Está seguro que desea cambiar el estado del estudio?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                    cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        axios.post(
                            route('studio.change_studio_status'),
                            {id, status},
                        ).then((response) => {
                            let res = response.data;

                            if(res.success) {
                                Toast.fire({
                                    icon: "success",
                                    title: "Estudio modificado correctamente",
                                });

                                this.getStudios();
                            } else {
                                Toast.fire({
                                    icon: "error",
                                    title: "No se pudo modificar el estudio. Por favor, intente mas tarde.",
                                    timer: 8000
                                });
                            }
                        }
                        ).catch((response) => {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error al modificar el estudio. Por favor, intente mas tarde.",
                            });
                        });
                    }
                });
            },
            modalCreateStudio() {
                $("#container-logo").spartanMultiImagePicker({
                    fieldName: 'image-logo',
                    maxCount: 1,
                    groupClassName: 'col-xs-12',
                    maxFileSize: 5000000,
                    onAddRow: function(index) {
                    },
                    onRenderedPreview : function(index){
                    },
                    onSizeErr: function(index, file){
                        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
                    }
                });
            },
            createStudio() {
                if(this.formCreate.name == "") {
                    Swal.fire({
                        title: '¡Atención!',
                        text: 'Debe ingresar el nombre del estudio',
                        icon: 'warning',
                    });

                    return;
                }

                if(this.createUser) {
                    if(
                        this.formUser.name == "" ||
                        this.formUser.last_name == "" ||
                        this.formUser.email == "" ||
                        this.formUser.password == "" ||
                        this.formUser.role_id == ""
                    ) {
                        Swal.fire({
                            title: '¡Atención!',
                            text: 'Debe completar todos los campos del usuario a crear',
                            icon: 'warning',
                        });

                        return;
                    }
                }

                let logo = $('input[name="image-logo"]').prop('files')[0];
                this.formCreate.logo = logo;

                let formData = new FormData();

                formData.append("name", this.formCreate.name);
                formData.append("shared", this.formCreate.shared);
                formData.append("logo", logo);
                formData.append("create_user", this.createUser);
                formData.append("user_name", this.formUser.name);
                formData.append("user_last_name", this.formUser.last_name);
                formData.append("user_email", this.formUser.email);
                formData.append("user_password", this.formUser.password);
                formData.append("user_role_id", this.formUser.role_id);

                let headers = {'Content-Type': "multipart/form-data;"};

                SwalGB.fire({
                    title: '<span class="opacity-pulse">Creando Estudio...</span>',
                    icon: 'info',
                    html:
                        '<hr>' +
                        '<div class="row mb-2" id="step-directory"><i class="fas fa-circle-notch text-info col-2 step-1 fa-pulse" style="display: table; top: 2px;"></i> <span class="col-10 text-left">Crear carpeta del estudio...</span></div>' +
                        '<div class="row mb-2" id="step-database"><i class="fas fa-clock text-info col-2 step-2" style="display: table; top: 2px;"></i> <span class="col-10 text-left">Crear base de datos...</span></div>' +
                        '<div class="row mb-2" id="step-seed"><i class="fas fa-clock text-info col-2 step-3" style="display: table; top: 2px;"></i> <span class="col-10 text-left">Insertar datos predeterminados...</span></div>',
                    showCloseButton: false,
                    showCancelButton: false,
                    showConfirmButton: false,
                    focusConfirm: false,
                    allowOutsideClick: false,
                });

                this.$refs['modal-create'].hide();

                axios.post(
                    route('studio.create_studio'),
                    formData,
                    headers
                ).then((response) => {
                    if (response.data.success) {
                        Toast.fire({
                            icon: "success",
                            title: "¡El estudio fue creado correctamente!",
                            timer: 8000,
                        });

                        this.formCreate.name = null;
                        this.formCreate.shared = null;
                        this.createUser = null;
                        this.formUser.name = null;
                        this.formUser.last_name = null;
                        this.formUser.email = null;
                        this.formUser.password = null;
                        this.formUser.role_id = null;

                        this.getStudios();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al crear el estudio. Por favor intente mas tarde.",
                        });
                    }
                }).catch((response) => {
                    if (response.response.status == 422) {
                        helper.VUE_CallBackErrors(response.response);
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                        });
                    }
                });
            },
            onCopy(e) {
                Toast.fire({
                    icon: "success",
                    title: "¡Enlace Copiado! " + e.text,
                });
            },
            onError(e) {
                Toast.fire({
                    icon: "error",
                    title: "No se pudo copiar el enlace automáticamente: " + e.text,
                    timer: 10000,
                });
            },
            changeStudioLogo() {
                let logo = $('input[name="change-studio-logo"]').prop('files')[0];

                let formData = new FormData();
                formData.append("logo", logo);
                formData.append("tenant_id", this.changeLogoTenantID);

                let headers = {'Content-Type': "multipart/form-data;"};

                axios.post(
                    route('studio.change_studio_logo'),
                    formData,
                    headers
                ).then((response) => {
                        if (response.data.success) {
                            Toast.fire({
                                icon: "success",
                                title: "¡Se ha modificado el logo correctamente!",
                                timer: 8000,
                            });

                            this.$refs['modal-logo'].hide();
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error al modificar el logo del estudio. Por favor intente mas tarde.",
                            });
                        }
                    }
                ).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al modificar la información. Por favor, intente mas tarde.",
                    });
                });
            },
            checkIfStudioExists() {
                if(this.formCreate.name != "") {
                    this.$refs['icon-name-status'].classList.value = "fa fa-spinner fa-pulse";

                    axios.post(
                        route('studio.check_if_studio_exists'),
                        { studio_name: this.formCreate.name },
                    ).then((response) => {
                            if (response.data.exists) {
                                Toast.fire({
                                    icon: "info",
                                    title: "Ya existe un estudio creado con este nombre.",
                                    timer: 5000
                                });

                                this.$refs['icon-name-status'].classList.value = "fa fa-times text-danger";
                                this.isValid = false;
                            } else {
                                this.$refs['icon-name-status'].classList.value = "fa fa-check text-success";
                                this.isValid = true;
                            }
                        }
                    ).catch((response) => {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                        });

                        this.$refs['icon-name-status'].classList.value = "fa fa-times text-danger";
                    });
                } else {
                    this.isValid = false;
                }
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
        },
    }
</script>

<style scoped>
    @-webkit-keyframes opacityPulse {
        0% {opacity: 0.0;}
        50% {opacity: 1.0;}
        100% {opacity: 0.0;}
    }

    .opacity-pulse {
        animation: opacityPulse 2s ease-out;
        animation-iteration-count: infinite;
        opacity: 1;
    }
</style>
