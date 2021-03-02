<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-accent-info shadow">
                <div class="card-header border-bottom-0">
                  <h5 class="mb-0">
                    <i class="far fa-calendar"></i>
                    Agenda
                  </h5>
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

        <!-- Modal -->
        <b-modal ref="modal-agenda-create" header-bg-variant="dark" header-close-content="" title="Crear Reserva" >
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <b-form>
                            <b-row class="mb-12 justify-content-between">
                                <b-col sm="12" md="12" class="my-1">
                                    <b-form-group class="mb-0" label="Titulo">
                                        <b-form-input v-model="reservation.title" type="search" id="filterInput"
                                                      placeholder="Revisión de documentos"
                                                      size="sm"></b-form-input>
                                    </b-form-group>
                                </b-col>
                                <b-col lg="12" class="my-1">
                                    <b-form-group class="mb-0" label="Descripción">
                                        <b-form-textarea v-model="reservation.description" type="search" id="filterInput"
                                                         placeholder="Opcional en caso de que requiera alguna descripción para recordar lo que debe realizar"
                                                         size="sm"></b-form-textarea>
                                    </b-form-group>
                                </b-col>

                                <b-col lg="12" class="my-1">
                                    <VueCtkDateTimePicker
                                        onlyDate
                                        range
                                        dark
                                        noHeader
                                        noLabel
                                        noButton
                                        v-model="date_reservation"
                                        inputSize='sm'
                                        format="YYYY-MM-DD"
                                        formatted="ll"
                                        autoClose
                                        noShortcuts
                                        label="Seleccione la Fecha"
                                        :no-value-to-custom-elem="(true)"}>
                                        <input class="form-control" type="text" v-model="date_reservation.start+' / '+ date_reservation.end"
                                                placeholder="Seleccione la Fecha">
                                    </VueCtkDateTimePicker>
                                </b-col>

                                <b-col lg="6" class="my-1">
                                    <label>Inicio</label>
                                    <VueCtkDateTimePicker
                                        onlyTime
                                        dark
                                        noHeader
                                        noLabel
                                        v-model="reservation.time_from"
                                        inputSize='sm'
                                        format="HH:mm a"
                                        formatted="HH:mm a"
                                        noShortcuts>
                                    </VueCtkDateTimePicker>
                                </b-col>

                                <b-col lg="6" class="my-1">
                                    <label>Fin</label>
                                    <VueCtkDateTimePicker
                                        onlyTime
                                        dark
                                        noHeader
                                        noLabel
                                        v-model="reservation.time_to"
                                        inputSize='sm'
                                        format="HH:mm a"
                                        formatted="HH:mm a"
                                        noShortcuts>
                                    </VueCtkDateTimePicker>
                                </b-col>

                                <b-col lg="12" sm="12" class="my-1">
                                    <b-form-group class="mb-0">
                                        <input class="form-control" type="color" v-model="reservation.color" value="#ff0000">
                                    </b-form-group>

                                </b-col>
                            </b-row>
                        </b-form>
                    </div>
                </div>
            </div>

            <template v-slot:modal-footer>
                <div class="w-100">
                    <b-button type="button" class="float-right" size="sm" variant="success" @click.stop.prevent="addAgendaEvent">
                        <i class="fas fa-save"></i> Guardar
                    </b-button>
                </div>
            </template>
        </b-modal>

    </div>
</template>

<script>
import FullCalendar from "@fullcalendar/vue";
import VueTimepicker from "vue2-timepicker";
import processes from "../../bookings/partials/processes";
import cronogramas from "../../bookings/partials/timetable";
import finalized from "../../bookings/partials/finalized";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import interactionPlugin from "@fullcalendar/interaction";
import bootstrapPlugin from "@fullcalendar/bootstrap";
import Tooltip from "bootstrap/js/src/tooltip";
import moment from "moment";

export default {
    name: "Agenda",
    components: {
        FullCalendar,
        VueTimepicker,
        processes,
        cronogramas,
        finalized
    },
    data() {
        return {
            date_reservation: {
                start: moment().format("YYYY-MM-DD"),
                end: moment().isoWeekday(6).format("YYYY-MM-DD"),
            },
            reservation: {
                title: "",
                description: "",
                start: "" ,
                end: "",
                time_from : '01:00 am',
                time_to : '02:00 am',
                color: '#4EFF00'
            },
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
                weekends: true,
                eventLimit: false,
                height: 400,
                headerToolbar: {
                    right: 'prev,today,next',
                    left: '',
                    center: 'title'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                },
                timeFormat: 'H(:mm)',
                events: [],
                validRange: function () {
                    return {
                        // start: moment().startOf('month').format('YYYY-MM-DD'),
                        end: moment().add(1, 'months').endOf('month').add(1, 'days').format('YYYY-MM-DD')
                    };
                },
            },
        }
    },
    created() {
        this.getAgenda();
    },
    methods: {
        getAgenda(){
            axios.get(route('home.get_agenda_events')).then((response) => {
                this.calendarOptions.events = response.data.reservations;
            }).catch((error) => {
                console.log(error);
            })
        },
        addAgendaEvent(){
            this.reservation.start = this.date_reservation.start;
            this.reservation.end = this.date_reservation.end;
            axios.post(route('home.create_agenda_event'), this.reservation).then((response) => {
                console.log(response.data);
                this.calendarOptions.events.push(response.data.reservation);
                this.date_reservation = {
                    start: moment().format("YYYY-MM-DD"),
                        end: moment().isoWeekday(6).format("YYYY-MM-DD"),
                };
                this.$refs['modal-agenda-create'].hide();
                this.reservation = {
                    title: "",
                        description: "",
                        start: "" ,
                        end: "",
                        time_from : '01:00 am',
                        time_to : '02:00 am',
                        color: '#4EFF00'
                };
                console.log(this.calendarOptions.events);
            }).catch((error) => {
                console.log(error)
            })

        },
        handleDateSelect(selectInfo) {
            let date = new Date()
            let currentDate = moment(selectInfo.start).format('YYYY-MM-DD')
            let selectedDate = moment(date).format('YYYY-MM-DD')

            if (currentDate < selectedDate) {
                this.reschedule = false
                SwalGB.fire({
                    icon: 'warning',
                    text: "No puede reservar en esta fecha!",
                    title: "¡Advertencia!",
                    showCancelButton: false,
                })
            } else {
                this.$refs['modal-agenda-create'].show();
                this.date_reservation = {
                    start: currentDate,
                    end: currentDate,
                }
                /*SwalGB.fire({
                    icon: 'info',
                    text: "Estamos trabajando en la mejora de este componente!",
                    title: "¡Info!",
                    showCancelButton: false,
                })*/

            }
        },
    }
}
</script>

<style>
.fc-col-header{
    width: 100% !important;
}
.fc-daygrid-body, .fc-scrollgrid-sync-table{
    width: 100% !important;
}
</style>
