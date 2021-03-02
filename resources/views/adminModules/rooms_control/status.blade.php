@extends('layouts.app')

@section('pageTitle', 'Estatus de Cuartos')

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Estatus de Cuartos</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-md-right row">
                    <label for="filter-locations" class="col-12 col-md-6 pt-2">Locación:</label>
                    <div class="col-12 col-md-6">
                        <select id="filter-locations" class="form-control">
                            <option value="">Seleccione...</option>
                            <option value="4">Penthouse</option>
                            <option value="8"> Tequendama</option>

                            {{-- @foreach ($locations as $location)
                                <option @if ($location->id == 1) selected @endif value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach --}}
                        </select>
                    </div>
                </div>
            </div>
            <div id="container-statuses" class="card-body d-none">
                <div id="container-rooms" class="row"></div>
                <div class="row mt-3">
                    <div class="col-12 col-md-3">
                        <span class="badge badge-pill taken" style="border-radius: 0">&nbsp;</span>
                        <span class="titleLegend"><strong><span class="hidden-xs">Cuarto </span>ocupado</strong></span>
                    </div>
                    <div class="col-12 col-md-4">
                        <span class="badge badge-pill available" style="border-radius: 2px">&nbsp;</span>
                        <span class="titleLegend"><strong><span class="hidden-xs">Cuarto </span>disponible</strong></span>
                    </div>
                    <div class="col-12 col-md-5 mt-3 text-right">
                        <h5 class="text-muted">Última actualización: <span id="span-last-updated"></span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection @push('scripts')
    <script>
        let location_id = null;

        let interval = setInterval(updateRoomStatus, 10000);

        $('#filter-locations').on('change', function() {
            $('#container-statuses').addClass('d-none');
            $('#container-rooms').html('');

            let selected_location_id = $(this).val();

            // console.log(selected_location_id);

            location_id = selected_location_id;
            $('#location_id').val(location_id);

            if (selected_location_id != '') {
                $('#global-spinner').removeClass('d-none');

                $.ajax({
                    url: '{{ route('roomscontrol.get_location') }}',
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        location_id
                    },
                }).done(function(res) {

                    // console.log(res)
                    let items = '';
                    let rooms = res.cantidad_cuartos;

                    for (let i = 1; i <= rooms; i++) {
                        items +=
                            '<div class="col-4 col-md-3 text-center">' +
                            '    <div class="room-status-inner available" id="room-' + i + '">' +
                            '        <div class="col-12">' +
                            '             <b>' + i + '</b>' +
                            '        </div>' +
                            '    </div>' +
                            '</div>'
                    }

                    $('#container-rooms').append(items);

                    $("#container-statuses").removeClass("d-none");
                    $("#global-spinner").addClass("d-none");

                    updateRoomStatus();
                }).fail(function(res) {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información",
                    });
                });
            }
        });

        function updateRoomStatus() {
            if (location_id != null) {
                $.ajax({
                    url: '{{ route('roomscontrol.get_location_rooms_status') }}',
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        location_id
                    },
                }).done(function(res) {

                    // console.log(res)
                    $.each(res, function(id, room) {

                        console.log(room);

                        // if (room.status == 1) {
                        //     console.log("Yes nene" + room.room_number)
                        // }
                        if (room.status == 1) {
                            $('#room-' + room.room_number).addClass('taken');
                            $('#room-' + room.room_number).removeClass('available');
                        } else {
                            $('#room-' + room.room_number).addClass('available');
                            $('#room-' + room.room_number).removeClass('taken');
                        }

                        $('#span-last-updated').text(res.last_updated);
                    });
                }).fail(function(res) {
                    clearInterval(interval);
                });
            }
        }

    </script>
@endpush
