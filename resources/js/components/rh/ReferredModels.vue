<template>
    <div id="container-referred-models" class="row">
        <b-overlay :show="show" no-wrap></b-overlay>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 mb-2">
                            <span class="span-title">Prospectos Referidos</span>
                        </div>
                        <div class="col-12 col-sm-4">
                            <b-form-select id="department" v-model="referred" @change="changeProspectView(formCreate.department_id)" v-if="can('human-resources-prospect')">
                                <b-form-select-option :value="1">Usuarios</b-form-select-option>
                                <b-form-select-option :value="2">Modelos</b-form-select-option>
                                <b-form-select-option :value="3">Modelos Referidos</b-form-select-option>
                            </b-form-select>
                        </div>
                        <div class="col-xs-12 col-sm-4 text-sm-right" v-if="can('human-resources-referred-prospect-create')">
                            <a class="btn btn-success btn-sm" v-b-modal.modal-create>
                                <i class="fa fa-plus"></i> Crear
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <b-form-select class="float-right" v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
                        </div>
                        <div class="col-12 col-md-4 push-3">
                            <b-input-group size="sm">
                                <b-form-input debounce="500" v-model.trim="filter" type="text" id="filterInput" placeholder="Buscar prospecto..."></b-form-input>
                            </b-input-group>
                        </div>
                    </div>
                    <hr>
                    <b-table show-empty small stacked="sm" :fields="fields"
                             :items="items" responsive="sm" class="table-striped"
                             :current-page="currentPage" :per-page="perPage" :filter="filter"
                             :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" ref="table"
                             selected-variant="info"
                             :busy.sync="isBusy" empty-text="No hay prospectos creados"
                    >
                        <template #cell(referred)="data">
                            <div class="row">
                                <div class="col-6">
                                    <b-checkbox class="" :checked="data.item.status == 1" :id="'checkbox-' + data.item.id" @change="referModel(data.item.id, $event)"></b-checkbox>
                                </div>
                                <div class="col-6 pl-0">
                                    <span :id="'loader-' + data.item.id" class="fa fa-spinner fa-pulse" style="display: none;"></span>
                                </div>
                            </div>
                        </template>

                        <template #cell(seen)="data">
                            <i class="fa fa-check text-success" :id="data.item.id" v-show="data.item.seen"></i>
                        </template>

                        <template #cell(actions)="data">
                            <button class="btn btn-outline-primary btn-sm" title="Ver" @click="modalEditModel(data.item)">
                                <i class="fa fa-eye"></i>
                            </button>
                            <a class="btn btn-outline-info btn-sm" title="Entrevista" :href="projectRoute('rh.interview.storeInterview', {id: data.item.id})">
                                <i class="fa fa-external-link"></i>
                            </a>
                            <button class="btn btn-outline-danger btn-sm" title="Eliminar" v-if="data.item.studio_creator_id == 1 && can('human-resources-referred-prospect-delete')" @click="deleteReferredModel(data.item.id)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </template>
                    </b-table>
                </div>
                <div class="card-footer">
                    <div class="float-left">
                        Total <b>{{ items.length }}</b> registros
                    </div>
                    <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm" class="float-right"></b-pagination>
                </div>
            </div>
        </div>

        <b-modal id="modal-create" title="Crear Prospecto" scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="modal-create">
            <form id="form-create-model">
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="name" class="required">Nombre:</label>
                        <input class="form-control" id="name" name="first_name" type="text" v-model="formCreate.first_name" placeholder="Camila"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="second-name">Segundo Nombre:</label>
                        <input class="form-control" id="second-name" type="text" v-model="formCreate.middle_name" placeholder="Antonieta"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="last-name" class="required">Apellido:</label>
                        <input class="form-control" id="last-name" name="last_name" type="text" v-model="formCreate.last_name" placeholder="Perez"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="second-last-name">Segundo Apellido:</label>
                        <input class="form-control" id="second-last-name" type="text" v-model="formCreate.second_last_name" placeholder="Martínez"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="email" class="">Correo:</label>
                        <input class="form-control" id="email" name="last_name" type="email" v-model="formCreate.email" placeholder="camilaperez@gmail.com"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone">Teléfono:</label>
                        <input class="form-control" id="phone" type="text" v-model="formCreate.phone" placeholder="3111234567"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="department" class="required">Departamento:</label>
                        <b-form-select v-model="formCreate.department_id" id="department" @change="getDepartmentCities(formCreate.department_id)" name="department_id">
                            <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                            <b-form-select-option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="city" class="required">Ciudad:</label>
                        <div class="input-group">
                            <b-form-select v-model="formCreate.city_id" id="city" name="city_id">
                                <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                                <b-form-select-option v-for="city in department_cities" :key="city.id" :value="city.id">{{ city.name }}</b-form-select-option>
                            </b-form-select>
                        </div>
                    </div>
                </div>
                <h6 class="text-muted mt-3">Fotografías</h6>
                <hr>
                <div class="form-group">
                    <div class="form-check form-check-inline mt-3 mb-2 w-100">
                        <div class="col-12">
                            <input class="form-check-input checkbox-for" id="checkbox-add-images" type="checkbox" v-model="add_images" @click="renderImagePicker('#container-images', 'images[]')"/>
                            <label for="checkbox-add-images" class="form-check-label">Añadir imágenes</label>
                        </div>
                    </div>
                    <div class="row" id="container-images" v-if="add_images">
                    </div>
                </div>
            </form>
            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="success" @click="createReferredModel()" v-if="btnCreate">
                            <i class="fas fa-plus"></i> Crear
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>

        <b-modal id="modal-edit" title="Ver / Editar Prospecto" scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="modal-edit">
            <form id="form-edit-model">
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="name" class="required">Nombre:</label>
                        <input class="form-control" id="edit-name" name="first_name" type="text" v-model="formEdit.first_name" placeholder="Camila"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="second-name">Segundo Nombre:</label>
                        <input class="form-control" id="edit-second-name" type="text" v-model="formEdit.middle_name" placeholder="Antonieta"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="last-name" class="required">Apellido:</label>
                        <input class="form-control" id="edit-last-name" name="last_name" type="text" v-model="formEdit.last_name" placeholder="Perez"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="second-last-name">Segundo Apellido:</label>
                        <input class="form-control" id="edit-second-last-name" type="text" v-model="formEdit.second_last_name" placeholder="Martínez"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="email" class="">Correo:</label>
                        <input class="form-control" id="edit-email" name="last_name" type="email" v-model="formEdit.email" placeholder="camilaperez@gmail.com"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone">Teléfono:</label>
                        <input class="form-control" id="edit-phone" type="text" v-model="formEdit.phone" placeholder="3111234567"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="department" class="required">Departamento:</label>
                        <b-form-select v-model="formEdit.department_id" id="edit-department" @change="getDepartmentCities(formEdit.department_id)" name="department_id">
                            <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                            <b-form-select-option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="city" class="required">Ciudad:</label>
                        <div class="input-group">
                            <b-form-select v-model="formEdit.city_id" id="edit-city" name="city_id">
                                <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                                <b-form-select-option v-for="city in department_cities" :key="city.id" :value="city.id">{{ city.name }}</b-form-select-option>
                            </b-form-select>
                        </div>
                    </div>
                </div>
                <h6 class="text-muted mt-3">Fotografías</h6>
                <hr>
                <div class="form-group">
                    <div class="row" id="container-current-images" v-if="formEdit.images.length > 0">
                        <div class="col-12">
                            <Lightbox
                                id="container-front-documents-files"
                                :images="formEdit.images"
                                :image_class="'model-image col-12 col-md-3 mx-2'"
                                :album_class="'container-current-images'"
                                :options="options">
                            </Lightbox>
                        </div>
                    </div>
                    <div class="form-check form-check-inline mt-3 mb-2 w-100">
                        <div class="col-12">
                            <input class="form-check-input checkbox-for" id="edit-checkbox-add-images" type="checkbox" v-model="edit_add_images" @click="renderImagePicker('#edit-container-images', 'edit-images[]')"/>
                            <label for="edit-checkbox-add-images" class="form-check-label">Añadir imágenes</label>
                        </div>
                    </div>
                    <div class="row" id="edit-container-images" v-if="edit_add_images"></div>
                </div>
            </form>
            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right" v-if="can('human-resources-referred-prospect-edit')">
                        <b-button type="button" size="sm" variant="warning" @click="editReferredModel()" v-if="btnEdit">
                            <i class="fas fa-edit"></i> Modificar
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>

    </div>
