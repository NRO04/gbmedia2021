<template>
  <div id="attendancetable">
    <b-container fluid>
      <b-row class="justify-content-between">
        <b-col md="12" lg="12" sm="12">
          <span><i class='fas text-success fa-calendar-check'></i> Llegada</span>
          <span class="mx-2"><i class='fas text-danger fa-tint'></i> Periodo</span>
          <span class="mx-2"><i class='fas text-warning fa-first-aid'></i> Enferma</span>
          <span class="mx-2"><i class='fas text-danger fa-calendar-times'></i> Falta injustificada</span>
          <span class="mx-2"><i class='fas text-info fa-times'></i> Falta justificada</span>
          <span class="mx-2"><i class='fas text-success fa-plane'></i> Vacaciones</span>
          <span><i class='fas text-info fa-bed'></i> Descanso</span>
          <hr>
        </b-col>
      </b-row>
      <b-row class="justify-content-between">
        <b-col>
          <b-row>
            <b-col md="11" lg="11" sm="12">
              <b-row class="justify-content-around">
                <b-col lg="5" class="my-1">
                  <b-form-group class="mb-0">
                    <b-input-group size="sm">
                      <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Buscar modelos..."></b-form-input>
                    </b-input-group>
                  </b-form-group>
                </b-col>
                <b-col lg="2" class="my-1">
                  <b-form-select v-model="search.selectedSession" @change="getAttendance()" size="sm">
                    <b-form-select-option :value="0">Todas las modelos</b-form-select-option>
                    <b-form-select-option v-for="session in sessions" :key="session.id" :value="session.id">Con jornada - {{ session.name }}</b-form-select-option>
                  </b-form-select>
                </b-col>
                <b-col lg="2" class="my-1">
                  <b-form-select v-model="search.selectedLocation" @change="getAttendance()" size="sm">
                    <b-form-select-option v-for="location in getAllLocations" :key="location.id" :value="location.id">
                      {{ location.name }}
                    </b-form-select-option>
                  </b-form-select>
                </b-col>
                <b-col lg="3" class="my-1">
                  <b-row>
                    <b-col sm="8" class="px-0">
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
              </b-row>
            </b-col>

            <b-col md="1" lg="1" sm="12">
              <!-- User Interface controls -->
              <b-form-group class="mb-0">
                <b-form-select v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
              </b-form-group>
            </b-col>
          </b-row>

          <hr>
        </b-col>
      </b-row>

      <b-table class="table-striped table-hover" show-empty small stacked="md" :items="items" :fields="fields" :current-page="currentPage"
                 :per-page="perPage" :filter="filter" :busy="isBusy" :filter-included-fields="filterOn" @filtered="onFiltered">
          <template #table-busy>
            <div class="text-center text-info my-2">
              <b-spinner class="align-middle"></b-spinner>
              <strong>Loading...</strong>
            </div>
          </template>

          <template #thead-top="data">
            <b-tr class="text-center">
              <b-th colspan="1"><span class="sr-only">Nick</span></b-th>
              <b-th colspan="7" variant="secondary">Dias semana</b-th>
              <b-th colspan="2" variant="info">Tiempo</b-th>
              <b-th colspan="1"><span class="sr-only">Resumen</span></b-th>
            </b-tr>
          </template>
          
          <template #cell(monday)="row">
            <div class="d-flex align-content-center align-items-center">
              <b-button
                  :disabled="row.item.days.monday.isDisabled"
                  :variant="row.item.days.monday.variant" size="sm"
                  v-b-popover.hover.top="row.item.days.monday.tip"
                  @click="showModal(
                      row.item, row.item.days.monday.date,
                      row.item.days.monday.has_attendance,
                      row.item.days.monday.attendance_type,
                      row.item.days.monday.day,
                      row.item.days.monday.attendance_id,
                      row.item.days.monday.comment_type.attendance_status_id
                  )"
              >
                <span v-html="row.item.days.monday.icon"></span>
              </b-button>
              <span style="font-size: 10px" class="pl-1">
                <span v-if="row.item.days.monday.due > 0">
                  <i class='fas fa-plus text-danger'></i> {{ row.item.days.monday.due }}
                </span> <br>
                <span v-if="row.item.days.monday.recover > 0">
                  <i class='fas fa-plus text-success'></i>  {{ row.item.days.monday.recover }}
                </span>
              </span>
            </div>
          </template>
          <template #cell(tuesday)="row">
            <div class="d-flex align-content-center align-items-center">
              <b-button
                  :disabled="row.item.days.tuesday.isDisabled"
                  :variant="row.item.days.tuesday.variant" size="sm"
                  v-b-popover.hover.top="row.item.days.tuesday.tip"
                  @click="showModal(
                      row.item,
                      row.item.days.tuesday.date,
                      row.item.days.tuesday.has_attendance,
                      row.item.days.tuesday.attendance_type,
                      row.item.days.tuesday.day,
                      row.item.days.tuesday.attendance_id,
                      row.item.days.tuesday.comment_type.attendance_status_id
                  )"
              >
                <span v-html="row.item.days.tuesday.icon"></span>
              </b-button>
              <span style="font-size: 10px" class="pl-1">
                <span v-if="row.item.days.tuesday.due > 0">
                  <i class='fas fa-plus text-danger'></i> {{ row.item.days.tuesday.due }}
                </span>  <br>
                <span v-if="row.item.days.tuesday.recover > 0">
                  <i class='fas fa-plus text-success'></i>  {{ row.item.days.tuesday.recover }}
                </span>
              </span>
            </div>
          </template>
          <template #cell(wednesday)="row">
            <div class="d-flex align-content-center align-items-center">
              <b-button
                  :disabled="row.item.days.wednesday.isDisabled"
                  :variant="row.item.days.wednesday.variant" size="sm"
                  v-b-popover.hover.top="row.item.days.wednesday.tip"
                  @click="showModal(
                      row.item,
                      row.item.days.wednesday.date,
                      row.item.days.wednesday.has_attendance,
                      row.item.days.wednesday.attendance_type,
                      row.item.days.wednesday.day,
                      row.item.days.wednesday.attendance_id,
                      row.item.days.wednesday.comment_type.attendance_status_id
                  )"
              >
                <span v-html="row.item.days.wednesday.icon"></span>
              </b-button>
              <span style="font-size: 10px" class="pl-1">
                <span v-if="row.item.days.wednesday.due > 0">
                  <i class='fas fa-plus text-danger'></i> {{ row.item.days.wednesday.due }}
                </span>  <br>
                <span v-if="row.item.days.wednesday.recover > 0">
                  <i class='fas fa-plus text-success'></i> {{ row.item.days.wednesday.recover }}
                </span>
              </span>
            </div>
          </template>
          <template #cell(thursday)="row">
            <div class="d-flex align-content-center align-items-center">
              <b-button
                  :disabled="row.item.days.thursday.isDisabled"
                  :variant="row.item.days.thursday.variant" size="sm"
                  v-b-popover.hover.top="row.item.days.thursday.tip"
                  @click="showModal(
                      row.item,
                      row.item.days.thursday.date,
                      row.item.days.thursday.has_attendance,
                      row.item.days.thursday.attendance_type,
                      row.item.days.thursday.day,
                      row.item.days.thursday.attendance_id,
                      row.item.days.thursday.comment_type.attendance_status_id
                  )"
              >
                <span v-html="row.item.days.thursday.icon"></span>
              </b-button>
              <span style="font-size: 10px" class="pl-1">
                <span v-if="row.item.days.thursday.due > 0">
                  <i class='fas fa-plus text-danger'></i> {{ row.item.days.thursday.due }}
                </span>  <br>
                <span v-if="row.item.days.thursday.recover > 0">
                  <i class='fas fa-plus text-success'></i> {{ row.item.days.thursday.recover }}
                </span>
              </span>
            </div>
          </template>
          <template #cell(friday)="row">
            <div class="d-flex align-content-center align-items-center">
              <b-button
                  :disabled="row.item.days.friday.isDisabled"
                  :variant="row.item.days.friday.variant" size="sm"
                  v-b-popover.hover.top="row.item.days.friday.tip"
                  @click="showModal(
                      row.item,
                      row.item.days.friday.date,
                      row.item.days.friday.has_attendance,
                      row.item.days.friday.attendance_type,
                      row.item.days.friday.day,
                      row.item.days.friday.attendance_id,
                      row.item.days.friday.comment_type.attendance_status_id
                  )"
              >
                <span v-html="row.item.days.friday.icon"></span>
              </b-button>
              <span style="font-size: 10px" class="pl-1">
                <span v-if="row.item.days.friday.due > 0">
                  <i class='fas fa-plus text-danger'></i> {{ row.item.days.friday.due }}
                </span>   <br>
                <span v-if="row.item.days.friday.recover > 0">
                  <i class='fas fa-plus text-success'></i>  {{ row.item.days.friday.recover }}
                </span>
              </span>
            </div>
          </template>
          <template #cell(saturday)="row">
            <div class="d-flex align-content-center align-items-center">
              <b-button :disabled="row.item.days.saturday.isDisabled"
                        :variant="row.item.days.saturday.variant" size="sm"
                        v-b-popover.hover.top="row.item.days.saturday.tip"
                        @click="showModal(
                      row.item,
                      row.item.days.saturday.date,
                      row.item.days.saturday.has_attendance,
                      row.item.days.saturday.attendance_type,
                      row.item.days.saturday.day,
                      row.item.days.saturday.attendance_id,
                      row.item.days.saturday.comment_type.attendance_status_id
                  )"
              >
                <span v-html="row.item.days.saturday.icon"></span>
              </b-button>
              <span style="font-size: 10px" class="pl-1">
                  <span v-if="row.item.days.saturday.due > 0">
                    <i class='fas fa-plus text-danger'></i> {{ row.item.days.saturday.due }}
                  </span>  <br>
                  <span v-if="row.item.days.saturday.recover > 0">
                    <i class='fas fa-plus text-success'></i> {{ row.item.days.saturday.recover }}
                  </span>
                </span>
            </div>
          </template>
          <template #cell(sunday)="row">
            <div class="d-flex align-content-center align-items-center">
              <b-button
                  :disabled="row.item.days.sunday.isDisabled"
                  :variant="row.item.days.sunday.variant" size="sm"
                  v-b-popover.hover.top="row.item.days.sunday.tip"
                  @click="showModal(
                      row.item,
                      row.item.days.sunday.date,
                      row.item.days.sunday.has_attendance,
                      row.item.days.sunday.attendance_type,
                      row.item.days.sunday.day,
                      row.item.days.sunday.attendance_id,
                      row.item.days.sunday.comment_type.attendance_status_id
                  )"
              >
                <span v-html="row.item.days.sunday.icon "></span>
              </b-button>
              <span style="font-size: 10px" class="pl-1">
                  <span v-if="row.item.days.sunday.due > 0">
                    <i class='fas fa-plus text-danger'></i> {{ row.item.days.sunday.due }}
                  </span>     <br>
                  <span v-if="row.item.days.sunday.recover > 0">
                    <i class='fas fa-plus text-success'></i> {{ row.item.days.sunday.recover }}
                  </span>
                </span>
            </div>
          </template>
          <template #cell(time)="row">
            <span v-html="row.value"></span>
          </template>
          <template #cell(total_recovery_minutes)="row">
            <span v-html="row.value"></span>
          </template>
          <template #cell(action)="row">
            <b-button variant="outline-info" size="sm" @click="detailsModal(row.item)">
              <i class="fas fa-eye"></i>
            </b-button>
          </template>
        </b-table>

      <b-row class="pt-5">
          <b-col sm="12" md="12" class="my-1">
            <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm" class="my-0 float-right"></b-pagination>
          </b-col>
        </b-row>
    </b-container>

    <b-modal @hidden="resetModal" no-close-on-backdrop hide-footer centered size="lg" header-close-content="" ref="attendance-modal" header-bg-variant="dark" :title-html="'Registro de asistencia ' + modalTitle">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-12">
            <b-form-group label="Que desea realizar?">
              <b-form-radio-group id="radio-group-attendance" v-model="attendance.attendanceOptions" name="attendanceOptions">
                <b-form-radio :value="0">Registrar asistencia</b-form-radio>
                <b-form-radio :value="1">Recuperar tiempo</b-form-radio>
              </b-form-radio-group>
            </b-form-group>
          </div>
        </div>

        <div class="row pt-3" v-if="attendance.attendanceOptions === 0">
          <div class="col-md-12">
            <b-form>
              <b-form-group label="¿Qué tipo de asistencia desea registrar?">
                <b-form-radio-group id="radio-group-type" v-model="attendance.type" name="type">
                  <b-form-radio v-model="clearOptions" :value="5">Falta Justificada</b-form-radio>
                  <b-form-radio v-model="clearOptions" :value="3">Falta por el periodo</b-form-radio>
                  <b-form-radio v-model="clearOptions" :value="6">Falta no justificada</b-form-radio>
                  <b-form-radio v-model="clearOptions" :value="1">Llegada</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <b-form-group label="¿Qué tipo de falta desea registrar?" v-if="attendance.type === 5">
                <b-form-radio-group id="radio-group-register" v-model="attendance.subtype" name="subtype">
                  <b-form-radio :value="4">Incapacidad (ENFERMA)</b-form-radio>
                  <b-form-radio :value="5">Otro</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <b-form-textarea
                  v-if="attendance.subtype === 5"
                  id="textarea"
                  v-model="attendance.text"
                  placeholder="Enter something..."
                  rows="3"
                  max-rows="6"
              >
              </b-form-textarea>

              <!-- Llegada -->
              <div class="row pt-3" v-if="attendance.type === 1">
                <div class="col-md-12">
                  <b-form-group>
                    <b-form-radio-group id="radio-group-4" v-model="attendance.subtype" name="subtype">
                      <b-form-radio :value="1">Puntual</b-form-radio>
                      <b-form-radio :value="2">Retraso</b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                </div>

                <div class="col-md-12" v-if="attendance.subtype === 2">
                  <b-form-group>
                    <b-form-radio-group id="radio-group-4" v-model="attendance.minutes_type" name="minutes_type">
                      <b-form-radio :value="1">Minutos Sencillos</b-form-radio>
                      <b-form-radio :value="2">Minutos Dobles</b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                </div>

                <div class="col-md-12" v-if="attendance.minutes_type">
                  <div class="row">
                    <div class="col-sm-2">
                      <b-form-group label="Minutos">
                        <b-form-input size="sm" v-model="calcMinutes" placeholder="Total minutos" readonly></b-form-input>
                      </b-form-group>
                    </div>
                    <div class="col-md-5 col-sm-12 col-lg-5 col-xl-5">
                      <b-form-group label="Hora Llegada">
                        <VueCtkDateTimePicker
                            v-model="attendance.from"
                            dark onlyTime overlay noLabel noHeader
                            id="from"
                            label="Seleccione hora de llegada"
                            inputSize="sm"
                            format="HH:mm a"
                            formatted="HH:mm a"
                        />
                      </b-form-group>
                    </div>
                    <div class="col-md-5 col-sm-12 col-lg-5 col-xl-5">
                      <b-form-group label="Debio ser">
                        <VueCtkDateTimePicker
                            v-model="attendance.to"
                            dark onlyTime overlay noLabel noHeader
                            label="Seleccione hora que debió llegar"
                            inputSize="sm"
                            format="HH:mm a"
                            formatted="HH:mm a"
                            id="to"
                        />
                      </b-form-group>
                    </div>
                  </div>
                </div>
              </div>

              <b-button :disabled="isLoading" v-if="isAttendanceBtn" variant="success" size="sm" class="float-right mt-2" @click.prevent="store">
                Guardar asistencia <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
              </b-button>
            </b-form>
          </div>
        </div>

        <div class="row pt-3" v-if="attendance.attendanceOptions === 1">
          <div class="col-md-12">
            <b-form>
              <b-form-group label="¿Qué tipo de asistencia desea recuperar?">
                <b-form-radio-group id="radio-group-recover" v-model="makeup.type" name="type">
                  <b-form-radio :value="11">Trabaja Dia Libre</b-form-radio>
                  <b-form-radio :value="12">Tiempo Extra</b-form-radio>
                  <b-form-radio :value="13">Doble Turno</b-form-radio>
                  <b-form-radio :value="14">Error Cometido</b-form-radio>
                  <b-form-radio :value="15" v-if="isUnjustified">Quitar día injustificado</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div class="row pt-3" v-if="makeup.type === 11 || makeup.type === 12 || makeup.type === 13">
                <div class="col-md-12">
                  <div class="row">
                    <div class="col-sm-2">
                      <b-form-group label="Minutos">
                        <b-form-input size="sm" v-model="makeupMinutes" placeholder="Total minutos" readonly></b-form-input>
                      </b-form-group>
                    </div>
                    <div class="col-md-5 col-sm-12 col-lg-5 col-xl-5">
                      <b-form-group label="Hora de inicio turno">
                        <VueCtkDateTimePicker v-model="makeup.from" dark onlyTime overlay noLabel noHeader label="Trabaja desde" inputSize="sm" format="HH:mm a" formatted="HH:mm a"/>
                      </b-form-group>
                    </div>
                    <div class="col-md-5 col-sm-12 col-lg-5 col-xl-5">
                      <b-form-group label="Hora de final turno">
                        <VueCtkDateTimePicker v-model="makeup.to" dark onlyTime overlay noLabel noHeader label="Trabaja hasta" inputSize="sm" format="HH:mm a" formatted="HH:mm a"/>
                      </b-form-group>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row pt-3" v-if="makeup.type === 14">
                <div class="col-sm-12 col-md-12">
                  <b-form-group label="Minutos recuperados">
                    <b-form-input type="number" v-model="makeup.minutes" placeholder="Minutos recuperados" name="minutes"></b-form-input>
                  </b-form-group>
                </div>
                <div class="col-sm-12 col-md-12">
                  <b-form-group label="Minutos recuperados">
                    <b-form-textarea id="textarea" v-model="makeup.text" placeholder="Ejemplo: Se le anotaron por error el 12 de Marzo" rows="3" max-rows="6"></b-form-textarea>
                  </b-form-group>
                </div>
              </div>
              <div class="row pt-3" v-if="makeup.type === 15">
                <div class="col-md-12">
                  <b-form-group label="¿Qué tipo de asistencia desea recuperar?">
                    <b-form-radio-group id="radio-group-recover" v-model="makeup.subtype" name="subtype">
                      <b-form-radio :value="3">Periodo</b-form-radio>
                      <b-form-radio :value="4">Incapacidad (ENFERMA)</b-form-radio>
                      <b-form-radio :value="16">Calamidad</b-form-radio>
                      <b-form-radio :value="17">Trabaja en horario diferente</b-form-radio>
                      <b-form-radio :value="15">Otro</b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                </div>
                <div class="col-sm-12 col-md-12" v-if="makeup.subtype === 15 || makeup.subtype === 16">
                  <b-form-group label="Minutos recuperados">
                    <b-form-textarea id="textarea" v-model="makeup.text" rows="3" max-rows="6"></b-form-textarea>
                  </b-form-group>
                </div>

                <div class="col-md-12" v-if="makeup.subtype !== 0">
                  <b-form-group label="¿Escoger fecha?">
                    <b-form-select v-model="makeup.date" class="mb-3">
                      <b-form-select-option v-for="day in unjustified" :value="day.date" :key="day.id">
                        {{ day.date }}
                      </b-form-select-option>
                    </b-form-select>
                  </b-form-group>
                </div>
              </div>
              <b-button :disabled="isLoading" variant="success" size="sm" class="float-right mt-3" @click.prevent="storeMakeup">
                Recuperar minutos <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
              </b-button>
            </b-form>
          </div>
        </div>
      </div>
    </b-modal>

    <b-modal @hidden="resetModal" ref="attendance-detail" header-bg-variant="dark" centered size="xl" header-close-content="" hide-footer :title-html="'Detalles registro de asistencia ' + modalTitle">
      <div class="container">
        <div class="col-md-12">
          <div class="container">
            <b-button :class="visible ? null : 'collapsed'" :aria-expanded="visible ? 'true' : 'false'" aria-controls="collapse-4" @click="visible = !visible" block variant="dark">
              Ver detalles
            </b-button>
            <b-collapse id="collapse-4" v-model="visible" class="mt-2">
              <div class="row">
                <div class="col-sm-12">
                  <b-overlay :show="show" rounded="sm">
                    <div class="card">
                      <div class="card-body">
                        <dl class="list-group list-group-flush">
                          <div class="list-group-item">
                            <div class="row px-4">
                              <dt class="col-sm-12 col-md-6">
                                Tipo Asistencia
                              </dt>
                              <dd class="col-sm-12 col-md-6">
                                {{ attendanceInfo.tip }}
                              </dd>
                            </div>
                          </div>

                          <div class="list-group-item">
                            <div class="row px-4">
                              <dt class="col-sm-12 col-md-6">
                                Tiempo debido
                              </dt>
                              <dd class="col-sm-12 col-md-6">
                                <span class="text-danger">{{ attendanceInfo.due }}</span> minutos
                              </dd>
                            </div>
                          </div>

                          <div class="list-group-item">
                            <div class="row px-4">
                              <dt class="col-sm-12 col-md-6">
                                Tiempo recuperado
                              </dt>
                              <dd class="col-sm-12 col-md-6">
                                <span class="text-success">{{ attendanceInfo.recover }}</span> minutos
                              </dd>
                            </div>
                          </div>

                          <div class="list-group-item">
                            <div class="row px-4">
                              <dt class="col-sm-12 col-md-6">
                                Descripcion
                              </dt>
                              <dd class="col-sm-12 col-md-6">
                                <ul class="list-unstyled">
                                  <li class="py-1" v-for="comment in attendanceInfo.comment" :key="comment.id">
                                  <span class="text-info font-weight-bold">
                                    {{ moment(comment.created_at).format('YYYY-MM-DD') + " @ " + moment(comment.created_at).format('H:mm a')}}:
                                  </span>
                                    {{ comment.comment }}
                                  </li>
                                </ul>
                              </dd>
                            </div>
                          </div>
                        </dl>
                      </div>
                    </div>
                  </b-overlay>
                </div>
              </div>
            </b-collapse>
          </div>
        </div>

        <!-- pause -->
        <div class="col-md-12">
          <div class="row justify-content-center" v-if="dontShowOnDate">
            <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12">
              <hr> <h5>Desconexion/Pausa de la modelo</h5>
            </div>
            <!-- pausa -->
            <div class="col-md-12">
              <b-form-group>
                <b-form-radio-group id="radio-group-4" v-model="connection.subtype" name="subtype">
                  <b-form-radio :value="1">Pausa</b-form-radio>
                  <b-form-radio :value="2">Desconexion</b-form-radio>
                </b-form-radio-group>
              </b-form-group>
            </div>
            <div class="col-md-12" v-if="connection.subtype === 1">
              <b-form-group>
                <b-form-radio-group id="radio-group-4" v-model="connection.minutes_type" name="minutes_type">
                  <b-form-radio :value="1">Minutos Sencillos</b-form-radio>
                  <b-form-radio :value="2">Minutos Dobles</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div class="row" v-if="connection.minutes_type">
                <div class="col-sm-12">
                  <b-form-group>
                    <b-row>
                      <b-col md="2" sm="12">
                        <b-form-group>
                          <b-form-input size="sm" v-model="conMinutes" placeholder="Total minutos" readonly></b-form-input>
                        </b-form-group>
                      </b-col>
                      <b-col md="4" sm="12">
                        <b-form-group>
                          <VueCtkDateTimePicker v-model="connection.from" dark onlyTime overlay noLabel noHeader label="Seleccione hora que empezo la pausa" inputSize="sm" format="HH:mm a" formatted="HH:mm a"/>
                        </b-form-group>
                      </b-col>
                      <b-col md="4" sm="12">
                        <b-form-group>
                          <VueCtkDateTimePicker v-model="connection.to" dark onlyTime overlay noLabel noHeader label="Seleccione hora que termino la pausa" inputSize="sm" format="HH:mm a" formatted="HH:mm a"/>
                        </b-form-group>
                      </b-col>
                      <b-col md="2" sm="12">
                        <b-button @click="storePause" v-if="isConnectionBtn" block variant="success" class="float-right" size="sm">Aceptar</b-button>
                      </b-col>
                    </b-row>
                  </b-form-group>
                </div>
              </div>
            </div>
            <div class="col-md-12" v-if="connection.subtype === 2">
              <b-form-group>
                <b-form-radio-group id="radio-group-4" v-model="connection.minutes_type" name="minutes_type">
                  <b-form-radio :value="1">Minutos Sencillos</b-form-radio>
                  <b-form-radio :value="2">Minutos Dobles</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div class="row" v-if="connection.minutes_type">
                <div class="col-sm-12">
                  <b-form-group>
                    <b-row>
                      <b-col md="2" sm="12">
                        <b-form-group>
                          <b-form-input size="sm" v-model="conMinutes" placeholder="Total minutos" readonly></b-form-input>
                        </b-form-group>
                      </b-col>
                      <b-col md="4" sm="12">
                        <b-form-group>
                          <VueCtkDateTimePicker
                              v-model="connection.from"
                              dark onlyTime overlay noLabel noHeader
                              label="Seleccione hora que se desconecto"
                              inputSize="sm"
                              format="HH:mm a"
                              formatted="HH:mm a"
                          />
                        </b-form-group>
                      </b-col>
                      <b-col md="4" sm="12">
                        <b-form-group>
                          <VueCtkDateTimePicker
                              v-model="connection.to"
                              dark onlyTime overlay noLabel noHeader
                              label="Seleccione hora de debio desconectarse"
                              inputSize="sm"
                              format="HH:mm a"
                              formatted="HH:mm a"
                          />
                        </b-form-group>
                      </b-col>
                      <b-col md="2" sm="12">
                        <b-form-group>
                          <b-button @click="storePause" v-if="isConnectionBtn" block variant="success" class="float-right" size="sm">Aceptar</b-button>
                        </b-form-group>
                      </b-col>
                    </b-row>
                  </b-form-group>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- connection -->
        <div class="col-md-12">
          <div v-if="connection.connectionOption" class="row justify-content-center">
            <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12">
              <hr> <h5>Conexion de la modelo</h5>
            </div>

            <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12">
              <b-form-group>
                <b-row>
                  <b-col>
                    <b-form-radio-group id="radio-group-10" v-model="connection.type" name="type">
                      <b-form-radio :value="1">Puntual</b-form-radio>
                      <b-form-radio :value="2">Retraso</b-form-radio>
                    </b-form-radio-group>
                  </b-col>
                  <b-col>
                    <b-button @click="storeConnection" v-if="connection.type === 1" variant="success" size="sm">Guardar conexión</b-button>
                  </b-col>
                </b-row>
              </b-form-group>
            </div>
            <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12 py-3" v-if="connection.type === 2">
              <b-form-group>
                <b-form-radio-group id="radio-group-11" v-model="connection.minutes_type" name="minutes">
                  <b-form-radio :value="1">Minutos Sencillos</b-form-radio>
                  <b-form-radio :value="2">Minutos Dobles</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div class="row pt-3" v-if="connection.minutes_type !== 0">
                <div class="col-sm-12">
                  <b-form-group>
                    <b-row>
                      <b-col sm="12" md="2">
                        <b-form-input size="sm" v-model="conMinutes" placeholder="Total minutos" readonly></b-form-input>
                      </b-col>
                      <b-col sm="12" md="4">
                        <VueCtkDateTimePicker v-model="connection.from" dark onlyTime overlay noLabel noHeader label="Conexion debio ser a..." inputSize="sm" format="HH:mm a" formatted="HH:mm a"/>
                      </b-col>
                      <b-col sm="12" md="4">
                        <VueCtkDateTimePicker v-model="connection.to" dark onlyTime overlay noLabel noHeader label="Hora en que se conecto la modelo..." inputSize="sm" format="HH:mm a" formatted="HH:mm a" id="connectionTime"/>
                      </b-col>
                      <b-col sm="12" md="2">
                        <b-button :disabled="isLoading" @click="storeConnection" v-if="isConnectionBtn" block variant="success" size="sm">
                          Guardar conexión <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
                        </b-button>
                      </b-col>
                    </b-row>
                  </b-form-group>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- makeup -->
        <div class="col-md-12">
            <div class="row justify-content-center" v-if="showMakeup">
              <div class="col-md-12 col-sm-12 col-lg-12 col-xl-12">
                <hr> <h5>Recuperar tiempo</h5>
              </div>

              <div class="col-md-12">
                <b-form>
                  <b-form-group>
                    <b-form-radio-group id="radio-group-recover" v-model="makeup.type" name="type">
                      <b-form-radio :value="11" v-if="dontShowOption">Trabaja Dia Libre</b-form-radio>
                      <b-form-radio :value="12" v-if="dontShowOption">Tiempo Extra</b-form-radio>
                      <b-form-radio :value="13" v-if="dontShowOption">Doble Turno</b-form-radio>
                      <b-form-radio :value="14">Error Cometido</b-form-radio>
                      <b-form-radio :value="15" v-if="isUnjustified">Quitar día injustificado</b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                  <div class="row pt-3" v-if="makeup.type === 11 || makeup.type === 12 || makeup.type === 13">
                    <div class="col-md-12">
                      <div class="row">
                        <div class="col-sm-2">
                          <b-form-group label="Minutos">
                            <b-form-input size="sm" v-model="makeupMinutes" placeholder="Total minutos" readonly></b-form-input>
                          </b-form-group>
                        </div>
                        <div class="col-md-5 col-sm-12 col-lg-5 col-xl-5">
                          <b-form-group label="Hora de inicio turno">
                            <VueCtkDateTimePicker v-model="makeup.from" dark onlyTime overlay noLabel noHeader label="Trabaja desde" inputSize="sm" format="HH:mm a" formatted="HH:mm a"/>
                          </b-form-group>
                        </div>
                        <div class="col-md-5 col-sm-12 col-lg-5 col-xl-5">
                          <b-form-group label="Hora de final turno">
                            <VueCtkDateTimePicker v-model="makeup.to" dark onlyTime overlay noLabel noHeader label="Trabaja hasta" inputSize="sm" format="HH:mm a" formatted="HH:mm a"/>
                          </b-form-group>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row pt-3" v-if="makeup.type === 14">
                    <div class="col-sm-12 col-md-12">
                      <b-form-group label="Minutos recuperados">
                        <b-form-input type="number" v-model="makeup.minutes" placeholder="Minutos recuperados" name="minutes"></b-form-input>
                      </b-form-group>
                    </div>
                    <div class="col-sm-12 col-md-12">
                      <b-form-group label="Minutos recuperados">
                        <b-form-textarea id="textarea" v-model="makeup.text" placeholder="Ejemplo: Se le anotaron por error el 12 de Marzo" rows="3" max-rows="6"></b-form-textarea>
                      </b-form-group>
                    </div>
                  </div>
                  <div class="row pt-3" v-if="makeup.type === 15">
                    <div class="col-md-12">
                      <b-form-group label="¿Qué tipo de asistencia desea recuperar?">
                        <b-form-radio-group id="radio-group-recover" v-model="makeup.subtype" name="subtype">
                          <b-form-radio :value="3" v-model="makeupwatch">Periodo</b-form-radio>
                          <b-form-radio :value="4" v-model="makeupwatch">Incapacidad (ENFERMA)</b-form-radio>
                          <b-form-radio :value="16" v-model="makeupwatch">Calamidad</b-form-radio>
                          <b-form-radio :value="17" v-model="makeupwatch">Trabaja en turno diferente</b-form-radio>
                          <b-form-radio :value="15" v-model="makeupwatch">Otro</b-form-radio>
                        </b-form-radio-group>
                      </b-form-group>
                    </div>
                    <div class="col-sm-12 col-md-12" v-if="makeup.subtype === 15 || makeup.subtype === 16">
                      <b-form-group label="Descripcion">
                        <b-form-textarea id="textarea" v-model="makeup.text" rows="3" max-rows="6"></b-form-textarea>
                      </b-form-group>
                    </div>

                    <div class="col-md-12" v-if="makeup.subtype === 3 || makeup.subtype === 4 || makeup.subtype === 15 || makeup.subtype === 16">
                      <b-form-group label="¿Escoger fecha?">
                        <b-form-select v-model="makeup.date" class="mb-3">
                          <b-form-select-option v-for="day in unjustified" :value="day.date" :key="day.id">
                            {{ day.date }}
                          </b-form-select-option>
                        </b-form-select>
                      </b-form-group>
                    </div>
                  </div>
                  <b-button v-if="isMakeUpBtn" :disabled="isLoading" variant="success" size="sm" class="float-right mt-3" @click.prevent="storeMakeup">
                    Recuperar minutos <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
                  </b-button>
                </b-form>
              </div>
            </div>
          </div>
      </div>
    </b-modal>

    <!-- General summary -->
    <b-modal @hidden="resetModal" ref="attendance-general" header-bg-variant="dark" centered size="lg" header-close-content="" hide-footer :title-html="'Resumen de asistencia ' + modalTitle">
      <div class="container">
        <div class="col-md-12">
          <div class="row">
            <div class="col-sm-12">
              <b-overlay :show="show" rounded="sm">
                <div class="card">
                  <div class="card-body">
                    <dl class="list-group list-group-flush">
                      <div class="list-group-item">
                        <div class="row px-4">
                          <dt class="col-sm-12 col-md-6">
                            Rango
                          </dt>
                          <dd class="col-sm-12 col-md-6">
                            {{ generalInfo.range }}
                          </dd>
                        </div>
                      </div>

                      <div class="list-group-item">
                        <div class="row px-4">
                          <dt class="col-sm-12 col-md-6">
                            Tiempo debido
                          </dt>
                          <dd class="col-sm-12 col-md-6">
                            <span class="text-danger font-weight-bold">{{ generalInfo.total_minutes }}</span> minutos debidos
                          </dd>
                        </div>
                      </div>

                      <div class="list-group-item">
                        <div class="row px-4">
                          <dt class="col-sm-12 col-md-6">
                            Tiempo recuperado
                          </dt>
                          <dd class="col-sm-12 col-md-6">
                            <span class="text-success font-weight-bold">{{ generalInfo.total_recovery_minutes }}</span> minutos recuperados
                          </dd>
                        </div>
                      </div>

                      <div class="list-group-item">
                        <div class="row px-4">
                          <dt class="col-sm-12 col-md-6">
                            meta semana
                          </dt>
                          <dd class="col-sm-12 col-md-6">
                            $<span class="font-weight-bold">{{ generalInfo.goal }}</span> dolares
                          </dd>
                        </div>
                      </div>

                      <div class="list-group-item">
                        <div class="row px-4">
                          <dt class="col-sm-12 col-md-6">
                            Descripcion
                          </dt>
                          <dd class="col-sm-12 col-md-6">
                            <span class="text-success font-weight-bold">{{ generalInfo.worked_days }}</span> dias trabajados <br>
                            <span class="text-warning font-weight-bold">{{ generalInfo.justified_days }}</span> dias justificados <br>
                            <span class="text-danger font-weight-bold">{{ generalInfo.unjustified_days }}</span> dias injustificados <br>
                            <span class="font-weight-bold">{{ generalInfo.period }}</span> periodos
                          </dd>
                        </div>
                      </div>

                      <div class="list-group-item">
                        <div class="row px-4">
                          <dt class="col-sm-12 col-md-6">
                            Last updated
                          </dt>
                          <dd class="col-sm-12 col-md-6">
                            {{ moment(generalInfo.updated_at).format('YYYY-MM-DD') + " a las " + moment(generalInfo.updated_at).format('HH:mm a') }}
                          </dd>
                        </div>
                      </div>
                    </dl>
                  </div>
                </div>
              </b-overlay>
            </div>
          </div>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import moment from "moment";
