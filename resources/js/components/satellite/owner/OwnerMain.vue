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
                            <div class="col-lg-3">
                                <span class="span-title">Propietarios </span>
                            </div>
                            <div class="col-lg-4">
                                <div class="row">
                                    <label class="mt-1  mr-2 ml-2">Tipo</label>
                                    <select v-model="view_owner" class="col-lg-8 form-control" >
                                        <option value="1">Satelite</option>
                                        <option value="2">Modelos GB</option>
                                        <option value="3">Gerenciados</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <a v-if="can('satellite-owners-create')" href="owner/create" target="_blank" type="button"
                                   class="btn btn-m btn-success float-right btn-sm"><i
                                    class="fa fa-plus"></i> Crear</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-if="view_owner == ''" class="alert alert-success text-center font-weight-bold" role="alert">
                            Seleccione la opcion por la cual desea filtrar los propietarios
                        </div>
                        <div v-if="view_owner == 1">
                            <owner-list :permissions="permissions"></owner-list>
                        </div>
                        <div v-if="view_owner == 2">
                            <owner-model :permissions="permissions"></owner-model>
                        </div>
                        <div v-if="view_owner == 3">
                            <owner-managed :permissions="permissions"></owner-managed>
                        </div>
                    </div>
                </div>
            </b-overlay>
        </div>

    </div>
</template>

<script>
    export default {
        name: "OwnerMain",
        props: [
            'permissions',
        ],
        data(){
            return{
                view_owner: 1,
                isBusy: false,
                show: false,
            }
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
        }
    }
</script>

<style scoped>

</style>
