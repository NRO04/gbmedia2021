<template>
  <div id="monitoringtable">
    <b-container fluid>
      <b-row class="justify-content-between">
        <b-col md="12" lg="12" sm="12">
          <span><i class="text-success fas fa-clipboard"></i> Reporte finalizado</span>
          <span class="mx-2"><i class="text-warning fas fa-clipboard"></i> Reporte asignado</span>
          <span class="mx-2"><i class="text-danger fas fa-clipboard"></i> Reporte pendiente</span>
          <span class="mx-2"><i class="text-secondary fas fa-clipboard"></i> Sin Reporte</span>
          <hr>
        </b-col>
      </b-row>

      <b-overlay :show="loading" rounded="sm" variant="dark" opacity="0.8">
        <template #overlay>
          <div class="text-center">
            <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
            <p id="cancel-label">Espere por favor...</p>
          </div>
        </template>
        <!-- User Interface controls -->
        <b-row class="justify-content-between">
          <b-col md="10" lg="10" xl="10" sm="12">
            <b-row>
              <b-col lg="4" sm="12" md="4" class="my-1">
                <b-form-group>
                  <b-input-group size="sm">
                    <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Type to Search"></b-form-input>
                  </b-input-group>
                </b-form-group>
              </b-col>
              <b-col lg="2" sm="12" md="2" class="my-1">
                <b-form-select v-model="search.selectedLocation" size="sm" @change="getReports()">
                  <b-form-select-option v-for="location in getAllLocations" :key="location.id" :value="location.id">
                    {{ location.name }}
                  </b-form-select-option>
                </b-form-select>
              </b-col>
              <b-col lg="3" sm="12" md="2" class="my-1">
                <VueCtkDateTimePicker
                    :no-value-to-custom-elem="(true)"
                    v-model="search"
                    autoClose
                    dark range
                    noShortcuts
                    noLabel
                    noButton
                    color="purple"
                    inputSize="sm"
                    format="YYYY-MM-DD"
                    formatted="ll"
                    id="RangeDatePicker">
                  <b-form-input size="sm" v-model="search.start +' / '+ search.end"></b-form-input>
                </VueCtkDateTimePicker>
              </b-col>
            </b-row>
          </b-col>
          <b-col md="1" lg="1" xl="1" sm="12">
            <b-row>
              <b-col sm="12" md="12" class="my-1">
                <b-form-group>
                  <b-form-select v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
        </b-row>

        <hr>

        <!-- Main table element -->
        <b-table class="table-striped" show-empty small stacked="md" :items="items" :fields="fields"
                 :current-page="currentPage" :per-page="perPage" :filter="filter" :busy="isBusy"
                 :filter-included-fields="filterOn" :sort-by.sync="sortBy" :sort-desc.sync="sortDesc"
                 :sort-direction="sortDirection" @filtered="onFiltered">
          <template #cell(nick)="row">
            {{ row.item.nick }}
          </template>

          <template #cell(monday)="row">
            <b-button
                :disabled="row.item.monday.report_id === 0"
                :variant="row.item.monday.variant" size="sm"
                @click="info(row.item, row.index, row.item.monday.day)"
                class="mr-1">
              <i class="far fa-clipboard"></i>
            </b-button>
          </template>
          <template #cell(tuesday)="row">
            <b-button
                :disabled="row.item.tuesday.report_id === 0"
                :variant="row.item.tuesday.variant"
                size="sm"
                @click="info(row.item, row.index, row.item.tuesday.day)"
                class="mr-1">
              <i class="far fa-clipboard"></i>
            </b-button>
          </template>
          <template #cell(wednesday)="row">
            <b-button
                :disabled="row.item.wednesday.report_id === 0"
                :variant="row.item.wednesday.variant"
                size="sm"
                @click="info(row.item, row.index, row.item.wednesday.day)"
                class="mr-1">
              <i class="far fa-clipboard"></i>
            </b-button>
          </template>
          <template #cell(thursday)="row">
            <b-button
                :disabled="row.item.thursday.report_id === 0"
                :variant="row.item.thursday.variant"
                size="sm"
                @click="info(row.item, row.index, row.item.thursday.day)"
                class="mr-1">
              <i class="far fa-clipboard"></i>
            </b-button>
          </template>
          <template #cell(friday)="row">
            <b-button
                :disabled="row.item.friday.report_id === 0"
                :variant="row.item.friday.variant"
                size="sm"
                @click="info(row.item, row.index, row.item.friday.day)"
                class="mr-1">
              <i class="far fa-clipboard"></i>
            </b-button>
          </template>
          <template #cell(saturday)="row">
            <b-button
                :disabled="row.item.saturday.report_id === 0 || row.item.saturday.disabled"
                :variant="row.item.saturday.variant" size="sm"
                @click="info(row.item, row.index, row.item.saturday.day)"
                class="mr-1">
              <i class="far fa-clipboard"></i>
            </b-button>
          </template>
          <template #cell(sunday)="row">
            <b-button
                :disabled="row.item.sunday.report_id === 0"
                :variant="row.item.sunday.variant" size="sm"
                @click="info(row.item, row.index, row.item.sunday.day)"
                class="mr-1">
              <i class="far fa-clipboard"></i>
            </b-button>
          </template>
        </b-table>

        <b-row class="py-5">
          <b-col sm="12" md="12" class="my-1">
            <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm" class="my-0 float-right"></b-pagination>
          </b-col>
        </b-row>
      </b-overlay>
    </b-container>

    <!-- Info modal -->
    <b-modal ref="report-modal" no-close-on-backdrop hide-footer header-close-content="" header-bg-variant="dark" centered size="lg"
        :id="infoModal.id" :title-html="'Reporte ' + infoModal.title" @hide="resetInfoModal">
      <div class="container-fluid">
        <flash message=""></flash>
        <div class="row">
          <div class="col-12 col-md-12 col-sm-12 col-lg-12 col-xl-12">
            <b-form-group id="fieldset-monitora" label="Seleccione una monitora y fecha del reporte">
              <div class="row">
                <div class="col-md-4">
                  <b-form-select v-model="report.range" :options="options" size="sm" v-if="report.status === 0 && passDate === false"></b-form-select>
                  <h5 v-if="report.status === 1 || report.status === 2">Fecha: {{ report.range }}</h5>
                  <h5 v-if="report.status === 0 && passDate === true">Fecha: {{ report.range }}</h5>
                </div>
                <div class="col-md-5">
                  <b-form-select size="sm" v-model="report.monitor_id" v-if="report.status === 0">
                    <template #first>
                      <b-form-select-option :value="null" disabled>-- Seleccione una monitora --</b-form-select-option>
                    </template>
                    <b-form-select-option v-for="monitor in monitors" :value="monitor.id" :key="monitor.id">
                      {{ monitor.first_name + " " + monitor.last_name }}
                    </b-form-select-option>
                  </b-form-select>
                  <h5 v-if="report.status === 1 || report.status === 2">Monitor: {{ report.monitor_name }}</h5>
                </div>
                <div class="col-md-3">
                  <h5 class="text-success" v-if="report.status === 1">Reporte asignado</h5>
                  <h5 class="text-success" v-if="report.status === 2">Reporte finalizado</h5>
                  <b-button size="sm" block variant="info" v-if="report.monitor_id !== null && report.status === 0" @click.prevent="assignReport">
                    <i class="fas fa-check"></i> Asignar reporte
                  </b-button>
                </div>
              </div>
            </b-form-group>
          </div>

          <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <hr>
            <b-form-group>
              <div class="row">
                <div class="col-md-4">
                  <b-form-group label="Hora de llegada">
                    <b-form-radio-group id="radio-group-from" v-model="report.arrival" name="arrival" disabled>
                      <b-form-radio :value="1">Puntual</b-form-radio>
                      <b-form-radio :value="2">Retraso</b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                </div>
                <div class="col-md-4">
                  <b-form-group label="Hora de conexion">
                    <b-form-radio-group id="radio-group-to" v-model="report.connection" name="connection" disabled>
                      <b-form-radio :value="1">Puntual</b-form-radio>
                      <b-form-radio :value="2">Retraso</b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                </div>
                <div class="col-md-3">
                  <h5>Vendió: $ {{ report.total }}</h5>
                </div>
              </div>
            </b-form-group>
          </div>
        </div>

        <div v-if="hasRequire">
          <b-card no-body>
            <b-tabs pills fill card active-nav-item-class="bg-success" v-model="report.step">
              <b-tab active :value="0">
                <template #title>
                  <strong><i class="fas fa-list"></i> Informacion General</strong>
                </template>
                <div class="row">
                  <div class="col-12">
                    <b-form-group label="Aspecto general" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-3" v-model="report.look" name="look">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="3">Regular</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Peinado, Pelo o Tinte" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-4" v-model="report.hairstyle" name="hairstyle">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="3">Regular</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Uñas" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-5" v-model="report.manicure_pedicure" name="manicure_pedicure">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="3">Regular</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Maquillaje" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-6" v-model="report.makeup" name="makeup">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="3">Regular</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Vestuario" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-7" v-model="report.lingerie" name="lingerie">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="3">Regular</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="¿Por qué?" v-if="regular">
                      <b-form-textarea :disabled="report.status == 2" v-model="report.comment_on_general" debounce="500" rows="3" max-rows="5"></b-form-textarea>
                    </b-form-group>
                  </div>
                  <div class="col-12" v-if="stepOneComplete && !allStepsComplete && report.status !== 2">
                    <b-button size="sm" class="float-right" variant="primary" @click="onSubmit" :disabled="isCreating">
                      <span v-if="!isCreating"><i class="fas fa-save"></i> Guardar</span>
                      <span v-else><i class="fas fa-spinner fa-spin"></i> Guardanado...</span>
                    </b-button>
                  </div>
                </div>
              </b-tab>
              <b-tab :value="1">
                <template #title>
                  <strong><i class="fas fa-video"></i> Durante Transmisión</strong>
                </template>
                <div class="row">
                  <div class="col-12">
                    <b-form-group label="Sonrie constantenmente" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-3" v-model="report.smiles" name="smiles">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Hace contacto visual" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-4" v-model="report.visual_contact" name="visual_contact">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Posa atractivamente" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-5" v-model="report.posture" name="posture">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Coquetea e incita al Privado" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-6" v-model="report.lures_users" name="lures_users">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Muestra sus Atributos" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-7" v-model="report.highlights_attributes" name="highlights_attributes">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Esconde sus Defectos" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-7" v-model="report.hide_flaws" name="hide_flaws">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Atiende indicaciones Monitor" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-7" v-model="report.takes_recommendations" name="takes_recommendations">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Interactúa con Miembros/Visitantes" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-7" v-model="report.interacts_online" name="interacts_online">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Cumple deseos del usuario en PTV" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-7" v-model="report.fulfills_user_wishes" name="fulfills_user_wishes">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="La Modelo habla por Micrófono" label-cols-sm="5">
                      <b-form-radio-group id="radio-group-7" v-model="report.uses_mic" name="uses_mic">
                        <b-form-radio :disabled="report.status == 2" :value="4">Sí</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="5">No</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="6">A veces</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="¿Por qué?" v-if="aveces">
                      <b-form-textarea :disabled="report.status == 2" v-model="report.comment_on_show" debounce="500" rows="3" max-rows="5"></b-form-textarea>
                    </b-form-group>
                  </div>
                  <div class="col-12" v-if="stepTwoComplete  && !allStepsComplete && report.status !== 2">
                    <b-button size="sm" class="float-right" variant="primary" @click="onSubmit" :disabled="isCreating">
                      <span v-if="!isCreating"><i class="fas fa-save"></i> Guardar</span>
                      <span v-else><i class="fas fa-spinner fa-spin"></i> Guardanado...</span>
                    </b-button>
                  </div>
                </div>
              </b-tab>
              <b-tab :value="2">
                <template #title>
                  <strong><i class="fas fa-bed"></i> El Cuarto</strong>
                </template>
                <div class="row">
                  <div class="col-12">
                    <b-form-group>
                      <div class="row">
                        <div class="col-6">
                          <b-form-group id="fieldset-location" label="Seleccione una locación">
                            <b-form-select size="sm" :disabled="report.status == 2" v-model="report.setting_location_id" @change="getRooms()">
                              <template #first>
                                <b-form-select-option :value="null" disabled>-- Seleccione una locacion --</b-form-select-option>
                              </template>
                              <b-form-select-option :disabled="report.status == 2" v-for="location in getAllLocations" :key="location.id" :value="location.id">
                                {{ location.name }}
                              </b-form-select-option>
                            </b-form-select>
                          </b-form-group>
                        </div>
                        <div class="col-6">
                          <b-form-group id="fieldset-numero-cuarto" label="Seleccione un nro. de cuarto">
                            <b-form-select :disabled="report.status == 2" size="sm" v-model="report.room_number">
                              <template #first>
                                <b-form-select-option :value="null" disabled>-- Seleccione un nro. cuarto --</b-form-select-option>
                              </template>
                              <b-form-select-option :disabled="report.status == 2" v-for="(i, key) in rooms" :value="i" :key="key">
                                Cuarto {{ i }}
                              </b-form-select-option>
                            </b-form-select>
                          </b-form-group>
                        </div>
                      </div>
                    </b-form-group>
                    <hr>
                    <b-form-group label="Equipo" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-3" v-model="report.room_equipment" name="room_equipment">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Iluminación" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-4" v-model="report.room_lighting" name="room_lighting">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Orden" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-5" v-model="report.room_cleanliness" name="room_cleanliness">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Camara" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-6" v-model="report.camera" name="camera">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Audio" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-7" v-model="report.audio" name="audio">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="Music" label-cols-sm="3">
                      <b-form-radio-group id="radio-group-7" v-model="report.music" name="music">
                        <b-form-radio :disabled="report.status == 2" :value="1">Bien</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="2">Mal</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="¿Por qué?" v-if="mal">
                      <b-form-textarea :disabled="report.status == 2" v-model="report.comment_on_room" debounce="500" rows="3" max-rows="5"></b-form-textarea>
                    </b-form-group>
                  </div>
                  <div class="col-12" v-if="stepThreeComplete  && !allStepsComplete && report.status !== 2">
                    <b-button size="sm" class="float-right" variant="primary" @click="onSubmit" :disabled="isCreating">
                      <span v-if="!isCreating"><i class="fas fa-save"></i> Guardar</span>
                      <span v-else><i class="fas fa-spinner fa-spin"></i> Guardanado...</span>
                    </b-button>
                  </div>
                </div>
              </b-tab>
              <b-tab :value="3">
                <template #title>
                  <strong><i class="fas fa-headset"></i> Actitud y Show</strong>
                </template>
                <div class="row">
                  <div class="col-12">
                    <b-form-group label="Describa actitud de modelo">
                      <b-form-textarea :disabled="report.status == 2" v-model="report.comment_on_model" debounce="500" rows="3" max-rows="5"></b-form-textarea>
                    </b-form-group>
                    <b-form-group label="Su cuarto es">
                      <b-form-radio-group id="radio-group-3" v-model="report.room_status" name="room_status">
                        <b-form-radio :disabled="report.status == 2" :value="7">Divertido</b-form-radio>
                        <b-form-radio :disabled="report.status == 2" :value="8">Aburrido</b-form-radio>
                      </b-form-radio-group>
                    </b-form-group>
                    <b-form-group label="¿Por qué?" v-if="report.room_status != null">
                      <b-form-textarea :disabled="report.status == 2" v-model="report.comment_room_status" debounce="500" rows="3" max-rows="5"></b-form-textarea>
                    </b-form-group>
                    <b-form-group id="fieldset-show" label="Califique el Show de la Modelo">
                      <b-form-select size="sm" :disabled="report.status == 2" v-model="report.show_score">
                        <b-form-select-option :disabled="report.status == 2" v-for="i in 10" :value="i" :key="i">{{ i }}</b-form-select-option>
                      </b-form-select>
                    </b-form-group>
                    <b-form-group label="¿Por qué?">
                      <b-form-textarea :disabled="report.status == 2" v-model="report.comment_on_score" debounce="500" rows="3" max-rows="5"></b-form-textarea>
                    </b-form-group>
                    <b-form-group label="Recomendaciones para la Modelo">
                      <b-form-textarea :disabled="report.status == 2" v-model="report.recommendations" debounce="500" rows="3" max-rows="5"></b-form-textarea>
                    </b-form-group>
                   <b-form-group class="py-2">
                     <b-form-checkbox id="checkbox-1" v-model="uploadImages" name="checkbox-1" :value="1" :unchecked-value="2">
                       Cargar imagenes?
                     </b-form-checkbox>
                   </b-form-group>
                    <b-form-group v-if="report.status !== 2 && uploadImages !== 2">
                      <vue-dropzone ref="myVueDropzone" id="dropzone" :options="dropzoneOptions" @vdropzone-sending="saveReportImages"></vue-dropzone>
                    </b-form-group>

                    <b-form-group>
                      <b-row class="justify-content-center">
                        <b-col v-for="image in images" :key="image.id">
                          <img :src="'storage/GB/reports/images/' + image.report_image" :alt="image.report_image" class="w-75">
                        </b-col>
                      </b-row>
                    </b-form-group>
                  </div>
                  <div class="col-12" v-if="stepFourComplete  && !allStepsComplete && report.status !== 2">
                    <b-button size="sm" class="float-right" variant="primary" @click="onSubmit" :disabled="isCreating">
                      <span v-if="!isCreating"><i class="fas fa-save"></i> Guardar</span>
                      <span v-else><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </b-button>
                  </div>
                </div>
              </b-tab>
            </b-tabs>
          </b-card>

          <div class="float-left" v-if="can('monitoring-delete')">
            <b-button size="sm" variant="danger" @click="deleteReport" :disabled="isCreating">
              <span v-if="!isCreating"> <i class="fas fa-trash"></i></span>
              <span v-else><i class="fas fa-spinner fa-spin"></i></span>
            </b-button>
          </div>

          <div class="float-right" v-if="can('monitoring-create')">
            <b-button size="sm" variant="success" v-if="allStepsComplete" @click="finalizeReport" :disabled="isCreating">
              <span v-if="!isCreating"> <i class="fas fa-save"></i> Finalizar</span>
              <span v-else><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
            </b-button>
            <div v-if="can('monitoring-edit')">
              <b-button size="sm" variant="info" v-if="report.status === 2 && !filed" @click="fileReport" :disabled="isCreating">
                <span v-if="!isCreating"> <i class="fas fa-archive"></i> Archivar</span>
                <span v-else><i class="fas fa-spinner fa-spin"></i> Archivando...</span>
              </b-button>
            </div>
          </div>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import moment from "moment";
