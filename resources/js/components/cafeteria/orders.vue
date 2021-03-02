<template>
    <div class="orders-calendar">
        <div class="alert alert-danger text-bold" v-if="user.setting_role_id === 11">
            Colocar en el CRON 'TodosLosDias' la función 'CRON_createDayCafeteriaTask' que está en el controlador'
        </div>

        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Pedidos Cafetería</span>
                </div>
            </div>
            <div class="card-body">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>

        <!-- Order -->
        <div class="modal fade" id="modal-order" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Realizar pedido de Cafetería</h4>
                    </div>
                    <div class="modal-body">
                        <form id="form-create">
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <div class="form-group row" v-if="user.setting_role_id === 11">
                                        <label for="" class="col-12 col-sm-4 col-form-label">Para:</label>
                                        <div class="col-12 col-sm-8 row">
                                            <div class="col-6">
                                                <label class="">
                                                    <input class="radio-order-for" type="radio" name="order_for" value="0" @change="orderFor($event)" checked>
                                                    <span class="label-text">Para mi</span>
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <label class="">
                                                    <input class="radio-order-for" type="radio" name="order_for" value="1" @change="orderFor($event)">
                                                    <span class="label-text">Para usuario</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row d-none" id="container-order-users">
                                        <label for="user" class="col-12 col-sm-4 col-form-label">Usuario:</label>
                                        <div class="col-12 col-sm-8">
                                            <select name="user" id="user" class="form-control" @change="" v-model="formOrder.user_id">
                                                <option value="">Seleccione el usuario...</option>
                                                <option v-for="user in users" v-text="user.show_name" :id="user.id" :value="user.id"></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="date" class="col-12 col-sm-4 col-form-label">Fecha:</label>
                                        <div class="col-12 col-sm-8">
                                            <input v-model="formOrder.date" type="date" id="date" name="date" class="form-control" @change="getMenu(selectedType)">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="" class="col-12 col-sm-4 col-form-label">Tipo:</label>
                                        <div class="col-form-label col-12 col-sm-8 row">
                                            <div v-for="type in types" class="col-12 menu-type" :id="'menu-type-' +  type.id">
