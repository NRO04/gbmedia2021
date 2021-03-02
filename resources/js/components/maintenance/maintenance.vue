<template>
    <div id="container-maintenance" class="row">
        <div class="card col-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Mantenimiento</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right" @click="modalCreateStudio()">
                    <a class="btn btn-success btn-sm" v-b-modal.modal-create v-if="can('maintenance-tasks-create')">
                        <i class="fa fa-plus"></i> Crear
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="select-status" class="col-12 col-md-1">Estado:</label>
                    <div class="col-12 col-md-2">
                        <b-form-select v-model="statusID" size="sm" id="select-status">
                            <b-form-select-option value="1">Pendientes</b-form-select-option>
                            <b-form-select-option value="3">Finalizados</b-form-select-option>
                        </b-form-select>
                    </div>
                    <label for="select-location" class="col-12 col-md-2 text-md-right">Locación:</label>
                    <div class="col-12 col-md-2">
                        <b-form-select v-model="locationID" size="sm" @change="getMaintenanceTasks()" id="select-location">
                            <b-form-select-option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                </div>
                <div v-if="statusID == 1">
                    <pending-maintenance :key="pendingKey" :location_id="locationID" :permissions="permissions"></pending-maintenance>
                </div>
                <div v-else-if="statusID == 3">
                    <finished-maintenance :key="finishedKey" :location_id="locationID"></finished-maintenance>
                </div>
            </div>
        </div>

        <b-modal id="modal-create" title="Crear Trabajo de Mantenimiento" scrollable size="md" header-bg-variant="primary" header-close-content="" ref="modal-create">
            <form id="form-create-studio">
                <div class="form-group">
                    <label for="name" class="required">Nombre:</label>
                    <input class="form-control" id="name" name="name" type="text" v-model="formCreate.name" placeholder="Arreglar goteo aire acondicionado cuarto 2"/>
                </div>
                <div class="form-group">
                    <label for="user-location-id" class="required">Locación:</label>
                    <div class="input-group">
                        <b-form-select v-model="formCreate.location_id" id="user-location-id">
                            <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                            <b-form-select-option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                </div>
            </form>
            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="success" @click="createMaintenanceTask()" v-if="btnCreate">
                            <i class="fas fa-plus"></i> Crear
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import * as helper from '../../../../public/js/vue-helper.js'
    import PendingMaintenance from "./partials/pending-maintenance";
    import FinishedMaintenance from "./partials/finished-maintenance";

    export default {
        components: {FinishedMaintenance, PendingMaintenance},
        props: [
            'permissions',
            'locations',
        ],
        data() {
            return {
                show: true,
                selected: [],
                formCreate: new Form({
                    name: '',
                    location_id: 0,
                }),
                isValid: false,
                locationID: 2,
                statusID: 1,
                pendingKey: 1,
                finishedKey: 2,
            }
        },
        computed: {
            btnCreate() {
                return this.formCreate.name !== '' && this.formCreate.location_id !== 0
            }
        },
        mounted() {
        },
        created() {
            //this.getMaintenanceTasks();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getMaintenanceTasks() {
                if(parseInt(this.statusID) === 1) {
                    this.pendingKey += 1;
                }
                else if (parseInt(this.statusID) === 3) {
                    this.finishedKey += 1;
                }
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
            modalCreateStudio() {
                helper.VUE_ResetValidations();
            },
            createMaintenanceTask() {
                helper.VUE_ResetValidations();
                helper.VUE_DisableModalActionButtons();

                axios.post(
                    route('maintenance.create_maintenance_task'),
                    {name: this.formCreate.name, location_id: this.formCreate.location_id}
                ).then((response) => {
                    let res = response.data;

                    if(res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Trabajo creado correctamente",
                        });

                        // Clear form imputs
                        this.formCreate.name = "";
                        this.formCreate.location_id = 0;

                        helper.VUE_ResetModalForm("#form-create");

                        this.pendingKey += 1;
                        this.$refs['modal-create'].hide();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "No se pudo asignar el trabajo. Por favor, intente mas tarde.",
                            timer: 8000
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                }).catch((res) => {
                    helper.VUE_CallBackErrors(res.response);
                });
            },
        },
    }
</script>

<style scoped>

</style>
