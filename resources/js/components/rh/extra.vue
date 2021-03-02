<template>
  <div class="bookings">

    <div class="row">
        <div class="card" style="width: 100%;">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm">
                        <span class="span-title">Horas Extras <span class="badge badge-primary"></span></span>
                    </div>
                    <div class="col-sm">
                        <button @click="showModal" class="btn btn-success btn-sm float-right mx-1"><i class="fa fa-plus"></i>&nbsp;Solicitar</button>
                        <a v-if="can('human-resources-extra-hour-approve')" :href="projectRoute('rh.extraHours.listProcess')" target="_blank"
                           class="btn btn-info btn-sm float-right mx-1"><i
                            class="fas fa-paper-plane"></i>&nbsp;&nbsp;Solicitudes</a>
                        <a v-if="can('human-resources-extra-hour-configuration-view')" :href="projectRoute('rh.extraHours.edit')" target="_blank"
                           class="btn btn-warning btn-sm float-right mx-1"><i
                            class="fas fa-cog"></i>&nbsp;&nbsp;Configuración</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-sm-4">
                        <div class="card">
                            <div class="card-body">
                                <label class="card-title">Seleccionar Periodo</label>
                                <select v-model="infotable.periot" @change="reloadDataTable()" class="form-control form-control-sm">
                                    <option v-for="rank in ranks" :key="rank.id">{{ rank.range }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4" v-if="can('human-resources-extra-hour-historial')">
                        <div class="card">
                            <div class="card-body">
                                <label class="card-title">Seleccione Usuario</label>
                                <select v-model="infotable.users" @change="reloadDataTable()" class="form-control form-control-sm">
                                    <option value="0">ALL</option>
                                    <option v-for="user in list_user" :value="user.id">{{ user.full_name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <table id="table_history" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Nombre Solicitante</th>
                            <th>Razón Horas Extras</th>
                            <th>Solicitado</th>
                            <th>Duración</th>
                            <th>Resumen</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <!--modal-->
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="modalEditTaskLabel">
        <div class="modal-dialog modal-lg modal-dark" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Solicitar Horas Extras</h5>
                </div>
                <div class="modal-body">
                    <form id="form_extra_hour">
                        <div class="form-group">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <div class="card border-secondary">
                                        <div class="card-body row">
                                            <div class="col-md-6">
                                                <label for="first_name">Nombre Solicitante</label><br>
                                                <label for="first_name"><b>{{this.user_name}}</b></label>
                                                </br>
                                                <span class="badge bg-primary" style="font-size: 15px;"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="first_name">Fecha</label>
                                                <select  v-model="form.date_form" class="form-control" @change="getExtraHourValue($event)">
                                                    <option>{{ this.current_date }}</option>
                                                    <option>{{ this.yesterday_date }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-secondary">
                                        <div class="card-body row">
                                            <div class="col-md-4">
                                                <label for="first_name">Hora comenzó Tiempo Extra</label>
                                                <vue-timepicker v-model="form.start_time" id="start_time" name="start_time" @change="getExtraHour()" format="hh:mm A" fixed-dropdown-button></vue-timepicker>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="first_name">Hora Terminó Tiempo Extra</label>
                                                <vue-timepicker v-model="form.end_time" id="end_time" name="end_time"  @change="getExtraHour()" format="hh:mm A"></vue-timepicker>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="first_name">Total Minutos</label>
                                                <input v-model="form.total_extras" type="text" class="form-control sm" name="total_extras" id="total_extras" onpaste=" return false" onfocus="this.blur()">
                                            </div>
                                            <div class="col-md-12">
                                                <label for="extra_reason" style="margin-top: 10px;">Descripción de Razón Horas Extras</label>
                                                <textarea v-model="form.extra_reason" class="form-control" id="extra_reason" name="extra_reason"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-secondary">
                                        <div class="card-body row" style="padding-left: 30px;padding-right: 30px;">
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td><label for="first_name">Minutos Diurnos</label></td>
                                                    <td><input v-model="form.daytime_minutes" type="text" class="form-control sm" name="daytime_minutes" id="daytime_minutes" onpaste=" return false" onfocus="this.blur()" style="width: 95%; margin-bottom: 6px;"></td>
                                                    <td><label for="first_name">Total Pesos</label></td>
                                                    <td><input v-model="form.daytime_total" type="text" class="form-control sm" name="daytime_total" id="daytime_total" onpaste=" return false" onfocus="this.blur()" style="margin-bottom: 6px;"></td>
                                                </tr>
                                                <tr>
                                                    <td>Minutos Nocturnos</td>
                                                    <td><input v-model="form.night_minutes" type="text" class="form-control sm" name="night_minutes" id="night_minutes" onpaste=" return false" onfocus="this.blur()" style="width: 95%; margin-bottom: 6px;"></td>
                                                    <td><label for="first_name">Total Pesos</label></td>
                                                    <td><input v-model="form.night_total" type="text" class="form-control sm" name="night_total" id="night_total" onpaste=" return false" onfocus="this.blur()" style="margin-bottom: 6px;"></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td><B>MONTO TOTAL</B></td>
                                                    <td><input v-model="form.total" type="text" class="form-control sm" name="total" id="total" onpaste=" return false" onfocus="this.blur()" style="background: #5bde30; color: black; font-weight: bold;"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-success btn-sm float-right mx-1" style="margin-bottom: 14px;" @click="sendForm"><i class="fas fa-check"></i> Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
    import * as helper from '../../../../public/js/vue-helper.js'
    import 'datatables.net'


    export default {
        name: 'table_history',
        props: ['dates','id_user','daytime_hours','night_hours','user_name','current_date', 'yesterday_date', 'list_user', 'ranks', 'permissions'],
        data() {
            return {
                form: new Form({
                    start_time: '01:00 AM',
                    end_time: '01:00 AM',
                    date_form: this.current_date,
                    id_user: this.id_user,
                    daytime_hours: this.daytime_hours.toString(),
                    night_hours: this.night_hours.toString(),
                    extra_reason: '',
                    total_extras: '0',
                    date: new Date(),
                    daytime_minutes: '0',
                    daytime_total: '0',
                    night_minutes: '0',
                    night_total: '0',
                    total: '0',
                }),
                infotable: new Form({
                    periot : this.ranks[this.ranks.length - 1],
                    users : this.id_user,
                }),
            }
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            showModal() {
                this.form.reset();
                $('#modal-edit').modal('show');
            },
            reloadDataTable()
            {
                this.form.reset();
                this.rank = this.infotable.periot;
                this.table_history.ajax.reload();
            },
            getExtraHour()
            {
                let daytime_minute = 0;
                let night_minute   = 0;
                let total_minutes  = 0;

                let start_time = this.getFormatDate(this.form.start_time);
                let end_time = this.getFormatDate(this.form.end_time);

                let starting_hour      = start_time[0];
                let starting_minutes   = start_time[1];
                let start_schedule     = start_time[2];

                let leaving_hour       = end_time[0];
                let leaving_minutes    = end_time[1];
                let leaving_schedule   = end_time[2];

                let start_entry = 0;
                let exit_ends   = 0;

                let init_schedule   = start_schedule;
                let end_schedule    = leaving_schedule;

                let const_day_hours     = 0;
                let const_night_hours   = 0;

                if(starting_hour != 12)
                {
                   start_entry = starting_hour;
                }

                if(starting_hour != 12)
                {
                   start_entry = starting_hour;
                }

                if(leaving_hour != 12)
                {
                   exit_ends = leaving_hour;
                }

                if(leaving_hour != 12)
                {
                   exit_ends = leaving_hour;
                }

                for(var i = start_entry; i <= 12; i++)
                {
                    if(i == 12)
                    {
                        if (init_schedule == "AM")
                        {
                            i = 0;
                            init_schedule = "PM";
                        }
                        else
                        {
                            i = 0;
                            init_schedule = "AM";
                        }

                    }

                    if(init_schedule == end_schedule && i == exit_ends)
                    {
                        break;
                    }
                    else
                    {
                        if(i < 6 && init_schedule == "AM" || i >= 9 && init_schedule == "PM" || i == 12 && init_schedule == "AM")
                        {
                            const_night_hours++;
                        }
                        if(i >= 6 && init_schedule == "AM" || i < 9 && init_schedule == "PM" || i == 12 && init_schedule == "PM")
                        {
                            const_day_hours++;
                        }
                    }

                    if(const_day_hours == 29)
                    {
                        alert("error");
                        break;
                    }
                }

                const_day_hours     = const_day_hours*60;
                const_night_hours   = const_night_hours*60;

                if(start_schedule == "AM")
                {
                    if(starting_hour < 6)
                    {
                        const_night_hours = const_night_hours - starting_minutes;
                    }
                    if(starting_hour >= 6 && starting_hour != 12)
                    {
                        const_day_hours = const_day_hours - starting_minutes;
                    }
                    if(starting_hour == 12)
                    {
                        const_night_hours = const_night_hours - starting_minutes;
                    }
                }

                if(start_schedule == "PM")
                {
                    if(starting_hour < 9)
                    {
                        const_day_hours = const_day_hours - starting_minutes;
                    }
                    if(starting_hour >= 9 && starting_hour != 12)
                    {
                        const_night_hours = const_night_hours - starting_minutes;
                    }
                    if(starting_hour == 12)
                    {
                        const_day_hours = const_day_hours - starting_minutes;
                    }
                }

                if(leaving_schedule == "AM")
                {
                    if(leaving_hour < 6)
                    {
                        const_night_hours = const_night_hours + leaving_minutes;
                    }
                    if(leaving_hour >= 6 && leaving_hour != 12)
                    {
                        const_day_hours = const_day_hours + leaving_minutes;
                    }
                    if(leaving_hour == 12)
                    {
                        const_night_hours = const_night_hours + leaving_minutes;
                    }
                }

                if(leaving_schedule == "PM")
                {
                    if(leaving_hour < 9)
                    {
                        const_day_hours = const_day_hours + leaving_minutes;
                    }
                    if(leaving_hour >= 9 && leaving_hour != 12)
                    {
                        const_night_hours = const_night_hours + leaving_minutes;
                    }
                    if(leaving_hour == 12)
                    {
                        const_day_hours = const_day_hours + leaving_minutes;
                    }
                }

                daytime_minute  = const_day_hours;
                night_minute    = const_night_hours;
                let min_total   = daytime_minute + night_minute;

                let value_x_min_daytime = this.form.daytime_hours/60;
                let daytime_total       = Math.floor(value_x_min_daytime * daytime_minute);

                this.form.daytime_minutes    = daytime_minute;
                this.form.daytime_total      = daytime_total;

                let value_x_min_night   = this.form.night_hours/60;
                let night_total         = Math.floor(value_x_min_night * night_minute);

                let total_value = daytime_total + night_total;

                if(night_minute > 0)
                {
                    this.form.night_minutes = night_minute;
                }
                else
                {
                    this.form.night_minutes = 0;
                }

                if(night_total > 0)
                {
                    this.form.night_total = night_total;
                }
                else
                {
                    this.form.night_total = 0;
                }

                if(total_value > 0)
                {
                    this.form.total = total_value;
                }
                else
                {
                    this.form.total = 0;
                }

                if(min_total > 0)
                {
                    this.form.total_extras = min_total;
                }
                else
                {
                    this.form.total_extras = '0';
                }

            },
            getExtraHourValue(event)
            {
                let date = event.target.value;
                let user_id = this.form.id_user;
                //let url = "/rh/extraHours/geOvertimeValue?date="+date+"&user_id="+user_id;
                let url = route('rh.getOvertimeValue', {date: date, user_id: user_id});

                axios.get(url).then((response) => {
                    this.form.daytime_hours     = response.data.daytime_hours;
                    this.form.night_hours       = response.data.night_hours;
                    this.form.start_time        = '01:00 AM';
                    this.form.end_time          = '01:00 AM';
                    this.form.extra_reason      = '';
                    this.form.total_extras      = '0';
                    this.form.date              = new Date();
                    this.form.daytime_minutes   = '0';
                    this.form.daytime_total     = '0';
                    this.form.night_minutes     = '0';
                    this.form.night_total       = '0';
                    this.form.total             = '0';

                }).catch((error) => {

                });

            },
            getFormatDate(date)
            {
                let time        =  (date).split(":");
                let hour        =  parseInt((time[0])*1);
                let minute      =  (((time[1]).split(" "))[0])*1;
                let schedule    =  ((time[1]).split(" "))[1];

                let array = [];
                array.push(hour, minute, schedule);

                return array;
            },
            sendForm()
            {
                let url = route('rh.extraHours.create');
                this.form.post(url).then((response) => {
                    $("#modal-edit").modal("hide");
                    Toast.fire({
                        icon: "success",
                        title: "La solictud se realizo con exito"
                    });
                    this.table_history.ajax.reload();
                }).catch((res) => {
                    helper.VUE_CallBackErrors(res.response);
                });
            },
            getSelectioPeriot()
            {
                let url = route('rh.geOvertimeValue.getRHExtraHourRange');
                this.isBusy = true;

                axios.get(url).then((response) => {
                    console.table(response.data);
                })
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
        },
        computed: {

        },
        mounted()   {
            let url = route('rh.extraHours.getExtraHourHistory');

            let _this           = this;
            this.table_history  = null;
            this.table_history  = $('#table_history').DataTable({
                processing  : true,
                serverSide  : true,
                ordering    : false,
                pageLength  : 100,
                destroy     : true,
                ajax: {
                    method: 'GET',
                    url   : url,
                    language: {
                        url: '/DataTables/Spanish.json',
                    },
                    data  : function (d) {
                        return $.extend({}, d, {
                            "periot": _this.infotable.periot,
                            "user"  : _this.infotable.users
                        });
                    },
                },
                columns: [
                    { data: "user" },
                    { data: "extra_reason" },
                    { data: "date_request"},
                    { data: "duration" },
                    { data: "resume" },
                    { data: "total" },
                    { data: "state_id" },
                ],
            });

            //this.getSelectioPeriot();
        }
    }
</script>

<style scoped>
</style>