</template>

<script>
    import Lightbox from 'vue-simple-lightbox'
    import * as helper from '../../../../public/js/vue-helper.js'

    export default {
        name: "ReferredModels",
        components: {
            Lightbox
        },
        props: [
            'user',
            'roles',
            'current_tenant_id',
            'permissions',
            'departments',
        ],
        data() {
            return {
                formCreate: new Form({
                    first_name: null,
                    middle_name: null,
                    last_name: null,
                    second_last_name: null,
                    phone: null,
                    email: null,
                    department_id: 1,
                    city_id: '',
                    images: [],
                }),
                formEdit: new Form({
                    id: null,
                    first_name: null,
                    middle_name: null,
                    last_name: null,
                    second_last_name: null,
                    phone: null,
                    email: null,
                    department_id: 1,
                    city_id: '',
                    images: [],
                }),
                selected_department_id: 1,
                show: true,
                department_cities: [],
                add_images: false,
                edit_add_images: false,
                isBusy: false,
                sortBy: 'id',
                sortDesc: true,
                totalRows: 1,
                currentPage: 1,
                perPage: 50,
                pageOptions: [50, 100, 200],
                filter: null,
                fields: [
                    {key: 'full_name', label: 'Nombre', sortable: true},
                    {key: 'studio_name', label: 'Estudio', sortable: true},
                    {key: 'phone_number', label: 'Teléfono', sortable: false},
                    {key: 'city_name', label: 'Departamento / Ciudad', sortable: true},
                    {key: 'referred', label: 'Referido', sortable: false},
                    {key: 'seen', label: 'Visto', sortable: true},
                    {key: 'created_at', label: 'Creado', sortable: true},
                    {key: 'actions', label: 'Acciones', sortable: false},
                ],
                items: [],
                options : {
                    closeText: 'x',
                    overlay: false,
                    captions: false,
                },
                referred: 3
            }
        },
        created()
        {
            this.getDepartmentCities(this.formCreate.department_id);
            this.getReferredModels();
        },
        computed: {
            btnCreate() {
                return this.formCreate.first_name != null && this.formCreate.first_name != '' &&
                    this.formCreate.last_name != null && this.formCreate.last_name != '' &&
                    this.formCreate.department_id != 0 && this.formCreate.city_id != 0
            },
            btnEdit() {
                return this.formEdit.first_name != null && this.formEdit.first_name != '' &&
                    this.formEdit.last_name != null && this.formEdit.last_name != '' &&
                    this.formEdit.department_id != 0 && this.formEdit.city_id != 0
            }
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getReferredModels() {
                this.show = true;

                axios.get(route('rh.get_referred_models'))
                    .then((response) => {
                        this.items = response.data;
                        this.totalRows = this.items.length;
                        this.show = false;
                    }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                });
            },
            getDepartmentCities(department_id) {
                this.formCreate.city_id = 0;
                this.formEdit.city_id = 0;

                axios.get(route('user.get_department_cities', {department_id: department_id}))
                    .then((response) => {
                        this.department_cities = response.data;
                    }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                });
            },
            createReferredModel() {
                let formData = new FormData();

                formData.append("first_name", this.formCreate.first_name);
                formData.append("middle_name", (this.formCreate.middle_name != null && this.formCreate.middle_name != '') ? this.formCreate.middle_name : '');
                formData.append("last_name", this.formCreate.last_name);
                formData.append("second_last_name", this.formCreate.second_last_name);
                formData.append("second_last_name", (this.formCreate.second_last_name != null && this.formCreate.second_last_name != '') ? this.formCreate.second_last_name : '');
                formData.append("email", (this.formCreate.email != null && this.formCreate.email != '') ? this.formCreate.email : '');
                formData.append("phone", (this.formCreate.phone != null && this.formCreate.phone != '') ? this.formCreate.phone : '');
                formData.append("department_id", this.formCreate.department_id);
                formData.append("city_id", this.formCreate.city_id);
                formData.append("add_images", (this.add_images) ? true : false);

                let images = $('input.spartan_image_input');

                for (let i in images) {
                    if (images[i].files !== undefined) {
                        if(images[i].files.length > 0) {
                            formData.append("images[]", images[i].files[0]);
                        }
                    }
                }

                let headers = {
                    'Content-Type': "multipart/form-data;",
                    "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                    };

                helper.VUE_DisableModalActionButtons();

                axios.post(
                    route('rh.create_referred_model'),
                    formData,
                    headers
                ).then((response) => {
                    if (response.data.success) {
                        Toast.fire({
                            icon: "success",
                            title: "¡El prospecto fue creado correctamente!",
                        });

                        this.formCreate.first_name = null;
                        this.formCreate.middle_name = null;
                        this.formCreate.last_name = null;
                        this.formCreate.second_last_name = null;
                        this.formCreate.phone = null;
                        this.formCreate.email = null;
                        this.formCreate.department_id = 0;
                        this.formCreate.city_id = 0;
                        this.formCreate.images = [];
                        this.formCreate.add_images = false;

                        this.$refs['modal-create'].hide();

                        this.getReferredModels();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al crear el estudio. Por favor intente mas tarde.",
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                }).catch((response) => {
                    if (response.response.status == 422) {
                        helper.VUE_CallBackErrors(response.response);
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                });
            },
            modalEditModel(item) {
                this.formCreate.department_id = item.department_id;

                this.getDepartmentCities(item.department_id);
                this.seenReferredModel(item.id);

                this.formEdit.id = item.id;
                this.formEdit.first_name = item.first_name;
                this.formEdit.middle_name = item.middle_name;
                this.formEdit.last_name = item.last_name;
                this.formEdit.second_last_name = item.second_last_name;
                this.formEdit.phone = item.phone_number;
                this.formEdit.email = item.email;
                this.formEdit.department_id = item.department_id;
                this.formEdit.city_id = item.city_id;
                this.formEdit.images = item.images;

                this.$refs['modal-edit'].show();
            },
            editReferredModel() {
                let formData = new FormData();

                formData.append("id", this.formEdit.id);
                formData.append("first_name", this.formEdit.first_name);
                formData.append("middle_name", (this.formEdit.middle_name != null && this.formEdit.middle_name != '') ? this.formEdit.middle_name : '');
                formData.append("last_name", this.formEdit.last_name);
                formData.append("second_last_name", this.formEdit.second_last_name);
                formData.append("second_last_name", (this.formEdit.second_last_name != null && this.formEdit.second_last_name != '') ? this.formEdit.second_last_name : '');
                formData.append("email", (this.formEdit.email != null && this.formEdit.email != '') ? this.formEdit.email : '');
                formData.append("phone", (this.formEdit.phone != null && this.formEdit.phone != '') ? this.formEdit.phone : '');
                formData.append("department_id", this.formEdit.department_id);
                formData.append("city_id", this.formEdit.city_id);
                formData.append("add_images", (this.edit_add_images) ? true : false);

                let images = $('input.spartan_image_input');

                for (let i in images) {
                    if (images[i].files !== undefined) {
                        if(images[i].files.length > 0) {
                            formData.append("images[]", images[i].files[0]);
                        }
                    }
                }

                let headers = {'Content-Type': "multipart/form-data;"};

                helper.VUE_DisableModalActionButtons();

                axios.post(
                    route('rh.edit_referred_model'),
                    formData,
                    headers
                ).then((response) => {
                    if (response.data.success) {
                        Toast.fire({
                            icon: "success",
                            title: "¡El prospecto fue modificado correctamente!",
                        });

                        this.formEdit.first_name = null;
                        this.formEdit.middle_name = null;
                        this.formEdit.last_name = null;
                        this.formEdit.second_last_name = null;
                        this.formEdit.phone = null;
                        this.formEdit.email = null;
                        this.formEdit.department_id = 0;
                        this.formEdit.city_id = 0;
                        this.formEdit.images = [];
                        this.formEdit.edit_add_images = false;

                        this.$refs['modal-edit'].hide();

                        this.getReferredModels();
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al modificar el prospecto. Por favor intente mas tarde.",
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                }).catch((response) => {
                    if (response.response.status == 422) {
                        helper.VUE_CallBackErrors(response.response);
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                });
            },
            seenReferredModel(id) {
                axios.post(route('rh.seen_referred_model'), {id}
                ).then((response) => {
                    $('#' + response.data.referred_model_id).css('display', 'block');
                }
                ).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                });
            },
            deleteReferredModel(id) {
                SwalGB.fire({
                    title: '¡Confirmar!',
                    text: '¿Está seguro que desea eliminar el prospecto?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                    cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        axios.post(
                            route('rh.delete_referred_model'),
                            {id},
                        ).then((response) => {
                                let res = response.data;

                                if(res.success) {
                                    Toast.fire({
                                        icon: "success",
                                        title: "Prospecto eliminado correctamente",
                                    });

                                    this.getReferredModels();
                                } else {
                                    Toast.fire({
                                        icon: "error",
                                        title: "No se pudo eliminar el prospecto. Por favor, intente mas tarde.",
                                        timer: 8000
                                    });
                                }
                            }
                        ).catch((response) => {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error al eliminar el prospecto. Por favor, intente mas tarde.",
                            });
                        });
                    }
                });
            },
            renderImagePicker(element_id, input_name) {
                setTimeout(function () {
                    $(element_id).spartanMultiImagePicker({
                        fieldName: input_name,
                        maxCount: 5,
                        groupClassName: 'col-12 col-sm-4',
                        maxFileSize: 5000000,
                        onAddRow: function(index) {
                        },
                        onRenderedPreview : function(index){
                        },
                        onSizeErr: function(index, file){
                            alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
                        }
                    });
                }, 200);
            },
            changeProspectView() {
                if(this.referred == 1)
                {
                    window.location.href = route('rh.interview.user.list');
                }
                else if(this.referred == 2)
                {
                    window.location.href = route('rh.interview.model.list');
                }
            },
            referModel(id, status) {
                $('#loader-' + id).css('display', 'inline-block');

                axios.post(
                    route('rh.refer_model'), {id, status})
                    .then((response) => {
                            $('#loader-' + id).removeClass('fa-pulse fa-spinner');
                            $('#loader-' + id).addClass('fa-check');
                            $('#loader-' + id).css('color', 'green');
                            $('#loader-' + id).fadeOut(2000);

                            Toast.fire({
                                icon: "success",
                                title: "Actualizado correctamente.",
                            });
                        }
                    ).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al guardar la información. Por favor, intente mas tarde.",
                    });

                    $('#loader-' + id).removeClass('fa-pulse fa-spinner');
                    $('#loader-' + id).addClass('fa-times');
                    $('#loader-' + id).css('color', 'red');
                    $('#loader-' + id).html('&nbsp;&nbsp;Error.');
                    $('#checkbox-' + id).prop('checked', false);
                });
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
        }
    }
</script>

<style scoped>
    .model-image {
        max-height: 100px !important;
        max-width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: center center !important;
    }

</style>
