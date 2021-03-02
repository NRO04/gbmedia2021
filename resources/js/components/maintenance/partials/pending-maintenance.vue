<template>
    <div id="container-pending-maintenance" class="row">
        <b-overlay :show="show" no-wrap></b-overlay>

        <div class="card-body col-12">
            <div class="row mb-3">
                <div class="col-md-2">
                    <b-form-select class="float-right" v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
                </div>
                <div class="col-12 col-md-3">
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
                     :busy.sync="isBusy" empty-text="No hay trabajos de mantenimientos pendientes"
            >
                <template #cell(viewed)="data">
                    <i style="cursor: pointer;" class="fa fa-eye" :class="data.item.viewed === 0 ? 'text-danger pulsing-active' : 'text-info'" :title="(data.item.viewed === 0 ? 'Marcar como Visto' : 'Visto')" v-b-tooltip.hover @click="markAsViewed(data.item.id, data.item.viewed)"></i>
                </template>

                <template #cell(is_pending)="data">
                    <button class="btn btn-outline-info btn-sm" :title="(data.item.comment === '' || data.item.comment == null ? 'Marcar como Realizado' : 'Comentario: ' + data.item.comment)" v-b-tooltip.hover v-if="data.item.is_pending" @click="markAsDone(data.item.id)">
                        <i class="fa fa-check"></i>
                    </button>
                    <span v-else>
                        <i class="fa fa-check text-success" :title="data.item.updated_date" v-b-tooltip.hover></i>
                    </span>
                </template>

                <template #cell(is_verified)="data">
                    <div v-if="data.item.is_verified">
                        <button class="btn btn-outline-success btn-sm" title="Aceptar" v-b-tooltip.hover @click="markAsVerified(data.item.id)">
                            <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" title="Rechazar" v-b-tooltip.hover @click="markAsRejected(data.item.id)">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </template>
            </b-table>
        </div>
        <div class="col-12 card-footer">
            <div class="float-left">
                Total <b>{{ items.length }}</b> registros
            </div>
            <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm" class="float-right"></b-pagination>
        </div>
    </div>
</template>

<script>
    import * as helper from '../../../../../public/js/vue-helper'

    export default {
        components: {},
        props: [
            'permissions',
            'locations',
            'location_id',
        ],
        data() {
            return {
                //show: true,
                isBusy: false,
                sortBy: 'date',
                sortDesc: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 50,
                pageOptions: [50, 100, 200],
                filter: null,
                fields: [
                    {key: 'viewed', label: 'Visto', sortable: true,},
                    {key: 'date', label: 'Fecha', sortable: true,},
                    {key: 'name', label: 'Título', sortable: true},
                    {key: 'is_pending', label: 'Realizado', sortable: false},
                    {key: 'is_verified', label: 'Verificado', sortable: false, class: (!this.can('maintenance-task-status') ? 'd-none' : '')},
                    // {key: 'assign', label: 'Asignar Estudios', sortable: false, class: (!this.can('studio-assign-view') ? 'd-none' : '')},
                ],
                items: [],
                selected: [],
                formCreate: new Form({
                    name: '',
                    location_id: 0,
                }),
                isValid: false,
                locationID: this.location_id,
                statusID: 1,
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
            this.getMaintenanceTasks();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getMaintenanceTasks() {
                this.show = true;
                axios.get(route('maintenance.get_maintenance_tasks', {location_id: this.locationID})).then(response => {
                    this.items = response.data;
                    this.totalRows = response.data.length;
                    this.show = false;
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

                        this.getMaintenanceTasks();
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
            markAsDone(id) {
                SwalGB.fire({
                    title: '¿Está seguro que desea marcar como realizado este trabajo?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    allowOutsideClick: true,
                }).then((result) => {
                    if (result.value) {
                        axios.post(
                            route('maintenance.mark_as_done'),
                            {id}
                        ).then((response) => {
                            let res = response.data;

                            if(res.success) {
                                Toast.fire({
                                    icon: "success",
                                    title: "Trabajo actualizado correctamente",
                                });

                                this.getMaintenanceTasks();
                            } else {
                                Toast.fire({
                                    icon: "error",
                                    title: "No se pudo actualizar la información el trabajo. Por favor, intente mas tarde.",
                                    timer: 8000
                                });
                            }
                        }).catch((response) => {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.",
                            });
                        });
                    }
                });
            },
            markAsVerified(id) {
                SwalGB.fire({
                    title: '¿Está seguro que desea marcar como verificado este trabajo?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    allowOutsideClick: true,
                }).then((result) => {
                    if (result.value) {
                        axios.post(
                            route('maintenance.mark_as_verified'),
                            {id}
                        ).then((response) => {
                            let res = response.data;

                            if(res.success) {
                                Toast.fire({
                                    icon: "success",
                                    title: "Trabajo actualizado correctamente",
                                });

                                if(res.count === 0) { // Remove pulsing from menu icon
                                    helper.VUE_removePulsingFromMenuIcon('#maintenance-pulsing');
                                }
                                this.getMaintenanceTasks();
                            } else {
                                Toast.fire({
                                    icon: "error",
                                    title: "No se pudo actualizar la información el trabajo. Por favor, intente mas tarde.",
                                    timer: 8000
                                });
                            }
                        }).catch((response) => {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.",
                            });
                        });
                    }
                });
            },
            markAsRejected(id) {
                SwalGB.fire({
                    title: '¿Está seguro que desea marcar como rechazado este trabajo?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    allowOutsideClick: true,
                    input: 'text',
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.post(
                            route('maintenance.mark_as_rejected'),
                            {
                                id,
                                comment: result.value
                            }
                        ).then((response) => {
                            let res = response.data;

                            if(res.success) {
                                Toast.fire({
                                    icon: "success",
                                    title: "Trabajo actualizado correctamente",
                                });

                                this.getMaintenanceTasks();
                            } else {
                                Toast.fire({
                                    icon: "error",
                                    title: "No se pudo actualizar la información el trabajo. Por favor, intente mas tarde.",
                                    timer: 8000
                                });
                            }
                        }).catch((response) => {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.",
                            });
                        });
                    }
                });
            },
            markAsViewed(id, status) {
                if(status === 0) {
                    axios.post(
                        route('maintenance.mark_as_viewed'),
                        {id}
                    ).then((response) => {
                        let res = response.data;

                        if(res.success) {
                            Toast.fire({
                                icon: "success",
                                title: "Trabajo actualizado correctamente",
                            });

                            this.getMaintenanceTasks();

                            if(res.count === 0) { // Remove pulsing from menu icon
                                helper.VUE_removePulsingFromMenuIcon('#maintenance-pulsing');
                            }
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: "No se pudo actualizar la información el trabajo. Por favor, intente mas tarde.",
                                timer: 8000
                            });
                        }
                    }).catch((response) => {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.",
                        });
                    });
                }
            },
        },
    }
</script>

<style scoped>

</style>
