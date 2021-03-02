<template>
  <div class="bookings">
    <b-overlay :show="show" no-wrap></b-overlay>

    <div class="row" v-show="!show">
      <div class="col-md-12">
        <div class="card rounded-0 border-info shadow">
          <div class="card-header border-bottom-0">
            <span>
              <span class="h4"><i class="text-info fas fa-paint-brush"></i> Reserva Maquillaje </span>

              <span v-if="can('bookingsschedules-create')">
                <b-button variant="success" size="sm" class="float-right mx-3" @click="scheduleModal">
                  <i class="fas fa-plus"></i> Crear Horario
                </b-button>
              </span>

              <span v-if="can('bookingsschedules-view')">
                 <timetable :bookingid="bookingid"></timetable>
                 <b-button variant="dark" size="sm" class="float-right" @click="asignDayAndHeadquarters">
                  <i class="fas fa-calendar-check"></i> <span class="mx-1">Sede-Día</span>
                 </b-button>
              </span>
            </span>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <FullCalendar :options="calendarOptions"/>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <b-modal centered v-model="openModal" ref="my-modal" header-bg-variant="dark" header-close-content="" title="Hacer una reserva" @hidden="resetModal">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <b-form>
              <b-form-group>
                <strong>Fecha: </strong> {{ booking.startDate }}
              </b-form-group>

              <b-form-group label="¿Que deseas hacer?">
                <b-form-radio-group id="radio-group-2" v-model="makeAppointment">
                  <b-form-radio :value="1">Agendar cita</b-form-radio>
                  <b-form-radio :value="2">Bloquear horario</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div v-if="makeAppointment === 1">
                <b-form-group label="Escoger un horario para agendar" v-if="schedules.length > 0">
                  <b-form-radio-group id="radio-group-2" v-model="booking.time">
                    <b-form-radio v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">
                      {{ schedule.hour + ":" + schedule.minutes + "" + schedule.meridiem }}
                    </b-form-radio>

                    <!--<b-form-radio v-model="disableDate.schedule" :value="true">
                      Bloquear el día
                    </b-form-radio>-->
                  </b-form-radio-group>
                </b-form-group>

                <div v-else class="py-2">
                  <p>
                    No hay horarios creados aun. Por favor, crear uno para proceder
                  </p>
                </div>

                <b-form-group label="Modelo: ">
                  <b-form-select v-model="booking.models" class="mb-3" multiple :select-size="8">
                    <b-form-select-option v-for="model in getModels" :key="model.id" :value="{id: model.id, nick: model.nick}">
                      {{ model.nick }}
                    </b-form-select-option>
                  </b-form-select>
                </b-form-group>

                <b-form-group v-if="booking.models.length >= 1">
                  <div class="container">
                    <div class="row">
                      <div class="col-md-12">
                        Reserva para modelo(s):
                        <ul class="list-unstyled list-inline">
                          <li class="list-inline-item font-weight-bold text-warning" v-for="(model, i) in booking.models" :key="i" v-text="model.nick"></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </b-form-group>
              </div>

              <div v-else-if="makeAppointment === 2">
                <b-form-group label="Escoger un horario para bloquear" v-if="schedules.length > 0">
                  <b-form-radio-group id="radio-group-2" v-model="booking.time">
                    <b-form-radio v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">
                      {{ schedule.hour }}:{{ schedule.minutes }} {{ schedule.meridiem }}
                    </b-form-radio>

                    <b-form-radio v-model="disableDate.schedule" :value="true">
                      Bloquear el día
                    </b-form-radio>
                  </b-form-radio-group>
                </b-form-group>
              </div>
            </b-form>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <b-button v-if="booking.time != null && disableDate.schedule != null && booking.models.length <= 0" type="button" class="float-left mx-3" size="sm" variant="danger" @click.stop.prevent="banAppointment">
          <i class="fas fa-ban"></i> Deshabilitar
        </b-button>

        <b-button v-if="booking.models.length >= 1 && schedules.length > 0 && booking.time != null" type="button" class="float-right" size="sm" variant="success" @click.stop.prevent="addAppointment">
          <i class="fas fa-save"></i> Reservar
        </b-button>
      </template>
    </b-modal>

    <b-modal centered id="schedule-modal" ref="schedule" hide-footer header-bg-variant="dark" header-close-content="" title="Crear Horario">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <b-form>
              <div class="row">
                <div class="col-md-10">
                  <div class="row">
                    <div class="col-2">
                      <label class="mr-sm-2">Horario: </label>
                    </div>
                    <div class="col-6">
                      <vue-timepicker format="hh:mm A" v-model="form" fixed-dropdown-button></vue-timepicker>
                    </div>

                    <div class="col-4">
                      <div v-if="editMode">
                        <b-button v-if="!isCreating" type="button" class="float-right" size="sm" variant="success" @click="saveSchedule(sch_id)">
                          <i class="fas fa-save"></i> Editar
                        </b-button>

                        <b-button v-else type="button" class="float-right" size="sm" variant="success" :disabled="isCreating">
                          <i class="fas fa-spinner fa-spin"></i> Editando...
                        </b-button>
                      </div>

                      <div v-else>
                        <b-button v-if="!isCreating" type="button" class="float-right" size="sm" variant="success" @click="createSchedule">
                          <i class="fas fa-save"></i> Crear
                        </b-button>

                        <b-button v-else type="button" class="float-right" size="sm" variant="success" :disabled="isCreating">
                          <i class="fas fa-spinner fa-spin"></i> Creando...
                        </b-button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-12 my-4">
                  <hr>

                  <table class="table table-striped">
                    <thead>
                    <tr>
                      <th scope="col">Horario</th>
                      <th scope="col">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="schedule in schedules" :key="schedule.id" :id="'update_'+schedule.id">
                      <td>
                        <strong class="h5">{{ schedule.hour + ":"+ schedule.minutes + " " + schedule.meridiem }}</strong>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-warning" @click.prevent="editSchedule(schedule.id)">
                          <i class="fas fa-edit"></i>
                        </button>
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </b-form>
          </div>
        </div>
      </div>
    </b-modal>

    <b-modal centered id="detail-modal" size="lg" ref="detail" header-bg-variant="dark" header-close-content="" title="Detalles de la agenda" @hidden="resetModal">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="row p-3">
              <div class="col-md-12 col-lg-12">
                <span>
                  <strong> Horario: </strong> {{ time }}
                </span>
                <span class="mx-4">
                  <strong>Fecha: </strong> {{ booking.startDate }}
                </span>
              </div>

              <div class="col-md-6 col-md-12">
                <hr>
                <table class="table">
                  <thead>
                  <tr>
                    <th scope="col">Modelo</th>
                    <th scope="col">Asistencia</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td>
                      <span class="text-warning font-weight-bold">{{ model }}</span>
                    </td>
                    <td>
                      <div class="form-check">
                        <b-form-radio  :disabled="!isAttending || booking.status == 1 || booking.status == 2" v-model="bookingProccess.status" :value="1">Asistió</b-form-radio>

