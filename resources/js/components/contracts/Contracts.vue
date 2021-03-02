<template>
    <div id="container-contracts">
        <div class="card col-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Contratos</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    <a class="btn btn-info btn-sm mt-1" @click="getConfiguration()">
                        <i class="fa fa-cogs"></i> &nbsp;Configuración Estudio
                    </a>
                    <a class="btn btn-primary btn-sm mt-1" v-b-modal.modal-roles>
                        <i class="fa fa-cog"></i> &nbsp;Configuración Roles y Funciones
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <b-table show-empty small stacked="sm" :fields="fields"
                             :items="items" responsive="sm" class="table-striped"
                             :current-page="currentPage" :per-page="perPage" :filter="filter"
                             :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" ref="table"
                             selected-variant="info"
                             :busy.sync="isBusy" empty-text="No hay contratos"
                    >
                        <template #cell(description)="data">
                            <b-button class="btn btn-info btn-sm" :title="data.item.description" v-b-tooltip.hover>
                                <i class="fa fa-info-circle"></i>
                            </b-button>
                        </template>

                        <template #cell(active)="data">
                            <div class="row">
                                <div class="col-1">
                                    <b-form-checkbox value="1" unchecked-value="0" :checked="data.item.active" @change="changeContractStatus(data.item.id, $event)"></b-form-checkbox>
                                </div>
                                <div class="col-1">
                                    <span :id="'loader-' + data.item.id" class="d-none">
                                        <i class="fa fa-pulse fa-spinner"></i>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template #cell(actions)="data">
                            <div @click="openEditContractModal(data.item)">
                                <b-button class="btn btn-info btn-sm" variant="warning" v-b-modal.modal-edit-contract>
                                    <i class="fa fa-edit"></i>
                                </b-button>
                            </div>
                        </template>
                    </b-table>
                </div>
            </div>
            <div class="card-footer"></div>
        </div>

        <b-modal id="modal-edit-contract" title="Modificar Contrato" scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="modal-edit-contract">
            <div class="form-group row">
                <div class="col-12">
                    <label for="title" class="required">Título:</label>
                    <input class="form-control" id="title" name="title" type="text" v-model="formEditContract.title" placeholder="Contrato de Arrendamiento"/>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-12">
                    <label for="description" class="required">Descripción:</label>
                    <textarea class="form-control" name="description" id="description" cols="30" rows="3" v-model="formEditContract.description" placeholder="Contrato de Arrendamiento"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-12">
                    <label for="url" class="required">URL:</label>
                    <input class="form-control" id="url" name="url" type="text" v-model="formEditContract.url" placeholder="ExportContratoAT"/>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-12">Imagen:</label>
                <div class="col-12" id="container-contract-image"></div>
            </div>
            <hr>
            <h6>Asignar a Roles</h6>
            <div class="form-group col-12" id="container-roles">
                <div class="row">
                    <div class="form-check col-12 px-3">
                        <input type="checkbox" class="form-check-input" @click="checkSelectAll()" v-model="selectAll" id="select-all">
                        <label class="form-check-label" for="select-all">Seleccionar todos</label>
                    </div>
                    <hr>
                    <div class="col-12 col-sm-6 col-md-4" v-for="role in roles">
                        <input class="form-check-input check-role-id" type="checkbox" v-model="formEditContract.assignToRoles" :id="role.name" :value="role.id">
                        <label class="form-check-label" :for="role.name">{{ role.name }}</label>
                    </div>
                </div>
            </div>

            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="success" @click="editContract()" :disabled="false">
                            <i class="fa fa-check"></i> Editar
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>

        <b-modal id="modal-configuration" title="Configuración Estudio" scrollable size="xl" header-bg-variant="primary" header-close-content="" ref="modal-configuration">
            <div id="studio-info">
                <h5 class="text-muted">Información Estudio</h5>
                <hr class="mt-0">
                <div class="form-group row">
                    <div class="col-12">
                        <label for="name" class="">Nombre Empresa:</label>
                        <input class="form-control" id="name" name="name" type="text" v-model="formConfiguration.name" placeholder="Mi Empresa SAS"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="document-type" class="">Tipo de Documento:</label>
                        <b-form-select v-model="formConfiguration.document_type" id="document-type" name="document_type">
                            <b-form-select-option value="">Seleccione...</b-form-select-option>
                            <b-form-select-option v-for="document in global_documents" :key="document.id" :value="document.id">{{ document.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="document-number" class="">Número de Documento:</label>
                        <input class="form-control" id="document-number" name="document_number" type="text" v-model="formConfiguration.document_number" placeholder="1234567890"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="address" class="">Dirección:</label>
                        <textarea name="address" id="address" cols="30" rows="1" class="form-control" v-model="formConfiguration.address" placeholder="Cra 15 #23-67. Barrio Los Cámbulos"></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone" class="">Teléfono:</label>
                        <input class="form-control" id="phone" name="phone" type="text" v-model="formConfiguration.phone" placeholder="1234567890"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="email" class="">Correo:</label>
                        <input class="form-control" id="email" name="email" type="email" v-model="formConfiguration.email" placeholder="admin@miempresa.com"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="city" class="">Cuidad:</label>
                        <input class="form-control" id="city" name="city" type="text" v-model="formConfiguration.city" placeholder="Cali"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <label for="legal-representative" class="">Representante Legal:</label>
                        <input class="form-control" id="legal-representative" name="legal_representative" type="text" v-model="formConfiguration.legal_representative" placeholder="Pedro Perez">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="legal-representative-document-type" class="">Tipo de Documento:</label>
                        <b-form-select v-model="formConfiguration.legal_representative_document_type" id="legal-representative-document-type" name="legal_representative_document_type">
                            <b-form-select-option value="">Seleccione...</b-form-select-option>
                            <b-form-select-option v-for="document in global_documents" :key="document.id" :value="document.id">{{ document.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="legal-representative-document-number" class="">Número de Documento:</label>
                        <input class="form-control" id="legal-representative-document-number" name="legal_representative_document_number" type="text" v-model="formConfiguration.legal_representative_document_number" placeholder="1234567890">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="dian-code" class="">Código DIAN:</label>
                        <input class="form-control" id="dian-code" name="dian_code" type="text" v-model="formConfiguration.dian_code" placeholder="DE02">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="dian-number-autorization" class="">Autorización de numeración Dian No:</label>
                        <input class="form-control" id="dian-number-autorization" name="dian_number_authorization" type="text" v-model="formConfiguration.dian_number_authorization" placeholder="1010101010101 desde la DE02-1 hasta DE02-10000">
                    </div>
                </div>
            </div>

            <div id="info">
                <h5 class="text-muted">Información necesaria contratos</h5>
                <hr class="mt-0">
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="daily-rent" class="">Arriendo x Dia contrato:</label>
                        <input class="form-control" id="daily-rent" name="daily_rent" type="text" v-model="formConfiguration.daily_rent" placeholder="10000"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="marketing-contract-vigency" class="">Vigencia de contrato Marketing & Publicidad:</label>
                        <input class="form-control" id="marketing-contract-vigency" name="marketing_contract_validity" type="text" v-model="formConfiguration.marketing_contract_validity" placeholder="seis (06) meses"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="cleaning-participation-accounts" class="">Limpieza Cuentas Participación:</label>
                        <input class="form-control" id="cleaning-participation-accounts" name="cleaning_participation_accounts" type="text" v-model="formConfiguration.cleaning_participation_accounts" placeholder="15000"/>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="min-conexion-minuts-participation-accounts" class="">Mins. minimo conexión semana cuentas participación:</label>
                        <input class="form-control" id="min-conexion-minuts-participation-accounts" name="min_connection_minutes_participation_accounts" type="text" v-model="formConfiguration.min_connection_minutes_participation_accounts" placeholder="2400"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="payment-frecuency" class="">Frecuencia de Pagos:</label>
                        <b-form-select v-model="formConfiguration.payment_frequency" id="payment-frecuency" name="payment_frequency">
                            <b-form-select-option value="weekly">Semanal</b-form-select-option>
                            <b-form-select-option value="quaterly">Quncenal</b-form-select-option>
                            <b-form-select-option value="monthly">Mensual</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="salary-quantity-confidentiality-agreement-penalty" class="">Nr. Salarios Penalidad Acuerdo confidencialidad:</label>
                        <input class="form-control" id="salary-quantity-confidentiality-agreement-penalty" name="salary_quantity_confidentiality_agreement_penalty" type="text" v-model="formConfiguration.salary_quantity_confidentiality_agreement_penalty" placeholder="50"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="pagare-interest-ea" class="">Pagaré Interés EA:</label>
                        <input class="form-control" id="pagare-interest-ea" name="pagare_interest_ea" type="text" v-model="formConfiguration.pagare_interest_ea" placeholder="24"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <label for="witness-name" class="">Nombre de Testigo:</label>
                        <input class="form-control" id="witness-name" name="witness_name" type="text" v-model="formConfiguration.witness_name" placeholder="María Caicedo"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="witness-document-type" class="">Tipo Documento Testigo:</label>
                        <b-form-select v-model="formConfiguration.witness_document_type" id="witness-document-type" name="witness_document_type">
                            <b-form-select-option value="">Seleccione...</b-form-select-option>
                            <b-form-select-option v-for="document in global_documents" :key="document.id" :value="document.id">{{ document.name }}</b-form-select-option>
                        </b-form-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="witness-document-number" class="">Número Documento Testigo:</label>
                        <input class="form-control" id="witness-document-number" name="witness_document_number" type="text" v-model="formConfiguration.witness_document_number" placeholder="1234567890"/>
                    </div>
                </div>

                <h6 class="text-muted">Turnos</h6>
                <hr class="mt-0">
                <div class="form-group row">
                    <div class="col-12 col-md-6">
                        <label for="turns" class="">Cantidad de turnos:</label>
                        <b-form-select v-model="formConfiguration.turns_quantity" id="turns" name="turns">
                            <b-form-select-option value=1>1</b-form-select-option>
                            <b-form-select-option value=2>2</b-form-select-option>
                            <b-form-select-option value=3>3</b-form-select-option>
                            <b-form-select-option value=4>4</b-form-select-option>
                            <b-form-select-option value=5>5</b-form-select-option>
                            <b-form-select-option value=6>6</b-form-select-option>
                        </b-form-select>
                        <hr>
                    </div>
                    <div class="col-12">
                        <div class="row" id="container-turns">
                            <div class="col-12 col-md-6 turn" v-for="index in parseInt(formConfiguration.turns_quantity)">
                                <label :for="'turn-' + index" class="">Turno {{ index }}:</label>
                                <input class="form-control mb-2" :id="'turn-' + index" :name="'turn_' + index" type="text" v-model="formConfiguration.turns[index].name" placeholder="Nombre del Turno"/>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <VueCtkDateTimePicker
                                            v-model="formConfiguration.turns[index].start_time"
                                            dark onlyTime overlay noLabel noHeader
                                            label="Inicio Turno"
                                            inputSize="sm"
                                            format="HH:mm a"
                                            formatted="HH:mm a"
                                        />
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <VueCtkDateTimePicker
                                            v-model="formConfiguration.turns[index].end_time"
                                            dark onlyTime overlay noLabel noHeader
                                            label="Fin Turno"
                                            inputSize="sm"
                                            format="HH:mm a"
                                            formatted="HH:mm a"
                                        />
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="locations">
                <h5 class="text-muted">Locaciones</h5>
                <hr class="mt-0">
                <div class="form-group row">
                    <div class="col-12 col-md-6 form-group" v-for="location in locations" :key="location.id">
                        <label :for="'location-' + location.id" class="">Dirección {{ location.name }}:</label>
                        <input class="form-control" :id="'location-' + location.id" :name="'location_' + location.id" type="text" v-model="formConfiguration.locations[location.id].address" placeholder="Calle 3 Nr. 56 - 12 / Barrio Tequendama"/>
                    </div>
                </div>
            </div>

            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="warning" @click="editConfiguration()" :disabled="false">
                            <i class="fa fa-edit"></i> Editar
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>

        <b-modal id="modal-roles" @hidden="doSomethingOnHidden()" title="Configuración Roles y Funciones" scrollable size="xl" header-bg-variant="primary" header-close-content="" ref="modal-roles">
            <b-overlay :show="show" no-wrap></b-overlay>

            <div class="form-group row">
                <div class="col-12 col-md-6">
                    <label for="role" class="">Rol:</label>
                    <b-form-select v-model="formRolesAndFunctionsConfiguration.role_id" id="role" name="setting_role_id" @change="getRole()">
                        <b-form-select-option value="">Seleccione...</b-form-select-option>
                        <b-form-select-option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                    </b-form-select>
                </div>
                <div class="col-12 col-md-6">
                    <label for="contract-name" class="">Nombre en Contrato:</label>
                    <input class="form-control" id="contract-name" name="contract_name" type="text" v-model="formRolesAndFunctionsConfiguration.alternative_name" placeholder="Asesor de Ventas"/>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-12">
                    <b-form-group id="" label="Funciones y Tareas:" label-for="tasks">
                        <editor :plugins="plugins"
                                :toolbar ="toolbar"
                                :init="init"
                                id="tasks"
                                v-model.trim="formRolesAndFunctionsConfiguration.tasks"
                                api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"
                                placeholder="Información del Módulo"
                                ref="tasks-editor"
                        />
                    </b-form-group>
                </div>
            </div>

            <template v-slot:modal-footer>
                <div class="footer">
                    <div class="text-right">
                        <b-button type="button" size="sm" variant="warning" @click="editRolesAndFunction()" :disabled="false">
                            <i class="fa fa-edit"></i> Editar
                        </b-button>
                    </div>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
import * as helper from '../../../../public/js/vue-helper.js'
import Editor from "@tinymce/tinymce-vue";

export default {
    name: "Contracts",
    props: [
        'user',
        'roles',
        'permissions',
        'global_documents',
        'locations',
    ],
    components: {
        'editor': Editor
    },
    data() {
        return {
            isBusy: false,
            sortBy: 'title',
            sortDesc: false,
            totalRows: 1,
            currentPage: 1,
            perPage: 50,
            pageOptions: [50, 100, 200],
            filter: null,
            fields: [
                {key: 'title', label: 'Título', sortable: true},
                {key: 'description', label: 'Descripción', sortable: false},
                {key: 'active', label: 'Activo', sortable: true},
                {key: 'actions', label: 'Acciones', sortable: false},
            ],
            items: [],
            formEditContract: new Form({
                title: null,
                description: null,
                url: null,
                image: null,
                assignToRoles: []
            }),
            formConfiguration: new Form({
                name: null,
                document_number: null,
                document_type: '',
                address: null,
                phone: null,
                email: null,
                city: null,
                legal_representative: null,
                legal_representative_document_type: '',
                legal_representative_document_number: null,
                dian_code: null,
                dian_number_authorization: null,
                daily_rent: null,
                marketing_contract_validity: null,
                cleaning_participation_accounts: null,
                min_connection_minutes_participation_accounts: null,
                payment_frequency: null,
                salary_quantity_confidentiality_agreement_penalty: null,
                pagare_interest_ea: null,
                witness_name: null,
                witness_document_type: '',
                witness_document_number: null,
                turns_quantity: 1,
                turns: {
                    1: {
                        name: null,
                        start_time: null,
                        end_time: null,
                    },
                    2: {
                        name: null,
                        start_time: null,
                        end_time: null,
                    },
                    3: {
                        name: null,
                        start_time: null,
                        end_time: null,
                    },
                    4: {
                        name: null,
                        start_time: null,
                        end_time: null,
                    },
                    5: {
                        name: null,
                        start_time: null,
                        end_time: null,
                    },
                    6: {
                        name: null,
                        start_time: null,
                        end_time: null,
                    },
                },
                locations: {
                    1: {
                        address: null,
                    },
                    2: {
                        address: null,
                    },
                    3: {
                        address: null,
                    },
                    4: {
                        address: null,
                    },
                    5: {
                        address: null,
                    },
                    6: {
                        address: null,
                    },
                },
            }),
            formRolesAndFunctionsConfiguration: new Form({
                role_id: '',
                role_name: null,
                alternative_name: null,
                tasks: null,
            }),
            selectAll: false,
            formEditModuleInfo: new Form({
                title: null,
                description: null,
            }),
            toolbar: 'fontsizeselect | bold alignleft aligncenter alignright alignjustify | backcolor forecolor | \ bullist numlist | link | emoticons | image | code',
            plugins: [
                'emoticons advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            init: {
                height: 300,
                menubar: false,
                skin: 'oxide-dark',
                content_css: 'dark',
            },
            show: false,
        }
    },
    created() {
        this.getContracts();
    },
    methods: {
        can(permission_name) {
            return this.permissions.indexOf(permission_name) !== -1;
        },
        getContracts() {
            axios.get(route('contracts.get_contracts')).then(response => {
                this.items = response.data;
                this.totalRows = response.data.length;
                $('#global-spinner').addClass('d-none');
            });
        },
        openEditContractModal(item) {
            this.formEditContract.id = item.id;
            this.formEditContract.title = item.title;
            this.formEditContract.description = item.description;
            this.formEditContract.url = item.url;
            this.formEditContract.image = item.image;
            this.formEditContract.assignToRoles = item.roles;

            $("#container-contract-image").spartanMultiImagePicker({
                fieldName: 'contract-image',
                maxCount: 1,
                groupClassName: 'col-xs-12',
                maxFileSize: 5000000,
                placeholderImage: {image: '/images/contracts/' + item.image , width: '40%'},
                onAddRow: function(index) {
                },
                onRenderedPreview : function(index){
                },
                onSizeErr: function(index, file){
                    alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
                }
            });
        },
        editContract() {
            helper.VUE_DisableModalActionButtons();
            let form_data = new FormData();

            for (let key in this.formEditContract) {
                form_data.append(key, this.formEditContract[key]);
            }

            form_data.append('image', $('input[name="contract-image"]').prop('files')[0]);

            axios.post(
                route("contracts.edit"),
                form_data,
                {'Content-Type': "multipart/form-data;"}
            ).then((response) => {
                let res = response.data;
                Toast.fire({
                    icon: "success",
                    title: "Información modificada correctamente",
                });

                this.$refs['modal-edit-contract'].hide();
                this.getContracts();

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

                helper.VUE_EnableModalActionButtons();
            });
        },
        checkSelectAll() {
            let _this = this;
            let items = $('.check-role-id');

            $('.check-role-id').prop('checked', !this.selectAll);

            if(!this.selectAll) {
                $.each(items, function (i, item) {
                    _this.formEditContract.assignToRoles.push(parseInt($(item).val()))
                });
            } else {
                $.each(items, function (i, item) {
                    _this.formEditContract.assignToRoles = [];
                });
            }
        },
        editConfiguration() {
            axios.post(
                route("contracts.edit_configuration"),
                this.formConfiguration,
            ).then((response) => {
                let res = response.data;

                if(res.success) {
                    Toast.fire({
                        icon: "success",
                        title: "Información modificada correctamente",
                    });
                } else {
                    Toast.fire({
                        icon: "error",
                        title: "No se pudo modificar la información. Por favor, intente mas tarde.",
                        timer: 8000
                    });
                }

                this.$refs['modal-configuration'].hide();
                this.getContracts();

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

                helper.VUE_EnableModalActionButtons();
            });

        },
        editRolesAndFunction() {
            helper.VUE_DisableModalActionButtons();

            axios.post(
                route("contracts.edit_roles_and_functions"),
                this.formRolesAndFunctionsConfiguration,
            ).then((response) => {
                let res = response.data;

                if(res.success) {
                    Toast.fire({
                        icon: "success",
                        title: "Información modificada correctamente",
                    });
                } else {
                    Toast.fire({
                        icon: "error",
                        title: "No se pudo modificar la información. Por favor, intente mas tarde.",
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

                helper.VUE_EnableModalActionButtons();
            });

        },
        getConfiguration() {
            axios.get(route("contracts.get_configuration")).then((response) => {
                let res = response.data;

                this.formConfiguration.name = res.contract_company_name;
                this.formConfiguration.document_number = res.contract_document_number;
                this.formConfiguration.document_type = res.contract_document_type;
                this.formConfiguration.address = res.contract_address;
                this.formConfiguration.phone = res.contract_phone;
                this.formConfiguration.email = res.contract_email;
                this.formConfiguration.city = res.contract_city;
                this.formConfiguration.legal_representative = res.contract_legal_representative;
                this.formConfiguration.legal_representative_document_type = res.contract_legal_representative_document_type;
                this.formConfiguration.legal_representative_document_number = res.contract_legal_representative_document_number;
                this.formConfiguration.dian_code = res.contract_dian_code;
                this.formConfiguration.dian_number_authorization = res.contract_dian_number_authorization;
                this.formConfiguration.daily_rent = res.contract_daily_rent;
                this.formConfiguration.marketing_contract_validity = res.contract_marketing_contract_validity;
                this.formConfiguration.cleaning_participation_accounts = res.contract_cleaning_participation_accounts;
                this.formConfiguration.min_connection_minutes_participation_accounts = res.contract_min_connection_minutes_participation_accounts;
                this.formConfiguration.payment_frequency = res.contract_payment_frequency;
                this.formConfiguration.salary_quantity_confidentiality_agreement_penalty = res.contract_salary_quantity_confidentiality_agreement_penalty;
                this.formConfiguration.pagare_interest_ea = res.contract_pagare_interest_ea;
                this.formConfiguration.witness_name = res.contract_witness_name;
                this.formConfiguration.witness_document_type = res.contract_witness_document_type;
                this.formConfiguration.witness_document_number = res.contract_witness_document_number;
                this.formConfiguration.turns_quantity = res.contract_turns_quantity;

                for(let index in res.locations_addresses) {
                    this.formConfiguration.locations[index].address = res.locations_addresses[index];
                }

                for(let index in res.turns) {
                    this.formConfiguration.turns[index].name = res.turns[index].name;
                    this.formConfiguration.turns[index].start_time = res.turns[index].start_time;
                    this.formConfiguration.turns[index].end_time = res.turns[index].end_time;
                }

                this.$refs['modal-configuration'].show();
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

                helper.VUE_EnableModalActionButtons();
            });

        },
        getRole() {
            let role_id = this.formRolesAndFunctionsConfiguration.role_id;

            this.show = true;

            axios.post(
                route("contracts.get_role"),
                {role_id},
            ).then((response) => {
                let res = response.data;

                this.formRolesAndFunctionsConfiguration.role_id = res.id;
                this.formRolesAndFunctionsConfiguration.role_name = res.name;
                this.formRolesAndFunctionsConfiguration.alternative_name = res.alternative_name;
                this.formRolesAndFunctionsConfiguration.tasks = res.tasks != null ? res.tasks : '';

                this.show = false;
            }).catch((res) => {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error al obtener la información. Por favor intente mas tarde.",
                    timer: 8000,
                });
            });

        },
        changeContractStatus(id, status) {
            let active = parseInt(status);
            $('#loader-' + id).removeClass('d-none');

            axios.post(
                route("contracts.change_contract_status"),
                {
                    id,
                    active,
                },
            ).then((response) => {
                this.getContracts();
                $('#loader-' + id).addClass('d-none');
            }).catch((res) => {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error al guardar la información. Por favor intente mas tarde.",
                    timer: 8000,
                });

                $('#loader-' + id).addClass('d-none');
            });

        },
        doSomethingOnHidden() {

        },
    },
}
</script>

<style scoped>
    .modal-body {
        overflow-x: hidden !important;
    }
</style>
