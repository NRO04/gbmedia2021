<template>
  <div class="bookings">
    <b-overlay :show="show" no-wrap></b-overlay>

    <div class="row" v-show="!show">
      <div class="col-md-12">
        <div class="card border-info shadow">
          <div class="card-header border-bottom-0">
            <span>
              <span class="h4"><i class="text-info fas fa-video"></i> Reserva Audiovisuales </span>

              <span>
                  <b-button v-if="can('bookingsschedules-create')" variant="success" size="sm" class="float-right mx-3" @click="scheduleModal">
                    <i class="fas fa-plus"></i> Crear Horario
                  </b-button>
                  <finalized :bookingid="bookingid" :permissions="permissions" v-if="can('audiovisual-create')"></finalized>
                  <timetable :bookingid="bookingid" :permissions="permissions" v-if="can('audiovisual-create')"></timetable>
                  <processes :bookingid="bookingid" :permissions="permissions" v-if="can('audiovisual-create')"></processes>
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

    <b-modal centered ref="my-modal" header-bg-variant="dark" header-close-content="" title="Hacer una reserva" @hidden="resetModal">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <b-form>
              <b-form-group>
                <strong>Fecha: </strong> {{ booking.startDate }}
              </b-form-group>

              <b-form-group label="Escoger un horario para agendar" v-if="schedules.length > 0">
                <b-form-radio-group id="radio-group-2" v-model="booking.time">
                  <b-form-radio v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">
                    {{ schedule.hour + ":"+ schedule.minutes + " " + schedule.meridiem }}
                  </b-form-radio>

                  <b-form-radio v-model="disableDate.schedule" :value="true">
                    Bloquear el día
                  </b-form-radio>
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
            </b-form>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <b-button v-if="disableDate.schedule"  type="button" class="float-left mx-3" size="sm" variant="danger" @click.stop.prevent="banAppointment">
            <i class="fas fa-ban"></i> Deshabilitar
          </b-button>

          <b-button v-if="booking.models.length >= 1 && schedules.length > 0 && booking.time != null" type="button" class="float-right" size="sm" variant="success" @click.stop.prevent="addAppointment">
            <i class="fas fa-save"></i> Reservar
          </b-button>
        </div>
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
      <div v-if="booking.status == 0 || booking.status == 1 || booking.status == 2" class="container">
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
                <table class="table table-striped">
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
                        <b-form-radio  :disabled="!isAttending || booking.status == 1 || booking.status == 2" v-model="bookingProcess.status" :value="1">Asistió</b-form-radio>
                        <!--<input :disabled="!isAttending || booking.status == 1 || booking.status == 2" class="form-check-input" type="radio" id="status_attended" :value="1" v-model="bookingProcess.status">
                        <label class="form-check-label" for="status_attended">Asistió</label>-->
                      </div>
                    </td>
                    <td>
                      <div class="form-check">
                        <b-form-radio  :disabled="!isAttending || booking.status == 1 || booking.status == 2" v-model="bookingProcess.status" :value="2">No asistió</b-form-radio>
                        <!--<input :disabled="!isAttending || booking.status == 1 || booking.status == 2" class="form-check-input" type="radio" id="status_not_attended" :value="2" v-model="bookingProcess.status">
                        <label class="form-check-label" for="status_not_attended">No asistió</label>-->
                      </div>
                    </td>
                  </tr>
                  </tbody>
                </table>

                <div v-if="booking.description">
                  <hr>
                  <p>
                    <span><strong>Nota: </strong></span> {{ booking.description }}
                  </p>
                  <hr>
                </div>

                <form v-if="booking.status == 0 && bookingProcess.status != 2 && booking.startDate == moment().format('YYYY-MM-DD')">
                  <b-form-group label="Seleccione el tipo de sesion">
                    <b-form-radio-group id="radio-group-5">
                      <b-form-radio :value="1" v-model="bookingProcess.sessionType">Fotografía</b-form-radio>
                      <b-form-radio :value="2" v-model="bookingProcess.sessionType">Video</b-form-radio>
                      <b-form-radio :value="3" v-model="bookingProcess.sessionType">Fotografía y video</b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>

                  <div v-if="bookingProcess.sessionType === 1">
                    <b-form-group label="Seleccione quien realizo la sesion">
                      <b-form-select class="mb-3" v-model="bookingProcess.photographer">
                        <b-form-select-option v-for="photo in getPhotographers" :value="photo.id" :key="photo.id">
                          {{ photo.first_name + " " + photo.last_name }}
                        </b-form-select-option>
                      </b-form-select>
                    </b-form-group>
                  </div>

                  <div v-if="bookingProcess.sessionType === 2">
                    <b-form-group label="Seleccione quien realizo la sesion">
                      <b-form-select class="mb-3" v-model="bookingProcess.videographer">
                        <b-form-select-option v-for="video in getVideographers" :key="video.id" :value="video.id">
                          {{ video.first_name + " " + video.last_name }}
                        </b-form-select-option>
                      </b-form-select>
                    </b-form-group>
                  </div>

                  <div class="row" v-if="bookingProcess.sessionType === 3">
                    <div class="col-md-6">
                      <b-form-group label="Seleccione quien realizo la sesion de fotografia">
                        <b-form-select class="mb-3" v-model="bookingProcess.photographer">
                          <b-form-select-option v-for="photo in getPhotographers" :value="photo.id" :key="photo.id">
                            {{ photo.first_name + " " + photo.last_name }}
                          </b-form-select-option>
                        </b-form-select>
                      </b-form-group>
                    </div>
                    <div class="col-md-6">
                      <b-form-group label="Seleccione quien realizo la sesion de video">
                        <b-form-select class="mb-3" v-model="bookingProcess.videographer">
                          <b-form-select-option v-for="video in getVideographers" :key="video.id" :value="video.id">
                            {{ video.first_name + " " + video.last_name }}
                          </b-form-select-option>
                        </b-form-select>
                      </b-form-group>
                    </div>
                  </div>
                </form>

                <div v-else-if="bookingProcess.status == 2 || booking.status == 2" class="text-center pt-2">
                  <strong class="text-danger">
                    La modelo {{ booking.models }} no asistió a la designación de audiovisuales
                  </strong>
                </div>

                <div v-else-if="booking.status == 1 || booking.status == 4" class="text-center pt-2">
                  <strong class="text-success">
                    La modelo {{ booking.models }} asistió a la designación de audiovisuales
                  </strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="booking.status == 4" class="container">
        <div class="row text-center">
          <div class="col-md-12 p-3">
            <h4 class="text-warning">Reservas para esta fecha han sido {{ booking.models }}</h4>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <b-button v-if="booking.status == 0 && can('audiovisual-delete')" type="button" class="float-right mx-2" size="sm" variant="danger" @click.prevent="deleteBooking(booking.id)">
            <i class="fas fa-trash"></i> Delete
          </b-button>

          <b-button v-if="booking.status == 0 && addAnother == true && can('audiovisual-edit')" type="button" class="float-right mx-2" size="sm" variant="primary" @click="addMoreModels">
            <i class="fas fa-plus"></i> Añadir mas modelos
          </b-button>

          <b-button v-if="booking.status == 0 && reschedule == true && can('audiovisual-edit')" type="button" class="float-right mx-2" size="sm" variant="dark" @click="rescheduleModal">
            <i class="fab fa-algolia"></i> Reprogramar
          </b-button>

          <b-button v-if="bookingProcess.status == 2 && booking.status == 0" type="button" class="float-right" size="sm" variant="warning" @click.prevent="updateAppointment(booking.id)">
            <i class="fas fa-check"></i> Confirmar inasistencia
          </b-button>

          <b-button v-if="can('audiovisual-edit') && booking.status == 0 && (bookingProcess.status == 1) && (bookingProcess.videographer != null || bookingProcess.photographer != null)" type="button" class="float-right" size="sm" variant="success"
                    @click.prevent="updateAppointment(booking.id)">
            <i class="fas fa-check"></i> Confirmar asistencia
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
                   <span><strong>Horario: </strong> <span class="text-success">{{ time }}</span></span>
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
import Tooltip from "bootstrap/js/src/tooltip"