<!--                        <input :disabled="!isAttending || booking.status == 1 || booking.status == 2" class="form-check-input" type="radio" id="status_attended" :value="1" v-model="bookingProccess.status">
                        <label class="form-check-label" for="status_attended">Asistió</label>-->
                      </div>
                    </td>
                    <td>
                      <div class="form-check">
                        <b-form-radio  :disabled="!isAttending || booking.status == 1 || booking.status == 2" v-model="bookingProccess.status" :value="2">No asistió</b-form-radio>

                        <!--                        <input :disabled="!isAttending || booking.status == 1 || booking.status == 2" class="form-check-input" type="radio" id="status_not_attended" :value="2" v-model="bookingProccess.status">
                                                <label class="form-check-label" for="status_not_attended">No asistió</label>-->
                      </div>
                    </td>
                  </tr>
                  </tbody>
                </table>

                <hr>

                <div v-if="bookingProccess.status == 2 || booking.status == 2" class="text-center pt-2">
                  <strong class="text-danger">
                    {{ booking.description }}
                  </strong>
                </div>

                <div v-else-if="booking.status == 1 || booking.status == 4" class="text-center pt-2">
                  <strong class="text-success">
                    {{ booking.description }}
                  </strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <b-button type="button" v-if="can('makeup-delete') && booking.status == 0" class="float-right mx-2" size="sm" variant="danger" @click.prevent="deleteBooking(booking.id)">
            <i class="fas fa-trash"></i> Delete
          </b-button>
          <b-button v-if="can('makeup-edit') && booking.status == 0 && addAnother == true" type="button" class="float-right mx-2" size="sm" variant="primary" @click="addMoreModels">
            <i class="fas fa-plus"></i> Añadir mas modelos
          </b-button>

          <b-button v-if="can('makeup-edit') && booking.status == 0 && reschedule == true" type="button" class="float-right mx-2" size="sm" variant="dark" @click="rescheduleModal">
            <i class="fab fa-algolia"></i> Reprogramar
          </b-button>

          <b-button v-if="can('makeup-edit') && booking.status == 0 && bookingProccess.status != 2 && booking.startDate == moment().format('YYYY-MM-DD')" type="button" class="float-right" size="sm" variant="success"
                    @click.prevent="updateAppointment(booking.id)">
            <i class="fas fa-check"></i> Confirmar asistencia
          </b-button>

          <b-button v-if="can('makeup-edit') && bookingProccess.status == 2 && booking.status == 0" type="button" class="float-right" size="sm" variant="warning" @click.prevent="updateAppointment(booking.id)">
            <i class="fas fa-check"></i> Confirmar inasistencia
          </b-button>
        </div>
      </template>
    </b-modal>

    <b-modal centered id="placement-modal" size="lg" ref="placement" hide-footer header-bg-variant="dark" header-close-content="" title="Exonerar usuarios" @hidden="resetModal">
      <div class="container">
        <div class="row">
          <div class="col-md-12 col-lg-12 col-sm-12">
            <b-form>
              <div class="row">
                <div class="col-md-12 col-lg-12 col-sm-12 py-3">
                  <b-form-group label="Seleccione la(s) modelo(s) a exonerar">
                    <b-form-select v-model="userExonerated.user_id" class="mb-3" multiple :select-size="8">
                      <b-form-select-option v-for="model in getModels" :key="model.id" :value="{id: model.id, nick: model.nick}">
                        {{ model.nick }}
                      </b-form-select-option>
                    </b-form-select>
                  </b-form-group>

                  <b-form-group v-if="booking.models.length >= 1">
                    <div class="container">
                      <div class="row">
                        <div class="col-md-12">
                          Exonerando modelo(s):
                          <ul class="list-unstyled list-inline">
                            <li class="list-inline-item font-weight-bold text-warning" v-for="(model, i) in booking.models" :key="i" v-text="model.nick"></li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </b-form-group>

                  <div class="w-100">
                    <b-button v-if="!isCreating" type="button" class="float-right" variant="success" @click.prevent="exonerate()">
                      <i class="fas fa-save"></i> Exonerando
                    </b-button>

                    <b-button v-else type="button" class="float-right" variant="success" :disabled="isCreating">
                      <i class="fas fa-spinner fa-spin"></i> Exonerando...
                    </b-button>
                  </div>
                </div>
              </div>
            </b-form>
          </div>

          <div class="col-md-12 col-lg-12 col-sm-12" v-if="exonerated.length > 0">
            <hr>
            <table class="table table-striped">
              <thead>
              <tr>
                <th scope="col">Usuario exonerado</th>
                <th scope="col">Accion</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="user in exonerated" :key="user.id">
                <td v-text="user.nick"></td>
                <td>
                  <b-button size="sm" variant="danger" @click.prevent="removeExonerate(user.id)">
                    <i class="fas fa-trash"></i>
                  </b-button>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </b-modal>

    <b-modal centered id="dayhq-modal" ref="dayhq" header-bg-variant="dark" header-close-content="" title="Asignar Sede-Día" @hidden="resetModal">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <b-form>
              <div class="row">
                <div v-if="!editMode" class="col-md-12 col-lg-12 col-sm-12 pt-3">
                  <b-form-group>
                    <div class="container">
                      <div class="row" v-for="(day, i) in getDays" :key="day.id">
                        <div class="col-md-6">
                          <ul class="list-unstyled">
                            <li>
                              <h5>{{ day.day_name }}</h5>
                            </li>
                          </ul>
                        </div>
                        <div class="col-md-6">
                          <b-form-select v-model="days[i].location" class="mb-3">
                            <b-form-select-option v-for="location in getLocations" :key="location.id" :value="{id:location.id, name:location.name}">
                              {{ location.name }}
                            </b-form-select-option>
                          </b-form-select>
                        </div>
                      </div>
                    </div>
                  </b-form-group>
                </div>

                <!--edit-->
                <div class="col-md-12 col-lg-12 col-sm-12 pt-3" v-else>
                  <b-form-group>
                    <div class="container">
                      <div class="row" v-for="(day, i) in getDays" :key="day.id">
                        <div class="col-md-6">
                          <ul class="list-unstyled">
                            <li>
                              <h5>{{ day.day_name }}</h5>
                            </li>
                          </ul>
                        </div>
                        <div class="col-md-6">
                          <b-form-select v-model="days[i].setting_location_id" class="mb-3">
                            <b-form-select-option v-for="location in getLocations" :key="location.id" :value="location.id" :selected="location.id === days[i].setting_location_id">
                              {{ location.name }}
                            </b-form-select-option>
                          </b-form-select>
                        </div>
                      </div>
                    </div>
                  </b-form-group>
                </div>
              </div>
            </b-form>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <b-button v-if="!isCreating" type="button" size="sm" class="float-right" variant="success" @click="editMode ? updateAllocateDays() : allocateDays()">
            <i class="fas fa-save"></i> Guardar
          </b-button>

          <b-button v-else type="button" class="float-right" size="sm" variant="success" :disabled="isCreating">
            <i class="fas fa-spinner fa-spin"></i> Guardando...
          </b-button>
        </div>
      </template>
    </b-modal>

    <b-modal centered id="reschedule-modal" ref="reschedule" header-bg-variant="dark" header-close-content="" title="Reprogramar Horario">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <b-form>
              <div class="row">
                <div class="col-md-12">
                  <span>Horario actual:
                    <strong class="text-info" v-for="schedule in schedules" :key="schedule.id" :value="schedule.id" v-if="schedule.id == booking.time">
                        {{ schedule.hour + ":" + schedule.minutes + "" + schedule.meridiem }}
                    </strong>
                  </span>
                  <span class="mx-4">Fecha actual: <strong class="text-info">{{ booking.startDate }}</strong></span>
                </div>

                <div class="col-md-12 pt-4">
                  <b-form-group label="Escoger un horario para agendar" v-if="schedules.length > 0">
                    <b-form-radio-group id="radio-group-2" v-model="booking.time">
                      <b-form-radio v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">
                        {{ schedule.hour }}:{{ schedule.minutes }} {{ schedule.meridiem }}
                      </b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                </div>

                <div class="col-md-12 pt-2">
                  <label>Escoger nueva fecha</label>
                  <input v-model="booking.startDate" class="form-control" id="date-input" type="date" name="date-input" placeholder="date">
                </div>
              </div>
            </b-form>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <b-button v-if="!isCreating" type="button" class="float-right" size="sm" variant="success" @click="rescheduleAppointment(booking.id)">
            <i class="fas fa-save"></i> Guardar
          </b-button>

          <b-button v-else type="button" class="float-right" sm="sm" variant="success" :disabled="isCreating">
            <i class="fas fa-spinner fa-spin"></i> Guardando...
          </b-button>
        </div>
      </template>
    </b-modal>

    <b-modal centered id="addmore-modal" ref="addMore" header-bg-variant="dark" header-close-content="" title="Añadir más modelos">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <b-form>
              <div class="row">
                <div class="col-md-12">
                  <p>
                    <span class="mr-5"><strong>Fecha: </strong> <span class="text-success">{{ addBooking.startDate }}</span></span>
                    <span><strong>Horario: </strong> <span class="text-success">{{ addBooking.time }}</span></span>
                  </p>
                </div>

                <div class="col-md-12 py-3">
                  <label>Escoja modelos: </label>
                  <b-form-select v-model="addBooking.models" class="mb-3" multiple :select-size="8">
                    <b-form-select-option v-for="model in getModels" :key="model.id" :value="{id: model.id, nick: model.nick}">
                      {{ model.nick }}
                    </b-form-select-option>
                  </b-form-select>
                </div>
              </div>
            </b-form>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <b-button v-if="!isCreating" type="button" class="float-right" size="sm" variant="success" @click="addModels">
            <i class="fas fa-save"></i> Guardar
          </b-button>

          <b-button v-else type="button" class="float-right" size="sm" variant="success" :disabled="isCreating">
            <i class="fas fa-spinner fa-spin"></i> Guardando...
          </b-button>
        </div>
      </template>
    </b-modal>
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import bootstrapPlugin from '@fullcalendar/bootstrap'
import {mapGetters} from 'vuex'
import VueTimepicker from 'vue2-timepicker'
import Tooltip from "bootstrap/js/src/tooltip";

