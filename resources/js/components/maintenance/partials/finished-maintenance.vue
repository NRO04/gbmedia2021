<template>
    <div id="container-finished-maintenance" class="row">
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
                     :busy.sync="isBusy" empty-text="No hay trabajos de mantenimientos finalizados"
            >
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
                show: true,
                isBusy: false,
                sortBy: 'date',
                sortDesc: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 50,
                pageOptions: [50, 100, 200],
                filter: null,
                fields: [
                    {key: 'created_date', label: 'Fecha Creación', sortable: true,},
                    {key: 'name', label: 'Título', sortable: true},
                    {key: 'finish_date', label: 'Fecha Finalizado', sortable: false},
                    {key: 'total_time', label: 'Tiempo Transcurrido', sortable: false},
                    {key: 'location', label: 'Locación', sortable: false},
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
            this.getFinishedMaintenanceTasks();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getFinishedMaintenanceTasks() {
                this.show = true;
                axios.get(route('maintenance.get_finished_maintenance_tasks', {location_id: this.locationID})).then(response => {
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
        },
    }
</script>

<style scoped>

</style>
