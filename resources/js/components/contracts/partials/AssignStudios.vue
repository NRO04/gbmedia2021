<template>
    <div id="container-assign-studios">
        <div class="col-12 bg-danger">
            FALTA: Colocar el compartir automatico al crear un estudio (eso va a Configuraciones del Estudio)
        </div>
        <div class="card col-12">
            <div class="card-header row">
                <div class="col-12 col-sm-6 mb-2">
                    <span class="span-title">Asignar Estudios</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    <a class="btn btn-primary btn-sm mt-1" v-b-modal.modal-config>
                        <i class="fa fa-cog"></i> &nbsp;Configuración
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <b-table show-empty small stacked="sm" :fields="fields"
                             :items="items" responsive="sm" class="table-striped"
                             :current-page="currentPage" :per-page="perPage" :filter="filter"
                             :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" ref="table"
                             selected-variant="info"
                             :busy.sync="isBusy" empty-text="No hay estudios"
                    >

                        <template #cell(active)="data">
                            <div class="row">
                                <div class="col-1">
                                    <b-form-checkbox value="1" unchecked-value="0" :disabled="data.item.id === 1" :checked="data.item.active" @change="changeStudioStatus(data.item.id, $event)"></b-form-checkbox>
                                </div>
                                <div class="col-1">
                                    <span :id="'loader-' + data.item.id" class="d-none">
                                        <i class="fa fa-pulse fa-spinner"></i>
                                    </span>
                                </div>
                            </div>
                        </template>
                    </b-table>
                </div>
            </div>
        </div>

        <b-modal id="modal-config" title="Gestionar Acceso en Estudios" scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="modal-config">
            <div class="form-group">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <b-form-checkbox class="form-check-input" id="share-automatic" value="1" unchecked-value="0" v-model="formConfig.share_automatic"></b-form-checkbox>
                            <label for="share-automatic" class="form-check-label mt-1">Compartir Automáticamente:</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <b-form-radio class="form-check-input" id="select-studios" value="1" v-model="formConfig.select_tenants" name="select-studios"></b-form-radio>
                            <label for="select-studios" class="form-check-label mt-1">Seleccionar Estudios:</label>
                        </div>
                        <div class="col-12 col-md-4">
                            <b-form-radio class="form-check-input" id="all-studios" value="2" v-model="formConfig.select_tenants" name="select-studios"></b-form-radio>
                            <label for="all-studios" class="form-check-label mt-1">Todos los Estudios ({{ items.length }}):</label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-12 mt-2" id="container-options">
                    <div class="row">
                        <div class="col-12">
                            <b-form-radio class="form-check-input" id="enable-module" value="1" v-model="formConfig.option" name="status-module"></b-form-radio>
                            <label for="enable-module" class="form-check-label mt-1">Habilitar Módulo</label>
                        </div>
                        <div class="col-12">
                            <b-form-radio class="form-check-input" id="disable-module" value="0" v-model="formConfig.option" name="status-module"></b-form-radio>
                            <label for="disable-module" class="form-check-label mt-1">Deshabilitar Módulo</label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-12 mt-2" id="container-studios" v-if="formConfig.select_tenants == 1">
                    <div class="row">
                        <div class="col-12">
                            <b-form-select v-model="formConfig.selected_tenants" id="role" name="studio" multiple>
                                <b-form-select-option v-for="studio in items" :key="studio.id" :value="studio.id">{{ studio.studio }}</b-form-select-option>
                            </b-form-select>
                        </div>
                    </div>
                </div>
            </div>

            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="warning" @click="changeStudioStatusBulk()" v-if="btnEdit">
                            <i class="fa fa-edit"></i> Editar
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import * as helper from '../../../../../public/js/vue-helper.js'

    export default {
        name: "AssignStudios",
        data() {
            return {
                isBusy: false,
                sortBy: 'id',
                sortDesc: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 50,
                pageOptions: [50, 100, 200],
                filter: null,
                fields: [
                    {key: 'id', label: '#', sortable: true},
                    {key: 'studio', label: 'Estudio', sortable: true},
                    {key: 'active', label: 'Activo', sortable: false},
                ],
                items: [],
                formConfig: new Form({
                    share_automatic: 0,
                    selected_tenants: [],
                    select_tenants: null,
                    option: null,
                }),
            }
        },
        computed: {
            btnEdit() {
                return this.formConfig.option != null &&
                    this.formConfig.select_tenants != null || this.formConfig.select_tenants == 2 &&
                    this.formConfig.selected_tenants.length > 0
            }
        },
        created() {
            this.getStudios();
            $('#global-spinner').removeClass('d-none');
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getStudios() {
                axios.get(route("contracts.get_studios")).then(response => {
                    this.items = response.data;
                    this.totalRows = response.data.length;
                    $('#global-spinner').addClass('d-none');
                });
            },
            changeStudioStatus(id, status) {
                let active = parseInt(status);
                $('#loader-' + id).removeClass('d-none');

                axios.post(
                    route("contracts.change_studio_status"),
                    {
                        id,
                        active,
                    },
                ).then((response) => {
                    let res = response.data;

                    if(res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Estatus del estudio cambiado correctamente",
                        });

                        this.getStudios();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "No se pudo activar el módulo. Por favor, intente mas tarde.",
                            timer: 8000
                        });
                    }

                    $('#loader-' + id).addClass('d-none');
                }).catch((res) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al guardar la información. Por favor intente mas tarde.",
                        timer: 8000,
                    });

                    $('#loader-' + id).addClass('d-none');
                });
            },
            changeStudioStatusBulk() {
                axios.post(
                    route("contracts.change_studio_status_bulk"),
                    this.formConfig
                ).then((response) => {
                    let res = response.data;

                    if(res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Información actualizada correctamente",
                        });

                        this.getStudios();
                        this.$refs['modal-config'].hide();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "No se pudo actualizar la información. Por favor, intente mas tarde.",
                            timer: 8000
                        });
                    }
                }).catch((res) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al guardar la información. Por favor intente mas tarde.",
                        timer: 8000,
                    });
                });
            },
        },
    }
</script>

<style scoped>

</style>