import {mapActions, mapGetters} from 'vuex';

export default {
  props: ['user', 'permissions'],
  name: "attendancetable",
  data: () => {
    return {
      attendance: {
        attendanceOptions: 0,
        model_id: 0,
        summary_id: 0,
        range: null,
        date: null,
        minutes: 0,
        type: 0,
        subtype: 0,
        text: "",
        minutes_type: 0,
        created_at: null,
        attendance_type: 0,
        comment_type: 0,
      },
      makeup: {
        attendanceOptions: 1,
        model_id: 0,
        range: null,
        date: null,
        minutes: 0,
        type: 0,
        subtype: 0,
        text: ""
      },
      attendanceInfo: {},
      connection: {
        connectionOption: false,
        from: null,
        minutes_type: 0,
        to: null,
        type: null,
        minutes: 0,
        model_id: 0
      },
      generalInfo: {},
      
      sessions: [],
      search: {
        selectedSession: 0,
        selectedLocation: 0,
        start: moment().isoWeekday(0).format("YYYY-MM-DD"),
        end: moment().isoWeekday(6).format("YYYY-MM-DD"),
      },
      items: [],
      fields: [],
      totalRows: 1,
      currentPage: 1,
      perPage: 25,
      pageOptions: [25, 50, 100],
      filter: null,
      filterOn: [],
      isBusy: false,
      modalTitle: null,
      moment: moment,
      clearOptions: 0,
      isUnjustified: false,
      dontShowOnDate: false,
      dontShowOption: false,
      showMakeup: false,
      visible: false,
      show: false,
      makeupwatch: false,
      unjustified: [],
      isLoading: false
    }
  },

  watch: {
    clearOptions: function () {
      if (this.clearOptions !== 5) {
        this.attendance.type = this.clearOptions
        this.attendance.subtype = this.clearOptions
        this.attendance.text = ""
      }
    },

    makeupwatch: function () {
      if (this.makeupwatch !== 15 || this.makeupwatch !== 16) {
        this.makeup.text = ""
      }
    },

    search: function () {
      const startOfWeek = moment(this.search.start).startOf('week').isoWeekday(0).format("YYYY-MM-DD")
      const endOfWeek = moment(this.search.start).startOf('isoweek').isoWeekday(6).format("YYYY-MM-DD")
      this.search.start = startOfWeek
      this.search.end = endOfWeek

      console.log("start of week: " + startOfWeek)
      console.log("end of week: " + endOfWeek)

      if (this.user.setting_location_id == 1) {
        this.search.selectedLocation = 2
        this.search.selectedSession = 1
      } else {
        this.search.selectedLocation = this.user.setting_location_id
        this.search.selectedSession = 1
      }
      this.getAttendance()
    },
  },

  methods: {
    ...mapActions(["GET_ALL_LOCATIONS"]),

    showModal(item, date, hasAttendance, type, day, id, status_id) {

      this.modalTitle = '<span class="text-danger">' + item.nick + '( ' + date + ' )</span>'
      const currentDate = moment().format('YYYY-MM-DD')
      let startDate = moment(currentDate, "YYYY-MM-DD")
      let result = startDate.diff(date, 'days')
      let range = this.search.start + " / " + this.search.end
      this.getUnjustifiedDates(range, item.model_id)
      
      if(hasAttendance) {
         this.$refs['attendance-detail'].show()
         this.attendanceInfo = item.days[day];
         this.isUnjustified = item.isUnjustified
         this.connection.attendance_id = item.days[day].attendance_id
         this.showMakeup = true;
         this.makeup.model_id = item.model_id
         this.makeup.date = item.days[day].date
         this.makeup.range = this.search.start + " / " + this.search.end

         // connection
         if (type === 1){
           if(item.days[day].comment_type.attendance_status_id !== 2){
             this.connection.connectionOption = true;
             this.connection.model_id = item.model_id
             this.connection.date = item.days[day].date
             this.connection.range = this.search.start + " / " + this.search.end
             this.connection.type = type
             if (result <= 1){
               this.dontShowOption = true
             }
           }else{
              // pause/disconnection
             this.connection.model_id = item.model_id
             this.connection.range = this.search.start + " / " + this.search.end
             this.connection.date = item.days[day].date
             this.connection.type = type

             console.log(result)
             
             if (result <= 0){
               this.dontShowOnDate = true
               this.dontShowOption = true
             }
           }
         }else{
           this.connection.connectionOption = false;
           if (result <= 1){
             this.dontShowOption = true
           }
         }
      }
      else{
         this.$refs['attendance-modal'].show()

         //data for attendance
         this.attendance.model_id = item.model_id
         this.attendance.summary_id = item.summary_id
         this.attendance.range = this.search.start + " / " + this.search.end
         this.attendance.date = item.days[day].date
         this.makeup.model_id = item.model_id
         this.makeup.date = item.days[day].date
         this.makeup.range = this.search.start + " / " + this.search.end
      }
    },

    detailsModal(item) {
      this.$refs['attendance-general'].show();
      this.modalTitle = '<span class="text-danger">' + item.nick + '</span>'
      this.show = true
      const url = route("monitoring.summary")
      axios.post(url, {
        model_id: item.model_id,
        range: item.range,
      }).then((response) => {
        this.generalInfo = response.data
        this.show = false
      })
    },

    resetModal() {
      this.attendance = {
        attendanceOptions: 0,
            model_id: 0,
            summary_id: 0,
            range: null,
            date: null,
            minutes: 0,
            type: 0,
            subtype: 0,
            text: "",
            minutes_type: 0,
            created_at: null,
            attendance_type: 0,
            comment_type: 0,
      },
      this.connection = {
        connectionOption: false,
        from: null,
        minutes_type: null,
        to: null,
        type: null,
        minutes: 0
      },
      this.makeup = {
        attendanceOptions: 1,
        model_id: 0,
        range: null,
        date: null,
        minutes: 0,
        type: 0,
        subtype: 0,
        text: ""
      },
      this.dontShowOnDate = false
      this.dontShowOption = false
      this.visible = false
    },

    onFiltered(filteredItems) {
      this.totalRows = filteredItems.length
      this.currentPage = 1
    },

    getSessions() {
      // let url = "monitoring/sessions";
      const url = route("monitoring.sessions")
      axios.get(url).then((response) => {
        this.sessions = response.data
      }).catch((error) => {
        console.log(error)
      })
    },

    getUnjustifiedDates(range, model_id) {
      const url = route("monitoring.searchUnjustifiedDate")
      axios.post(url, {
        range: range,
        model_id: model_id,
        type: 6,
      }).then((response) => {
        this.unjustified = response.data
      })
    },

    getAttendance() {
      const url = route("monitoring.allAttendances")
      this.isBusy = true
      axios.post(url, this.search).then((response) => {
        this.items = response.data.attendances
        this.fields = response.data.columns
        this.totalRows = this.items.length
        this.isBusy = false
      }).catch((error) => {
        this.isBusy = false
      })
    },

    store() {
      const url = route("monitoring.store_attendance")
      const vm = this
      this.isLoading =  true
      axios.post(url, this.attendance).then((response) => {
        if (response.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });

          vm.resetModal()
          Fire.$emit("addedAttendance")
          this.$refs['attendance-modal'].hide()
          this.isLoading =  false

        } else if (response.data.code === 404) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading =  false
        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading = false
        }
      }).catch((error) => {
        SwalGB.fire({
          icon: "error",
          text: "Ha ocurrido un error comuniquese con el admin",
          title: "¡Error!",
          showCancelButton: false,
        })
        this.isLoading = false
      })
    },

    storeMakeup() {
      const url = route("monitoring.store_attendance")
      const vm = this
      this.isLoading =  true

      axios.post(url, this.makeup).then((response) => {
        if (response.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });

          vm.resetModal()
          Fire.$emit("addedAttendance")
          this.$refs['attendance-detail'].hide()
          this.isLoading = false

        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading = false
        }  else if (response.data.code === 404) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading = false
        }
      }).catch((error) => {
        SwalGB.fire({
          icon: "error",
          text: "Ha ocurrido un error comuniquese con el admin",
          title: "¡Error!",
          showCancelButton: false,
        })
        this.isLoading = false
      })
    },

    storeConnection() {
      const id = this.connection.attendance_id
      const url = route("monitoring.saveConnection", {id})
      let vm = this
      this.isLoading =  true

      axios.post(url, this.connection).then((response) => {
        if (response.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });

          this.isLoading =  true
          vm.resetModal()
          Fire.$emit("addedAttendance")
          this.$refs['attendance-detail'].hide()
          
        } else if (response.data.code === 404) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading =  true
        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading =  true
        }
      }).catch((error) => {
        console.log(error)
        this.isLoading =  true
      })
    },

    storePause(){
      const url = route("monitoring.pauseDisconnection")
      this.isLoading =  true

      axios.post(url, this.connection).then((response) => {
        if (response.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });

          this.resetModal()
          Fire.$emit("addedAttendance")
          this.$refs['attendance-detail'].hide()
          this.isLoading =  true

        } else if (response.data.code === 404) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading =  true
        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
          this.isLoading =  true
        }
      }).catch((error) => {
        console.log(error)
        this.isLoading =  true
      })
    }
  },

  computed: {
    ...mapGetters(["getAllLocations"]),

    calcMinutes: {
      get: function () {
        if (this.attendance.from && this.attendance.to) {
          let startTime = moment(this.attendance.from, "HH:mm a")
          let endTime = moment(this.attendance.to, "HH:mm a")
          let minutes = startTime.diff(endTime, 'minutes');

          return this.attendance.minutes = minutes * this.attendance.minutes_type
        }

        return this.attendance.minutes = 0;
      },
      set: function (minutes) {
        this.attendance.minutes = minutes
      }
    },

    conMinutes: {
      get: function () {
        if (this.connection.from && this.connection.to) {
          let startTime = moment(this.connection.from, "HH:mm a")
          let endTime = moment(this.connection.to, "HH:mm a")
          let minutes = endTime.diff(startTime, 'minutes');

          console.log(minutes)

          return this.connection.minutes = minutes * this.connection.minutes_type
        }

        return this.connection.minutes = 0;
      },
      set: function (minutes) {
        this.connection.minutes = minutes
      }
    },

    makeupMinutes: {
      get: function () {
        if (this.makeup.from && this.makeup.to) {
          let startTime = moment(this.makeup.from, "HH:mm a")
          let endTime = moment(this.makeup.to, "HH:mm a")
          let minutes = endTime.diff(startTime, 'minutes');

          return this.makeup.minutes = minutes
        }

        return this.makeup.minutes = 0;
      },
      set: function (minutes) {
        this.makeup.minutes = minutes
      }
    },
    
    isConnectionBtn(){
      if (this.connection.type === 1){
        return true
      }else{
        return this.connection.from && this.connection.to
      }
    },

    isAttendanceBtn(){
      if (this.attendance.type === 5 && this.attendance.subtype === 4){
        return true
      }
      else if(this.attendance.type === 5 && this.attendance.subtype === 5 && this.attendance.text !== ""){
        return true
      }
      else if(this.attendance.type === 1){
        if (this.attendance.subtype === 1 ){
          return true
        }
        else if (this.attendance.subtype === 2){
          if (this.attendance.from && this.attendance.to){
            return true
          }
        }
      }
      else if(this.attendance.type === 6 || this.attendance.type === 3){
        return true
      }
      else{
        return false
      }
    },

    isMakeUpBtn(){
      if (this.makeup.type === 14 && this.makeup.text !== ""){
        return true
      }
      else if(this.makeup.type === 11 || this.makeup.type === 12 || this.makeup.type === 13){
        if (this.makeup.from && this.makeup.to){
          return true
        }
      }
      else {
        if (this.makeup.type === 15){
          if ((this.makeup.subtype === 3 || this.makeup.subtype === 4) && this.makeup.date !== null){
            return true
          }
          else if (this.makeup.subtype === 16 && this.makeup.date !== null && this.makeup.text !== ""){
            return true
          }
          else if (this.makeup.subtype === 15  && this.makeup.date !== null && this.makeup.text !== ""){
            return true
          }
          else if(this.makeup.subtype === 17){
            return true
          }
        }
      }
    },
  },

  mounted() {
    this.getSessions()
    this.GET_ALL_LOCATIONS()
    if (this.user.setting_location_id == 1) {
      this.search.selectedLocation = 2
      this.search.selectedSession = 1
    }
    else {
      this.search.selectedLocation = this.user.setting_location_id
      this.search.selectedSession = 1
    }

    this.getAttendance()
    Fire.$on("addedAttendance", () => {
      this.getAttendance()
    })
  }
}
</script>

<style scoped>
</style>