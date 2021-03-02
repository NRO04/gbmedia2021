<template>
    <div class="row">
        <create-user :locations="locations" :roles="roles" :departments="departments"></create-user>
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
                            <div class="col-lg-3">
                                <span class="span-title">Usuarios </span>
                            </div>
                            <div class="col-lg-4">
                                <div class="row">
                                    <label class="mt-1  mr-2 ml-2">Tipo</label>
                                    <select v-model="view_user" class="col-lg-8 form-control" >
                                        <option value="1">Todos</option>
                                        <option value="2">Modelos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <a class="btn btn-m btn-success float-right btn-sm" v-b-modal.modal-create v-if="can('users-create')">
                                    <i class="fa fa-plus"></i> Crear
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-if="view_user == ''" class="alert alert-success text-center font-weight-bold" role="alert">
                            Seleccione la opcion por la cual desea filtrar los usuarios
                        </div>
                        <div v-if="view_user == 1">
                            <user-all :permissions="permissions"></user-all>
                        </div>
                        <div v-if="view_user == 2">
                            <user-models :permissions="permissions"></user-models>
                        </div>
                    </div>
                </div>
            </b-overlay>
        </div>

    </div>
</template>

<script>
    import CreateUser from "./partials/CreateUser";

    export default {
        name: "UserMain",
        props: [
            'user',
            'locations',
            'roles',
            'permissions',
            'departments',
        ],
        components: {CreateUser},
        data(){
            return{
                view_user: 1,
                isBusy: false,
                show: false,
            }
        },
        methods:{
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
        }
    }
</script>

<style scoped>

</style>
