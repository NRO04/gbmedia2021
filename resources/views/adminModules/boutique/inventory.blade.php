@extends('layouts.app')

@section('pageTitle', 'Ingreso de Inventario')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Boutique</li>
    <li class="breadcrumb-item active">Ingreso de Inventario</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Ingreso de Inventario</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    @can('alarms')
                        <a class="btn btn-success btn-sm" href="{{ route('boutique.inventory_ingresses') }}">
                            <i class="fa fa-th-list"></i> &nbsp;Historial Ingresos Inventario
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <form id="formulario">
                    @csrf
                    <input type="hidden" name="totalrow" id="totalrow" value="">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <label for="A1" class="mb-0">Cambio Dolar A1</label>
                                </th>
                                <th>
                                    <label for="A2" class="mb-0">Total Trasporte Pago A2</label>
                                </th>
                                <th>
                                    <label for="A3" class="mb-0">Trasporte x Peso A3</label>
                                </th>
                                <th>
                                    <label for="A4" class="mb-0">Total Producto Pesos A4</label>
                                </th>
                                <th>
                                    <label for="A5" class="mb-0">Total Compra A5</label>
                                </th>
                                <th>
                                    <label for="A6" class="mb-0">Venta Total A6</label>
                                </th>
                                <th>
                                    <label for="A7" class="mb-0">Utilidad A7</label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input class="form-control" type="text" name="A1" id="A1" size="8" onkeyup="enable()">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="A2" id="A2" size="8" disabled onkeyup="calculate()">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="A3" id="A3" size="8" onfocus="this.blur()">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="A4" id="A4" size="8" onfocus="this.blur()">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="A5" id="A5" size="8" onfocus="this.blur()">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="A6" id="A6" size="8" onfocus="this.blur()">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="A7" id="A7" size="8" onfocus="this.blur()">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <hr>
                    <table class="table table-striped" name="mitabla" id="mitabla">
                        <thead>
                            <tr>
                                <th style="padding-right: 8px; font-size: x-small;">Productos</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Numero de Unidades">Nr Uni B1</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Precio por Unidad">Precio Uni B2</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Total Compra en Dólares">Total B3</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Cambio en Pesos">Cambio $ B4</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Trasporte Productos">Trasp Prod B5</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Precio Total Compra + Transporte">Precio T B6</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Precio por Unidad en Pesos">Precio x Uni B7</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Minimo % de Incremento">Min % Venta B8</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Aumento según % de Incremento">Aumento % B9</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Precio Sugerido por Unidad">$ Sug Uni B10</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Venta Total ">Venta T B11</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Precio Final por Unidad">$ Final Uni B12</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Precio Final Total">Precio Final T B13</th>
                                <th style="padding-right: 8px; font-size: x-small;" title="Precio Mayorista">Precio Mayorista</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <td>
                                <select class="form-control form-control-sm" style="margin-right: 8px;width:auto" name="producto[]" class="producto" id="1-select" onchange="getProduct(this.id)">
                                    <option value="">Seleccione...</option>
                                    @foreach($products AS $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B1" id="1_B1" size = "3" style="margin-right: 8px;" >
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B2" id="1_B2" size = "3" style="margin-right: 8px" onkeyup="calculate()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B3" id="1_B3" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B4" id="1_B4" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B5" id="1_B5" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B6" id="1_B6" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B7" id="1_B7" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B8" id="1_B8" size = "3" style="margin-right: 8px;" value="50" onkeyup="calculate()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B9" id="1_B9" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B10" id="1_B10" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B11" id="1_B11" size = "3" style="margin-right: 8px;border: none"  onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B12" id="1_B12" size = "3" style="margin-right: 8px;" onkeyup="calculateB13()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B13" id="1_B13" size = "3" style="margin-right: 8px;border: none" onfocus="this.blur()">
                            </td>
                            <td>
                                <input class="form-control form-control-sm" type="text" name="1_B14" id="1_B14" size = "3" style="margin-right: 8px;border: none">
                            </td>
                        </tr>
                        </tbody>
                </table>
                </form>
                <div class="col-12 text-right modal-footer">
                    <button onclick="clone()" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Añadir Fila</button>
                    <button onclick="send()" class="btn btn-info btn-sm">Ingresar <i class="fa fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection @push('scripts')
    <script>
        $(document).ready(function () {
            collapseMenu();

            if ($("#categories-table").length > 0) {
                new Vue({
                    el: "#categories-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#categories-table").DataTable({
                                processing: true,
                                serverSide: true,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('boutique.get_categories') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    {data: "name"},
                                    {data: "created_at"},
                                    {data: "products_count"},
                                    {data: "actions"},
                                ],
                                columnDefs: [
                                    {
                                        targets: [3],
                                        orderable: false,
                                    },
                                    {
                                        targets: [3],
                                        searchable: false,
                                    },
                                ],
                                fnDrawCallback: function () {
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        },
                    },
                });
            }
        });

        // CONTROL ACTIONS

        function send()
        {
            SwalGB.fire({
                title: '¿Está seguro que desea registrar el inventario?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {
                    enviar = read();

                    if (enviar != false) {
                        DisableModalActionButtons();

                        let form_data = $("#formulario").serialize();

                        $.ajax({
                            url: '{{ route('boutique.insert_inventory') }}',
                            type: 'POST',
                            data: form_data,
                        }).done(function (res) {
                            if(res.success) {
                                SwalGB.fire({
                                    title: '¡Correcto!',
                                    text: 'Inventario registrado correctamente',
                                    icon: 'success',
                                    showCancelButton: false,
                                    confirmButtonText: "Continuar <i class='fas fa-arrow-right'></i>",
                                }).then(function () {
                                    window.location.replace('{{ route('boutique.inventory_ingresses') }}');
                                    //location.reload();
                                });
                            } else {
                                switch (res.code) {
                                    case 1: //
                                        Toast.fire({
                                            icon: "error",
                                            title: res.msg,
                                            timer: 8000,
                                        });
                                        break;

                                    default: // Can't insert the inventory
                                        Toast.fire({
                                            icon: "error",
                                            title: 'No se ha podido registrar el inventario. Por favor, intenta mas tarde.',
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
                        }).always(function (res) {
                            EnableModalActionButtons();
                        });
                    }
                }
            });
        }

        function enable()
        {
            A1 = document.getElementById("A1").value;
            A2 = document.getElementById("A2").value;

            if (A1 == "") {
                document.getElementById("A2").disabled = true;
                document.getElementById("A2").value = "";
                calculate();
            } else {
                document.getElementById("A2").disabled = false;
            }
        }

        function calculateB13()
        {
            totalrow = document.getElementById("mitabla").rows.length;
            A6 = 0;
            for (var i = 1; i < totalrow; i++) {
                identificador = i + "_";
                B1 = document.getElementById(identificador + "B1").value;
                B12 = document.getElementById(identificador + "B12").value;
                B13 = B1 * B12;
                document.getElementById(identificador + "B13").value = B13;
                if (!isNaN(B13)) {
                    A6 = parseFloat(A6) + parseFloat(B13);
                }
            }
            document.getElementById("A6").value = A6;
            A5 = document.getElementById("A5").value;
            document.getElementById("A7").value = parseFloat(A6 - A5);
        }

        function roundToTwo(num)
        {
            return +(Math.round(num + "e+2") + "e-2");
        }

        function calculate()
        {
            totalrow = document.getElementById("mitabla").rows.length;
            var table = document.getElementById("mitabla");
            A1 = document.getElementById("A1").value;
            A2 = document.getElementById("A2").value;

            if (A1 != "" && A2 != "") {


                for (var i = 1; i < totalrow; i++) {
                    identificador = i + "_";

                    //B1 y B2
                    B1 = document.getElementById(identificador + "B1").value;
                    if (isNaN(B1)) {
                        B1 = 0;
                    }

                    B2 = document.getElementById(identificador + "B2").value;
                    if (isNaN(B2)) {
                        B2 = 0;
                    }
                    //B3 es B1*B2
                    B3 = B1 * B2;
                    B3 = parseFloat(B3).toFixed(3);
                    B3 = roundToTwo(B3);
                    document.getElementById(identificador + "B3").value = B3;

                    //B4 es A1*B3
                    B4 = A1 * B3;
                    B4 = Math.round(B4);
                    document.getElementById(identificador + "B4").value = B4;

                }

                A4 = 0;
                //suma para obtener A4
                for (var i = 1; i < totalrow; i++) {
                    identificador = i + "_";
                    B4 = document.getElementById(identificador + "B4").value;
                    A4 = parseFloat(A4) + parseFloat(B4);
                }
                document.getElementById("A4").value = A4;

                //A3 es A2/A4
                if (A4 == 0) {
                    A3 = 0;
                } else {
                    A3 = A2 / A4;
                    A3 = parseFloat(A3)
                }


                document.getElementById("A3").value = A3;

                //B5 es su B4 por A3
                for (var i = 1; i < totalrow; i++) {
                    identificador = i + "_";
                    B4 = document.getElementById(identificador + "B4").value;
                    B5 = A3 * B4;
                    B5 = Math.round(B5);
                    document.getElementById(identificador + "B5").value = B5;
                }

                //B6 es B4 + B5
                for (var i = 1; i < totalrow; i++) {
                    identificador = i + "_";
                    B1 = document.getElementById(identificador + "B1").value;
                    B4 = document.getElementById(identificador + "B4").value;
                    B5 = document.getElementById(identificador + "B5").value;
                    B6 = parseFloat(B4) + parseFloat(B5);
                    document.getElementById(identificador + "B6").value = B6;

                    B7 = B6 / B1;
                    if (isNaN(B7)) {
                        B7 = 0;
                    }
                    B7 = Math.round(B7);
                    document.getElementById(identificador + "B7").value = B7;

                    B8 = document.getElementById(identificador + "B8").value;
                    B8 = B8 / 100;
                    B9 = B7 * B8;

                    document.getElementById(identificador + "B9").value = B9;
                    B10 = parseFloat(B7) + parseFloat(B9);
                    B10 = Math.round(B10);
                    document.getElementById(identificador + "B10").value = B10;
                    B11 = B10 * B1;
                    document.getElementById(identificador + "B11").value = B11;
                    B12 = B10;
                    document.getElementById(identificador + "B12").value = B12;
                    B13 = B12 * B1;
                    document.getElementById(identificador + "B13").value = B13;
                }

                A5 = 0;
                A6 = 0;
                A7 = 0;

                for (var a = 1; a < totalrow; a++) {
                    identificador_a = a + "_";
                    B6 = document.getElementById(identificador_a + "B6").value;
                    B13 = document.getElementById(identificador_a + "B13").value;

                    if (isNaN(B6)) {
                        B6 = 0;
                    }
                    A5 = parseFloat(A5) + parseFloat(B6);
                    if (isNaN(B13)) {
                        B13 = 0;
                    }
                    A6 = parseFloat(A6) + parseFloat(B13);


                }
                document.getElementById("A5").value = A5;
                document.getElementById("A6").value = A6;
                A7 = parseFloat(A6) - parseFloat(A5);
                document.getElementById("A7").value = A7;

            }//fin de if si A1 y A2 son distintos de vacio
            else {
                window.alert("Debe llenar los campos Cambio Dolar y Total Trasporte Pago");
                document.getElementById("A3").value = "";
                document.getElementById("A4").value = "";
                document.getElementById("A5").value = "";
                document.getElementById("A6").value = "";
                document.getElementById("A7").value = "";
                for (var i = 1; i < totalrow; i++) {
                    identificador = i + "_";
                    document.getElementById(identificador + "B1").value = "";
                    document.getElementById(identificador + "B2").value = "";
                    document.getElementById(identificador + "B3").value = "";
                    document.getElementById(identificador + "B4").value = "";
                    document.getElementById(identificador + "B5").value = "";
                    document.getElementById(identificador + "B6").value = "";
                    document.getElementById(identificador + "B7").value = "";

                    document.getElementById(identificador + "B9").value = "";
                    document.getElementById(identificador + "B10").value = "";
                    document.getElementById(identificador + "B11").value = "";
                    document.getElementById(identificador + "B12").value = "";
                    document.getElementById(identificador + "B13").value = "";

                }
            }
        }

        function clone()
        {
            totalrow = document.getElementById("mitabla").rows.length;
            var table = document.getElementById("mitabla");

            document.getElementById("totalrow").value = totalrow;

            var productos = document.getElementById("1-select");
            var c_producto = productos.cloneNode(true);
            c_producto.id = totalrow + "-select";

            var row = table.insertRow(totalrow);
            //inserta los td al tr creado
            var cell1 = row.insertCell(0).appendChild(c_producto);

            //crear inputs

            for (var i = 1; i <= 14; i++) {
                identificador = totalrow + "_B" + i;
                var x = document.createElement("INPUT");
                x.setAttribute("type", "text");
                x.setAttribute("class", "form-control form-control-sm");
                x.setAttribute("size", "3");
                x.setAttribute("value", "0");
                x.setAttribute("name", identificador);

                if (i == "12") {
                    x.setAttribute("onkeyup", "calculateB13()");
                }

                if (i == "1" || i == "2") {
                    if (i == "2") {
                        x.setAttribute("onkeyup", "calculate()");
                    }
                    x.setAttribute("style", "margin-right: 8px;");
                } else {
                    if (i == "8" || i == "12") {
                        if (i == "8") {
                            x.setAttribute("value", "50");
                            x.setAttribute("onkeyup", "calculate()");
                        }
                        x.setAttribute("style", "margin-right: 8px");
                    } else {
                        x.setAttribute("style", "margin-right: 8px; border: none");
                        if (i != "14") {
                            x.setAttribute("onfocus", "this.blur()");
                        }
                    }
                }
                x.setAttribute("id", identificador);

                var cells = row.insertCell(i).appendChild(x);
            }
            //incrementar el totalrow
            totalrow = document.getElementById("mitabla").rows.length;
            document.getElementById("totalrow").value = totalrow;
        }

        function read()
        {
            totalrow = document.getElementById("mitabla").rows.length;

            for (var i = 1; i < totalrow; i++) {
                identificador = i + "-select";
                producto = document.getElementById(identificador).value;
                if (producto == "") {
                    window.alert("El nombre de los productos que desea ingresar no deben ir vacios");
                    return false;
                }
            }
            for (var x = 1; x < totalrow; x++) {
                identificador_x = x + "_";
                B1 = document.getElementById(identificador_x + "B1").value;
                B2 = document.getElementById(identificador_x + "B2").value;
                B8 = document.getElementById(identificador_x + "B8").value;
                B12 = document.getElementById(identificador_x + "B12").value;

                if (B1 == "" || B1 == "0" || B2 == "" || B8 == "" || B8 == "0" || B12 == "" || B12 == "0") {
                    window.alert("Ninguno de los campos Nr Uni, Precio Uni, Min % Venta o $ Final Uni debe ir vacio ");
                    return false;
                }
            }
        }

        function getProduct(id)
        {
            var row = id.split("-");
            var num_row = row[0];
            var id = $("#" + id).val();

            $.ajax({
                url: '{{ route('boutique.get_product') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id,
                },
            }).done(function (res) {
                let wholesaler_price = res.wholesaler_price;
                $("#" + num_row + "_B14").val(wholesaler_price);
            }).fail(function (res) {
            });
        }
    </script>
@endpush