<!--                                                <p>{{ moment(moment().format('YYYY-MM-DD') + ' ' + type.max_order_time, 'YYYY-MM-DD HH:mm:ss').isAfter(currentTime) }}</p>-->
                                                <label>
                                                    <input class="radio-menu-type" type="radio" name="menu_type" :key="type.id" :value="type.id" :id="type.id" @change="getMenu(type.id)">
                                                    <span class="label-text" v-text="type.name"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div id="container-breakfast" class="d-none">
                                        <div class="accordion" id="accordion" role="tablist">
                                            <div class="card mb-0 breakfast-category" v-for="category in breakfast_categories">
                                                <div data-toggle="collapse" :id="category.id" :href="'#' + category.name" aria-expanded="true" :aria-controls="category.id" role="tab" class="card-header row">
                                                    <span class="mb-0 col-11" v-text="category.name"></span>
                                                    <span class="col-1 span-badge d-none" :id="'span-badge-' + category.id"><i class="fa fa-check-circle text-success"></i></span>
                                                </div>
                                                <div class="collapse" :id="category.name" role="tabpanel" :aria-labelledby="category.name" data-parent="#accordion" style="">
                                                    <div class="card-body">
                                                       <div class="container-breakfast-type py-2 row mb-1" v-for="breakfast_type in category.breakfast_types">
                                                           <div class="col-9">
                                                               <input type="checkbox" :data-categoryid="category.id" class="checkbox-type" :data-id="breakfast_type.id" :id="'input-' + breakfast_type.id" @change="breakfastType(breakfast_type.id)">
                                                               <label :id="'input-label-' + breakfast_type.id" :for="'input-' + breakfast_type.id" v-text="breakfast_type.name"></label>
                                                               <small class="text-muted">($<span class="text-muted" :id="'breakfast-type-price-' + breakfast_type.id">{{ breakfast_type.price }}</span>)</small>
                                                           </div>
                                                           <div class="col-3 pull-right text-right">
                                                               <input class="form-control input-sm input-quantity d-none" type="number" min="1" value="1" :id="'quantity-type-' + breakfast_type.id" @change="calculateBreakfastTypeTotal(breakfast_type.id)">
                                                               <small class="text-muted text-bold label-total-quantity d-none" :id="'span-total-'+ breakfast_type.id">Total: $<span class="text-muted">{{ breakfast_type.price }}</span></small>
                                                           </div>
                                                       </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="form-group row">
                                                <label for="details" class="col-12 col-sm-4 col-form-label">Detalles</label>
                                                <div class="col-12 col-sm-8">
                                                    <textarea v-model="formOrder.details" class="form-control" name="details" id="details" rows="3" placeholder="Desayuno..." disabled></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div id="container-menu-description" class="d-none">
                                        <div class="form-group row mb-0">
                                            <label class="col-12 col-sm-4 col-form-label">Descripción:</label>
                                            <div class="col-form-label col-12 col-sm-8 d-flex" id="span-menu-description">
                                                <div class="sk-wave d-none loader">
                                                    <div class="sk-rect sk-rect1"></div>
                                                    <div class="sk-rect sk-rect2"></div>
                                                    <div class="sk-rect sk-rect3"></div>
                                                    <div class="sk-rect sk-rect4"></div>
                                                    <div class="sk-rect sk-rect5"></div>
                                                </div>
                                                <h6></h6>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-12 col-sm-4 col-form-label">Precio Unitario:</label>
                                            <div class="col-form-label col-12 col-sm-8 d-flex" id="span-menu-unitary-price">
                                                <div class="sk-wave d-none loader">
                                                    <div class="sk-rect sk-rect1"></div>
                                                    <div class="sk-rect sk-rect2"></div>
                                                    <div class="sk-rect sk-rect3"></div>
                                                    <div class="sk-rect sk-rect4"></div>
                                                    <div class="sk-rect sk-rect5"></div>
                                                </div>
                                                <h6></h6>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="form-group row">
                                        <label for="observations" class="col-12 col-sm-4 col-form-label">Observaciones</label>
                                        <div class="col-12 col-sm-8">
                                            <textarea v-model="formOrder.observations" class="form-control" name="observations" id="observations" rows="3" placeholder="Sin ensalada, con pollo, etc.."></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="quantity" class="col-12 col-sm-4 col-form-label">Cantidad:</label>
                                        <div class="col-12 col-sm-4">
                                            <input type="number" v-model="formOrder.quantity" class="form-control" id="quantity" min="1" @change="calculateTotal()">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-4 col-form-label"><h5>Total:</h5></label>
                                        <div class="col-form-label col-12 col-sm-8 text-success" id="span-menu-total">
                                            <div class="sk-wave d-none loader">
                                                <div class="sk-rect sk-rect1"></div>
                                                <div class="sk-rect sk-rect2"></div>
                                                <div class="sk-rect sk-rect3"></div>
                                                <div class="sk-rect sk-rect4"></div>
                                                <div class="sk-rect sk-rect5"></div>
                                            </div>
                                            <h5 class="text-bold">$0</h5>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group row">
                                        <label for="" class="col-12 col-sm-4 col-form-label">Locación:</label>
                                        <div class="col-form-label col-12 col-sm-8 row">
                                            <label v-for="location in locations" class="col-12">
                                                <input v-model="formOrder.location_id" type="radio" name="location" :key="location.id" :value="location.id" :id="'location-' + location.id">
                                                <span class="label-text" v-text="location.name"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-sm btn-success" id="btn-order" @click="order">Ordenar</button>
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
    import Tooltip from "bootstrap/js/src/tooltip";

    export default {
        components: {
            FullCalendar // make the <FullCalendar> tag available
        },
        props: [
            'types',
            'locations',
            'user',
            'breakfast_categories',
            'users',
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
                            title: info.event.extendedProps.description + ' <br><br>Cantidad: ' + info.event.extendedProps.quantity + ' | $' + new Intl.NumberFormat("de-DE").format(info.event.extendedProps.total) + ' ' + (info.event.extendedProps.observations != null ? ' <br><br>Observaciones: ' + info.event.extendedProps.observations : '') + '',
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body',
                            html: true
                        });
                    },
                },
                //types: [],
                formOrder: new Form({
                    menu_id: null,
                    quantity: 1,
                    price: null,
                    total: null,
                    observations: null,
                    details: null,
                    date: null,
                    location_id: null,
                    user_id: this.user.id,
                }),
                selectedType: null,
                orderForUsers: 0,
                isValid: false,
                breakfastTypes: [],
                breakfastDescription: [],
                currentTime: moment().format("YYYY-MM-DD HH:mm:ss"),
                selectedMenuType: null,
            }
        },
        created() {
            this.getOrders();
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            handleDateClick: function(arg) {
                let date = moment(arg.dateStr);
                if(date.isBefore(moment().format("YYYY-MM-DD"))) { return; } // If it is before today

                $('.menu-type').css('display', 'block');

                let currentTime = moment().format("YYYY-MM-DD HH:mm:ss");

                $.each(this.types, function (i, type) {
                    let type_id = type.id;
                    let max_order_time = type.max_order_time;

                    let is_same_or_before = moment(arg.dateStr + ' ' + max_order_time, 'YYYY-MM-DD HH:mm:ss').isSameOrBefore(currentTime);

                    if(is_same_or_before) {
                        $('#menu-type-' + type_id).css('display', 'none');
                    }
                });

                $('.radio-menu-type').prop('checked', false);
                $('#span-menu-description h6').text('');
                $('#span-menu-unitary-price h6').text('');
                $('#span-menu-total h5').text('$0');
                this.formOrder.details = '';
                this.formOrder.quantity = 1;
                this.formOrder.date = arg.dateStr;
                this.selectedType = null;

                if(this.user.setting_role_id === 11) {
                    $('#date').prop('disabled', false);
                } else {
                    $('#date').prop('disabled', true);
                }

                //let formatted_date = moment(arg.dateStr).format("DD/MM/YYYY");
                //$('#span-date').text(formatted_date);
                $('#modal-order').modal('show');
            },
            handleEventClick: function(arg) {},
            getOrders: function() {
                axios.get(route('cafeteria.get_orders')).then(response => {
                    this.calendarOptions.events = response.data
                });
            },
            order() {
                if(this.selectedType == null) {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar el tipo de menú",
                    });

                    return;
                }

                if(this.formOrder.menu_id == null) {
                    Toast.fire({
                        icon: "warning",
                        title: "No hay menú registrado para este día",
                    });

                    return;
                }

                if(this.formOrder.location_id == null) {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar la locación",
                    });

                    return;
                }

                if(this.formOrder.menu_id === 0 && $(".checkbox-type:checked").length === 0) {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar al menos un tipo de desayuno",
                    });

                    return;
                }

                if(!this.isValid) {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe completar el formulario correctamente",
                    });

                    return;
                }

                //this.formOrder.details = this.formOrder.details + (this.formOrder.observations != null ? ' - ' + this.formOrder.observations : '');

                SwalGB.fire({
                    title: '¡Confirmar!',
                    text: 'Estás por solicitar este pedido. ¿Estás seguro que deseas continuar?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                    cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                    confirmButtonText: 'Pedir',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        this.formOrder.post(route('cafeteria.save_order')).then((response) => {
                            let res = response.data;

                            if(res.exists) {
                                Toast.fire({
                                    icon: "info",
                                    title: "¡Ya tienes un pedido realizado de este tipo para este día!",
                                    timer: 3000,
                                });

                                return;
                            }

                            if(res.success) {
                                // Clear form imputs
                                this.formOrder.details = "";
                                this.formOrder.description = "";
                                this.formOrder.observations = "";
                                this.formOrder.price = "";

                                $.each($(".checkbox-type:checked"), function() {
                                    $(this).prop('checked', false);
                                });

                                $('.span-badge').addClass('d-none');
                                $('.input-quantity').addClass('d-none');
                                $('.label-total-quantity').addClass('d-none');
                                $('.collapse').collapse('hide');
                                $('#container-breakfast').addClass('d-none');

                                this.getOrders();

                                $('#modal-order').modal('hide');

                                Toast.fire({
                                    icon: "success",
                                    title: "¡Pedido creado exitosamente!",
                                });
                            } else {
                                switch (res.code) {
                                    case 1: // No owner assigned to selected model
                                        Toast.fire({
                                            icon: "error",
                                            title: res.msg,
                                            timer: 8000,
                                        });

                                    break;

                                    default: // Can't order
                                        Toast.fire({
                                            icon: "error",
                                            title: 'No se ha podido registrar el pedido. Por favor, intenta mas tarde.',
                                        });
                                    break;
                                }
                            }
                        }).catch((res) => {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error al registrar el pedido. Por favor, intente mas tarde.",
                            });
                        });
                    }
                });
            },
            getMenu(type) {
                if(type == null) { return; }

                $('#span-menu-description h6').text('');
                $('#span-menu-unitary-price h6').text('');
                $('#span-menu-total h5').text('');
                $('#container-breakfast').addClass('d-none');
                $('#container-menu-description').addClass('d-none');

                if(type === 1) {
                    $('#container-breakfast').removeClass('d-none');
                    $('#details').prop('disabled', true);
                    this.formOrder.menu_id = 0;
                    this.isValid = true;
                } else {
                    $('.loader').removeClass('d-none');
                    $('#container-menu-description').removeClass('d-none');
                    $('#details').prop('disabled', false);

                    axios.post(route('cafeteria.get_day_menu'), {
                        type: type,
                        date: this.formOrder.date
                    }).then((response) => {
                        let data = response.data;

                        if(!data.exists) {
                            Toast.fire({
                                icon: "warning",
                                title: "No hay menú registrado para este día.",
                            });

                            this.formOrder.price = 0;
                            this.formOrder.menu_id = null;
                            this.formOrder.total = 0;
                            this.formOrder.details = '';

                            $('#span-menu-description h6').text('-');
                            $('#span-menu-unitary-price h6').text('-');

                            this.calculateTotal();

                            return;
                        }

                        let menu = data.menu;

                        let menu_id = menu.id;
                        let description = menu.description;
                        let price = menu.price;

                        this.formOrder.price = price;
                        this.formOrder.total = price;
                        this.formOrder.menu_id = menu_id;
                        this.formOrder.details = '';

                        $('#span-menu-description h6').text(description);
                        $('#span-menu-unitary-price h6').text('$' + new Intl.NumberFormat("de-DE").format(price));

                        this.calculateTotal();
                        this.isValid = true;
                    }).catch((res) => {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                        });
                    }).then(() => {
                        $('.loader').addClass('d-none');
                    });
                }

                this.formOrder.description = "";
                this.selectedType = type;
                this.calculateTotalCost();
            },
            orderFor(event) {
                let order_for = parseInt(event.target.value);

                if(order_for === 1) { // for users
                    this.orderForUsers = 1;
                    //this.formOrder.user_id = 1;
                    $('#container-order-users').removeClass('d-none');
                } else {
                    this.orderForUsers = 0;
                    this.formOrder.user_id = this.user.id;
                    $('#container-order-users').addClass('d-none');
                }
            },
            breakfastType(id) {
                let checked = $('#input-' + id).is(':checked');
                let quantity = parseInt($('#quantity-type-' + id).val());
                let total = parseInt($('#breakfast-type-price-' + id).text());

                if(checked) {
                    $('#quantity-type-' + id).removeClass('d-none');
                    $('#span-total-' + id).removeClass('d-none');
                } else {
                    $('#quantity-type-' + id).addClass('d-none');
                    $('#span-total-' + id).addClass('d-none');
                    $('#quantity-type-' + id).val(1);
                    $('#span-total-' + id).val(1);
                }

                this.calculateTotalCost();
            },
            calculateTotal() {
                let total = this.formOrder.price * this.formOrder.quantity;

                if(this.formOrder.price === 0) {
                    total = 0;
                }

                this.formOrder.total = total;

                $('#span-menu-total h5').text('$' + new Intl.NumberFormat("de-DE").format(total));
            },
            calculateBreakfastTypeTotal(id) {
                let quantity = parseInt($('#quantity-type-' + id).val());
                let price = $('#breakfast-type-price-' + id).text();
                let total = price * quantity;
                $('#span-total-' + id).text('Total: $' + total);

                this.calculateTotalCost();
            },
            calculateTotalCost() {
                $('.span-badge').addClass('d-none');

                let description = '';
                let total = 0;

                $.each($(".checkbox-type:checked"), function() {
                    let breakfast_total = 0;

                    let id = $(this).data('id');
                    let quantity = parseInt($('#quantity-type-' + id).val());
                    let price = $('#breakfast-type-price-' + id).text();
                    let name = $('#input-label-' + id).text();
                    let category_id = $('#input-' + id).data('categoryid');

                    $('#span-badge-' + category_id).removeClass('d-none');

                    description += name + ' (' + quantity + ') | ';
                    breakfast_total = price * quantity;
                    total = total + breakfast_total;
                });

                $('#span-menu-total h5').text('$' + new Intl.NumberFormat("de-DE").format(total));
                this.formOrder.details = description.substring(0, description.length - 2);
                this.formOrder.price = total;

                this.calculateTotal();
            },
        },
    }
</script>

<style scoped>
    .sk-wave {
        margin: 0 !important;
        height: auto !important;
    }

    .breakfast-category {
        cursor: pointer !important;
    }

    .container-breakfast-type {
        border-bottom: 1px solid #3a3a3a;
        cursor: auto !important;
    }
</style>
