<template>
    <div class="row">
        <div class="col-lg-12">
            <b-overlay :show="show" rounded="sm" variant="dark" opacity="0.8">
                <template #overlay>
                    <div class="text-center">
                        <b-icon
                            icon="stopwatch"
                            variant="info"
                            font-scale="3"
                            animation="fade"
                        ></b-icon>
                        <p id="cancel-label">Espere por favor...</p>
                    </div>
                </template>
                <div class="card table-responsive">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-7">
                                <span class="span-title"
                                    >Listado de Páginas
                                </span>
                            </div>
                            <div class="col-lg-5">
                                <button
                                    class="btn btn-m btn-success float-right btn-sm"
                                    data-toggle="modal"
                                    data-target="#modal-maintenance"
                                >
                                    <i class="fa fa-plus"></i> Crear
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <b-row class="mb-5 justify-content-between">
                            <b-col sm="2" md="2" class="my-1">
                                <b-form-group class="mb-0">
                                    <b-form-select
                                        v-model="perPage"
                                        id="perPageSelect"
                                        size="sm"
                                        :options="pageOptions"
                                    ></b-form-select>
                                </b-form-group>
                            </b-col>
                            <b-col lg="4" class="my-1">
                                <b-form-group class="mb-0">
                                    <b-form-input
                                        v-model="filter"
                                        type="search"
                                        id="filterInput"
                                        placeholder="Type to Search"
                                        size="sm"
                                    ></b-form-input>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <!-- Main table element -->
                        <b-table
                            show-empty
                            small
                            stacked="md"
                            :items="pages"
                            :fields="fields"
                            :current-page="currentPage"
                            :per-page="perPage"
                            :filter="filter"
                            :filter-included-fields="filterOn"
                            @filtered="onFiltered"
                            :busy="isBusy"
                        >
                            <template #cell(create_task)="row">
                                <button
                                    v-on:click="page_id = row.item.id"
                                    class="btn btn-success btn-sm"
                                    data-target="#modal-ro"
                                    data-toggle="modal"
                                >
                                    <i class="fa fa-plus"></i>
                                </button>
                            </template>
                            <template #cell(tasks)="row">
                                <button
                                    data-toggle="modal"
                                    data-target="#modal-tasks"
                                    class="btn btn-info btn-sm"
                                    v-on:click="getTasks(row.item.id)"
                                >
                                    <i class="fa fa-list"></i>
                                </button>
                            </template>
                            <template #cell(edit)="row">
                                <button class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </template>
                            <template #cell(admin)="row">
                                <button class="btn btn-dark btn-sm">
                                    <i class="fa fa-check"></i>
                                </button>
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

        <!-- Modal Form  Task-->

        <div id="modal-ro" class="modal fade z-i-modal" role="dialog">
            <div class="modal-dialog modal-primary">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Crear Tarea para ({{ filterNamepage }})
                        </h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-create" method="post">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input
                                    class="form-control"
                                    id="name"
                                    name="name"
                                    value="taskName"
                                    placeholder="Nombre de tarea"
                                    type="text"
                                    v-model="taskName"
                                />
                            </div>
                            <div class="form-group">
                                <label for="page_task_type_id"
                                    >Tipo de Tarea</label
                                >

                                <b-form-select
                                    v-model="seletedType"
                                    id="page_task_type_id"
                                    size="sm"
                                    :options="pagesTypes"
                                >
                                    ></b-form-select
                                >
                            </div>

                            <div v-if="seletedType == 2 || seletedType == 3">
                                <div class="form-group">
                                    <label for="name">Cantidad</label>
                                    <input
                                        class="form-control"
                                        id="name"
                                        name="name"
                                        placeholder="Cantidad de opciones"
                                        type="text"
                                        v-model="numberInputs"
                                    />
                                </div>
                                <div class="form-group overflow">
                                    <div
                                        class="bottom fade-ro"
                                        v-for="index in parseInt(nInputs)"
                                        :key="index"
                                    >
                                        <input
                                            class="form-control"
                                            id="name"
                                            name="setting_options_task"
                                            placeholder="nombre"
                                            type="text"
                                            v-model="optionsType[index - 1]"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Descripción</label>
                                <textarea
                                    v-model="description"
                                    class="form-control"
                                    cols="30"
                                    id="description"
                                    name="description"
                                    placeholder="Descripción de tarea"
                                    rows="4"
                                ></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            class="btn btn-danger"
                            data-dismiss="modal"
                            type="button"
                            @click="clearFiels"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            id="btn_create"
                            @click="createTask"
                        >
                            Aceptar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit  Task-->

        <div id="modal-edit" class="modal fade z-i-modal" role="dialog">
            <div class="modal-dialog modal-warning">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Editar Tarea
                        </h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-create" method="post">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input
                                    class="form-control"
                                    id="name"
                                    name="name"
                                    value="tasksActions.name"
                                    placeholder="Nombre de tarea"
                                    type="text"
                                    v-model="tasksActions.name"
                                />
                            </div>
                            <div class="form-group">
                                <label for="name">Tipo de Tarea</label>

                                <b-form-select
                                    value="tasksActions.taskType_id"
                                    v-model="tasksActions.taskType_id"
                                    id="perPageSelect"
                                    size="sm"
                                    :options="pagesTypes"
                                >
                                    ></b-form-select
                                >
                            </div>

                            <div
                                v-if="
                                    tasksActions.taskType_id == 2 ||
                                        tasksActions.taskType_id == 3
                                "
                            >
                                <div class="form-group">
                                    <label for="name">Cantidad</label>
                                    <input
                                        class="form-control"
                                        id="name"
                                        name="name"
                                        placeholder="Cantidad de opciones"
                                        type="text"
                                        v-model="tasksActions.nInputs"
                                    />
                                    <!-- v-model="tasksActions.length" -->
                                </div>
                                <div class="form-group overflow">
                                    <div
                                        class="bottom fade-ro"
                                        v-for="index in optionsLen"
                                        :key="index"
                                    >
                                        <input
                                            class="form-control"
                                            id="name"
                                            name="name"
                                            placeholder="nombre"
                                            type="text"
                                            v-model="
                                                tasksActions.options[index - 1]
                                            "
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Descripción</label>
                                <textarea
                                    v-model="tasksActions.description"
                                    aria-valuetext="tasksActions.description"
                                    class="form-control"
                                    cols="30"
                                    id="description"
                                    name="description"
                                    placeholder="Descripción de tarea"
                                    rows="4"
                                ></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            class="btn btn-danger"
                            data-dismiss="modal"
                            type="button"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            id="btn_create"
                            v-on:click="updateTask"
                        >
                            Aceptar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal show Pages's Tasks-->
        <div id="modal-tasks" class="modal fade" role="dialog">
            <div class="modal-dialog modal-primary modal-lg ">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Listado de Tareas ({{ filterNamepage }})
                        </h4>
                    </div>
                    <div class="modal-body">
                        <b-overlay
                            :show="show"
                            rounded="sm"
                            variant="dark"
                            opacity="0.8"
                        >
                            <template #overlay>
                                <div class="text-center">
                                    <b-icon
                                        icon="stopwatch"
                                        variant="info"
                                        font-scale="3"
                                        animation="fade"
                                    ></b-icon>
                                    <p id="cancel-label">Espere por favor...</p>
                                </div>
                            </template>
                            <div class="card table-responsive">
                                <div class="card-body">
                                    <b-row class="mb-5 justify-content-between">
                                        <b-col sm="2" md="2" class="my-1">
                                            <b-form-group class="mb-0">
                                                <b-form-select
                                                    v-model="perPage"
                                                    id="perPageSelect"
                                                    size="sm"
                                                    :options="pageOptions"
                                                ></b-form-select>
                                            </b-form-group>
                                        </b-col>
                                        <b-col lg="4" class="my-1">
                                            <b-form-group class="mb-0">
                                                <b-form-input
                                                    v-model="filter"
                                                    type="search"
                                                    id="filterInput"
                                                    placeholder="Type to Search"
                                                    size="sm"
                                                ></b-form-input>
                                            </b-form-group>
                                        </b-col>
                                    </b-row>
                                    <!-- Main table element -->
                                    <b-table
                                        show-empty
                                        small
                                        stacked="md"
                                        :items="tsks"
                                        :fields="tasksFields"
                                        :current-page="currentPage"
                                        :per-page="perPage"
                                        :filter="filter"
                                        :filter-included-fields="filterOn"
                                        @filtered="onFiltered"
                                        :busy="isBusy"
                                    >
                                        <!-- <template #cell(details)="row">
                                            <button
                                                v-on:click="
                                                    page_id =
                                                        row.item.taskType_id
                                                "
                                                class="btn btn-danger btn-sm"
                                            >
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </template> -->
                                        <template #cell(edit_task)="row">
                                            <button
                                                data-toggle="modal"
                                                data-target="#modal-edit"
                                                class="btn btn-warning btn-sm"
                                                v-on:click="editTask(row.item)"
                                            >
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </template>
                                        <!-- <template #cell(delete_task)="row">
                                            <button
                                                v-on:click="
                                                    deleteTask(row.item.id)
                                                "
                                                class="btn btn-danger btn-sm"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </template> -->
                                    </b-table>
                                    <b-row class="justify-content-end">
                                        <b-col
                                            sm="12"
                                            md="12"
                                            lg="12"
                                            class="my-1"
                                        >
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
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-maintenance" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-lg bg-primary">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Vista en Manteniento</h4>
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
import * as helper from "../../../../../public/js/vue-helper.js";

