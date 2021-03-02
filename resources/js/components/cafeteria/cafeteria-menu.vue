<template>
    <div class="menu-calendar">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Menú Cafetería</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    <a class="btn btn-info btn-sm" v-if="can('max-order-time-edit')" @click="handleMaxOrderTimeClick()">
                        <i class="fa fa-clock"></i> Hora máxima de pedido
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div v-if="can('total-sales')" class="card card-accent-info col-lg-6">
                    <div class="card-header"><h5>Total ventas</h5></div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <div class="col-xs-12">
                                <div class="form-group row">
                                    <label for="range" class="col-sm-4 col-form-label">Rango:</label>
                                    <div class="col-12 col-sm-8 col-form-label">
                                        <select name="range" id="range" class="form-control" @change="getWeekCafeteriaSales($event)">
                                            <option value="">Seleccione una semana...</option>
                                            <option v-for="week in weeks" v-text="week.formatted" :value="week.start + '|' + week.end"></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="container-totals d-none">
                                    <hr>
                                    <div class="form-group row mb-0">
                                        <label class="col-4 col-form-label">Desayuno:</label>
                                        <div class="col-8 col-form-label">
                                            <div class="row">
                                                <div class="sk-wave d-none loader">
                                                    <div class="sk-rect sk-rect1"></div>
                                                    <div class="sk-rect sk-rect2"></div>
                                                    <div class="sk-rect sk-rect3"></div>
                                                    <div class="sk-rect sk-rect4"></div>
                                                    <div class="sk-rect sk-rect5"></div>
                                                </div>
                                            </div>
                                            <span id="span-total-breakfast" class="d-none cafeteria-total">$0</span>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-4 col-form-label">Almuerzo:</label>
                                        <div class="col-8 col-form-label">
                                            <div class="row">
                                                <div class="sk-wave d-none loader">
                                                    <div class="sk-rect sk-rect1"></div>
                                                    <div class="sk-rect sk-rect2"></div>
                                                    <div class="sk-rect sk-rect3"></div>
                                                    <div class="sk-rect sk-rect4"></div>
                                                    <div class="sk-rect sk-rect5"></div>
                                                </div>
                                            </div>
                                            <span id="span-total-lunch" class="d-none cafeteria-total">$0</span>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-4 col-form-label">Refrigerio:</label>
                                        <div class="col-8 col-form-label">
                                            <div class="row">
                                                <div class="sk-wave d-none loader">
                                                    <div class="sk-rect sk-rect1"></div>
                                                    <div class="sk-rect sk-rect2"></div>
                                                    <div class="sk-rect sk-rect3"></div>
                                                    <div class="sk-rect sk-rect4"></div>
                                                    <div class="sk-rect sk-rect5"></div>
                                                </div>
                                            </div>
                                            <span id="span-total-snack" class="d-none cafeteria-total">$0</span>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-4 col-form-label">Trasnocho:</label>
                                        <div class="col-8 col-form-label">
                                            <div class="row">
                                                <div class="sk-wave d-none loader">
                                                    <div class="sk-rect sk-rect1"></div>
                                                    <div class="sk-rect sk-rect2"></div>
                                                    <div class="sk-rect sk-rect3"></div>
                                                    <div class="sk-rect sk-rect4"></div>
                                                    <div class="sk-rect sk-rect5"></div>
                                                </div>
                                            </div>
                                            <span id="span-total-dinner" class="d-none cafeteria-total">$0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <FullCalendar :options="calendarOptions" />
                <div class="my-2" id="container-legend">
                    <p class="text-muted mb-0" v-if="can('menu-create')">
                        <i class="fa fa-info-circle text-info"></i> Haga clic en una fecha para crear un nuevo menú
                    </p>
                    <p class="text-muted mb-0" v-if="can('menu-edit')">
                        <i class="fa fa-info-circle text-info"></i> Puede modificar un menú haciendo clic el el mismo
                    </p>
                </div>
            </div>
        </div>

        <!-- Create Menu-->
        <div v-if="can('menu-create')" class="modal fade" id="modal-save" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Crear menú | Fecha: <span id="span-date"></span></h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-create">
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <div class="form-group row">
                                        <label for="type" class="col-md-4 col-form-label required">Tipo</label>
                                        <div class="col-md-8 col-form-label">
                                            <select v-model="formCreate.type" name="type" id="type" class="form-control">
                                                <option value="" selected>Seleccione...</option>
                                                <option v-for="type in types" v-if="type.name !== 'Desayuno'" :key="type.id" :value="type.id" v-text="type.name"></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="required">Descripción</label>
                                        <input v-model="formCreate.description" id="description" name="description" type="text" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="price" class="required">Precio</label>
                                        <input v-model="formCreate.price" id="price" name="price" type="number" class="form-control">
                                    </div>
                                    <input v-model="formCreate.date" type="hidden" id="date" name="date">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-sm btn-success" id="btn-save" @click="createMenu">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Menu-->
        <div v-if="can('menu-edit')" class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Menú | Fecha: <span id="span-edit-date"></span></h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-edit">
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <div class="form-group row">
                                        <label for="edit-type" class="col-md-4 col-form-label required">Tipo</label>
                                        <div class="col-md-8 col-form-label">
                                            <select v-model="formEdit.type" name="type" id="edit-type" class="form-control" disabled>
                                                <option v-for="type in types" :key="type.id" :value="type.id" v-text="type.name"></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-description" class="required">Descripción</label>
                                        <input v-model="formEdit.description" id="edit-description" name="description" type="text" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-price" class="required">Precio</label>
                                        <input v-model="formEdit.price" id="edit-price" name="price" type="number" class="form-control">
                                    </div>
                                    <input v-model="formEdit.date" type="hidden" id="edit-date" name="date" disabled>
                                </div>
                            </div>
                        </form>
                        <div id="container-menu-orders">
                            <hr>
                            <h5 class="mx-2">Pedidos: <small class="badge badge-pill badge-info" id="span-total-orders"></small></h5>
                            <ul class="px-4"></ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-sm btn-warning" id="btn-edit" @click="editMenu">Modificar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Max Order Hour-->
        <div v-if="can('max-order-time-edit')" class="modal fade" id="modal-max-order-time" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Hora máxima de pedidos</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-max-order-time" class="form-horizontal">
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <div class="form-group row" v-for="type in types">
                                        <label :for="type.id" class="col-12 col-sm-3 col-form-label">{{ type.name }}:</label>
                                        <div class="col-12 col-sm-9">
                                            <input class="form-control" :name="type.name" :id="type.id" type="time" :value="type.max_order_time" @change="setTypeMaxOrderTime($event)" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-sm btn-warning" id="btn-edit-max-order-time" @click="editMaxOrderTime()">Modificar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import FullCalendar from '@fullcalendar/vue'
    import dayGridPlugin from '@fullcalendar/daygrid'
    import interactionPlugin from '@fullcalendar/interaction'
    import bootstrapPlugin from '@fullcalendar/bootstrap'
    import moment from "moment";
    import 'moment/locale/es'
    import Tooltip from "bootstrap/js/src/tooltip"
    import * as helper from '../../../../public/js/vue-helper.js'

    export default {
        components: {
            FullCalendar // make the <FullCalendar> tag available
        },
        props: [
            'types',
            'weeks',
            'permissions',
        ],
        data() {
            return {
                moment: moment,
                calendarOptions: {
                    themeSystem: 'bootstrap',
                    plugins: [ dayGridPlugin, interactionPlugin, bootstrapPlugin ],
                    initialView: 'dayGridMonth',
                    dateClick: this.handleDateClick,
                    eventClick: this.handleEventClick,
                    events: [],
                    editable: false,
                    selectable: false,
                    headerToolbar: {
                        left: '',
                        center: 'title',
                        right: 'prev,today,next'
                    },
                    buttonText: {
                        today: 'Hoy',
                    },
                    locale: 'es',
                    eventDidMount: function(info) {
                        new Tooltip(info.el, {
                            title: info.event.extendedProps.description + ' ($' + new Intl.NumberFormat("de-DE").format(info.event.extendedProps.price) + ')',
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body'
                        });
                    },
                },
                formCreate: new Form({
                    type: "",
                    description: null,
                    price: null,
                    date: null,
                }),
                formEdit: new Form({
                    type: null,
                    id: null,
                    description: null,
                    price: null,
                }),
                formMaxOrderTimeEdit: new Form({
                    Desayuno: this.types[0].max_order_time,
                    Almuerzo: this.types[1].max_order_time,
                    Refrigerio: this.types[2].max_order_time,
                    Trasnocho: this.types[3].max_order_time,
                }),
            }
        },
        created() {
            this.getMenus();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            handleDateClick: function(arg) {
                helper.VUE_ResetValidations();
                if(!this.can('menu-create')) { return; }

                let date = moment(arg.dateStr);
                if(date.isBefore(moment().format("YYYY-MM-DD"))) { return; } // If it is before today

                let formatted_date = moment(arg.dateStr).format("DD/MM/YYYY");
                this.formCreate.date = arg.dateStr;
                $('#span-date').text(formatted_date);
                $('#modal-save').modal('show');
            },
            handleEventClick: function(arg) {
                helper.VUE_ResetValidations();
                $('#container-menu-orders ul').html('');

                if(!this.can('menu-edit')) { return; }

                let date = moment(arg.event.startStr);

                if(date.isBefore(moment().format("YYYY-MM-DD"))) { // If it is before today
                    $('#edit-description').prop('disabled', true);
                    $('#edit-price').prop('disabled', true);
                    $('#btn-edit').addClass('d-none');
                } else {
                    $('#edit-description').prop('disabled', false);
                    $('#edit-price').prop('disabled', false);
                    $('#btn-edit').removeClass('d-none');
                }

                let event = arg.event.extendedProps;

                this.formEdit.id = event.menu_id;
                this.formEdit.type = event.type;
                this.formEdit.description = event.description;
                this.formEdit.price = event.price;

                let formatted_date = moment(event.menu_date).format("DD/MM/YYYY");

                $('#span-edit-date').text(formatted_date);

                $.each(event.orders, function (i, order) {
                    let total = '$' + new Intl.NumberFormat("de-DE").format(order.total);
                    let li = '<li>' + order.user + ' ' + (order.observations != "" ? '(' + order.observations + ')' : '') + ' - Cantidad: ' + order.quantity + ' | ' + total + '</li>';
                    $('#container-menu-orders ul').append(li);
                });

                if(event.orders.length === 0) {
                    $('#container-menu-orders ul').append('No hay pedidos.');
                }

                $('#span-total-orders').text(event.orders.length);

                $('#modal-edit').modal('show');
            },
            handleMaxOrderTimeClick: function(arg) {
                helper.VUE_ResetValidations();
                $('#modal-max-order-time').modal('show');
            },
            getMenus: function() {
                axios.get(route('cafeteria.get_menus')).then(response => {
                    this.calendarOptions.events = response.data
                });
            },
            createMenu() {
                helper.VUE_ResetValidations();
                helper.VUE_DisableModalActionButtons();

                this.formCreate.post("/cafeteria/saveMenu").then((response) => {
                    helper.VUE_EnableModalActionButtons();

                    let res = response.data;

                    if(res.exists) {
                        SwalGB.fire({
                            title: '¡Atención!',
                            text: 'Ya existe un menú creado de el tipo seleccionado para este día',
                            icon: 'warning',
                            showCancelButton: false,
                            confirmButtonText: 'OK',
                            allowOutsideClick: true,
                        });

                        return;
                    }

                    // Clear form imputs
                    this.formCreate.description = "";
                    this.formCreate.price = "";

                    helper.VUE_ResetModalForm("#form-create");
                    this.getMenus();

                    Toast.fire({
                        icon: "success",
                        title: "¡Menú creado exitosamente!",
                    });

                }).catch((res) => {
                    helper.VUE_CallBackErrors(res.response);
                });
            },
            editMenu() {
                helper.VUE_ResetValidations();
                helper.VUE_DisableModalActionButtons();

                this.formEdit.post("/cafeteria/editMenu").then((response) => {
                    // Clear form imputs
                    this.formEdit.description = "";
                    this.formEdit.price = "";

                    helper.VUE_ResetModalForm("#form-edit");
                    this.getMenus();

                    Toast.fire({
                        icon: "success",
                        title: "¡Menú modificado exitosamente!",
                    });

                    $('#modal-edit').modal('hide');
                }).catch((res) => {
                    helper.VUE_CallBackErrors(res.response);
                });
            },
            editMaxOrderTime() {
                helper.VUE_ResetValidations();
                helper.VUE_DisableModalActionButtons();

                this.formMaxOrderTimeEdit.post("/cafeteria/editMaxOrderTime").then((response) => {
                    Toast.fire({
                        icon: "success",
                        title: "¡Horario modificado exitosamente!",
                    });

                    $('#modal-max-order-time').modal('hide');
                }).catch((res) => {
                    helper.VUE_CallBackErrors(res.response);
                });
            },
            setTypeMaxOrderTime(event) {
                let type = parseInt(event.target.id);
                let time = event.target.value;

                switch (type) {
                    case 1:
                        this.formMaxOrderTimeEdit.Desayuno = time;
                    break;

                    case 2:
                        this.formMaxOrderTimeEdit.Almuerzo = time;
                    break;

                    case 3:
                        this.formMaxOrderTimeEdit.Refrigerio = time;
                    break;

                    case 4:
                        this.formMaxOrderTimeEdit.Trasnocho = time;
                    break;
                }
            },
            getWeekCafeteriaSales(event) {
                if(event.target.value == "") {
                    $('.container-totals').addClass('d-none');
                    return;
                }

                let split = event.target.value.split('|');
                let start = split[0];
                let end = split[1];

                $('.loader').removeClass('d-none');
                $('.cafeteria-total').addClass('d-none');

                axios.post(
                        route('cafeteria.get_week_total_sales'),
                        {start: start, end: end}
                    ).then(response => {
                    $('.container-totals').removeClass('d-none');
                    let totals = response.data.totals;

                    $.each(totals, function (i, total) {
                        switch (parseInt(i)) {
                            case 1:
                                $('#span-total-breakfast').text('$' + new Intl.NumberFormat("de-DE").format(total));
                            break;

                            case 2:
                                $('#span-total-lunch').text('$' + new Intl.NumberFormat("de-DE").format(total));
                            break;

                            case 3:
                                $('#span-total-snack').text('$' + new Intl.NumberFormat("de-DE").format(total));
                            break;

                            case 4:
                                $('#span-total-dinner').text('$' + new Intl.NumberFormat("de-DE").format(total));
                            break;
                        }

                        $('.loader').addClass('d-none');
                        $('.cafeteria-total').removeClass('d-none');
                    });
                });
            },
        },
    }
</script>

<style scoped>
    .sk-wave {
        margin: 0 !important;
        height: auto !important;
    }
</style>
