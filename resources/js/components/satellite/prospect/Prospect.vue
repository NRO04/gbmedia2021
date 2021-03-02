<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="card table-responsive">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-7">
                            <span class="span-title">Prospectos Propietarios </span>
                        </div>
                        <div class="col-lg-5">
                            <button v-if="can('satellite-owner-prospect-create')" data-toggle="modal" data-target="#modal-prospect-create"
                                    class="btn btn-m btn-success float-right btn-sm"
                                    @click="setCreate"><i
                                class="fa fa-plus"></i> Crear</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <b-overlay :show="show" rounded="sm" variant="dark" opacity="0.8">
                                <template #overlay>
                                    <div class="text-center">
                                        <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
                                        <p id="cancel-label">Espere por favor...</p>
                                    </div>
                                </template>
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
                                <b-table show-empty small stacked="md" :items="prospects" :fields="fields" :current-page="currentPage" :per-page="perPage"
                                         :filter="filter" :filter-included-fields="filterOn"
                                         @filtered="onFiltered" :busy ="isBusy">

                                    <template #cell(created_at)="row">
                                        <span class='badge badge-pill badge-info'>{{ moment(row.item.created_at).format("YYYY-MM-DD") }}</span>
                                    </template>

                                    <template #cell(actions)="row">
                                        <a href="" target="_blanck" :href="'/satellite/contract/pdf/'+row.item.id"
                                           class='btn btn-outline-success btn-sm'
                                           @click="pdfContract()"><i
                                            class='fa fa-file-pdf'></i></a>
                                        <button v-if="can('satellite-contract-edit')" data-target="#modal-contract" data-toggle="modal"
                                                class='btn btn-warning btn-sm'
                                                title='Editar' @click="setEdit(row.item)"><i class='fa fa-edit'></i></button>
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
                            </b-overlay>
                        </div>

                        <!-- Modal -->
                        <div id="modal-prospect-create" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-dark modal-lg">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title"> Crear Prospecto Propietario</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="myForm">
                                            <div class="row">
                                                <div class="col-lg-6 mt-2">
                                                    <label>Propietario</label>
                                                    <input type="text" v-model="prospect.owner" name="owner"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Nombre</label>
                                                    <input type="text" v-model="prospect.first_name" name="first_name"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Segundo Nombre</label>
                                                    <input type="text" v-model="prospect.second_name" name="second_name"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Apellido</label>
                                                    <input type="text" v-model="prospect.last_name" name="last_name"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Segundo Apellido</label>
                                                    <input type="text" v-model="prospect.second_last_name" name="second_last_name"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Documento Nro</label>
                                                    <input type="text" v-model="prospect.document_number" name="document_number"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>RUT</label>
                                                    <div class="inputFile">
                                                        <span class="btnFile btn-dark btn-sm">
                                                            <i class="fa fa-upload" aria-hidden="true"></i>
                                                            <span class="ml-1"> Selecionar archivo</span>
                                                        </span>
                                                        <input class="form-control pt-3"  type="file" name="rut"
                                                               @change='onFileChange'>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Email Principal</label>
                                                    <input type="text" v-model="prospect.email" name="email" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Teléfono</label>
                                                    <input type="text" v-model="prospect.phone" name="phone" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Dirección</label>
                                                    <input type="text" v-model="prospect.address" name="address"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Barrio</label>
                                                    <input type="text" v-model="prospect.neighborhood" name="neighborhood"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Departamento</label>
                                                    <input type="text" v-model="prospect.department_id" name="department_id"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Ciudad</label>
                                                    <input type="text" v-model="prospect.city_id" name="city_id"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Estudio</label>
                                                    <select v-model="prospect.studio"  name="studio"
                                                            class="form-control form-control-sm">
                                                        <option v-for="(row, index) in tenants" :value="row.id">{{ row.studio_name }}</option>
                                                    </select>
                                                </div>

                                                <div class="col-lg-12 mt-2">
                                                    <label>Notas</label>
                                                    <textarea v-model="prospect.note" name="note" cols="30" rows="10" class="form-control form-control-sm"></textarea>
                                                </div>

                                            </div>
                                        </form>
                                        <div class="col-lg-12 mt-2">
                                            <button class="btn btn-m btn-success float-right btn-sm" @click="storeProspect"><i
                                                class="fa fa-plus"></i>
                                                Crear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal -->