import processes from "./partials/processes"
import cronogramas from "./partials/timetable"
import finalized from "./partials/finalized"

export default {
  name: "audiov",
  props: ['user', 'bookingid', 'permissions'],

  components: {
    FullCalendar,
    VueTimepicker,
    processes,
    cronogramas,
    finalized
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
        eventLimit: false,
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
        timeFormat: 'H(:mm)',
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
        validRange: function () {
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
        booking_type_id: 1
      },
      bookingProcess: {
        videographer: null,
        photographer: null,
        date: null,
        model: null,
        sessionType: null,
        status: 1
      },
      form: {
        hh: '01',
        mm: '00',
        A: 'AM',
        booking_type_id: null
      },
      disableDate: {
        schedule: false,
        startDate: null,
        booking_type_id: null,
        time: []
      },

      model_role_id: 14,
      photo_role_id: 9,
      video_role_id: 10,
      schedules: [],
      bookingTypes: [],
      processes: [],
      isCreating: false,
      isAttending: false,
      show: false,
      reschedule: true,
      addAnother: true,
      moment: moment,
      time: null,
      model: null,

      updateProcess: {
        attachment: null,
        videoLink: false,
        process_status: null
      },
      addBooking: {
        models: [],
        time: null,
        startDate: null,
        booking_type_id: null
      },
      editMode: false,
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
      this.booking.description = null
      this.booking.time = null
      this.isAttending = false
      this.bookingProcess.status = 1
      this.bookingProcess.photographer = null
      this.bookingProcess.videographer = null
      this.bookingProcess.model = null
      this.bookingProcess.date = null
      this.bookingProcess.sessionType = null
      this.updateProcess.videoLink = null
      this.updateProcess.process_status = null
      this.reschedule = true
      this.addBooking.models = []
    },

    handleDateSelect(selectInfo) {
      let date = new Date()
      let currentDate = moment(selectInfo.start).format('YYYY-MM-DD')
      let selectedDate = moment(date).format('YYYY-MM-DD')
      this.disableDate.startDate = currentDate

      if (!(this.can('audiovisual-create'))) {
        SwalGB.fire({
          icon: 'warning',
          text: "No tiene permiso para hacer reservas!",
          title: "¡Advertencia!",
          showCancelButton: false,
        })
      } else {
        if (currentDate < selectedDate) {
          this.reschedule = false
          SwalGB.fire({
            icon: 'warning',
            text: "No puede reservar en esta fecha!",
            title: "¡Advertencia!",
            showCancelButton: false,
          })
        } else {
          this.$refs['my-modal'].show()

          this.booking.startDate = moment(selectInfo.start).format("DD-MM-YYYY")
          this.$store.commit("ADD_EVENT", this.booking)
          this.$store.dispatch("GET_MODELS", this.model_role_id)
          this.getSchedule()
        }
      }
    },

    handleEventClick: function (clickInfo) {

      if (!(this.can('audiovisual-create'))) {
        SwalGB.fire({
          icon: 'warning',
          text: "No tiene permiso para hacer reservas!",
          title: "¡Advertencia!",
          showCancelButton: false,
        })
      }
      else{
        this.$refs['detail'].show()

        let event = clickInfo.event._def.extendedProps;
        let date = new Date()
        let currentDate = moment(date).format('YYYY-MM-DD')
        let eventDate = moment(event.start_date).format('YYYY-MM-DD')

        this.booking.id = event.agenda_id;
        this.booking.models = event.model_nick;
        this.model = event.model_nick;
        this.time = event.time;
        this.booking.time = event.time_id;
        this.booking.description = event.description;
        this.booking.status = event.status;
        this.booking.startDate = event.start_date;

        this.bookingProcess.date = event.start_date;
        this.bookingProcess.model = event.model_id;
        this.bookingProcess.status = parseInt(event.status);

        this.$store.dispatch("GET_FILMAKERS", this.video_role_id)
        this.$store.dispatch("GET_PHOTOGRAPHERS", this.photo_role_id)

        if (currentDate === eventDate) {
          this.isAttending = true
          this.reschedule = false
        }

        this.reschedule = (eventDate > currentDate);
        this.addAnother = eventDate >= currentDate;

        this.getProcessesById()
      }
    },

    scheduleModal() {
      this.$refs['schedule'].show()
      this.getSchedule()
    },

    editSchedule(id){
      this.sch_id = id
      this.editMode = true
      // let url = "../../bookings/editSchedule/" + id
      const url  = route('bookings.editSchedule', {id})

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
      const id = this.bookingid;
      const url = route("bookings.getScheduleById", {id})
      axios.get(url)
          .then((response) => {
            this.schedules = response.data.schedules;
          }).catch((error) => {
        console.log(error)
      })
    },

    getProcessesById() {
      // let url = "../../bookings/getProcesses/" + this.bookingid
      const id = this.bookingid;
      const url = route("bookings.getProcessesById", {id})
      axios.get(url)
          .then((response) => {
            console.log(response)
            this.processes = response.data.processes;
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
      axios.post(url, this.booking)
          .then((response) => {
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
        Toast.fire({
          icon: "error",
          title: "Por favor, complete los campos requeridos",
        })
      })
    },

    updateAppointment(id) {
      // let url = "../../bookings/update/" + id
      const url = route("bookings.update", {id})
      axios.put(url, this.bookingProcess)
          .then((response) => {
            Fire.$emit("addedAgenda")
            this.$refs['detail'].hide()
            Toast.fire({
              icon: "success",
              title: "Reserva actualizada correctamente!",
            })
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
            Fire.$emit("addedAgenda")
            this.$refs['my-modal'].hide()

          }).then((error) => {
        Toast.fire({
          icon: "error",
          title: "Por favor, complete los campos requeridos",
        })
      })
    },

    GetAgenda() {
      this.show = true
      // let url = "../../bookings/agenda/" + this.bookingid
      const id = this.bookingid;
      const url = route("bookings.agenda", {id})
      axios.get(url)
          .then((response) => {
            this.show = false
            this.calendarOptions.events = response.data.bookings;
            console.log(this.calendarOptions)
          }).catch((error) => {
        this.show = false
        console.log(error)
      })
    },

    updateProcesses(id) {
      // let url = "../../bookings/updateprocess/" + id
      const url = route("bookings.updateProcess", {id})
      axios.put(url, this.updateProcess).then((response) => {
        Toast.fire({
          icon: "success",
          title: "¡Se actualizó correctamente!",
        })
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
      const  url = route("bookings.reschedule", {id})
      this.isCreating = true

      axios.put(url, this.booking).then((response) => {
        Fire.$emit("addedAgenda")
        this.$refs['reschedule'].hide()
        this.$refs['detail'].hide()
        this.isCreating = false
        
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
        this.isCreating = false
        
      })
    },

    deleteBooking(id) {
      // let url = "../../bookings/deleteBooking/" + id
      const url = route("bookings.destroy", {id})
      axios.delete(url).then((response) => {
        this.$refs['detail'].hide()
        Fire.$emit("addedAgenda")
        Toast.fire({
          icon: "error",
          title: "¡Se eliminó la cita correctamente!",
        })
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
    }
  },

  computed: {
    ...mapGetters(['getModels', 'getPhotographers', 'getVideographers'])
  },

  mounted() {
    if (this.disableDate.schedule){
       this.booking.models = []
    }

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
.fc-daygrid-block-event .fc-event-time, .fc-daygrid-block-event .fc-event-title {
  padding: 3px;
}

.fc-day-sun {
  background-color: red !important;
}
</style>