export default {
    name: "PagesMain",
    data() {
        return {
            tasksActions: {
                nInputs: 0 // Cantidad de inputs dinamicos
            },
            tsks: [], //Tareas
            optionsType: [],
            numberInputs: 0,
            seletedType: 0,
            page_id: 0,
            taskName: "",
            description: "",
            page_Type: null,
            pagesTypes: [],
            pages: [],
            isBusy: false,
            show: false,
            tasksFields: [
                { key: "name", label: "Nombre", sortable: true },
                { key: "description", label: "Descripcion", sortable: true },
                { key: "typeName", label: "Tipo de Tarea", sortable: true },
                { key: "edit_task", label: "Editar ", sortable: true }
                // { key: "delete_task", label: "Eliminar ", sortable: true }
            ],

            fields: [
                { key: "name", label: "Nombre", sortable: true },
                { key: "create_task", label: "Crear Tareas", sortable: true },
                { key: "tasks", label: "Tareas de Página\t", sortable: true },
                { key: "edit", label: "Editar", sortable: true },
                { key: "admin", label: "Admin" }
            ],
            totalRows: 1,
            currentPage: 1,
            perPage: 100,
            pageOptions: [100, 200, 300, { value: 400, text: "Show a lot" }],
            filter: null,
            filterOn: []
        };
    },

    computed: {
        /** Crea inputs dinamicos de acuerdo a la cantidad digitada..*/
        nInputs() {
            return isNaN(parseInt(this.numberInputs))
                ? 0
                : parseInt(this.numberInputs);
        },
        /** Filtra las tareas de la pagina seleccionada.*/

        filterTask() {
            return this.tsks.filter(el => el.page_id == this.page_id);
        },
        /** Busca el nombre de la pagina seleccionada.*/
        filterNamepage() {
            let namePage = {
                ...this.pages.find(el => el.id == this.page_id)
            };

            return namePage.name;
        },

        optionsLen() {
            return isNaN(parseInt(this.tasksActions.nInputs))
                ? 0
                : parseInt(this.tasksActions.nInputs);
        },
        /** Filtra las opciones de la tarea seleccionada.*/

        filterOp() {
            let selectedTask = this.tasksActions.edit.task_id; //Actualiza el estado con la tarea seleccionada
            let task = {
                ...this.filterTask.find(el => el.id == selectedTask)
            }; //Se obtiene la informacion de la tarea seleccionada.

            this.tasksActions.edit.seletedType = task.taskType_id; //Actualiza

            return task.options;
        }
    },

    mounted() {
        this.getPages();
    },
    methods: {
        /** Crea una tarea para la pagina*/
        createTask() {
            helper.VUE_ResetValidations();
            let infoTask = {
                page_id: this.page_id,
                name: this.taskName,
                description: this.description,
                page_task_type_id: this.seletedType,
                setting_options_task: this.optionsType
            };
            axios
                .post(route("setting.page.create_task"), infoTask)
                .then(response => response.data)
                .then(res => {
                    if (res.success == true) {
                        this.getTasks();
                        this.clearFiels();
                        $("#modal-ro").modal("toggle");
                    }
                })
                .catch(error => {
                    helper.VUE_CallBackErrors(error.response);
                    Toast.fire({
                        icon: "error",
                        title: "Verifique la informacion de los campos"
                    });
                });
        },
        /** Edita una tarea de la pagina*/
        editTask(task) { //Recibe el objeto de la tarea.
            helper.VUE_ResetValidations();

            this.tasksActions = { ...this.tasksActions, ...task };
            const { infoTask } = {
                infoTask: {
                    ...this.tasksActions,
                    ["options"]: [...task.options.map(el => el.name)]
                }
            };
            let len = infoTask.options.length; //Longitud: Opciones de la tarea

            this.tasksActions = infoTask;
            this.tasksActions.nInputs = len;
        },

        updateTask() {
            helper.VUE_ResetValidations();

            axios
                .put(route("setting.page.update_task"), this.tasksActions)
                .then(response => response.data)
                .then(res => {
                    // console.log(res);
                    if (res.success == true) {
                        this.getTasks(this.tasksActions.page_id);
                        // this.clearFiels();
                        $("#modal-edit").modal("toggle");
                        // this.tasksActions = { nInputs: 0 };
                    }
                })
                .catch(error => {
                    helper.VUE_CallBackErrors(error.response);
                    Toast.fire({
                        icon: "error",
                        title: "Verifique la informacion de los campos"
                    });
                });
        },
        /** Limpia campos del formulario.*/
        clearFiels() {
            this.page_id = 0;
            this.taskName = "";
            this.description = "";
            this.seletedType = 0;
            this.optionsType = [];
        },
        /** Obtiene todas las tareas.*/
        getTasks(id) {
            this.page_id = id;
            let params = { page_id: id };
            axios
                .get(route("setting.page.get_tasks", params))
                .then(response => response.data)
                .then(res => {
                    // let tasks = [...res.data];

                    this.tsks = [...res.data];
                });
        },
        /** Obtiene todas las paginas .*/
        getPages() {
            this.show = true;
            axios.get(route("setting.page.get_pages")).then(response => {
                this.pages = response.data.pages;
                this.show = false;

                let typ = response.data.pages_types.map((item, i) => ({
                    value: item.id,
                    text: item.name
                }));
                let temp = [{ value: "null", text: "Seleccione..." }, ...typ];

                this.pagesTypes = temp;
            });
        },

        sortOptions() {
            return this.fields
                .filter(f => f.sortable)
                .map(f => {
                    return { text: f.label, value: f.key };
                });
        },
        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
            this.currentPage = 1;
        }
    }
};
</script>

<style scoped>
.overflow {
    border-radius: 10px;
    overflow-y: scroll;
    max-height: 150px;
}

.bottom {
    margin-bottom: 0.5rem;
}

.z-i-modal {
    z-index: 999999999;
}

.fade-ro {
    transition: opacity 0.15s linear;
}
</style>
