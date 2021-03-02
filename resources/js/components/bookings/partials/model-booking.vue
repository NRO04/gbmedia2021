<template>
  <div class="bookings">
    <b-overlay :show="show" no-wrap></b-overlay>

    <div class="row" v-show="!show">
      <div class="col-md-12">
        <div class="card rounded-0 border-info shadow">
          <div class="card-header border-bottom-0">
            <span>
              <span class="h4"> <i class="text-info fas fa-bookmark"></i> Hacer una reserva </span>
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

    <b-modal centered v-model="openModal" id="reserve-model" ref="reserve-model" header-bg-variant="primary" size="lg" header-close-content="" title="Realizar una reserva" @hidden="resetModal">

      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <form>
              <b-form-group>
                <strong>Fecha de reserva: </strong> <strong class="text-success">{{ booking.startDate }}</strong>
              </b-form-group>

              <b-form-group label="Seleccione el tipo de reserva">
                <b-form-radio-group id="radio-group-2" name="booking_type">
                  <b-form-radio v-for="type in seeds.booking_types" :key="type.id" :value="type.id" v-model="booking.booking_type_id" @change="search(type.id)">
                    {{ type.booking }}
                  </b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div v-if="booking.booking_type_id === 1">
                <div v-if="schedules.length > 0">
                  <b-form-group label="Seleccione un horario para reservar">
                    <b-form-radio-group id="radio-group-3" v-model="booking.time" name="booking_time">
                      <b-form-radio v-for="(schedule, i) in test.schedules" :key="i" :value="schedule.sch_id">
                        {{ schedule.hour }}
                      </b-form-radio>
                    </b-form-radio-group>
                  </b-form-group>
                </div>

                <hr>
                <b-form-group label="Propuesta: ">
                  <b-form-textarea v-model="booking.description" rows="6" max-rows="20" placeholder="Ejemplo: Me gustaria hacer fotos con un traje de latex nuevo que me compre"></b-form-textarea>
                  <small id="passwordHelpBlock" class="form-text text-info">
                    Propuestas o ideas que tengo para esta sesión de: Fotos o Video. Dejar en blanco si no tiene ninguna!!!
                  </small>
                </b-form-group>
              </div>

              <div v-for="(date,i) in seeds.booking_types" v-if="booking.booking_type_id != null && booking.booking_type_id !== 1">
                <div v-for="(day,j) in date.days">
                  <div v-for="d in day">
                    <div :class="dday == booking.startDate ? 'alert alert-success shadow' : 'alert alert-danger shadow'"
                         v-for="dday in d" v-if="booking.startDate === dday && booking.booking_type_id === date.id">
                      <div v-if="schedules.length > 0">
                        <b-form-group label="Seleccione un horario para reservar">
                          <b-form-radio-group id="radio-group-3" v-model="booking.time" name="booking_time">
                            <b-form-radio v-for="(schedule, i) in test.schedules" :key="i" :value="schedule.sch_id">
                              {{ schedule.hour }}
                            </b-form-radio>
                          </b-form-radio-group>
                        </b-form-group>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>

            <div class="alert alert-dark shadow" v-if="booking.booking_type_id != null && booking.booking_type_id !== 1">
              <h3 class="alert-heading">Atencion!</h3>
              <h6>
                <i data-toggle="tooltip" title="" data-original-title="Orden en que aparecerán los ítems en la APP" class="text-info fas fa-question-circle"></i>
                Fechas en las que puede reservar para <span class="text-danger font-weight-bold">{{ seed.name }}</span>
              </h6>

              <div class="container">
                <div class="row">
                  <div class="col-md-12" v-for="(date,i) in seeds.booking_types" v-if="user.setting_location_id === date.location_id && booking.booking_type_id === date.id">
                    <div class="row" v-for="(day,j) in date.days">
                      <div class="col-md-12" v-for="d in day">
                        <hr>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="row">
                              <button class="col-md-3 m-1" v-for="dday in d"
                                      :class="dday >= moment().format('YYYY-MM-DD') ? 'btn  btn-success' : 'btn btn-danger'"
                                      :disabled="dday < moment().format('YYYY-MM-DD')" @click="changeDate(dday, date.booking)">
                                {{ dday }}
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <b-button v-if="!isCreating && booking.booking_type_id != null && booking.time != null" type="button" class="float-right" size="sm" variant="success" @click.stop.prevent="addAppointment">
            <i class="fas fa-check"></i> Confirmar
          </b-button>

          <b-button v-else-if="isCreating && booking.booking_type_id != null && booking.time != null" type="button" class="float-right" size="sm" variant="success" :disabled="isCreating">
            <i class="fas fa-spinner fa-spin"></i> Agendando reserva...
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
import Tooltip from "bootstrap/js/src/tooltip";

