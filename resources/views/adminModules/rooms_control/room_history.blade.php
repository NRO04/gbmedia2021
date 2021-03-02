@extends('layouts.app')

@section('pageTitle', 'Historial de Entregas y Recibos')

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header">
                <span class="span-title">Historial de Entregas y Recibos <span class="text-danger">({{ $location_name }} | Cuarto {{$room_number}})</span></span>
            </div>
            <div class="card-body">
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
        </div>
    </div>

    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-room-history" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Observaciones</h4>
                </div>
                <div class="modal-body">
                    <div class="col-12 text-center" id="container-image"></div>
                    <hr>
                    <div class="col-12" id="container-observations"></div>
                    <hr>
                    <div class="col-12 d-none" id="container-extra"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-secondary" data-dismiss="modal" type="button">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection @push('scripts')
    <script>
        let location_id = "{{ $location_id }}";
        let room_number = "{{ $room_number }}";
        let table = null;
        let room_history_table = null;

        $(document).ready(function () {
            if ($("#table-room-history").length > 0) {
                new Vue({
                    el: "#table-room-history",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#table-room-history").DataTable({
                                processing: true,
                                serverSide: true,
                                pageLength: 50,
                                lengthMenu: [[50, 100, 150, -1], [50, 100, 150, "Todos"]],
                                ordering: false,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('roomscontrol.get_room_history') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                    data: function(data) {
                                        data.location_id = location_id;
                                        data.room_number = room_number;
                                    },
                                },
                                columns: [
                                    { data: "monitor" },
                                    { data: "model" },
                                    { data: "date" },
                                    { data: "status" },
                                    { data: "observations" },
                                ],
                            });
                        },
                    },
                });
            }
        });

        // CONTROL ACTIONS
        function openRoomHistory(id)
        {
            $.ajax({
                url: '{{ route('roomscontrol.get_room_control') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            }).done(function (res) {
                let image = res.image;
                let observations = res.observations;
                let have_observations = res.have_observations;
                let extra_observations = res.extra_observations;
                let have_extra_observations = res.have_extra_observations;

                $('#container-image').html(image);

                if(have_observations)
                {
                    $('#container-observations').html(observations);
                }
                else
                {
                    $('#container-observations').html('<div class="text-center">Sin observaciones</div>');
                }

                if(have_extra_observations)
                {
                    $('#container-extra').removeClass('d-none');
                    $('#container-extra').append('<div class="text-center">Observaciones extras</div><br>');
                    $('#container-extra').append(extra_observations);
                }
                else
                {
                    $('#container-extra').html('');
                    $('#container-extra').addClass('d-none');
                }

                $('#modal-room-history').modal('show');
            }).fail(function (res) {
                Toast.fire({
                    icon: "error",
                    title: 'Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.',
                    timer: 10000,
                });

                $('#modal-room-history').modal('hide');
            });
        }
    </script>
@endpush
