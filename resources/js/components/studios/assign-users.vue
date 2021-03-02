<template>
    <div id="container-assign-users" class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Usuarios Asignados a Estudios</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    <a class="btn btn-success btn-sm" v-b-modal.modal-assign v-if="can('studio-assign-user-create')">
                        <i class="fa fa-plus"></i> Asignar Nuevo
                    </a>
                    <a class="btn btn-info btn-sm" :href="projectRoute('studio.list')" v-if="can('studio')">
                        <i class="fa fa-th-list"></i> &nbsp;Listado Estudios
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-2">
                        <b-form-select class="float-right" v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
                    </div>
                    <div class="col-12 col-md-4 push-3">
                        <b-input-group size="sm">
                            <b-form-input debounce="500" v-model.trim="filter" type="text" id="filterInput" placeholder="Buscar..."></b-form-input>
                        </b-input-group>
                    </div>
                </div>
                <hr>
                <b-table show-empty small stacked="sm" :fields="fields"
                         :items="items" responsive="sm" class="table-striped"
                         :current-page="currentPage" :per-page="perPage" :filter="filter"
                         :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" ref="table"
                         selected-variant="info" @row-selected="onRowSelected"
                         :busy.sync="isBusy" empty-text="No hay usuarios asignados a estudios"
                >
                    <template #cell(actions)="data">
                        <div @click="openAssignStudioModal(data.item)" v-if="can('studio-assign-user-edit')">
                            <b-button class="btn btn-warning btn-sm" v-b-modal.modal-assign-studios>
                                <i class="fa fa-edit"></i>
                            </b-button>
                        </div>
                    </template>

                    <template #cell(assigned_to)="data">
                        <span :title="data.item.assigned_to_names" v-b-tooltip.hover class="badge badge-secondary">
                            {{ data.item.assigned_to.length }}
                        </span>
                    </template>
                </b-table>
            </div>
            <div class="card-footer">
                <div class="float-left">
                    Total <b>{{ items.length }}</b> registros
                </div>
                <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm" class="float-right"></b-pagination>
            </div>
        </div>

        <b-modal id="modal-assignments" :title="'Asignaciones del Usuario: ' + removeUserSelectedName" scrollable size="md" header-bg-variant="primary" header-close-content="" ref="modal-assignments">
            <div class="row mb-3">
                <div class="col-md-3">
                    <b-form-select class="float-right" v-model="perPageAssignments" id="perPageSelect" size="sm" :options="pageOptionsAssignments"></b-form-select>
                </div>
                <div class="col-12 col-md-9 push-3">
                    <b-input-group size="sm">
                        <b-form-input debounce="500" v-model.trim="filterAssignments" type="text" id="filterInput" placeholder="Buscar..."></b-form-input>
                    </b-input-group>
                </div>
            </div>
            <hr>
            <b-table show-empty small stacked="sm" :fields="fieldsAssignments"
                     :items="itemsAssignments" responsive="sm" class="table-striped"
                     :current-page="currentPageAssignments" :per-page="perPageAssignments" :filter="filterAssignments"
                     :sort-by.sync="sortByAssignments" :sort-desc.sync="sortDescAssignments" ref="table"
                     selected-variant="info" @row-selected="onRowSelected"
                     :busy.sync="isBusy" empty-text="No hay estudios asignados al usuario"
            >

                <template #cell(studio_id)="data">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" v-model="assignmentsStudiosToRemove" :value="data.item.studio_id">
                    </div>
                </template>
            </b-table>

            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="danger" @click="removeAssignments()" v-if="btnDelete">
                            <i class="fas fa-trash"></i> Eliminar
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>

        <b-modal id="modal-assign" title="Asignar Usuario a Estudios" scrollable size="md" header-bg-variant="primary" header-close-content="" ref="modal-assign">
            <div class="form-group row">
                <div class="col-12">
                    <label for="select-users" class="col-form-label">
                        Usuario:
                    </label>
                    <b-form-select v-model="selectedUser" id="select-users">
                        <b-form-select-option value="0">Seleccione usuario</b-form-select-option>
                        <b-form-select-option v-for="user in users" :value="user.id" :key="user.id">{{ user.first_name }} {{ user.last_name }}</b-form-select-option>
                    </b-form-select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-12">
                    <label class="col-form-label">
                        Estudios:
                    </label>
                    <div class="col-12 row">
                        <div class="form-check col-12">
                            <input type="checkbox" class="form-check-input" @click="checkSelectAll()" v-model="selectAll" id="select-all">
                            <label class="form-check-label" for="select-all">Seleccionar todos</label>
                        </div>
                        <hr>
                        <div class="form-check col-12 col-sm-6 col-md-4" v-for="studio in assignments" v-bind:key="studio.has_tenant_id">
                            <input type="checkbox" class="form-check-input check-tenant-id" v-model="assignToTenants" :id="studio.has_tenant_id" :value="studio.has_tenant_id">
                            <label class="form-check-label" :for="studio.has_tenant_id">{{ studio.has_tenant.studio_name }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="info" @click="assignUserToStudios()">
                            <i class="fas fa-plus"></i> Asignar
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
            'users',
            'assignments',
            'current_tenant_id',
            'permissions',
        ],
        data() {
            return {
                isBusy: false,
                sortBy: 'email',
                sortDesc: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 50,
                filter: null,
                pageOptions: [50, 100, 200],
                items: [],
                fields: [
                    {key: 'user_name', label: 'Usuario', sortable: true},
                    {key: 'user_role', label: 'Rol', sortable: true},
                    {key: 'assigned_to', label: 'Asignado a', sortable: true},
                    // {key: 'assigned_date', label: 'Última Asignación', sortable: true},
                    {key: 'actions', label: 'Editar', sortable: false, class: (!this.can('studio-assign-user-edit') ? 'd-none' : '')},
                ],
                sortByAssignments: 'email',
                sortDescAssignments: false,
                totalRowsAssignments: 1,
                currentPageAssignments: 1,
                perPageAssignments: 50,
                pageOptionsAssignments: [50, 100, 200],
                filterAssignments: null,
                itemsAssignments: [],
                fieldsAssignments: [
                    {key: 'studio_name', label: 'Estudio', sortable: true},
                    {key: 'studio_id', label: 'Eliminar', sortable: false},
                ],
                validationErrors: [],
                selectedUser: 0,
                assignToTenants: [],
                assignmentsStudiosToRemove: [],
                selectAll: false,
                selectedTenantID: null,
                removeUserSelectedID: null,
                removeUserSelectedName: null,
            }
        },
        computed: {
            btnDelete() {
                return this.assignmentsStudiosToRemove.length > 0
            },
            computedSelectedAll: {
                get: function () {
                    return this.users ? this.selected.length == this.users.length : false;
                },
                set: function (value) {
                    let selected = [];

                    if (value) {
                        this.users.forEach(function (user) {
                            selected.push(user.id);
                        });
                    }

                    this.assignToTenants = selected;
                }
            }
        },
        mounted() {
            $('#global-spinner').removeClass('d-none');
        },
        created() {
            this.getAssignedUsers();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getAssignedUsers() {
                axios.get(route('studio.get_assigned_users')).then(response => {
                    this.items = response.data;
                    this.totalRows = response.data.length;
                    $('#global-spinner').addClass('d-none');
                });
            },
            assignUserToStudios() {
                if(this.selectedUser == 0 || this.assignToTenants.length === 0) {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar el usuario y los estudios que desea asignar",
                    });

                    return;
                }

                helper.VUE_DisableModalActionButtons();

                axios.post(
                    route('studio.assign_user_to_tenant'),
                    {
                        user_id: this.selectedUser,
                        tenants: this.assignToTenants
                    }
                ).then((response) => {
                    let res = response.data;

                    if(res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Usuario asignado correctamente",
                        });

                        this.$refs['modal-assign'].hide();
                        this.getAssignedUsers();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "No se pudo asignar el usuario. Por favor, intente mas tarde.",
                            timer: 8000
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.",
                    });

                    helper.VUE_EnableModalActionButtons();
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
            openAssignStudioModal(data) {
                this.itemsAssignments = data.assigned_to;
                this.totalRowsAssignments = data.assigned_to.length;

                this.removeUserSelectedID = data.user_id;
                this.removeUserSelectedName = data.user_name;

                this.$refs['modal-assignments'].show();
            },
            removeAssignments() {
                helper.VUE_DisableModalActionButtons();

                axios.post(
                    route('studio.remove_assignments'),
                    {
                        tenants: this.assignmentsStudiosToRemove,
                        user_id: this.removeUserSelectedID,
                    }
                ).then((response) => {
                    let res = response.data;

                    if(res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Proceso completado correctamente",
                        });

                        this.$refs['modal-assignments'].hide();
                        this.assignmentsStudiosToRemove = [];
                        this.removeUserSelectedID = null;
                        this.getAssignedUsers();
                        helper.VUE_EnableModalActionButtons();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "No se pudo completar el proceso. Por favor, intente mas tarde.",
                            timer: 8000
                        });
                    }
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al completar el proceso. Por favor, intente mas tarde.",
                    });

                    helper.VUE_EnableModalActionButtons();
                });
            },
            checkSelectAll() {
                let _this = this;
                let items = $('.check-tenant-id');

                $('.check-tenant-id').prop('checked', !this.selectAll);

                if(!this.selectAll) {
                    $.each(items, function (i, item) {
                        _this.assignToTenants.push(parseInt($(item).val()))
                    });
                } else {
                    $.each(items, function (i, item) {
                        _this.assignToTenants = [];
                    });
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