export default {
  name: "bookings",
  props: ['user', 'seed'],

  components: {
    FullCalendar
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
          left: 'title'
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

        viewDidMount: function (arg) {
          console.log(arg)
        },

        hiddenDays: [0],

        validRange: function () {
          return {
            start: moment().startOf('month').format('YYYY-MM-DD'),
            end: moment().add(1, 'months').endOf('month').add(1, 'days').format('YYYY-MM-DD')
          };
        }
      },
      booking: {
        startDate: null,
        time: null,
        models: [{
          id: null,
          nick: null
        }],
        description: null,
        booking_type_id: null
      },
      schedules: [],
      sundays: [],
      isCreating: false,
      show: false,
      openModal: false,
      seeds: [],
      test: [],
      moment: moment
    }
  },

  methods: {
    resetModal() {
      this.booking.models = [{
        id: null,
        nick: null
      }]
      this.booking.startDate = null
      this.booking.time = null
      this.booking.description = null
      this.booking.booking_type_id = null
      this.schedules = []
    },

    handleDateSelect(selectInfo) {
      console.log(selectInfo)

      let date = new Date()
      let currentDate = moment(selectInfo.start).format('YYYY-MM-DD')
      let selectedDate = moment(date).format('YYYY-MM-DD')

      if (currentDate < selectedDate) {
        this.openModal = false
        SwalGB.fire({
          icon: 'warning',
          text: "No puede reservar en esta fecha!",
          title: "¡Advertencia!",
          showCancelButton: false,
        })

      } else {
        this.openModal = true
        this.booking.startDate = moment(selectInfo.start).format("YYYY-MM-DD")
        this.booking.models = [{
          id: this.user.id,
          nick: this.user.nick
        }]
        this.getBookingType()
      }

      this.sundays.forEach((value, index) => {
        if (selectInfo.startStr === value) {
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

    getSchedule() {
      axios.get('getSchedule')
          .then((response) => {
            this.schedules = response.data.schedules;
          }).catch((error) => {
        console.log(error)
      })
    },

    getBookingType() {
      axios.get('getBookingTypes')
          .then((response) => {
            this.bookingTypes = response.data.bookingTypes;
            this.seeds = response.data.data;
          }).catch((error) => {
        console.log(error)
      })
    },

    addAppointment() {
      axios.post('store', this.booking)
          .then((response) => {
            Fire.$emit("addedAgenda")
            this.$refs['reserve-model'].hide()

            if (response.data.code === 403) {
              SwalGB.fire({
                icon: response.data.icon,
                title: 'Oops...',
                text: response.data.msg,
                showCancelButton: false,
              }).then((result) => {
                if (result.isConfirmed) {
                  this.booking.booking_type_id = null
                  this.booking.time = null
                }
              })
            } else {
              Toast.fire({
                icon: response.data.icon,
                title: response.data.msg
              })
            }
          }).catch((error) => {
        console.log(error)
        Toast.fire({
          icon: "error",
          title: "Por favor, complete los campos requeridos",
        })
      })
    },

    GetAgenda() {
      this.show = true
      let url = "getModelSchedule/" + this.user.id
      axios.get(url)
          .then((response) => {
            console.log(response)
            this.show = false
            this.calendarOptions.events = response.data.bookings;
            this.sundays = response.data.sundays;
          }).catch((error) => {
        console.log(error)
        this.show = false
      })
    },

    search(id) {
      this.booking.time = null
      this.booking.description = null

      let url = "getSchedule/" + id
      axios.get(url)
          .then((response) => {
            this.schedules = response.data.schedules;
            this.test = response.data.data;
          }).catch((error) => {
        console.log(error)
      })
    },

    changeDate(day, type) {
      let d = moment(day).format('DD')
      let m = moment(day).format('MMMM')
      let y = moment(day).format('YYYY')
      let date = moment(day).format('YYYY-MM-DD')
      SwalGB.fire({
        icon: "question",
        title: "Esta seguro que...",
        html: "desea escoger el " + "<span class='text-info'>" + d + " de " + m + " de " + y + "</span>" + " para su cita de " + type + "?",
      }).then((result) => {
        if (result.isConfirmed) {
          this.booking.startDate = day;
          Toast.fire({
            icon: "info",
            title: "Escogió la fecha " + "<span class='text-info'>" + date + "</span>" + " para su cita de " + type + "!"
          })
        }
      })
    }
  },

  mounted() {
    this.GetAgenda()
    this.getBookingType()
    Fire.$on("addedAgenda", () => {
      this.GetAgenda()
    });

    Echo.private('appointmentCreated')
        .listen('.AppointmentCreated', (e) => {
          console.log(e)
          this.GetAgenda()
          Toast.fire({
            icon: "info",
            title: "!Reserva actualizada!",
          })
        });
  }
}
</script>

<style scoped>
td:hover {
  cursor: pointer !important;
}

.fc-daygrid-block-event .fc-event-time, .fc-daygrid-block-event .fc-event-title {
  padding: 3px;
}
</style>