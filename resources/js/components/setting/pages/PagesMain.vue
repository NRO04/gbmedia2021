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
                            <div class="col-lg-7">
                                <span class="span-title">Listado de Páginas </span>
                            </div>
                            <div class="col-lg-5">
                                <button class="btn btn-m btn-success float-right btn-sm" data-toggle="modal" data-target="#modal-maintenance"><i
                                    class="fa fa-plus"></i> Crear</button>
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
                        <b-table show-empty small stacked="md" :items="pages" :fields="fields" :current-page="currentPage" :per-page="perPage"
                                 :filter="filter" :filter-included-fields="filterOn"
                                 @filtered="onFiltered" :busy ="isBusy">

                            <template #cell(create_task)="row">
                                <button class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button>
                            </template>
                            <template #cell(tasks)="row">
                                <button class="btn btn-info btn-sm"><i class="fa fa-list"></i></button>
                            </template>
                            <template #cell(edit)="row">
                                <button class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></button>
                            </template>
                            <template #cell(admin)="row">
                                <button class="btn btn-dark btn-sm"><i class="fa fa-check"></i></button>
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
                        <h4 class="modal-title"> Vista en Manteniento</h4>
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
    import PlatformInMaintenance from "../../PlatformInMaintenance";
    export default {
        name: "PagesMain",
        data(){
            return{
                pages: [],
                isBusy: false,
                show: false,
                fields: [
                    { key: 'name', label: 'Nombre', sortable: true },
                    { key: 'create_task', label: 'Crear Tareas', sortable: true },
                    { key: 'tasks', label: 'Tareas de Página\t', sortable: true },
                    { key: 'edit', label: 'Editar', sortable: true },
                    { key: 'admin', label: 'Admin'},

                ],
                totalRows: 1,
                currentPage: 1,
                perPage: 100,
                pageOptions: [100, 200, 300, { value: 400, text: "Show a lot" }],
                filter: null,
                filterOn: [],
            }
        },
        created() {
            this.getPages();
        },
        methods:{
            getPages(){
                this.show = true;
                axios.get(route('setting.page.get_pages')).then(response => {
                    console.log(response.data);
                    this.pages = response.data;
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
