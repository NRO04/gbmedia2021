@extends('layouts.app')

@section('pageTitle', 'Histórico de Ventas')

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Histórico de Ventas | <span class="text-info"><span id="title-week">{{ $selected_week }}</span></span></span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right" id="container-export">
                    @can('boutique-export-sales')
                        <a class="btn btn-outline-success btn-sm" id="export-week-sales" href="{{ route('boutique.export_week_sales', [$selected_week_start, $selected_week_end]) }}">
                            <i class="fa fa-file-excel"></i> Exportar
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-8">
                        <select id="select-week" class="form-control col-sm-5 form-control-sm">
                            @foreach($weeks as $week)
                                <option data-start="{{ $week->start }}" data-end="{{ $week->end }}" value="{{ $week->formatted }}">{{ $week->formatted }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 text-right mt-2">
                        @can('boutique-total-sells-money')
                            <h5 class="text-muted">Total ventas: <span id="total-sales-week" class="text-success text-bold">$0</span></h5>
                        @endcan
                    </div>
                </div>
                <input type="hidden" id="selected-week-start" value="{{ $selected_week_start }}">
                <input type="hidden" id="selected-week-end" value="{{ $selected_week_end }}">
                <hr>
                <table class="table table-hover" id="sales-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Cantidad</th>
                            <th>Total Venta</th>
                            <th>Fecha</th>
                            <th>Comprador</th>
                            <th>Vendedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection @push('scripts')
    <script>
        let selected_week = '{{ $selected_week }}';
        let start_date = $('#selected-week-start').val();
        let end_date = $('#selected-week-end').val();

        $(document).ready(function () {
            if ($("#sales-table").length > 0) {
                new Vue({
                    el: "#sales-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#sales-table").DataTable({
                                processing: true,
                                serverSide: true,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('boutique.get_sales') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                    data: function(data) {
                                        data.selected_week = selected_week;
                                        data.start_date = start_date;
                                        data.end_date = end_date;
                                    },
                                    complete: function (res) {
                                        let total = res.responseJSON.total;
                                        $('#total-sales-week').text(total);
                                    }
                                },
                                columns: [
                                    { data: "image" },
                                    { data: "product.name" },
                                    { data: "quantity" },
                                    { data: "total" },
                                    { data: "date" },
                                    { data: "buyer" },
                                    { data: "seller" },
                                    { data: "actions" },
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

        // CONTROL FUNCTIONS
        $('#select-week').on('change', function () {
            $('#container-export').html('');
            $('#total-sales-week').text("$0");

            let selected_week_start = $(this).find(':selected').data('start');
            let selected_week_end = $(this).find(':selected').data('end');
            let selected_week_formatted = $(this).find(':selected').val();

            start_date = selected_week_start;
            end_date = selected_week_end;

            $('#title-week').text(selected_week_formatted);

            let url = '{{ route("boutique.export_week_sales", [":start", ":end"]) }}';
            url = url.replace(':start', selected_week_start);
            url = url.replace(':end', selected_week_end);

            $('#container-export').append('<a class="btn btn-outline-success btn-sm" href="'+url+'"><i class="fa fa-file-excel"></i> Exportar</a>');

            table.ajax.reload();
        });

        function deleteSale(id)
        {
            SwalGB.fire({
                title: '¿Está seguro que desea eliminar la venta seleccionada?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('boutique.delete_sale') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        },
                    }).done(function (res) {
                        if(res.success) {
                            Toast.fire({
                                icon: "success",
                                title: "Venta eliminada exitosamente y productos regresados al inventario",
                                timer: 5000,
                            });

                            table.ajax.reload();
                        } else {
                            switch (res.code) {
                                case 1: // The sale have a payment in satellite
                                    Toast.fire({
                                        icon: "error",
                                        title: res.msg,
                                        timer: 8000,
                                    });
                                    break;

                                default:
                                    Toast.fire({
                                        icon: "error",
                                        title: 'No se ha podido eliminar la venta. Por favor, intenta mas tarde.',
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
        }
    </script>
@endpush