export default {
  name: "makeup",
  props: ['user', 'bookingid', 'permissions'],

  components: {
    FullCalendar,
    VueTimepicker
  },

  data() {
    return {
      calendarOptions: {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin, bootstrapPlugin],
        initialView: 'dayGridMonth',
        select: this.handleDateSelect,
        eventClick: this.handleEventClick,
        themeSystem: 'bootstrap',
        locale: "es",
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        height: 750,
        headerToolbar: {
          right: 'prev,today,next',
          left: 'dayGridMonth timeGridWeek',
          center: 'title'
        },
        buttonText: {
          today: 'Hoy',
          month: 'Mes',
          week: 'Semana'
        },
        events: [],
        eventDidMount: function (info) {
          new Tooltip(info.el, {
            title: info.event.extendedProps.msg,
            placement: 'top',
            trigger: 'hover',
            container: 'body',
            html: true
          });
        },
        validRange: function() {
          return {
            // start: moment().startOf('month').format('YYYY-MM-DD'),
            end: moment().add(1, 'months').endOf('month').add(1, 'days').format('YYYY-MM-DD')
          };
        },
        hiddenDays: [ 0 ]
      },

      booking: {
        startDate: null,
        time: null,
        models: [],
        booking_type_id: null,
        status: 0
      },

      form: {
        hh: '01',
        mm: '00',
        A: 'AM',
        booking_type_id: 3
      },

      bookingProccess: {
        status: 1,
        date: null,
        model: null,
      },

      userExonerated:{
        type: null,
        user_id: []
      },

      days: [],

      disableDate: {
        schedule: false,
        startDate: null,
        booking_type_id: null,
        time: []
      },

      addBooking: {
        models: [],
        time: null,
        startDate: null,
        booking_type_id: null
      },
      
      model_role_id: 14,
      schedules: [],
      bookingTypes: [],
      exonerated: [],
      sundays: [],
      daysBySeed: [],
      isCreating: false,
      isAttending: false,
      show: false,
      selected: false,
      makeAppointment: false,
      isReadOnly: true,
      editMode: false,
      openModal: false,
      moment: moment,
      reschedule: true,
      addAnother: true,
      time: null,
      model: null,
      sch_id: null
    }
  },

  methods: {
    can(permission_name) {
      return this.permissions.indexOf(permission_name) !== -1;
    },
    
    resetModal() {
      this.booking.models = []
      this.booking.startDate = null
      this.booking.time = null
      this.isAttending = false
      this.bookingProccess.status = 1
      this.userExonerated.user_id = []
      this.userExonerated.type = this.bookingid
      this.makeAppointment = 0
      this.openModal = false
    },

    handleDateSelect(selectInfo) {
      let date = new Date()
      let currentDate = moment(selectInfo.start).format('YYYY-MM-DD')
      let selectedDate = moment(date).format('YYYY-MM-DD')
      this.disableDate.startDate = currentDate

      if(currentDate < selectedDate){
        this.openModal = false
        SwalGB.fire({
          icon: 'warning',
          text: "No puede reservar en esta fecha!",
          title: "¡Advertencia!",
          showCancelButton: false,
        })
      }else{
        this.openModal = true
        this.booking.startDate = moment(selectInfo.start).format("DD-MM-YYYY")
        this.$store.commit("ADD_EVENT", this.booking)
        this.$store.dispatch("GET_MODELS", this.model_role_id)
        this.getSchedule()
      }

      this.sundays.forEach((value, index) => {
        if (selectInfo.startStr === value){
          this.openModal = false
          SwalGB.fire({
            icon: 'warning',
            text: "No puede reservar en dias Domingos!",
            title: "¡Advertencia!",
            showCancelButton: false,
          })
        }
      });
    },

    handleEventClick: function (clickInfo) {
      this.$refs['detail'].show()

      let event = clickInfo.event._def.extendedProps;

      let date = new Date()
      let currentDate = moment(date).format('YYYY-MM-DD')
      let eventDate = moment(event.start_date).format('YYYY-MM-DD')

      this.booking.id = event.agenda_id;
      this.booking.models = event.model_nick;
      this.model = event.model_nick;
      this.booking.time = event.time_id;
      this.time = event.time;
      this.booking.description = event.msg;
      this.booking.status = event.status;
      this.booking.startDate = event.start_date;
      this.bookingProccess.date = event.start_date;
      this.bookingProccess.model = event.model_id;
      this.bookingProccess.status = parseInt(event.status);


      if (currentDate === eventDate) {
        this.isAttending = true
        this.reschedule = false
      }

      if (event.start_date < currentDate  && event.status == 0){
        this.booking.description = "La cita no se actualizo"
      }
      
      this.reschedule = (eventDate > currentDate);
      this.addAnother = eventDate >= currentDate;
    },

    scheduleModal() {
      this.$refs['schedule'].show()
      this.getSchedule()
    },

    editSchedule(id){
      this.sch_id = id
      this.editMode = true
      // let url = "../../bookings/editSchedule/" + id
      const url = route("bookings.editSchedule", {id})

      axios.get(url)
          .then((response) => {
            this.form.hh = response.data.hour
            this.form.mm = response.data.minutes
            this.form.A = response.data.meridiem
          })
          .catch((error) => {
            console.log(error)
          })
    },

    saveSchedule(id){
      // let url = "../../bookings/updateSchedule/" + id
      const url = route("bookings.updateSchedule", {id})
      this.isCreating = true

      axios.put(url, this.form)
          .then((response) => {
            this.editMode = false
            this.isCreating = false
            if (response.data.code === 403){
              Toast.fire({
                icon: response.data.icon,
                title: response.data.msg,
              })
            }else{
              Toast.fire({
                icon: response.data.icon,
                title: "¡" + response.data.msg + "!"
              })
            }
            Fire.$emit("scheduleUpdated")
          })
          .catch((error) => {
            console.log(error)
            this.isCreating = false
          })
    },

    createSchedule() {
      // let url = "../../bookings/createSchedule"
      const url = route("bookings.createSchedule")
      this.isCreating = true
      this.form.booking_type_id = this.bookingid

      axios.post(url, this.form)
          .then((response) => {
            this.isCreating = false
            if (response.data.code == 403){
              Toast.fire({
                icon: response.data.icon,
                title: response.data.msg,
              })
            }else{
              Toast.fire({
                icon: response.data.icon,
                title: "¡" + response.data.msg + "!"
              })
            }
          }).catch((error) => {
        console.log(error)
        this.isCreating = false

      })
    },

    getSchedule() {
      // let url = "../../bookings/getSchedule/" + this.bookingid
      const id = this.bookingid
      const url = route("bookings.getScheduleById", {id})
      axios.get(url)
          .then((response) => {
            this.schedules = response.data.schedules;
          }).catch((error) => {
        console.log(error)
      })
    },

    getBookingType() {
      // let url = "../../bookings/getBookingTypes"
      const url = route("bookings.getBookingTypes")
      axios.get(url)
          .then((response) => {
            this.bookingTypes = response.data.bookingTypes;
          }).catch((error) => {
        console.log(error)
      })
    },

    addAppointment() {
      // let url = "../../bookings/store"
      const url = route("bookings.store")
      this.booking.booking_type_id = this.bookingid

      axios.post(url, this.booking)
          .then((response) => {
            Fire.$emit("addedAgenda")
            this.$refs['my-modal'].hide()
            if (response.data.code === 403){
              SwalGB.fire({
                icon: response.data.icon,
                text: response.data.msg,
                title: "¡Advertencia!",
                showCancelButton: false,
              })
            }else{
              Toast.fire({
                icon: response.data.icon,
                title: "¡" + response.data.msg + "!"
              })
            }
          }).catch((error) => {
        Toast.fire({
          icon: "error",
          title: "Por favor, complete los campos requeridos",
        })
      })
    },

    banAppointment() {
      // let url = "../../bookings/block"
      const url = route("bookings.blockDate")

      if (this.disableDate.schedule === true) {
        this.disableDate.time = this.schedules
      } else {
        let time = []
        time.push({
          id: this.booking.time
        })
        this.disableDate.time = time
      }

      this.disableDate.booking_type_id = this.bookingid

      axios.post(url, this.disableDate)
          .then((response) => {
            Fire.$emit("addedAgenda")
            this.$refs['my-modal'].hide()

            if (response.data.code === 403) {
              SwalGB.fire({
                icon: response.data.icon,
                title: 'Oops...',
                text: response.data.msg,
                showCancelButton: false
              })
            } else {
              Toast.fire({
                icon: response.data.icon,
                title: "¡" + response.data.msg + "!"
              })
            }
          }).then((error) => {

        console.log(error)
        Toast.fire({
          icon: "error",
          title: "Por favor, complete los campos requeridos",
        })
      })
    },

    GetAgenda() {
      this.show = true
      // let url = "../../bookings/agenda/" + this.bookingid
      const id = this.bookingid
      const url = route("bookings.agenda", {id})
      axios.get(url)
          .then((response) => {
            this.show = false
            this.calendarOptions.events = response.data.bookings;
            this.sundays = response.data.sundays;
          }).catch((error) => {
        this.show = false
        console.log(error)
      })
    },

    placement(){
      this.$refs['placement'].show()
      this.$store.dispatch("GET_MODELS", this.model_role_id)
      this.getExoneratedUsers()
    },

    exonerate(){
      // let  url = "../../bookings/exonerate"
      const url = route("bookings.exonerate")
      this.isCreating = true
      axios.post(url, this.userExonerated).then((response) => {
        if (response.data.code === 403) {
          this.isCreating = false
          SwalGB.fire({
            icon: response.data.icon,
            title: 'Oops...',
            text: response.data.msg,
            showCancelButton: false
          })
        } else {
          this.isCreating = false
          this.getExoneratedUsers()
          Toast.fire({
            icon: response.data.icon,
            title: "¡" + response.data.msg + "!"
          })
        }

      }).catch((error) => {
        console.log(error)
        this.isCreating = false
      })
    },

    getExoneratedUsers() {
      // let url = "../../bookings/getExonerated/" + this.bookingid
      const id = this.bookingid
      const url = route("bookings.getExonerated", {id})
      axios.get(url)
          .then((response) => {
            this.exonerated = response.data.data;
          }).catch((error) => {
        console.log(error)
      })
    },

    removeExonerate(id){
      // let url = "../../bookings/deleteExonerate/" + id
      const url = route("bookings.deleteExonerate", {id})
      axios.delete(url).then((response) => {
        this.getExoneratedUsers()
        Toast.fire({
          icon: "success",
          title: "¡Usuario removido exitosamente!",
        })
      }).catch((error) => {
        console.log(error)
      })
    },

    deleteBooking(id){
      // let url = "../../bookings/deleteBooking/" + id
      const url = route("bookings.deleteBooking", {id})
      axios.delete(url).then((response) => {
        this.$refs['detail'].hide()
        Fire.$emit("addedAgenda")
        Toast.fire({
          icon: "success",
          title: "¡Removido exitosamente!",
        })
      }).catch((error) => {
        console.log(error)
      })
    },

    updateAppointment(id) {
      // let url = "../../bookings/update/" + id
      const url = route("bookings.update", {id})
      this.isCreating = true
      axios.put(url, this.bookingProccess)
          .then((response) => {
            this.isCreating = false
            Fire.$emit("addedAgenda")
            this.$refs['detail'].hide()
            Toast.fire({
              icon: "success",
              title: "Reserva actualizada correctamente!",
            })
          }).catch((error) => {
        this.isCreating = false
        Toast.fire({
          icon: "error",
          title: "Por favor, complete los campos requeridos",
        })
      })
    },

    asignDayAndHeadquarters(){
      this.selected = true
      this.isReadOnly = true
      this.$refs['dayhq'].show()
      this.$store.dispatch("GET_DAYS")
      this.$store.dispatch("GET_LOCATIONS")
      this.seedDays()
    },

    allocateDays(){
      // let url = "../../bookings/allocateDays"
      const url = route("bookings.allocateDays")
      this.isCreating = true

      axios.post(url, {
        'days' : this.days,
        'booking_type_id': this.bookingid
      }).then((response) => {
        if (response.data.code === 200) {
          this.isCreating = false
          Toast.fire({
            icon: response.data.icon,
            title: "¡" + response.data.msg + "!"
          })
        }
      }).catch((error) => {
        console.log(error)
      })
    },

    updateAllocateDays(){
      // let url = "../../bookings/updateAllocateDays/" + this.bookingid
      const id = this.bookingid
      const url = route("bookings.updateAllocateDays", {id})
      this.isCreating = true

      axios.post(url, {
        'days' : this.days
      }).then((response) => {
        if (response.data.code === 200) {
          this.isCreating = false
          Toast.fire({
            icon: response.data.icon,
            title: "¡" + response.data.msg + "!"
          })
        }
      }).catch((error) => {
        this.isCreating = false
        console.log(error)
      })
    },

    seedDays(){
      // let url = "../../bookings/seedDays/" + this.bookingid
      const id = this.bookingid
      const url = route("bookings.seedDays", {id})
      axios.get(url).then((response) => {
        let res = response.data.days

        if (res.length > 0){
          this.days = response.data.days
          this.editMode = true
        }else{
          this.editMode = false
          for (let i = 1; i <= 6; i++){
            this.days.push({
              location: '',
              day: { id: i },
            })
          }
        }
      }).catch((error) => {
        console.log(error)
      })
    },
    
    addMoreModels() {
      this.$refs['addMore'].show()
      this.$store.dispatch("GET_MODELS", this.model_role_id)
      this.getSchedule()

      this.addBooking.startDate = this.booking.startDate
      this.addBooking.time = this.time
      this.addBooking.booking_type_id = this.bookingid
    },

    addModels() {
      // let url = "../../bookings/addMoreModels"
      const url = route("bookings.addMoreModels")
      axios.post(url, this.addBooking).then((response) => {
        this.$refs['addMore'].hide()
        this.$refs['detail'].hide()
        Fire.$emit("addedAgenda")
        if (response.data.code === 403) {
          SwalGB.fire({
            icon: response.data.icon,
            title: 'Oops...',
            text: response.data.msg,
            showCancelButton: false,
          })
        } else {
          Toast.fire({
            icon: response.data.icon,
            title: "¡" + response.data.msg + "!"
          })
        }
      }).catch((error) => {
        console.log(error)
      })
    },

    rescheduleModal() {
      this.$refs['reschedule'].show()
      this.getSchedule()
    },

    rescheduleAppointment(id) {
      // let url = "../../bookings/reschedule/" + id
      const url = route("bookings.reschedule", {id})
      axios.put(url, this.booking).then((response) => {
        Fire.$emit("addedAgenda")
        this.$refs['reschedule'].hide()
        this.$refs['detail'].hide()
        if (response.data.code === 403) {
          SwalGB.fire({
            icon: response.data.icon,
            title: 'Oops...',
            text: response.data.msg,
            showCancelButton: false,
          })
        } else {
          Toast.fire({
            icon: response.data.icon,
            title: "¡" + response.data.msg + "!"
          })
        }
      }).catch((error) => {
        console.log(error)
      })
    }
  },

  computed: {
    ...mapGetters(['getModels', 'getDays', 'getLocations'])
  },

  mounted() {
    Echo.private('appointmentCreated')
        .listen('.AppointmentCreated', (e) => {
          this.GetAgenda()
        });
    
    this.GetAgenda()
    this.getBookingType()
    Fire.$on("addedAgenda", () => {
      this.GetAgenda()
    });

    Fire.$on("scheduleUpdated", () => {
      this.getSchedule()
      $("#update_" + this.sch_id).addClass('table-success')

      setTimeout(() => {
        $("#update_" + this.sch_id).removeClass('table-success')
      },4000);
    });
  }
}
</script>

<style scoped>
</style>