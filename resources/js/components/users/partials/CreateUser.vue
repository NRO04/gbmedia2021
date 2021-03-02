<template>
    <div id="container-users">
        <b-modal id="modal-create" title="Crear Usuario" scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="modal-create">
            <form id="form-create-user">
                <h5 class="text-muted">Datos Personales</h5>
                <hr>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="name" class="required">Nombre:</label>
                        <input class="form-control" id="name" type="text" v-model="formCreate.first_name" placeholder="Pedro"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="second-name">Segundo Nombre:</label>
                        <input class="form-control" id="second-name" type="text" v-model="formCreate.middle_name" placeholder="José"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="last-name" class="required">Apellido:</label>
                        <input class="form-control" id="last-name" type="text" v-model="formCreate.last_name" placeholder="Perez"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="second-last-name">Segundo Apellido:</label>
                        <input class="form-control" id="second-last-name" type="text" v-model="formCreate.second_lastname" placeholder="Martínez"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="user-email" class="required">Email:</label>
                        <input class="form-control" id="user-email" name="email" type="email" v-model="formCreate.email" placeholder="pedroperez@gmail.com"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="user-password" class="required">Contraseña:</label>
                        <input class="form-control" id="user-password" type="password" v-model="formCreate.password"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="user-birth-date" class="required">Fecha de Nacimiento:</label>
                        <input class="form-control" id="user-birth-date" type="date" v-model="formCreate.birth_date"/>
                    </div>
                    <div class="col-12 col-md-6">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="department" class="required">Departamento:</label>
                        <b-form-select v-model="formCreate.department_id" id="department" @change="getDepartmentCities()">
                            <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                            <b-form-select-option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="city" class="required">Ciudad:</label>
                        <div class="input-group">
                            <b-form-select v-model="formCreate.city_id" id="city">
                                <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                                <b-form-select-option v-for="city in department_cities" :key="city.id" :value="city.id">{{ city.name }}</b-form-select-option>
                            </b-form-select>
                        </div>
                    </div>
                </div>
                <h5 class="text-muted mt-2">Opciones</h5>
                <hr>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="user-location-id" class="required">Locación:</label>
                        <div class="input-group">
                            <b-form-select v-model="formCreate.location_id" id="user-location-id">
                                <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                                <b-form-select-option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</b-form-select-option>
                            </b-form-select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="user-role-id" class="required">Rol:</label>
                        <div class="input-group">
                            <b-form-select v-model="formCreate.role_id" id="user-role-id">
                                <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                                <b-form-select-option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                            </b-form-select>
                        </div>
                    </div>
                </div>
                <div class="form-group row" v-if="formCreate.role_id === 14">
                    <div class="col-12 col-md-6"></div>
                    <div class="col-12 col-md-6">
                        <label for="nick">Nick:</label>
                        <input class="form-control" id="nick" type="text" v-model="formCreate.model_nick" placeholder=""/>
                    </div>
                </div>
            </form>
            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="success" @click="createUser()" v-if="btnCreate">
                            <i class="fas fa-plus"></i> Crear
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import * as helper from '../../../../../public/js/vue-helper'

    export default {
        name: 'create-user',
        components: {},
        props: [
            'user',
            'locations',
            'roles',
            'permissions',
            'departments',
        ],
        data() {
            return {
                formCreate: new Form({
                    /*first_name: 'Carmen',
                    middle_name: '',
                    last_name: 'Gonzales',
                    second_lastname: '',
                    birth_date: '1994-08-16',
                    email: 'model@gas.com',
                    password: '123456',
                    model_nick: 'PatriceSexy',
                    role_id: 14,
                    location_id: 2,
                    department_id: 1,
                    city_id: 0,*/

                    first_name: '',
                    middle_name: '',
                    last_name: '',
                    second_lastname: '',
                    birth_date: '',
                    email: '',
                    password: '',
                    model_nick: '',
                    role_id: 0,
                    location_id: 0,
                    department_id: 1,
                    city_id: 0,
                }),
                department_cities: [],
            }
        },
        computed: {
            btnCreate() {
                return this.formCreate.first_name !== '' &&
                    this.formCreate.last_name !== 0 &&
                    this.formCreate.birth_date !== '' &&
                    this.formCreate.email !== '' &&
                    this.formCreate.password !== '' &&
                    this.formCreate.role_id !== 0 &&
                    this.formCreate.location_id !== 0 &&
                    this.formCreate.department_id !== 0 &&
                    this.formCreate.city_id !== 0
            }
        },
        mounted() {
        },
        created() {
            this.getDepartmentCities();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            createUser() {
                helper.VUE_ResetValidations();
                helper.VUE_DisableModalActionButtons();

                axios.post(
                    route('user.create'),
                    this.formCreate
                ).then((response) => {
                    let res = response.data;

                    if(res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Usuario creado correctamente",
                        });

                        // Clear form imputs
                        this.formCreate.first_name = '';
                        this.formCreate.middle_name = '';
                        this.formCreate.last_name  = '';
                        this.formCreate.second_lastname  = '';
                        this.formCreate.birth_date = '';
                        this.formCreate.email = '';
                        this.formCreate.password = '';
                        this.formCreate.model_nick = '';
                        this.formCreate.role_id  = 0;
                        this.formCreate.location_id = 0;

                        //Load here the users table again
                        //this.getUsers();

                        this.$refs['modal-create'].hide();

                        SwalGB.fire({
                            title: 'Usuario creado correctamente. ¿Desea continuar editando el usuario recién creado?',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Si',
                            cancelButtonText: 'Cerrar',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.value) {
                                window.location.replace("/user/edit/" + res.user_id);
                            }
                        });
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "No se pudo crear el usuario. Por favor, intente mas tarde.",
                            timer: 8000
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                }).catch((res) => {
                    if(res.response.status === 422) {
                        Toast.fire({
                            icon: "error",
                            title: "Por favor corriga los campos marcados en rojo",
                            timer: 5000,
                        });
                        helper.VUE_CallBackErrors(res.response);
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al guardar la información. Por favor intente mas tarde.",
                            timer: 8000,
                        });
                    }
                });
            },
            getDepartmentCities() {
                this.formCreate.city_id = 0;

                axios.get(route('user.get_department_cities', {department_id: this.formCreate.department_id}))
                .then((response) => {
                    this.department_cities = response.data;
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                });
            },
        },
    }
</script>

<style scoped>

</style>
