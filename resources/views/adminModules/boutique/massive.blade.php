@extends('layouts.app')

@section('pageTitle', 'Venta Masiva')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Boutique</li>
    <li class="breadcrumb-item active">Venta Masiva</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Venta Masiva</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right"></div>
            </div>
            <div class="card-body row">
                <div class="col-12 col-lg-8 b-r-1" style="border-color: rgb(76, 79, 84);">
                    <table class="table table-hover" id="products-table" style="width:100%">
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Imagen</th>
                            <th>Precio Unitario</th>
                            <th>Precio Mayorista</th>
                            <th>Cantidad</th>
                            <th>Categoría</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="col-12 col-lg-4">
                    <h5><i class="fa fa-shopping-cart"></i> Detalles de la Venta</h5>
                    <hr>
                    <h6 class="text-muted" id="text-info-shopping-cart">Para agregar al carrito, primero seleccione el/los producto/s.</h6>
                    <div id="container-sells">
                        <div id="container-sells-products"></div>
                        <div id="container-sell-to" class="d-none">
                            <hr>
                            <div id="container-to">
                                <label for="" class="col-12 row">Vender a:</label>
                                <div class="form-group row">
                                    <div class="col-12 row">
                                        <div class="col-4">
                                            <label>
                                                <input type="radio" name="selected_user" id="for-model" value="models" class="radio-for">
                                                <span class="label-text">Modelos</span>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label>
                                                <input type="radio" name="selected_user" id="for-user" value="users" class="radio-for">
                                                <span class="label-text">Otro cargo</span>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label>
                                                <input type="radio" name="selected_user" id="for-satellite" value="satellite" class="radio-for">
                                                <span class="label-text">Satélite</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="d-none" id="container-for-models">
                                        <hr>
                                        <label for="select-for-model">Modelos</label>
                                        <select class="col-12 form-control" name="user_id" id="select-for-model">
                                            <option value="">Seleccione...</option>
                                            @foreach($models AS $model)
                                                <option value="{{ $model->id }}">{{ $model->nick }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-none" id="container-for-users">
                                        <hr>
                                        <label for="select-for-user">Usuarios</label>
                                        <select class="col-12 form-control" name="user_id" id="select-for-user">
                                            <option value="">Seleccione...</option>
                                            @foreach($users AS $user)
                                                <option value="{{ $user->id }}">{{ $user->roleUserShortName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-none" id="container-for-satellites">
                                        <hr>
                                        <label for="select-for-owner">Propietarios</label>
                                        <select class="col-12 form-control" name="owner_id" id="select-for-owner">
                                            <option value="">Seleccione...</option>
                                            @foreach($owners AS $owner)
                                                <option value="{{ $owner->id }}">{{ $owner->owner }}</option>
                                            @endforeach
                                        </select>
                                        <div class="">
                                            <div class="form-check checkbox mt-2">
                                                <input class="form-check-input" id="wholesaler-price" type="checkbox" value="1">
                                                <label class="form-check-label" for="wholesaler-price">Precio Mayorista</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-12 text-right">
                                <h5 class="text-muted">Total: <span id="span-total" class="text-success text-bold">$0</span></h5>
                            </div>
                        </div>
                        <div id="container-do-sell" class="text-right d-none">
                            <hr>
                            <a class="btn btn-success btn-sm" id="btn-sell">
                                <i class="fa fa-dollar-sign"></i> Vender
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection @push('scripts')
    <script>
        let shopping_cart = [];
        let locations = [];
        let category_id = null;
        let stock = null;
        let location_id = null;

        let no_stock = false;
        let table = null;

        $(document).ready(function () {
            collapseMenu();

            if ($("#products-table").length > 0) {
                new Vue({
                    el: "#products-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#products-table").DataTable({
                                select: true,
                                processing: true,
                                serverSide: false,
                                pageLength: 25,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('boutique.get_products') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                    data: function(data) {
                                        data.category_id = category_id;
                                        data.stock = stock;
                                        data.location_id = location_id;
                                    },
                                    beforeSend: function() {
                                        $('html,body').animate({
                                            scrollTop: $('html').offset().top
                                        }, 200);

                                        $('#global-spinner').removeClass('d-none');
                                    },
                                    complete: function (res) {
                                        let total = res.responseJSON.total;
                                        $('#total-inventory').text(total);

                                        $('html,body').animate({
                                            scrollTop: $('html').offset().top
                                        }, 1);

                                        $('#global-spinner').addClass('d-none');
                                    },
                                },
                                columns: [
                                    { data: "name" },
                                    { data: "image" },
                                    { data: "unit_price" },
                                    { data: "wholesaler_price" },
                                    { data: "quantity" },
                                    { data: "category_name" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [1, 4],
                                        orderable: false,
                                    },
                                ],
                                fnDrawCallback: function() {
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        },
                    },
                });
            }

            table
                .on('select', function (e, dt, type, indexes) {
                    let row_data = table.rows(indexes).data().toArray();
                    let product_id = row_data[0].id;
                    getProduct(product_id);
                })
                .on('deselect', function (e, dt, type, indexes) {
                    return;
                });

            $('#products-table tbody').on('click', 'tr', function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                } else {
                    table.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                }
            });
        });

        // CONTROL ACTIONS

        $('.radio-for').on('click', function () {
            $('#container-for-models').addClass('d-none');
            $('#container-for-users').addClass('d-none');
            $('#container-for-satellites').addClass('d-none');
            $('#wholesaler-price').prop('checked', false);

            let selected = $(this).val();

            if(selected === 'users')
            {
                $('#container-for-users').removeClass('d-none');
                $('#select-for-user').prop('disabled', false);
                $('#select-for-model').prop('disabled', true);
            }
            else if (selected === 'models')
            {
                $('#container-for-models').removeClass('d-none');
                $('#select-for-model').prop('disabled', false);
                $('#select-for-user').prop('disabled', true);
            }
            else if (selected === 'satellite')
            {
                $('#container-for-satellites').removeClass('d-none');
                $('#select-for-model').prop('disabled', false);
                $('#select-for-user').prop('disabled', true);
            }

            calculateTotal();
        });

        $('#wholesaler-price').on('change', function () {
            calculateTotal();
        });

        $('#btn-sell').on('click', function () {
            let flag = true;
            let buyer_id = null;
            let wholesaler_price = false;

            if(shopping_cart.length === 0) {
                Toast.fire({
                    icon: "warning",
                    title: "Debe seleccionar al menos un producto.",
                });

                return;
            }

            $.each(shopping_cart, function (i, item) {
                if(item.quantity <= 0) {
                    flag = false;
                }
            });

            if(!flag) {
                Toast.fire({
                    icon: "warning",
                    title: "Debe seleccionar la locación y la cantidad de cada producto en el carrito.",
                    timer: 5000,
                });

                return;
            }

            let sale_to = $(".radio-for:checked").val();

            if(sale_to === undefined) {
                Toast.fire({
                    icon: "warning",
                    title: "Debe seleccionar a quién se le venderá los productos.",
                    timer: 5000,
                });

                return;
            }

            if(sale_to === 'users')
            {
                let selected_user = $('#select-for-user').val();

                if(selected_user == "") {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar el usuario a quien se le venderán los productos.",
                        timer: 5000,
                    });

                    return;
                } else {
                    buyer_id = selected_user;
                }
            }
            else if (sale_to === 'models')
            {
                let selected_model = $('#select-for-model').val();

                if(selected_model == "") {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar la modelo a quien se le venderán los productos.",
                        timer: 5000,
                    });

                    return;
                } else {
                    buyer_id = selected_model;
                }
            }
            else if (sale_to === 'satellite')
            {
                let selected_owner = $('#select-for-owner').val();

                if(selected_owner == "") {
                    Toast.fire({
                        icon: "warning",
                        title: "Debe seleccionar el propietario a quien se le venderán los productos.",
                        timer: 5000,
                    });

                    return;
                } else {
                    wholesaler_price = $('#wholesaler-price').is(':checked');
                    buyer_id = selected_owner;
                }
            }

            let info = {
                sale_to: sale_to,
                buyer_id: buyer_id,
                wholesaler_price: wholesaler_price,
            };

            SwalGB.fire({
                title: '¿Está seguro que desea realizar la venta?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('boutique.massive_sell') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            shopping_cart,
                            info
                        },
                    }).done(function (res) {
                        if(res.success) {
                            Toast.fire({
                                icon: "success",
                                title: "Venta registrada exitosamente",
                            });

                            clearShoppingCart();
                            table.ajax.reload();
                        } else {
                            switch (res.code) {
                                case 1: // No owner assigned to selected user
                                    Toast.fire({
                                        icon: "error",
                                        title: res.msg,
                                        timer: 8000,
                                    });
                                break;

                                case 2: // User is blocked to buy this amount
                                    Toast.fire({
                                        icon: "error",
                                        title: res.msg,
                                        timer: 8000,
                                    });
                                break;

                                case 3: // No enough products in the location
                                    Toast.fire({
                                        icon: "error",
                                        title: res.msg,
                                        timer: 8000,
                                    });

                                    $('#modal-sell').modal('hide');
                                    EnableModalActionButtons();
                                    table.ajax.reload();

                                break;

                                default: // Can't sell the product
                                    Toast.fire({
                                        icon: "error",
                                        title: 'No se ha podido registrar la venta. Por favor, intenta mas tarde.',
                                    });
                                    break;
                            }
                        }
                    }).fail(function (res) {
                        let json = res.responseJSON;
                        if (!json.success) {
                            Toast.fire({
                                icon: "error",
                                title: json.msg,
                                timer: 10000,
                            });
                        }
                    });
                }
            });
        });

        function showShoppingCart()
        {
            //$('#container-sells-products').html('');
            $('#text-info-shopping-cart').addClass('d-none');

            if(shopping_cart.length === 0) {
                $('#text-info-shopping-cart').removeClass('d-none');
                $('#container-sell-to').addClass('d-none');
                $('#container-do-sell').addClass('d-none');
            } else {
                $('#container-sell-to').removeClass('d-none');
                $('#container-do-sell').removeClass('d-none');
            }
        }

        function calculateTotal()
        {
            let total = 0;
            let wholesaler_price = $('#wholesaler-price').is(':checked');
            let sale_to = $(".radio-for:checked").val();

            $.each(shopping_cart, function (i, product) {
                if(sale_to === 'satellite' && wholesaler_price) {
                    total = total + (product.wholesaler_price * product.quantity);
                } else {
                    total = total + (product.price * product.quantity);
                }
            });

            $('#span-total').text('$' + new Intl.NumberFormat("de-DE").format(total));
        }

        function addToShoppingCart(data)
        {
            let exists = false;

            $.each(shopping_cart, function (i, product) {
                if(data.id === product.id){
                    exists = true;
                }
            });

            if(!exists) {
                let product = {
                    id: data.id,
                    name: data.name,
                    price: data.price,
                    price_format: data.price_format,
                    wholesaler_price: data.wholesaler_price,
                    wholesaler_price_format: data.wholesaler_price_format,
                    locations_inventory: data.locations_inventory,
                    location_id: null,
                    quantity: 0,
                };

                shopping_cart.push(product);

                let item_locations = '';

                $.each(product.locations_inventory, function (i, location) {
                    if(location.quantity > 0) {
                        let options = '';

                        for (let j = 1; j <= location.quantity; j++) {
                            options += '<option value="' + j +'">' + j +'</option>';
                        }

                        item_locations +=
                            '<div class="form-group" id="container-product-' + product.id + '">' +
                            '   <div class="row">' +
                            '       <div class="form-check col-5 col-sm-3 col-md-3 col-lg-6">' +
                            '           <input id="product-' + product.id + '-location-' + location.id + '" type="radio" name="product-' + product.id + '" value="' + location.id + '" data-productid="' + product.id + '" class="checkbox-product-location mr-1" onchange="showSelectQuantity(' + product.id + ', ' + location.id + ')"> ' +
                            '           <label for="product-' + product.id + '-location-' + location.id + '" class="form-check-label mr-1">' + location.name + '</label>' +
                            '       </div>' +
                            '       <select id="select-product-' + product.id + '-location-' + location.id + '" class="select-quantity form-control form-control-sm col-6 col-sm-2 col-md-3 col-lg-3 d-none" onchange="updateQuantity(' + product.id + ', ' + location.id + ')">' +
                            '           ' + options +
                            '       </select>' +
                            '   </div>' +
                            '</div>';
                    }
                });

                let item =
                    '<div class="card card-accent-success" id="container-product-' + product.id + '">' +
                    '    <div class="card-header">' +
                    '       ' + product.name + ' | <span class="text-success">' + product.price_format + '</span>' +
                    '       <div class="card-header-actions">' +
                    '           <i class="remove-product-cart fas fa-times text-danger" onclick="removeFromShoppingCart(' + product.id + ')" title="Quitar"></i>' +
                    '       </div>' +
                    '   </div>' +
                    '   <div class="card-body p-2 px-3">' + item_locations + '</div>' +
                    '</div>';

                $('#container-sells-products').append(item);
                $('#container-sells').removeClass('d-none');
            }
        }

        function removeFromShoppingCart(product_id)
        {
            let index_to_delete = null;

            $.each(shopping_cart, function (i, product) {
                if(product_id === product.id){
                    index_to_delete = i;
                }
            });

            if(index_to_delete != null){
                shopping_cart.splice(index_to_delete, 1);
                $('#container-product-' + product_id).remove();
            }

            calculateTotal();

            if(shopping_cart.length === 0) {
                clearShoppingCart();
            }
        }

        function clearShoppingCart()
        {
            shopping_cart = [];

            $('#container-sells-products').html('');
            $('#text-info-shopping-cart').removeClass('d-none');
            $('#container-sells').addClass('d-none');
            $('#container-sell-to').addClass('d-none');
            $('#container-do-sell').addClass('d-none');
            calculateTotal();
        }

        function getProduct(id)
        {
            $.ajax({
                url: '{{ route('boutique.get_product') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            }).done(function (res) {
                let product_id = res.id;
                let product_name = res.name;
                let product_price = res.unit_price;
                let product_price_format = res.unit_price_format;
                let product_wholesaler_price = res.wholesaler_price;
                let product_wholesaler_price_format = res.wholesaler_price_format;
                let product_locations_inventory = res.locations_inventory;

                let data = {
                    id: product_id,
                    name: product_name,
                    price: product_price,
                    price_format: product_price_format,
                    wholesaler_price: product_wholesaler_price,
                    wholesaler_price_format: product_wholesaler_price_format,
                    locations_inventory: product_locations_inventory,
                    quantity: 0,
                };

                addToShoppingCart(data);
                showShoppingCart();
            }).fail(function (res) {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error al obtener el producto. Por favor, intente mas tarde.",
                });
            });
        }

        function showSelectQuantity(product_id, location_id)
        {
            $.each(shopping_cart, function (i, product) {
                if(parseInt(product_id) === parseInt(product.id)){
                    shopping_cart[i].quantity = 1;
                    shopping_cart[i].location_id = location_id;
                }
            });

            $('#container-product-' + product_id + ' .select-quantity').addClass('d-none');
            $('#select-product-' + product_id + '-location-' + location_id).removeClass('d-none');

            calculateTotal();
        }

        function updateQuantity(product_id, location_id)
        {
            let quantity = $('#select-product-' + product_id + '-location-' + location_id).val();

            $.each(shopping_cart, function (i, product) {
                if(parseInt(product_id) === parseInt(product.id)){
                    shopping_cart[i].quantity = parseInt(quantity);
                    shopping_cart[i].location_id = parseInt(location_id);
                }
            });

            calculateTotal();
        }
    </script>
@endpush