import {mapActions, mapGetters} from "vuex";
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import {ValidationObserver, ValidationProvider} from "vee-validate";

export default {
  props: ['user', 'permissions'],
  name: "monitoringtable",
  data: () => {
    return {
      options: [
        {value: moment().format('YYYY-MM-DD'), text: moment().format('YYYY-MM-DD')},
        {value: moment().subtract(1, 'day').format('YYYY-MM-DD'), text: moment().subtract(1, 'day').format('YYYY-MM-DD')},
      ],
      items: [],
      fields: [
        {key: 'nick', label: 'Modelo', sortable: true, sortDirection: 'desc'},
        {key: 'sunday', label: 'Domingo', class: 'text-center'},
        {key: 'monday', label: 'Lunes', class: 'text-center'},
        {key: 'tuesday', label: 'Martes', class: 'text-center'},
        {key: 'wednesday', label: 'Miercoles', class: 'text-center'},
        {key: 'thursday', label: 'Jueves', class: 'text-center'},
        {key: 'friday', label: 'Viernes', class: 'text-center'},
        {key: 'saturday', label: 'Sabado', class: 'text-center'},
      ],
      totalRows: 1,
      currentPage: 1,
      perPage: 25,
      pageOptions: [25, 50, 100],
      sortBy: '',
      sortDesc: false,
      sortDirection: 'asc',
      filter: null,
      filterOn: [],
      images: [],
      infoModal: {
        id: 'info-modal',
        title: '',
      },
      report: {
        monitor_id: null,
        range: null,
        arrival: null,
        connection: null,
        status: null,
        step: null,
        smiles: null,
        posture: null,
        lures_users: null,
        visual_contact: null,
        highlights_attributes: null,
        hide_flaws: null,
        interacts_online: null,
        fulfills_user_wishes: null,
        uses_mic: null,
        takes_recommendations: null,
        room_number: null,
        room_equipment: null,
        room_lighting: null,
        room_cleanliness: null,
        camera: null,
        audio: null,
        music: null,
        setting_location_id: null,
        comment_on_model: null,
        room_status: null,
        comment_on_score: null,
        comment_room_status: null,
        comment_on_general: null,
        recommendations: null,
        look: null,
        hairstyle: null,
        manicure_pedicure: null,
        makeup: null,
        lingerie: null,
        total: 0,
        monitor_name: null
      },
      monitors: [],
      search: {
        selectedType: 0,
        selectedLocation: 0,
        start: moment().isoWeekday(0).format("YYYY-MM-DD"),
        end: moment().isoWeekday(6).format("YYYY-MM-DD"),
      },
      loading: true,
      rooms: 0,
      finalize: false,
      isCreating: false,
      passDate: false,
      dropzoneOptions: {
        url: route('monitoring.saveReportImages'),
        thumbnailWidth: 100, // px
        thumbnailHeight: 100,
        addRemoveLinks: true,
        autoProcessQueue: false,
        headers: {
          "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content,
        },
        paramName: function (n) {
          return "report_images[]";
        },
        uploadMultiple: true,
        parallelUploads: 10,
        dictRemoveFile: "Remover",
        dictMaxFilesExceeded: "Only {{maxFiles}} files are allowed",
        dictDefaultMessage:
            "<i class='text-white fas fa-cloud-upload-alt fa-7x'></i><br><br>\n" +
            "<span class='h5 text-white' style='padding: 10px'>Click aqui o arrastre archivo para cargar la imagen</span>",
      },
      savereport: {},
      isBusy: false,
      filed: false,
      uploadImages: 2
    }
  },

  methods: {
    ...mapActions(["GET_ALL_LOCATIONS"]),

    can(permission_name) {
      return this.permissions.indexOf(permission_name) !== -1;
    },

    info(item, index, day) {
      console.log(item[day]);
      // this.report = {}
      this.$root.$emit('bv::show::modal', this.infoModal.id)
      this.infoModal.title = '<span class="text-danger">' + item.nick + '</span>'
      this.report.monitoring_id = item[day].report_id
      this.report.monitor_id = item[day].monitor_id
      this.report.status = item[day].status
      this.report.range = item[day].date
      this.report.arrival = item[day].arrival
      this.report.connection = item[day].connection
      this.filed = item[day].filed_for_user
      this.report.total = item[day].total
      this.report.monitor_name = item[day].monitor_name

      const currentDate = moment().format('YYYY-MM-DD')
      let startDate = moment(currentDate, "YYYY-MM-DD")
      let result = startDate.diff(item[day].date, 'days')
      console.log(result)
      
      if (result >= 2 && item[day].status === 0){
        this.report.range = item[day].date
        this.passDate = true;
      }

      this.getMonitors();
      this.getRooms();

      if (item[day].report !== null) {
        this.report.look = item[day].report.look
        this.report.hairstyle = item[day].report.hairstyle
        this.report.makeup = item[day].report.makeup
        this.report.lingerie = item[day].report.lingerie
        this.report.manicure_pedicure = item[day].report.manicure_pedicure
        this.report.comment_on_general = item[day].report.comment_on_general

        this.report.smiles = item[day].report.smiles
        this.report.visual_contact = item[day].report.visual_contact
        this.report.posture = item[day].report.posture
        this.report.lures_users = item[day].report.lures_users
        this.report.highlights_attributes = item[day].report.highlights_attributes
        this.report.hide_flaws = item[day].report.hide_flaws
        this.report.takes_recommendations = item[day].report.takes_recommendations
        this.report.interacts_online = item[day].report.interacts_online
        this.report.fulfills_user_wishes = item[day].report.fulfills_user_wishes
        this.report.uses_mic = item[day].report.uses_mic
        this.report.comment_on_show = item[day].report.comment_on_show

        this.report.setting_location_id = item[day].setting_location_id
        this.report.room_number = item[day].report.room_number
        this.report.room_equipment = item[day].report.room_equipment
        this.report.room_lighting = item[day].report.room_lighting
        this.report.room_cleanliness = item[day].report.room_cleanliness
        this.report.camera = item[day].report.camera
        this.report.audio = item[day].report.audio
        this.report.music = item[day].report.music
        this.report.comment_on_room = item[day].report.comment_on_room

        this.report.room_status = item[day].report.room_status
        this.report.comment_room_status = item[day].report.comment_room_status
        this.report.comment_on_model = item[day].report.comment_on_model
        this.report.show_score = item[day].report.show_score
        this.report.comment_on_score = item[day].report.comment_on_score
        this.report.recommendations = item[day].report.recommendations
        this.images = item[day].report.images
      }
    },

    resetInfoModal() {
      this.infoModal.title = ''
      this.finalize = false
      this.passDate = false
      this.images = []
      for (let key in this.report) {
        this.report[key] = null;
      }
    },

    onFiltered(filteredItems) {
      this.totalRows = filteredItems.length
      this.currentPage = 1
    },

    getReports() {
      const url = route("monitoring.allReports")
      this.loading = true
      this.isBusy = true
      axios.post(url, {
        selectedLocation: this.search.selectedLocation,
        range: this.search.start + " / " + this.search.end,
        start: this.search.start,
        end: this.search.end,
      }).then((response) => {
        this.items = response.data.reports;
        this.fields = response.data.columns;
        this.loading = false
        this.isBusy = false
      }).catch((error) => {
        this.loading = false
        this.isBusy = false
      })
    },

    getMonitors() {
      // let url = "/monitoring/monitors/" + this.search.selectedLocation
      const id = this.search.selectedLocation
      const url = route("monitoring.getMonitors", {id})
      axios.get(url).then((response) => {
        this.monitors = response.data;
      })
    },

    getRooms() {
      // let url = "/monitoring/rooms/" + this.search.selectedLocation
      const id = this.search.selectedLocation
      const url = route("monitoring.rooms", {id})
      axios.get(url).then((response) => {
        this.rooms = response.data;
      })
    },

    assignReport() {
      // let url = "/monitoring/assign"
      const url = route("monitoring.assignReport")
      const vm = this
      axios.post(url, {
        monitor_id: vm.report.monitor_id,
        date: vm.report.range,
        status: 1,
        monitoring_id: vm.report.monitoring_id,
      }).then((response) => {
        if (response.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          Fire.$emit("activityMonitor")
          this.report.status = response.data.status
        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
        }
      }).catch((error) => {
        SwalGB.fire({
          icon: "error",
          text: "Ha ocurrido un error comuniquese con el admin",
          title: "¡Error!",
          showCancelButton: false,
        })
      })
    },

    onSubmit() {
      // const url = "/monitoring/saveStep"
      const url = route("monitoring.saveStep")
      this.isCreating = true
      const vm = this
      this.savereport.finalize = this.allStepsComplete
      this.finalize = this.allStepsComplete

      for (let key in this.report) {
        if (this.report[key] !== null) {
          this.savereport[key] = this.report[key];
        }
      }


      axios.post(url, this.savereport).then((response) => {
        if (response.data.code === 200 && vm.report.step == 3) {
          vm.saveReportImages()
          flash(response.data.msg, response.data.icon)
          this.isCreating = false
          Fire.$emit("activityMonitor")
        } else {
          flash(response.data.msg, response.data.icon)
          this.isCreating = false
          Fire.$emit("activityMonitor")
        }
        this.isCreating = false
      })
    },

    finalizeReport() {
      const url = route("monitoring.finalizeReport")
      this.isCreating = true
      const vm = this
      this.savereport.finalize = this.allStepsComplete
      this.finalize = this.allStepsComplete

      for (let key in this.report) {
        if (this.report[key] !== null) {
          this.savereport[key] = this.report[key];
        }
      }

      axios.post(url, this.savereport).then((response) => {
        if (response.data.code === 200) {
          this.isCreating = false
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          Fire.$emit("activityMonitor")
          this.$refs['report-modal'].hide()
        }
        else {
          this.isCreating = false
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
        this.isCreating = false
      }).catch((error) => {
        console.log(error)
      })
    },

    saveReportImages(file, xhr, formData) {
      this.$refs.myVueDropzone.processQueue();
      const monitoring_id = this.report.monitoring_id
      formData.append('monitoring_id', monitoring_id)
      this.isCreating = false
      Fire.$emit("activityMonitor")
    },

    fileReport() {
      // let url = "/monitoring/archive"
      const url = route("monitoring.archiveReport")
      const vm = this
      axios.post(url, {
        monitoring_id: vm.report.monitoring_id,
      }).then((response) => {
        if (response.data.code === 200) {
          this.$refs['report-modal'].hide()
          flash(response.data.msg, response.data.icon)
          Fire.$emit("activityMonitor")
          this.report.status = response.data.status
        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
        }
      }).catch((error) => {
        SwalGB.fire({
          icon: "error",
          text: "Ha ocurrido un error comuniquese con el admin",
          title: "¡Error!",
          showCancelButton: false,
        })
      })
    },

    deleteReport() {
      // let url = "/monitoring/delete/" + this.report.monitoring_id

      const id = this.report.monitor_id
      const url = route("monitoring.destroy", {id})
      axios.delete(url).then((response) => {
        if (response.data.code === 200) {
          this.$refs['report-modal'].hide()
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          Fire.$emit("activityMonitor")
          this.report.status = response.data.status
        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
        }
      }).catch((error) => {
        console.log(error)
        SwalGB.fire({
          icon: "error",
          text: "Ha ocurrido un error comuniquese con el admin",
          title: "¡Error!",
          showCancelButton: false,
        })
      })
    },
  },

  components: {
    vueDropzone: vue2Dropzone,
    ValidationObserver,
    ValidationProvider
  },

  computed: {
    ...mapGetters(["getAllLocations"]),

    sortOptions() {
      // Create an options list from our fields
      return this.fields
          .filter(f => f.sortable)
          .map(f => {
            return {text: f.label, value: f.key}
          })
    },
    hasRequire() {
      return this.report.monitor_id != null && this.report.status !== 0
    },
    allStepsComplete() {
      if (this.stepOneComplete && this.stepTwoComplete && this.stepThreeComplete && this.stepFourComplete && this.report.status !== 2) {
        return true
      }

      return false
    },
    stepOneComplete() {
      return this.report.look !== null
          && this.report.hairstyle !== null
          && this.report.manicure_pedicure !== null
          && this.report.makeup !== null
          && this.report.lingerie !== null
    },
    stepTwoComplete() {
      return this.report.smiles !== null
          && this.report.posture !== null
          && this.report.lures_users !== null
          && this.report.visual_contact !== null
          && this.report.highlights_attributes !== null
          && this.report.hide_flaws !== null
          && this.report.interacts_online !== null
          && this.report.fulfills_user_wishes !== null
          && this.report.uses_mic !== null
          && this.report.takes_recommendations !== null
    },
    stepThreeComplete() {
      return this.report.room_number !== null
          && this.report.room_equipment !== null
          && this.report.room_lighting !== null
          && this.report.room_cleanliness !== null
          && this.report.camera !== null
          && this.report.audio !== null
          && this.report.music !== null
          && this.report.setting_location_id !== null
    },
    stepFourComplete() {
      return this.report.comment_on_model !== null
          && this.report.room_status !== null
          && this.report.comment_on_score !== null
          && this.report.comment_room_status !== null
          && this.report.recommendations !== null
    },
    mal() {
      return this.report.room_number !== null
          && (this.report.room_equipment === 2
              || this.report.room_lighting === 2
              || this.report.room_cleanliness === 2
              || this.report.camera === 2
              || this.report.audio === 2
              || this.report.music === 2)
    },
    aveces() {
      return (this.report.smiles === 6 || this.report.posture === 6 || this.report.lures_users === 6 || this.report.visual_contact === 6 || this.report.highlights_attributes === 6
          || this.report.hide_flaws === 6 || this.report.interacts_online === 6 || this.report.fulfills_user_wishes === 6 || this.report.uses_mic === 6 && this.report.takes_recommendations === 6) ||
          (this.report.smiles === 5 || this.report.posture === 5 || this.report.lures_users === 5 || this.report.visual_contact === 5 || this.report.highlights_attributes === 5
              || this.report.hide_flaws === 5 || this.report.interacts_online === 5 || this.report.fulfills_user_wishes === 5 || this.report.uses_mic === 5 && this.report.takes_recommendations === 5)
    },
    regular() {
      return (this.report.look === 3 || this.report.look === 2)
          || (this.report.hairstyle === 3 || this.report.hairstyle === 2)
          || (this.report.manicure_pedicure === 3 || this.report.manicure_pedicure === 2)
          || (this.report.makeup === 3 || this.report.makeup === 2)
          || (this.report.lingerie === 3 || this.report.lingerie === 2)
    },
  },

  watch: {
    search: function () {
      const startOfWeek = moment(this.search.start).startOf('week').isoWeekday(0).format("YYYY-MM-DD")
      const endOfWeek = moment(this.search.start).startOf('isoweek').isoWeekday(6).format("YYYY-MM-DD")
      this.search.start = startOfWeek
      this.search.end = endOfWeek

      if (this.user.setting_location_id == 1) {
        this.search.selectedLocation = 2
      } else {
        this.search.selectedLocation = this.user.setting_location_id
      }
      this.getReports()
    }
  },

  mounted() {
    this.totalRows = this.items.length;
    if (this.user.setting_location_id == 1) {
      this.search.selectedLocation = 2
      this.report.setting_location_id = 2
    } else {
      this.search.selectedLocation = this.user.setting_location_id
    }

    this.GET_ALL_LOCATIONS()
    this.getReports();
    Fire.$on("activityMonitor", () => {
      this.getReports()
    })
  }
}
</script>

<style scoped>

</style>