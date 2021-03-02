<template>
    <div id="container-edit-user">
        <div class="card col-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Editando Usuario <span class="text-info">{{ user.first_name + ' ' + user.last_name }}</span></span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    <!--<a class="btn btn-info btn-sm" href="/studio/assign-users">
                        <i class="fa fa-address-book"></i> &nbsp;Hoja de Vida
                    </a>-->
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="list-group" id="list-tab" role="tablist">
                            <a class="list-group-item list-group-item-action item-sm active" data-toggle="list" href="#personal-info" role="tab" aria-controls="home">Datos Personales</a>
                            <a class="list-group-item list-group-item-action item-sm" data-toggle="list" href="#access-info" role="tab" aria-controls="home">Información de Acceso</a>
                            <a class="list-group-item list-group-item-action item-sm" data-toggle="list" href="#payroll" role="tab" aria-controls="home">Nómina y Contrato</a>
                            <a class="list-group-item list-group-item-action item-sm" data-toggle="list" href="#payment-method" role="tab" aria-controls="home" v-if="can('user-payment-method')">Datos Bancarios</a>
                            <a class="list-group-item list-group-item-action item-sm" data-toggle="list" href="#extra-info" role="tab" aria-controls="profile">Información Adicional y Opciones</a>
                        </div>
                    </div>
                    <div class="col-12 col-md-9 mt-sm-4 mt-md-0">
                        <div class="bg-danger text-center p-1" v-if="user.status === 0">
                            Este usuario se encuenta inactivo
                        </div>
                        <div class="tab-content" id="nav-tabContent">
                            <!-- Personal Info -->
                            <div class="tab-pane fade active show" id="personal-info" role="tabpanel">
                                <form id="form-personal-info">
                                    <div class="card border-secondary">
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="name" class="required">Nombre:</label>
                                                <input class="form-control" id="name" name="first_name" type="text" v-model="formPersonalInfo.first_name" placeholder="Pedro"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="second-name">Segundo Nombre:</label>
                                                <input class="form-control" id="second-name" type="text" v-model="formPersonalInfo.middle_name" placeholder="José"/>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="last-name" class="required">Apellido:</label>
                                                <input class="form-control" id="last-name" name="last_name" type="text" v-model="formPersonalInfo.last_name" placeholder="Perez"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="second-last-name">Segundo Apellido:</label>
                                                <input class="form-control" id="second-last-name" type="text" v-model="formPersonalInfo.second_lastname" placeholder="Martínez"/>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="user-birth-date" class="required">Fecha de Nacimiento:</label>
                                                <input class="form-control" id="user-birth-date" name="birth_date" type="date" v-model="formPersonalInfo.birth_date"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="nationality" class="required">Nacionalidad:</label>
                                                <div class="input-group">
                                                    <b-form-select v-model="formPersonalInfo.country_id" id="nationality" name="country_id">
                                                        <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                        <b-form-select-option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</b-form-select-option>
                                                    </b-form-select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="phone" class="">Teléfono:</label>
                                                <input class="form-control" id="phone" type="text" v-model="formPersonalInfo.mobile_number" placeholder="3145581249"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="user-rh" class="required">Grupo Sanguineo (RH):</label>
                                                <div class="input-group">
                                                    <b-form-select v-model="formPersonalInfo.rh_id" id="user-rh" name="rh_id">
                                                        <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                        <b-form-select-option v-for="blood_type in blood_types" :key="blood_type.id" :value="blood_type.id">{{ blood_type.name }}</b-form-select-option>
                                                    </b-form-select>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="text-muted">Documentos</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="document-type" class="required">Tipo de Documento:</label>
                                                <div class="input-group">
                                                    <b-form-select v-model="formPersonalInfo.document_type" id="document-type" name="document_type">
                                                        <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                        <b-form-select-option v-for="document_type in documents_types" :key="document_type.id" :value="document_type.id">{{ document_type.name }}</b-form-select-option>
                                                    </b-form-select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="document-number" class="required">Número de Documento:</label>
                                                <input class="form-control mb-2" id="document-number" name="document_number" type="text" v-model="formPersonalInfo.document_number"/>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input checkbox-for" id="document-has-expiration-date" type="checkbox" v-model="formPersonalInfo.document_has_expiration_date"/>
                                                    <label for="document-has-expiration-date" class="form-check-label">Asignar fecha vencimiento:</label>
                                                </div>
                                                <div class="" v-if="formPersonalInfo.document_has_expiration_date">
                                                    <input class="form-control col-12" id="document-expiration-date" type="date" v-model="formPersonalInfo.document_expiration_date"/>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group row">
                                            <div class="col-12">
                                                <div id="documents-files" class="row">
                                                    <div class="col-12 col-md-6">
                                                        <div id="container-add-front-document-file">
                                                            <label for="document-front-file" class="">
                                                                Frente documento (archivo):
                                                                <i class="fa fa-info-circle text-warning" title="Puede subir un archivo tipo imagen o un PDF." v-b-tooltip.hover></i>
                                                            </label>
                                                            <div id="container-current-front-document-files" class="mb-2">
                                                                <div v-if="this.formPersonalInfo.front_document_images.length > 0">
                                                                    <Lightbox
                                                                        id="container-front-documents-files"
                                                                        :images="formPersonalInfo.front_document_images"
                                                                        :image_class="'document-file mx-2'"
                                                                        :album_class="'container-front-documents-files'"
                                                                        :options="options">
                                                                    </Lightbox>
                                                                </div>
                                                                <div id="container-current-front-document-pdf-files" v-if="this.formPersonalInfo.front_document_files.length > 0">
                                                                    <div class="col-6 col-md-2 border border-info mr-1 bg-dark text-center" style="cursor: pointer; border-radius: 3px; padding: 5px;" v-for="file in formPersonalInfo.front_document_files">
                                                                        <img @click="embedDocuments(file)" style="height: 32px; margin-left:8px;" src="http://gbmediagroup.com/laravel/public/images/svg/pdf.svg">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-check-inline mt-3 mb-2 w-100">
                                                                <div class="col-12">
                                                                    <input class="form-check-input checkbox-for" id="checkbox-add-front-document-file" type="checkbox" v-model="formPersonalInfo.add_front_document_file" @click="renderImagePicker('#container-front-document-file', 'front-document-file')"/>
                                                                    <label for="checkbox-add-front-document-file" class="form-check-label">Agregar archivo</label>
                                                                </div>
                                                            </div>
                                                            <div class="" id="container-front-document-file" v-if="formPersonalInfo.add_front_document_file"></div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div id="container-add-back-document-file">
                                                            <label for="document-back-file" class="">
                                                                Reverso documento (archivo):
                                                                <i class="fa fa-info-circle text-warning" title="Puede subir un archivo tipo imagen o un PDF." v-b-tooltip.hover></i>
                                                            </label>
                                                            <div id="container-current-back-document-files" class="mb-2">
                                                                <div v-if="this.formPersonalInfo.back_document_images.length > 0">
                                                                    <Lightbox
                                                                        id="container-back-documents-files"
                                                                        :images="formPersonalInfo.back_document_images"
                                                                        :image_class="'document-file mx-2'"
                                                                        :album_class="'container-back-documents-files'"
                                                                        :options="options">
                                                                    </Lightbox>
                                                                </div>
                                                                <div id="container-current-back-document-pdf-files" v-if="this.formPersonalInfo.back_document_files.length > 0">
                                                                    <div class="col-6 col-md-2 border border-info mr-1 bg-dark text-center" style="cursor: pointer; border-radius: 3px; padding: 5px;" v-for="file in formPersonalInfo.back_document_files">
                                                                        <img @click="embedDocuments(file)" style="height: 32px; margin-left:8px;" src="http://gbmediagroup.com/laravel/public/images/svg/pdf.svg">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-check-inline mt-3 mb-2 w-100">
                                                                <div class="col-12">
                                                                    <input class="form-check-input checkbox-for" id="checkbox-add-back-document-file" type="checkbox" v-model="formPersonalInfo.add_back_document_file" @click="renderImagePicker('#container-back-document-file', 'back-document-file')"/>
                                                                    <label for="checkbox-add-back-document-file" class="form-check-label">Agregar archivo</label>
                                                                </div>
                                                            </div>
                                                            <div class="" id="container-back-document-file" v-if="formPersonalInfo.add_back_document_file"></div>
                                                        </div>
                                                        <hr>
                                                    </div>
                                                    <div class="col-12 col-md-6" v-if="formExtraInfo.setting_role_id === 14">
                                                        <div id="container-add-face-id-document-file">
                                                            <label for="document-face-id-file" class="">
                                                                Rostro - Cédula (archivo):
                                                                <i class="fa fa-info-circle text-warning" title="Puede subir un archivo tipo imagen o un PDF." v-b-tooltip.hover></i>
                                                            </label>
                                                            <div id="container-current-face-id-document-files" class="mb-2">
                                                                <div v-if="this.formPersonalInfo.face_id_document_images.length > 0">
                                                                    <Lightbox
                                                                        id="container-face-id-documents-files"
                                                                        :images="formPersonalInfo.face_id_document_images"
                                                                        :image_class="'document-file mx-2'"
                                                                        :album_class="'container-face-id-documents-files'"
                                                                        :options="options">
                                                                    </Lightbox>
                                                                </div>
                                                                <div id="container-current-face-id-document-pdf-files" v-if="this.formPersonalInfo.face_id_document_files.length > 0">
                                                                    <div class="col-6 col-md-2 border border-info mr-1 bg-dark text-center" style="cursor: pointer; border-radius: 3px; padding: 5px;" v-for="file in formPersonalInfo.face_id_document_files">
                                                                        <img @click="embedDocuments(file)" style="height: 32px; margin-left:8px;" src="http://gbmediagroup.com/laravel/public/images/svg/pdf.svg">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-check-inline mt-3 mb-2 w-100">
                                                                <div class="col-12">
                                                                    <input class="form-check-input checkbox-for" id="checkbox-add-face-id-document-file" type="checkbox" v-model="formPersonalInfo.add_face_id_document_file" @click="renderImagePicker('#container-face-id-document-file', 'face-id-document-file')"/>
                                                                    <label for="checkbox-add-face-id-document-file" class="form-check-label">Agregar archivo</label>
                                                                </div>
                                                            </div>
                                                            <div class="" id="container-face-id-document-file" v-if="formPersonalInfo.add_face_id_document_file"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div id="container-add-rut-document-file">
                                                            <label for="document-rut-file" class="">
                                                                RUT (archivo):
                                                                <i class="fa fa-info-circle text-warning" title="Puede subir un archivo tipo imagen o un PDF." v-b-tooltip.hover></i>
                                                            </label>
                                                            <div id="container-current-rut-document-files" class="mb-2">
                                                                <div v-if="this.formPersonalInfo.rut_document_images.length > 0">
                                                                    <Lightbox
                                                                        id="container-rut-documents-files"
                                                                        :images="formPersonalInfo.rut_document_images"
                                                                        :image_class="'document-file mx-2'"
                                                                        :album_class="'container-rut-documents-files'"
                                                                        :options="options">
                                                                    </Lightbox>
                                                                </div>
                                                                <div id="container-current-rut-document-pdf-files" v-if="this.formPersonalInfo.rut_document_files.length > 0">
                                                                    <div class="col-6 col-md-2 border border-info mr-1 bg-dark text-center" style="cursor: pointer; border-radius: 3px; padding: 5px;" v-for="file in formPersonalInfo.rut_document_files">
                                                                        <img @click="embedDocuments(file)" style="height: 32px; margin-left:8px;" src="http://gbmediagroup.com/laravel/public/images/svg/pdf.svg">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-check form-check-inline mt-3 mb-2 w-100">
                                                                <div class="col-12">
                                                                    <input class="form-check-input checkbox-for" id="checkbox-add-rut-document-file" type="checkbox" v-model="formPersonalInfo.add_rut_document_file" @click="renderImagePicker('#container-rut-document-file', 'rut-document-file')"/>
                                                                    <label for="checkbox-add-rut-document-file" class="form-check-label">Agregar archivo</label>
                                                                </div>
                                                            </div>
                                                            <div class="" id="container-rut-document-file" v-if="formPersonalInfo.add_rut_document_file"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="text-muted">Estado del Usuario</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="form-check form-check-inline col-12 col-md-6 mr-0 pl-3">
                                                <input class="form-check-input checkbox-for" id="activated-user" name="status" type="radio" v-model="formPersonalInfo.status" value="1"/>
                                                <label for="activated-user" class="form-check form-check-inline mb-0">Activo</label>

                                                <input class="form-check-input checkbox-for" id="inactivated-user" name="status" type="radio" v-model="formPersonalInfo.status" value="0"/>
                                                <label for="inactivated-user" class="form-check form-check-inline mb-0">Inactivo</label>
                                            </div>
                                            <div class="col-12 col-md-6 text-right">
                                                <button class="btn btn-info btn-sm" type="button" v-b-modal.modal-retirement-history @click.prevent="getRetirementHistory()">
                                                    <i class="fa fa-history"></i>
                                                    Historial de Retiros
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group row" v-if="formPersonalInfo.status == 0 && user.status === 1">
                                            <div class="col-12 col-md-6">
                                                <label for="inactivated-user-reason" class="required">
                                                    Razón
                                                </label>
                                                <textarea name="inactivated_user_reason" class="form-control" id="inactivated-user-reason" cols="30" rows="3" v-model="formPersonalInfo.inactivated_user_reason" placeholder="Indique la razón del porqué está desactivando el usuario"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="text-right">
                                            <b-button type="button" size="sm" variant="warning" @click="editPersonalInfo()">
                                                <i class="fas fa-plus"></i> Guardar
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>

                            <!-- Access Info -->
                            <div class="tab-pane fade" id="access-info" role="tabpanel">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h5 class="text-muted">Acceso a la Plataforma</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="user-email" class="required">Email o Hangouts:</label>
                                                <input class="form-control" id="user-email" name="email" type="email" v-model="formAccessInfo.email" placeholder="pedroperez@gmail.com"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="user-hangouts-password" class="">Contraseña Hangouts:</label>
                                                <input class="form-control" id="user-hangouts-password" type="text" v-model="formAccessInfo.hangouts_password"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check form-check-inline col-12">
                                                <input class="form-check-input checkbox-for" id="change-password" type="checkbox" v-model="formAccessInfo.change_password"/>
                                                <label for="change-password" class="form-check-label">Cambiar contraseña de acceso</label>
                                            </div>
                                            <div id="container-change-password" class="row mt-2" v-if="formAccessInfo.change_password">
                                                <div class="col-12 col-md-6">
                                                    <label for="user-new-password" class="required">Nueva Contraseña:</label>
                                                    <input class="form-control" id="user-new-password" name="new_password" type="password" v-model="formAccessInfo.new_password"/>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="user-confirm-new-password" class="required">Confirmar Contraseña:</label>
                                                    <input class="form-control" id="user-confirm-new-password" name="confirm_new_password" type="password" v-model="formAccessInfo.confirm_new_password"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="text-right">
                                            <b-button type="button" size="sm" variant="warning" @click="editAccessInfo()">
                                                <i class="fas fa-plus"></i> Guardar
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payroll -->
                            <div class="tab-pane fade" id="payroll" role="tabpanel">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h5 class="text-muted">Contrato</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="contract-type" class="">
                                                    Tipo de Contrato:
                                                    <i class="fa fa-info-circle text-warning" title="Prestación de Servicios se disparará alarma cada 6 meses para recordar renovación contrato. Contrato indefinido no habrá alarma después de la firma del contrato." v-b-tooltip.hover></i>
                                                </label>
                                                <div class="input-group">
                                                    <b-form-select v-model="formPayroll.contract_type" id="contract-type" name="contract_type">
                                                        <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                        <b-form-select-option v-for="type in contract_types" :key="type.id" :value="type.id">{{ type.name }}</b-form-select-option>
                                                    </b-form-select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="admission-date" class="">Fecha de Inicio:</label>
                                                <input class="form-control" id="admission-date" type="date" name="admission_date" v-model="formPayroll.admission_date"/>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="contract-sign-date" class="">Fecha de Firma de Contrato:</label>
                                                <input class="form-control" id="contract-sign-date" type="date" v-model="formPayroll.contract_sign_date"/>
                                            </div>
                                            <div class="col-12 col-md-6" v-if="formExtraInfo.setting_role_id !== 14 && can('user-current-salary-view')">
                                                <label for="month-salary" class="">Salario Mensual:</label>
                                                <input class="form-control" id="month-salary" type="text" name="current_salary" v-model="formPayroll.current_salary" placeholder="1800000" :disabled="!can('user-current-salary-edit')"/>
                                            </div>
                                        </div>
                                        <div id="container-social-security" v-if="can('user-social-security-view')">
                                            <h5 class="text-muted">Seguridad Social</h5>
                                            <hr class="mt-0">
                                            <div class="form-group row">
                                                <div class="col-12 col-md-6">
                                                    <div class="form-check form-check-inline col-12">
                                                        <input class="form-check-input checkbox-for" id="has-social-security" type="checkbox" v-model="formPayroll.has_social_security" :disabled="!can('user-social-security-edit')"/>
                                                        <label for="has-social-security" class="form-check-label">Tiene Seguridad Social</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row" v-if="formPayroll.has_social_security && formPayroll.contract_type === 1">
                                                <div class="col-12 col-md-6">
                                                    <label for="social-security-amount" class="">Valor Seguridad Social:</label>
                                                    <input class="form-control" id="social-security-amount" name="social_security_amount" type="text" v-model="formPayroll.social_security_amount" placeholder="12000"/>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="eps" class="">EPS:</label>
                                                    <div class="input-group">
                                                        <b-form-select v-model="formPayroll.eps_id" id="eps" name="eps_id">
                                                            <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                            <b-form-select-option v-for="eps in all_eps" :key="eps.id" :value="eps.id">{{ eps.name }}</b-form-select-option>
                                                            <b-form-select-option value="other">Otra</b-form-select-option>
                                                        </b-form-select>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-12" v-if="formPayroll.eps_id === 'other'">
                                                            <label for="new-eps" class="">Nombre de la EPS:</label>
                                                            <input class="form-control" id="new-eps" type="text" name="new_eps" v-model="formPayroll.new_eps" placeholder="EPS Del Valle S.A.S"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group" v-else-if="formPayroll.has_social_security">
                                                <p>
                                                    <i class="fa fa-info-circle text-warning"></i>
                                                    Para los usuarios con tipo de contrato a Término Indefindo, el valor de Seguridad Social es la suma del básico quincenal, horas extras y recargo nocturno. Dicha sumatioria se multiplica por el 8%.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="form-group row" v-if="formPayroll.contract_type == 2 && formExtraInfo.setting_role_id !== 14">
                                            <div class="col-12">
                                                <h5 class="text-muted">Auxilio de Transporte</h5>
                                                <hr class="mt-0">
                                                <div class="col-12">
                                                    <div class="row">
                                                        <div class="form-check form-check-inline col-12 col-md-6 mr-0">
                                                            <input class="form-check-input checkbox-for" id="has-transportation-aid" type="checkbox" v-model="formPayroll.has_transportation_aid"/>
                                                            <label for="has-transportation-aid" class="form-check-label">
                                                                Recibe Auxilio de Transporte
                                                                <i class="fa fa-info-circle text-warning" title="El Auxilio de Transporte solo se confiere a los usuarios que reciban menos de $1.755.606 (Menos de dos salarios mínimos) y a contrato a término indefinido. Dicho valor se defiere por quincena." v-b-tooltip.hover></i>
                                                            </label>
                                                        </div>
                                                        <div id="container-transportation-aid" class="" v-if="formPayroll.has_transportation_aid">
                                                            <div class="">
                                                                <label for="transportation-aid-amount" class="required">Monto Auxilio de Transporte:</label>
                                                                <input class="form-control" id="transportation-aid-amount" name="transportation_aid_amount" type="text" v-model="formPayroll.transportation_aid_amount" placeholder="54500" disabled/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="" v-if="formExtraInfo.setting_role_id !== 14">
                                            <h5 class="text-muted">Bonificación</h5>
                                            <hr class="mt-0">
                                            <div class="form-group">
                                                <div class="col-12 row">
                                                    <div class="form-check form-check-inline col-12 col-md-6 mr-0">
                                                        <input class="form-check-input checkbox-for" id="has-bonus" type="checkbox" v-model="formPayroll.has_bonus"/>
                                                        <label for="has-bonus" class="form-check-label">
                                                            Recibe bonificación
                                                            <i class="fa fa-info-circle text-warning" title="La bonificación es un valor adicional que recibe el usuario que no se incluye en nómina." v-b-tooltip.hover></i>
                                                        </label>
                                                    </div>
                                                    <div id="container-bonus-amount" class="" v-if="formPayroll.has_bonus">
                                                        <div class="">
                                                            <label for="bonus-amount" class="required">Monto bonificación:</label>
                                                            <input class="form-control" id="bonus-amount" name="bonus_amount" type="text" v-model="formPayroll.bonus_amount" placeholder="50000"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="" v-if="formExtraInfo.setting_role_id !== 14">
                                            <h5 class="text-muted">Auxilio de Movilización "Rodamiento"</h5>
                                            <hr class="mt-0">
                                            <div class="form-group">
                                                <div class="col-12 row">
                                                <div class="form-check form-check-inline col-12 col-md-6 mr-0">
                                                    <input class="form-check-input checkbox-for" id="has-mobilization" type="checkbox" v-model="formPayroll.has_mobilization"/>
                                                    <label for="has-mobilization" class="form-check-label">
                                                        Recibe Auxilio de Movilización
                                                        <i class="fa fa-info-circle text-warning" title="El Auxilio de Movilización es un valor que será diferido por quincena al usuario." v-b-tooltip.hover></i>
                                                    </label>
                                                </div>
                                                <div id="container-mobilization-amount" class="" v-if="formPayroll.has_mobilization">
                                                    <div class="">
                                                        <label for="mobilization-amount" class="required">Monto Auxilio de Movilización:</label>
                                                        <input class="form-control" id="mobilization-amount" name="mobilization_amount" type="text" v-model="formPayroll.mobilization_amount" placeholder="50000"/>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="container-contracts" v-if="have_contracts_access">
                                            <h5 class="text-muted">
                                                Exportar Contratos
                                            </h5>
                                            <hr class="mt-0">
                                            <div class="in-maintenance">
                                                <h5>Próximamente...</h5>
                                            </div>
                                            <div v-if="false">
                                                <small class="text-warning">Antes de exportar contratos para su respectiva firma, asegúrese que toda la información del usuario esté actualizada, al igual que la información de la empresa.</small>
                                                <div class="form-group mt-3">
                                                    <div class="col-12 row" v-if="user_contracts.length > 0">
                                                        <div v-for="user_contract in user_contracts" class="col-6 col-sm-3 col-md-2 mb-4 text-center">
                                                            <a :href="projectRoute('contracts.PDF_' + user_contract.contract.url, {id: user.id})" target="_blank" class="contract-link">
                                                                <div class="container-contract">
                                                                    <img class="img-fluid contract-icon" :src="'/images/contracts/' + user_contract.contract.image" :alt="user_contract.contract.description">
                                                                    <div class="mt-2">{{ user_contract.contract.title }}</div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 row" v-else>
                                                        No hay contratos activos para el usuario.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="text-right">
                                            <b-button type="button" size="sm" variant="warning" @click="editPayroll()">
                                                <i class="fas fa-plus"></i> Guardar
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="tab-pane fade" id="payment-method" role="tabpanel">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h5 class="text-muted">Datos Bancarios</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <div class="form-check form-check-inline col-12">
                                                    <input class="form-check-input checkbox-for" id="has-bank-account" type="checkbox" v-model="formBankAccountInfo.has_bank_account"/>
                                                    <label for="has-bank-account" class="form-check-label">Tiene cuenta de banco</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group" v-if="formBankAccountInfo.has_bank_account">
                                            <div class="form-check form-check-inline col-12 row mb-3 px-3">
                                                <input class="form-check-input checkbox-for" id="has-bank-without-retention" type="checkbox" v-model="formBankAccountInfo.bank_without_retention"/>
                                                <label for="has-bank-without-retention" class="form-check-label">Banco sin retención</label>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-12 col-md-6">
                                                    <label for="bank" class="required">Banco:</label>
                                                    <div class="input-group">
                                                        <b-form-select v-model="formBankAccountInfo.bank_account_id" id="document-type" name="bank_account_id">
                                                            <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                            <b-form-select-option v-for="bank in banks" :key="bank.id" :value="bank.id">{{ bank.name }}</b-form-select-option>
                                                        </b-form-select>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="bank-account-city" class="required">Ciudad:</label>
                                                    <input class="form-control" id="bank-account-city" name="bank_account_city" type="text" v-model="formBankAccountInfo.bank_account_city" placeholder="Cali"/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-12 col-md-6">
                                                    <label for="bank-owner" class="required">Titular de la Cuenta:</label>
                                                    <input class="form-control" id="bank-owner" name="bank_account_owner" type="text" v-model="formBankAccountInfo.bank_account_owner" placeholder="Pedro Andres Pere Pirela"/>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="document-type" class="required">Tipo de Documento:</label>
                                                    <div class="input-group">
                                                        <b-form-select v-model="formBankAccountInfo.bank_document_type" id="document-type" name="bank_document_type">
                                                            <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                            <b-form-select-option v-for="document_type in documents_types" :key="document_type.id" :value="document_type.id">{{ document_type.name }}</b-form-select-option>
                                                        </b-form-select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-12 col-md-6">
                                                    <label for="bank-document-number" class="required">Número de Documento:</label>
                                                    <input class="form-control" id="bank-document-number" name="bank_document_number" type="text" v-model="formBankAccountInfo.bank_document_number" placeholder="12315581"/>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-12 col-md-6">
                                                    <label for="bank-account-number" class="required">Número de Cuenta:</label>
                                                    <input class="form-control" id="bank-account-number" name="bank_account_number" type="text" v-model="formBankAccountInfo.bank_account_number" placeholder="12315581"/>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="bank-account-type" class="required">Tipo de Cuenta:</label>
                                                    <input class="form-control" id="bank-account-type" name="bank_account_type" type="text" v-model="formBankAccountInfo.bank_account_type" placeholder="Ahorros"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="text-right">
                                            <b-button type="button" size="sm" variant="warning" @click="editBankAccountInfo()" v-if="can('user-payment-method-edit')">
                                                <i class="fas fa-plus"></i> Guardar
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Extra info and options -->
                            <div class="tab-pane fade" id="extra-info" role="tabpanel">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h5 class="text-muted">Opciones</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="role" class="required">Rol Principal:</label>
                                                <div class="input-group">
                                                    <b-form-select v-model="formExtraInfo.setting_role_id" id="role" name="setting_role_id">
                                                        <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                        <b-form-select-option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                                                    </b-form-select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-check form-check-inline col-12 mb-2">
                                                    <input class="form-check-input checkbox-for" id="has-extended-role" type="checkbox" v-model="formExtraInfo.has_extended_role"/>
                                                    <label for="has-extended-role" class="form-check-label">
                                                        Agregar rol extendido
                                                        <i class="fa fa-info-circle text-warning" title="Puede agregar un rol extendido para que el usuario tenga también acceso a los permisos de éste" v-b-tooltip.hover></i>
                                                    </label>
                                                </div>
                                                <div class="" v-if="formExtraInfo.has_extended_role">
                                                    <v-select :options="all_roles" multiple  v-model="formExtraInfo.extended_roles"></v-select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="location" class="required">Locación:</label>
                                                <div class="input-group">
                                                    <b-form-select v-model="formExtraInfo.setting_location_id" id="location" name="setting_location_id">
                                                        <b-form-select-option value="">Seleccione...</b-form-select-option>
                                                        <b-form-select-option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</b-form-select-option>
                                                    </b-form-select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6" v-if="formExtraInfo.setting_role_id === 14">
                                                <label for="model-nick" class="">Nick Modelo:</label>
                                                <input class="form-control" id="model-nick" type="text" v-model="formExtraInfo.model_nick"/>
                                            </div>
                                        </div>
                                        <h5 class="text-muted">Información Complementaria</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="personal-email" class="">Email Personal:</label>
                                                <input class="form-control" id="personal-email" type="email" v-model="formExtraInfo.personal_email"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="emergency-contact" class="">Contacto de Emergencia:</label>
                                                <input class="form-control" id="emergency-contact" type="text" v-model="formExtraInfo.emergency_contact" placeholder="Juan Ramos"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="emergency-phone-contact" class="">Teléfono de Contacto:</label>
                                                <input class="form-control" id="emergency-phone-contact" type="text" v-model="formExtraInfo.emergency_phone" placeholder="3158932541"/>
                                            </div>
                                        </div>
                                        <h5 class="text-muted">Residencia</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="department" class="">Departamento:</label>
                                                <b-form-select v-model="formExtraInfo.department_id" id="department" @change="getDepartmentCities()" name="department_id">
                                                    <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                                                    <b-form-select-option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</b-form-select-option>
                                                </b-form-select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="city" class="">Ciudad:</label>
                                                <div class="input-group">
                                                    <b-form-select v-model="formExtraInfo.city_id" id="city" name="city_id">
                                                        <b-form-select-option :value="0">Seleccione...</b-form-select-option>
                                                        <b-form-select-option v-for="city in department_cities" :key="city.id" :value="city.id">{{ city.name }}</b-form-select-option>
                                                    </b-form-select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <label for="neighborhood" class="">Barrio:</label>
                                                <input class="form-control" id="neighborhood" name="neighborhood" type="text" v-model="formExtraInfo.neighborhood"/>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="address" class="">Dirección:</label>
                                                <textarea class="form-control" name="address" id="address" cols="30" rows="1" v-model="formExtraInfo.address"></textarea>
                                            </div>
                                        </div>
                                        <h5 class="text-muted">Uniforme</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <div class="form-check form-check-inline col-12">
                                                    <input class="form-check-input checkbox-for" id="has-uniform" type="checkbox" v-model="formExtraInfo.has_uniform"/>
                                                    <label for="has-uniform" class="form-check-label">Tiene Uniforme</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row" v-if="formExtraInfo.has_uniform">
                                            <div class="col-12 col-md-3">
                                                <label for="blouse-size" class="">Talla Blusa:</label>
                                                <input class="form-control" id="blouse-size" type="email" v-model="formExtraInfo.blouse_size" placeholder="XL"/>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label for="pants-size" class="">Talla Pantalón:</label>
                                                <input class="form-control" id="pants-size" type="email" v-model="formExtraInfo.pants_size" placeholder="XL"/>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label for="pants-long" class="">Largo Pantalón:</label>
                                                <input class="form-control" id="pants-long" type="email" v-model="formExtraInfo.pants_long" placeholder="120"/>
                                            </div>
                                        </div>
                                        <h5 class="text-muted">Imagen de Perfil</h5>
                                        <hr class="mt-0">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-6">
                                                <div id="container-current-profile-pic" class="mb-2">
                                                    <div v-if="user.avatar == null || user.avatar == ''">
                                                        <img src="/images/svg/no-photo.svg" :alt="user.first_name + ' ' + user.last_name" class="img-fluid" style="width: 30%;">
                                                    </div>
                                                    <div v-else>
                                                        <img :src="'../../../storage/app/public/' + studio_slug + '/avatars/' + user.avatar" :alt="user.first_name" class="img-fluid">
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <input class="form-check-input checkbox-for" id="checkbox-add-profile-pic" v-model="formExtraInfo.add_profile_pic" type="checkbox" @click="renderImagePicker('#container-profile-pic', 'profile-pic')"/>
                                                    <label for="checkbox-add-profile-pic" class="form-check-label">Editar Imagen</label>
                                                </div>
                                                <div class="mt-2" v-if="formExtraInfo.add_profile_pic">
                                                    <div id="container-profile-pic"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <!--Imagen Carnet-->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="text-right">
                                            <b-button type="button" size="sm" variant="warning" @click="editExtraInfo()">
                                                <i class="fas fa-plus"></i> Guardar
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer"></div>
        </div>

        <b-modal id="modal-retirement-history" title="Historial de Retiros" scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="modal-retirement-history">
            <b-table show-empty small stacked="sm" :fields="fields"
                     :items="items" responsive="sm" class="table-striped"
                     :current-page="currentPage" :per-page="perPage" :filter="filter"
                     :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" ref="table"
                     selected-variant="info"
                     :busy.sync="isBusy" empty-text="No hay histórico de retiros"
            >
            </b-table>

            <template v-slot:modal-footer>
                <div class="text-right">
                    <b-button variant="secondary" size="sm" @click="$bvModal.hide('modal-retirement-history')">Cerrar</b-button>
                </div>
            </template>
        </b-modal>


        <b-modal id="modal-documents" title="Documento" scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="modal-documents">
            <embed ref="embed-documents" :src="src_embed_document" style="height: 600px; width: 100%"></embed>

            <template v-slot:modal-footer>
                <div class="text-right">
                    <b-button variant="secondary" size="sm" @click="$bvModal.hide('modal-documents')">Cerrar</b-button>
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import * as helper from '../../../../../public/js/vue-helper.js'
    import Lightbox from 'vue-simple-lightbox'

    export default {
        name: 'edit-user',
        components: {
            Lightbox
        },
        props: [
            'user',
            'studio_slug',
            'locations',
            'roles',
            'permissions',
            'departments',
            'countries',
            'blood_types',
            'contract_types',
            'documents_types',
            'banks',
            'all_eps',
            'quarter_transportation_aid_value',
            'user_contracts',
            'have_contracts_access',
        ],
        data() {
            return {
                formPersonalInfo: new Form({
                    first_name: this.user.first_name,
                    middle_name: this.user.middle_name,
                    last_name: this.user.last_name,
                    second_lastname: this.user.second_last_name,
                    birth_date: this.user.birth_date,
                    mobile_number: this.user.mobile_number,
                    country_id: this.user.nationality,
                    rh_id: this.user.blood_type_id,
                    document_type: this.user.document_id,
                    document_number: this.user.document_number,
                    document_has_expiration_date: this.user.expiration_date != null,
                    document_expiration_date: this.user.expiration_date,
                    inactivated_user: false,
                    status: this.user.status,
                    add_front_document_file: false,
                    front_document_files: [],
                    front_document_images: [],
                    add_back_document_file: false,
                    back_document_images: [],
                    back_document_files: [],
                    add_face_id_document_file: false,
                    face_id_document_images: [],
                    face_id_document_files: [],
                    add_rut_document_file: false,
                    rut_document_images: [],
                    rut_document_files: [],
                }),
                formAccessInfo: new Form({
                    change_password: false,
                    email: this.user.email,
                    hangouts_password: this.user.hangouts_password,
                    new_password: '',
                    confirm_new_password: '',
                    user_id: this.user.id,
                }),
                formPayroll: new Form({
                    current_salary: this.user.current_salary,
                    has_transportation_aid: this.user.has_transportation_aid,
                    transportation_aid_amount: this.quarter_transportation_aid_value,
                    has_bonus: this.user.has_bonus,
                    bonus_amount: this.user.bonus_amount,
                    has_mobilization: this.user.has_mobilization,
                    mobilization_amount: this.user.mobilization_amount,
                    admission_date: this.user.admission_date,
                    contract_sign_date: this.user.contract_date,
                    contract_type: this.user.contract_id,
                    has_social_security: this.user.has_social_security,
                    social_security_amount: (this.user.social_security_amount != '' && this.user.social_security_amount != null ? this.user.social_security_amount : 0),
                    eps_id: this.user.eps_id,
                    new_eps: this.user.new_eps,
                    user_id: this.user.id,
                }),
                formBankAccountInfo: new Form({
                    has_bank_account: this.user.has_bank_account,
                    bank_without_retention: this.user.has_bank_without_retention,
                    bank_account_id: this.user.bank_account_id,
                    bank_account_owner: this.user.bank_account_owner,
                    bank_document_type: this.user.bank_account_document_id,
                    bank_document_number: this.user.bank_account_document_number,
                    bank_account_type: this.user.bank_account_type,
                    bank_account_number: this.user.bank_account_number,
                    bank_account_city: this.user.bank_account_city,
                    user_id: this.user.id,
                }),
                formExtraInfo: new Form({
                    has_extended_role: this.user.roles.length > 1,
                    setting_role_id: this.user.setting_role_id,
                    extended_roles: [],
                    setting_location_id: this.user.setting_location_id,
                    model_nick: this.user.nick,
                    personal_email: this.user.personal_email,
                    address: this.user.address,
                    department_id: this.user.department_id,
                    city_id: this.user.city_id,
                    neighborhood: this.user.neighborhood,
                    emergency_contact: this.user.emergency_contact,
                    emergency_phone: this.user.emergency_phone,
                    has_uniform: this.user.has_uniform,
                    blouse_size: this.user.blouse_size,
                    pants_size: this.user.pants_size,
                    pants_long: this.user.pants_long,
                    add_profile_pic: false,
                    profile_pic_files: [],
                    user_id: this.user.id,
                }),
                src_embed_document: null,
                department_cities: [],
                all_roles: [],
                editing_user_id: this.user.id,
                options : {
                    closeText: 'x',
                    overlay: false,
                    captions: false,
                },
                isBusy: false,
                sortBy: 'starting_date',
                sortDesc: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 50,
                pageOptions: [50, 100, 200],
                filter: null,
                fields: [
                    {key: 'starting_date', label: 'Fecha Ingreso', sortable: true},
                    {key: 'ending_date', label: 'Fecha Retiro', sortable: true},
                    {key: 'description', label: 'Descripción', sortable: false},
                    {key: 'created_by', label: 'Creado Por', sortable: false},
                ],
                items: [],
            }
        },
        computed: {},
        mounted() {},
        created() {
            console.log(this.have_contracts_access)
            let _this = this;

            $.each(this.roles, function (i, role) {
                _this.all_roles.push({id: role.id, label: role.name});
            });

            let re = /(?:\.([^.]+))?$/;

            for (let key in this.user.documents) {
                let file_ext = re.exec(this.user.documents[key].file_name)[1];

                switch (this.user.documents[key].document_id) {
                    case 5: // front_document

                        if(file_ext == 'pdf')
                        {
                            this.formPersonalInfo.front_document_files.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'Frente Documento'
                                }
                            );
                        }
                        else
                        {
                            this.formPersonalInfo.front_document_images.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'Frente Documento'
                                }
                            );
                        }

                        break;

                    case 6: // back_document
                        if(file_ext == 'pdf')
                        {
                            this.formPersonalInfo.back_document_files.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'Reverso Documento'
                                }
                            );
                        }
                        else
                        {
                            this.formPersonalInfo.back_document_images.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'Reverso Documento'
                                }
                            );
                        }

                        break;

                    case 7: // face_id_document
                        if(file_ext == 'pdf')
                        {
                            this.formPersonalInfo.face_id_document_files.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'Rostro - Cédula'
                                }
                            );
                        }
                        else
                        {
                            this.formPersonalInfo.face_id_document_images.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'Rostro - Cédula'
                                }
                            );
                        }

                        break;

                    case 8: // rut_document
                        if(file_ext == 'pdf') {
                            this.formPersonalInfo.rut_document_files.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'RUT'
                                }
                            );
                        }
                        else
                        {
                            this.formPersonalInfo.rut_document_images.push(
                                {
                                    src: '../../../storage/app/public/' + _this.studio_slug + '/documents/' + this.user.documents[key].file_name,
                                    title: 'RUT'
                                }
                            );
                        }
                        break;
                }
            }

            if(this.user.roles.length > 1) {
                for (let key in this.user.roles) {

                    if(this.user.roles[key].id !== this.user.setting_role_id) {
                        this.formExtraInfo.extended_roles.push({
                            id: this.user.roles[key].id,
                            label: this.user.roles[key].name
                        });
                    }
                }
            }

            this.getDepartmentCities();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            editPersonalInfo() {
                helper.VUE_ResetValidations();

                let form_data = new FormData();
                form_data.append('user_id', this.editing_user_id);

                for (let key in this.formPersonalInfo) {
                    form_data.append(key, this.formPersonalInfo[key]);
                }

                if(this.formPersonalInfo.add_front_document_file) {
                    form_data.append('front_document_file', $('input[name="front-document-file"]').prop('files')[0]);
                }

                if(this.formPersonalInfo.add_back_document_file) {
                    form_data.append('back_document_file', $('input[name="back-document-file"]').prop('files')[0]);
                }

                if(this.formPersonalInfo.add_face_id_document_file) {
                    form_data.append('face_id_document_file', $('input[name="face-id-document-file"]').prop('files')[0]);
                }

                if(this.formPersonalInfo.add_rut_document_file) {
                    form_data.append('rut_document_file', $('input[name="rut-document-file"]').prop('files')[0]);
                }

                axios.post(
                    route('user.edit_personal_info'),
                    form_data,
                    {'Content-Type': "multipart/form-data;"}
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
            editAccessInfo() {
                helper.VUE_ResetValidations();

                axios.post(
                    route('user.edit_access_info'),
                    this.formAccessInfo,
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
            editPayroll() {
                if(this.formPayroll.has_social_security) {
                    if((this.formPayroll.social_security_amount == null || this.formPayroll.social_security_amount === '') && this.formPayroll.contract_type === 1) {
                        Toast.fire({
                            icon: "warning",
                            title: "Debe ingresar el monto de la seguridad social.",
                        });

                        return;
                    }
                }

                helper.VUE_ResetValidations();

                axios.post(
                    route('user.edit_payroll'),
                    this.formPayroll,
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
            editBankAccountInfo() {
                helper.VUE_ResetValidations();

                axios.post(
                    route('user.edit_bank_account_info'),
                    this.formBankAccountInfo,
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
            editExtraInfo() {
                helper.VUE_ResetValidations();

                let form_data = new FormData();
                form_data.append('user_id', this.editing_user_id);

                for (let key in this.formExtraInfo) {
                    form_data.append(key, this.formExtraInfo[key]);

                    if(key === 'extended_roles') {
                        let roles = [];

                        for (let role in this.formExtraInfo.extended_roles) {
                            roles.push(this.formExtraInfo.extended_roles[role].label);
                       }

                        form_data.append(key, roles);
                    }
                }

                if(this.formExtraInfo.add_profile_pic) {
                    form_data.append('profile_pic_file', $('input[name="profile-pic"]').prop('files')[0]);
                }

                axios.post(
                    route('user.edit_extra_info'),
                    form_data,
                    {'Content-Type': "multipart/form-data;"}
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
                //this.formExtraInfo.city_id = 0;

                axios.get(route('user.get_department_cities', {department_id: this.formExtraInfo.department_id}))
                .then((response) => {
                    this.department_cities = response.data;
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                });
            },
            renderImagePicker(element_id, input_name) {
                setTimeout(function () {
                    $(element_id).spartanMultiImagePicker({
                        fieldName: input_name,
                        maxCount: 1,
                        groupClassName: '',
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
            getRetirementHistory() {
                axios.post(
                    route('user.get_retirement_history'),
                    {id: this.user.id}
                ).then((response) => {
                    this.items = response.data;
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                });
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
            embedDocuments(file)
            {
                let re = /(?:\.([^.]+))?$/;
                let file_ext = re.exec(file.src)[1];

                if (file_ext == "pdf")
                {
                    this.src_embed_document = file.src;
                    this.$refs['modal-documents'].show();
                } else {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al cargar el archivo.",
                    });
                }
            }
        },
    }
</script>

<style scoped>
    .container-document-file {
        max-width: 115px;
        height: 60px;
        border: 1px dashed #bababa;
        border-radius: 2px;
        text-align: center;
        margin: 5px 10px;
    }
    .document-file {
        max-width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
    }
    .contract-icon {
        max-width: 60px;
    }
    .container-contract {
        border: 1px solid #484848;
        height: 100%;
        padding: 10px;
        transition: all 200ms;
    }
    .container-contract:hover {
        box-shadow: 1px 0px 5px 2px #171313;
    }
    .contract-link:hover {
        text-decoration: none !important;
    }
</style>
