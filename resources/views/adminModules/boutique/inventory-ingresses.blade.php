@extends('layouts.app')

@section('pageTitle', 'Historial Ingresos Inventario')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Historial Ingresos Inventario</li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Historial Ingresos Inventario</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    @can('boutique-inventory-create')
                        <a class="btn btn-success btn-sm" href="{{ route('boutique.inventory') }}">
                            <i class="fa fa-dolly-flatbed"></i> &nbsp;Nuevo Ingreso
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div id="container-filters" class="row">
                    <div class="col-12">
                        <div class="row">
                            <label for="filter-date" class="col-12 col-sm-2 pt-2">Fecha y Hora:</label>
                            <div class="col-12 col-md-4">
                                <select id="filter-date" class="form-control">
                                    <option value="">Seleccione</option>
                                    @foreach($ingresses_dates AS $date)
                                        <option @if($date->date == $selected_datetime) selected @endif value="{{ $date->date }}">{{ $date->formatted }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if (isset($ingresses['info']))
                                <div class="col-12 col-md-6 pt-2 text-right">
                                    <h5>
                                        <label class="text-muted">Creado por:</label> {{ $ingresses['info']->created_by }}
                                    </h5>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <hr>
                <div id="container-ingress">
                    @if (isset($ingresses['products']))
                        <div id="container-totals">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ $ingresses['info']->a1->title }}</th>
                                        <th>{{ $ingresses['info']->a2->title }}</th>
                                        <th>{{ $ingresses['info']->a3->title }}</th>
                                        <th>{{ $ingresses['info']->a4->title }}</th>
                                        <th>{{ $ingresses['info']->a5->title }}</th>
                                        <th>{{ $ingresses['info']->a6->title }}</th>
                                        <th>{{ $ingresses['info']->a7->title }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $ingresses['info']->a1->value }}</td>
                                        <td>{{ $ingresses['info']->a2->value }}</td>
                                        <td>{{ $ingresses['info']->a3->value }}</td>
                                        <td>{{ $ingresses['info']->a4->value }}</td>
                                        <td>{{ $ingresses['info']->a5->value }}</td>
                                        <td>{{ $ingresses['info']->a6->value }}</td>
                                        <td>{{ $ingresses['info']->a7->value }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-small" width="20%">Producto</th>
                                <th class="text-small">Nr Uni B1</th>
                                <th class="text-small">Precio Uni B2</th>
                                <th class="text-small">Total B3</th>
                                <th class="text-small">Cambio $ B4</th>
                                <th class="text-small">Trasp Prod B5</th>
                                <th class="text-small">Precio T B6</th>
                                <th class="text-small">Precio x Uni B7</th>
                                <th class="text-small">Min % Venta B8</th>
                                <th class="text-small">Aumento % B9</th>
                                <th class="text-small">$ Sug Uni B10</th>
                                <th class="text-small">Venta T B11</th>
                                <th class="text-small">$ Final Uni B12</th>
                                <th class="text-small">Precio Final T B13</th>
                                <th class="text-small">Precio Mayorista</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ingresses['products'] AS $product)
                                <tr>
                                    <td class="text-small">{{ $product->product_name }}</td>
                                    <td class="text-small">{{ $product->B1 }}</td>
                                    <td class="text-small">{{ $product->B2 }}</td>
                                    <td class="text-small">{{ $product->B3 }}</td>
                                    <td class="text-small">{{ $product->B4 }}</td>
                                    <td class="text-small">{{ $product->B5 }}</td>
                                    <td class="text-small">{{ $product->B6 }}</td>
                                    <td class="text-small">{{ $product->B7 }}</td>
                                    <td class="text-small">{{ $product->B8 }}</td>
                                    <td class="text-small">{{ $product->B9 }}</td>
                                    <td class="text-small">{{ $product->B10 }}</td>
                                    <td class="text-small">{{ $product->B11 }}</td>
                                    <td class="text-small">{{ $product->B12 }}</td>
                                    <td class="text-small">{{ $product->B13 }}</td>
                                    <td class="text-small">{{ $product->B14 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection @push('scripts')
    <script>
        $(document).ready(function () {
            collapseMenu();
        });

        // CONTROL ACTIONS

        $("#filter-date").on("change", function (e) {
            let datetime = $(this).val();

            if(datetime != '') {
                document.location.href = "inventory-ingresses?datetime=" + datetime;
            } else {
                document.location.href = "inventory-ingresses";
            }
        });
    </script>
@endpush
