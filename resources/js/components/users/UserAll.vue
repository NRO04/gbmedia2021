<template>
    <div class="row">
        <div class="col-lg-12">
            <b-row class="justify-content-end m-2">
                <b-col md="12">
                    <span class="mx-2"
                        ><i class="fas text-success fa-id-card"></i>
                        Cédula</span
                    >
                    <span class="mx-2"
                        ><i class="fas text-info fa-id-card-alt"></i> Rostro
                        Cédula</span
                    >
                    <span><i class="fas text-success fa-warning"></i> RUT</span>
                    <span class="mx-2"
                        ><i class="fas text-danger fa-briefcase"></i> Permiso
                        Trabajo</span
                    >
                </b-col>
            </b-row>
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
                    <b-col lg="4" class="my-1">
                        <select
                            v-model="user_status"
                            @change="getUsers"
                            class="form-control form-control-sm"
                        >
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </b-col>
                </b-row>

                <!-- Main table element -->
                <b-table
                    show-empty
                    small
                    stacked="md"
                    :items="users"
                    :fields="fields"
                    :current-page="currentPage"
                    :per-page="perPage"
                    :filter="filter"
                    :filter-included-fields="filterOn"
                    :sort-by.sync="sortBy"
                    @filtered="onFiltered"
                    :busy="isBusy"
                >
                    <template #cell(avatar)="row">
                        <div class="c-avatar">
                            <img
                                class="c-avatar-img zoom-img"
                                :src="row.item.avatar"
                            />
                        </div>
                    </template>

                    <template #cell(actions)="row">
                        <button
                            class="btn btn-info btn-sm"
                            title="Ver Info"
                            v-if="can('users-contact-info-view')"
                        >
                            <i class="fa fa-eye"></i>
                        </button>
                        <a
                            :href="
                                projectRoute('user.edit', { id: row.item.id })
                            "
                            class="btn btn-warning btn-sm"
                            target="_blank"
                            title="Modificar"
                            v-if="can('users-edit')"
                        >
                            <i class="fa fa-edit"></i>
                        </a>
                    </template>

                    <template #cell(documents)="row">
                        <span v-if="row.item.id_card == 1" title="Cédula"
                            ><i class="fas text-success fa-id-card"></i
                        ></span>
                        <span
                            v-if="row.item.id_card_front == 1"
                            class="mx-2"
                            title="Rostro Cédula"
                            ><i class="fas text-info fa-id-card-alt"></i
                        ></span>
                        <span v-if="row.item.rut == 1" class="mx-2" title="RUT"
                            ><i class="fas text-warning fa-check"></i
                        ></span>
                        <span
                            v-if="row.item.work_permision == 1"
                            class="mx-2"
                            title="Permiso Trabajo"
                            ><i class="fas text-danger fa-briefcase"></i
                        ></span>
                    </template>
                </b-table>
                <b-row class="justify-content-end">
                    <b-col cols="6" class="my-1">
                        Total <b>{{ users.length }}</b> registros
                    </b-col>
                    <b-col cols="6" class="my-1">
                        <b-pagination
                            v-model="currentPage"
                            :total-rows="totalRows"
                            :per-page="perPage"
                            size="sm"
                            class="my-0 float-right"
                        ></b-pagination>
                    </b-col>
                </b-row>
            </b-overlay>
        </div>
    </div>
</template>

<script>
export default {
    name: "user-all",
    props: ["permissions"],
    data() {
        return {
            users: [],
            sortBy: "full_name",
            user_status: 1,
            isBusy: false,
            show: true,
            fields: [
                { key: "full_name", label: "Nombre", sortable: true },
                { key: "avatar", label: "Imagen", sortable: true },
                { key: "email", label: "Email", sortable: true },
                { key: "role", label: "Rol", sortable: true },
                { key: "location", label: "Locacion", sortable: true },
                { key: "actions", label: "Acciones" },
                { key: "documents", label: "Documentos" }
            ],
            totalRows: 1,
            currentPage: 1,
            perPage: 100,
            pageOptions: [100, 200, 300, { value: 400, text: "Todos" }],
            filter: null,
            filterOn: []
        };
    },
    computed: {},
    created() {
        this.getUsers();
    },
    methods: {
        can(permission_name) {
            return this.permissions.indexOf(permission_name) !== -1;
        },
        getUsers() {
            this.show = true;
            axios
                .get(route("user.users", { user_status: this.user_status }))
                .then(response => {
                    this.users = response.data;
                    this.totalRows = response.data.length;
                    this.show = false;
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
        },
        projectRoute(route_name, params) {
            return route(route_name, params);
        }
    }
};
</script>
