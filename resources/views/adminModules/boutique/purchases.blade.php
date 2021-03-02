@extends('layouts.app')

@section('pageTitle', 'Mis Compras')

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Mis Compras</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right"></div>
            </div>
            <div class="card-body">
                <table class="table table-hover" id="purchases-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Fecha</th>
                            <th>Vendedor</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection @push('scripts')
    <script>
        $(document).ready(function () {
            //collapseMenu();

            if ($("#purchases-table").length > 0) {
                new Vue({
                    el: "#purchases-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#purchases-table").DataTable({
                                processing: true,
                                serverSide: true,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('boutique.get_my_purchases') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    { data: "image" },
                                    { data: "product.name" },
                                    { data: "quantity" },
                                    { data: "unit_price" },
                                    { data: "date" },
                                    { data: "seller" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [0, 2],
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
        });
    </script>
@endpush
