@extends('layouts.app')

@section('pageTitle', 'Historial de Entregas y Recibos')

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-12 col-md-6">
                    <span class="span-title">Historial de Entregas y Recibos <span class="text-info"></span></span>
                </div>
                <div class="col-12 col-md-6 text-right row">
                    <label for="filter-locations" class="col-12 col-md-6 pt-2">Locación:</label>
                    <div class="col-12 col-md-6">
                        <select id="filter-locations" class="form-control">
                            <option value="">Seleccione...</option>
                            @foreach($locations AS $location)
                                <option @if($location->id == 1) selected @endif value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <hr>
                <div id="container-no-location" class="text-center">
                    <h5>Por favor, seleccione una locación</h5>
                </div>
                <div id="container-table" class="d-none">
                    <table class="table table-hover table-striped" id="table-history" style="width:100%">
                        <thead>
                        <tr>
                            <th># Cuarto</th>
                            <th>Monitor</th>
                            <th>Modelo</th>
                            <th>Fecha</th>
                            <th>Estatus</th>
                            <th>Historial</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-room-history" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Historial cuarto <span id="span-room-number"></span></h4>
                </div>
                <div class="modal-body">
                    <table class="table table-hover table-striped" id="table-room-history" style="width:100%">
                        <thead>
                            <tr>
                                <th>Monitor</th>
                                <th>Modelo</th>
                                <th>Fecha</th>
                                <th>Estatus</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection @push('scripts')
    <script>
        let location_id = null;
        let table = null;
        let room_history_table = null;

        $(document).ready(function () {
            if ($("#table-history").length > 0) {
                new Vue({
                    el: "#table-history",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#table-history").DataTable({
                                processing: true,
                                serverSide: true,
                                pageLength: 25,
                                ordering: false,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('roomscontrol.get_history') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                    data: function(data) {
                                        data.location_id = location_id;
                                    },
                                    beforeSend: function (xhr) {
                                        if(location_id == null) {
                                            xhr.abort();
                                        }
                                    },
                                    complete: function () {
                                        $('#global-spinner').addClass('d-none');
                                    }
                                },
                                columns: [
                                    { data: "room_number" },
                                    { data: "monitor" },
                                    { data: "model" },
                                    { data: "date" },
                                    { data: "status" },
                                    { data: "history" },
                                ],
                            });
                        },
                    },
                });
            }
        });

        // CONTROL ACTIONS
        $('#filter-locations').on('change', function () {
            let selected_location_id = $(this).val();

            location_id = selected_location_id;

            if(selected_location_id != '') {
                $('#global-spinner').removeClass('d-none');

                table.ajax.reload();

                $('#container-no-location').addClass('d-none');
                $('#container-table').removeClass('d-none');
            } else {
                $('#container-no-location').removeClass('d-none');
                $('#container-table').addClass('d-none');
            }
        });
    </script>
@endpush