<!--                        <div id="modal-contract-edit" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-dark modal-lg">
                                &lt;!&ndash; Modal content&ndash;&gt;
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" v-if="edit"> Modificar Contrato</h4>
                                        <h4 class="modal-title" v-else> Crear Contrato</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="myForm">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <label>Nombre del Estudio</label>
                                                    <input type="text" v-model="contract.studio_name" name="studio_name" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6">
                                                    <label>Tipo</label>
                                                    <select v-model="contract.company_type"  name="company_type"  class="form-control form-control-sm">
                                                        <option>Persona Natural</option>
                                                        <option>Empresa</option>
                                                    </select>
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Representante Legal</label>
                                                    <input type="text" v-model="contract.holder" name="holder" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Nro Identificación</label>
                                                    <input type="text" v-model="contract.card_id" name="card_id" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2" v-if="contract.company_type == 'Empresa'">
                                                    <label>Empresa</label>
                                                    <input type="text" v-model="contract.company" name="company" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2" v-if="contract.company_type == 'Empresa'">
                                                    <label>NIT</label>
                                                    <input type="text" v-model="contract.nit" name="nit" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Dirección</label>
                                                    <input type="text" v-model="contract.address" name="address" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Ciudad</label>
                                                    <input type="text" v-model="contract.city" name="city" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Departamento</label>
                                                    <input type="text" v-model="contract.department" name="department"
                                                           class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Teléfono</label>
                                                    <input type="text" v-model="contract.phone" name="phone" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Email</label>
                                                    <input type="text" v-model="contract.email" name="email" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Porcentaje que se le pagará al asociado</label>
                                                    <input type="text" v-model="contract.percent" name="percent" class="form-control form-control-sm">
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Forma de Pago</label>
                                                    <select v-model="contract.payment_method"  name="payment_method"
                                                            class="form-control form-control-sm">
                                                        <option>Bancaria</option>
                                                        <option>Efecty</option>
                                                        <option>Paxum</option>
                                                    </select>
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Cláusula de Incumplimiento</label>
                                                    <select v-model="contract.clause"  name="clause"  class="form-control form-control-sm">
                                                        <option>50.000.000</option>
                                                        <option>100.000.000</option>
                                                        <option>150.000.000</option>
                                                        <option>200.000.000</option>
                                                        <option>250.000.000</option>
                                                        <option>300.000.000</option>
                                                    </select>
                                                </div>

                                                <div class="col-lg-6 mt-2">
                                                    <label>Duración del Contrato (en años)</label>
                                                    <select v-model="contract.years"  name="years"  class="form-control form-control-sm">
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="col-lg-12 mt-2">
                                            <button class="btn btn-m btn-warning float-right btn-sm" v-if="edit" @click="updateContract"><i
                                                class="fa fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-m btn-success float-right btn-sm" v-else @click="storeContract"><i
                                                class="fa fa-plus"></i>
                                                Crear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>-->

                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import moment from "moment";
    import"moment/locale/es";
    import * as helper from '../../../../../public/js/vue-helper.js'
    export default {
        name: "prospect",
        props : ['permissions'],
        data() {
            return{
                prospects: [],
                tenants: [],
                prospect: {
                    'owner' : '',
                    'first_name' : '',
                    'second_name' : '',
                    'last_name' : '',
                    'second_last_name' : '',
                    'document_number' : '',
                    'rut' : '',
                    'email' : '',
                    'phone' : '',
                    'address' : '',
                    'neighborhood' : '',
                    'department_id' : '',
                    'city_id' : '',
                    'studio' : '',
                    'note' : '',
                },
                file: null,
                isBusy: false,
                show: true,
                edit: false,
                moment: moment,
                fields: [
                    { key: 'holder', label: 'Representante', sortable: true },
                    { key: 'company', label: 'Empresa', sortable: true },
                    { key: 'studio_name', label: 'Estudio', sortable: true },
                    { key: 'created_at', label: 'Fecha de Creación', sortable: true },
                    { key: 'email', label: 'Email'},
                    { key: 'phone', label: 'Telefono'},
                    { key: 'from_name', label: 'Creado por'},
                    { key: 'actions', label: 'Acciones'},

                ],
                totalRows: 1,
                currentPage: 1,
                perPage: 100,
                pageOptions: [100, 200, 300, { value: 400, text: "Show a lot" }],
                filter: null,
                filterOn: [],
            }
        },
        computed: {
        },
        created() {
            this.getTenants();
            this.getContracts();
        },
        methods:{
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            getTenants(){
                axios.get(route('satellite.studios')).then(response => {
                    console.log(response.data);
                    this.tenants = response.data;
                })
            },
            onFileChange(event){
                let position = event.target.name;
                this.file = event.target.files[0];
            },
            getContracts(){
                this.show = true;
                axios.get(route('satellite.get_contracts')).then(response => {
                    console.log(response.data);
                    this.contracts = response.data;
                    this.show = false;
                })
            },
            setCreate(){
                this.edit = false;
                for (let key in this.contract) {
                    this.contract[key] = "";
                }
            },
            setEdit(item){
                console.log(item);
                this.edit = true;
                for (let key in this.contract) {
                    if (key == "contract_id")
                    {
                        this.contract[key] = item["id"];
                    }
                    else{
                        this.contract[key] = item[key];
                    }

                }

            },
            storeProspect(){
                const config = {
                    headers: {
                        'content-type': 'multipart/form-data'
                    }
                }
                helper.VUE_ResetValidations();

                let formData = new FormData(document.getElementById("myForm"));
                formData.append('prospect', JSON.stringify(this.prospect));

                axios.post(route('satellite.store_prospect'), formData, config).then(response =>{
                    if (response.data.success){
                        Toast.fire({
                            icon: "success",
                            title: "Prospecto Propietario creado exitosamente",
                        });

                        for (let key in this.prospect) {
                            this.prospect[key] = "";
                        }
                        this.prospects.push(response.data.prospect);
                    }
                    else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error, comuniquese con el ADMIN",
                        });
                    }
                })
                .catch(error => {
                  helper.VUE_CallBackErrors(error.response);
                  Toast.fire({
                      icon: "error",
                      title: "Verifique la informacion de los campos",
                  });
                });
            },
            /*updateContract(){
                helper.VUE_ResetValidations();
                axios.post(route('satellite.update_contract'), this.contract).then(response =>{
                    if (response.data.success){
                        Toast.fire({
                            icon: "success",
                            title: "Se ha modificado el contrato exitosamente",
                        });
                        this.getContracts();
                    }
                    else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error, comuniquese con el ADMIN",
                        });
                    }
                })
                    .catch(error => {
                        helper.VUE_CallBackErrors(error.response);
                        Toast.fire({
                            icon: "error",
                            title: "Verifique la informacion de los campos",
                        });
                    });
            },*/
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
