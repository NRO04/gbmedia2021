@extends('layouts.app')
@section('pageTitle', 'Horario')
@push('styles')
    <style>
        .c-dark-theme .creados {
            background: #0a0b18;
            border: none;
        }
    </style>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Horarios</li>
    <li class="breadcrumb-item active">Listado Horarios</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <span class="span-title">Listado Horarios</span>
                    <span class="ml-5"><i class="fa fa-check text-success"></i> En turno</span>
                    <span class="ml-3"><i class="fas fa-bed text-info"></i> Descanso</span>
                    @can('schedule-create')
                        <a type="button" class="btn btn-m btn-success float-right btn-sm" href='{{route('schedule.create')}}'><i class="fa fa-plus"></i> Crear</a>
                    @endcan
                </div>
                <div class="card-body">
                    <div id="div_schedule"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="modal-workingday-schedule" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Jornada <span class="text-danger" id="session_name"></span></h4>
                </div>
                <div class="modal-body">
                    <form action="{{ route('schedule.updateWorkingDay')}}" method="post" id="form-update-WorkingDay">
                        @csrf
                        <input type="hidden" name="schedule_sessions_id" id="schedule_sessions_id">
                        <input type="hidden" name="working_time" id="working_time">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th class="w-5">Nro Cuartos</th>
                                <th>Inicio Jornada</th>
                                <th>Fin Jornada</th>
                                <th>Pausa (min)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr id="class_tr">
                                <td class="w-5"><input class="form-control" type="number" name="available" id="available"></td>
                                <td>
                                    <div style='display:flex'>
                                        <select class="form-control mr-1" id="shift_start_h" name="shift_start_h" onchange="CalculateTimeBetweenDates()">
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <select class="form-control  mr-1" id="shift_start_m" name="shift_start_m" onchange="CalculateTimeBetweenDates()">
                                            @for ($i = 0; $i <= 59; $i++)
                                                <option>
                                                    {{ ($i < 9)? "0".$i : $i }}
                                                </option>
                                            @endfor
                                        </select>
                                        <select class="form-control  mr-1" id="shift_start_type" name="shift_start_type" onchange="CalculateTimeBetweenDates()">
                                            <option>AM</option>
                                            }
                                            <option>PM</option>
                                            }
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div style='display:flex'>
                                        <select class="form-control  mr-1" id="shift_end_h" name="shift_end_h" onchange="CalculateTimeBetweenDates()">
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <select class="form-control  mr-1" id="shift_end_m" name="shift_end_m" onchange="CalculateTimeBetweenDates()">
                                            @for ($i = 0; $i <= 59; $i++)
                                                <option>
                                                    {{ ($i < 9)? "0".$i : $i }}
                                                </option>
                                            @endfor
                                        </select>
                                        <select class="form-control  mr-1" id="shift_end_type" name="shift_end_type" onchange="CalculateTimeBetweenDates()">
                                            <option>AM</option>
                                            }
                                            <option>PM</option>
                                            }
                                        </select>
                                    </div>

                                </td>
                                <td>
                                    <select class="form-control" id="break" name="break">
                                        @for ($i = 0; $i <= 120; $i++)
                                            <option>{{ ($i < 9)? "0".$i : $i }}</option>
                                        @endfor
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-warning" id="btn_updateWorkingDay"><i class='fas fa-pencil-alt'></i> Editar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function getSchedules() {
            $("#carga").css("display", "block");
            $.ajax({
                url: '{{route('schedule.getSchedules')}}',
                type: 'GET',
            })
                .done(function (res) {
                    $("#div_schedule").html(res);
                });
        }

        function modalWorkingDay(array_working_day) {
            $("#schedule_sessions_id").val(array_working_day['schedule_availability']['id']);
            $("#session_name").html(array_working_day['session_name']);
            shift_start = array_working_day['schedule_availability']['shift_start'];
            shift_end = array_working_day['schedule_availability']['shift_end'];
            shift_start = shift_start.split(" ");
            shift_start1 = shift_start[0];
            shift_start2 = shift_start[1];
            shift_start1 = shift_start1.split(":");
            $("#shift_start_h").val(shift_start1[0]);
            $("#shift_start_m").val(shift_start1[1]);
            $("#shift_start_type").val(shift_start2);

            shift_end = shift_end.split(" ");
            shift_end1 = shift_end[0];
            shift_end2 = shift_end[1];
            shift_end1 = shift_end1.split(":");
            $("#shift_end_h").val(shift_end1[0]);
            $("#shift_end_m").val(shift_end1[1]);
            $("#shift_end_type").val(shift_end2);

            $("#break").val(array_working_day['schedule_availability']['break']);
            $("#available").val(array_working_day['schedule_availability']['available']);
            console.log(array_working_day);
        }

        $("#btn_updateWorkingDay").on("click", function () {
            bandera = true;
            minutos = CalculateTimeBetweenDates();

            if ($("#available").val() <= 0 || $("#available").val() <= "") {
                bandera = false;
                texto = "El Nro Equipos debe ser mayor que cero";
            }

            if (minutos <= 0) {
                bandera = false;
                texto = "La diferencia de minutos entre los horarios debe ser mayo que cero";
            }


            if (bandera) {
                ResetValidations();
                $.ajax({
                    url: "{{ route('schedule.updateWorkingDay')}}",
                    type: "POST",
                    data: $("#form-update-WorkingDay").serialize(),
                })
                    .done(function (res) {
                        if (res.success) {
                            Toast.fire({
                                icon: "success",
                                title: "La jornada fue modificada exitosamente",
                            });
                            $("#modal-workingday-schedule").modal("hide");
                            ResetModalForm("#form-update-WorkingDay");
                            getSchedules();
                        } else {
                            Toast.fire({
                                icon: "error",
                                title: "Ha ocurrido un error, comuniquese con el ADMIN",
                            });
                        }
                    })
                    .fail(function (res) {
                        CallBackErrors(res);
                    });

            } else {
                Toast.fire({
                    icon: "error",
                    title: texto,
                });
            }
        });

        function CalculateTimeBetweenDates() {
            var hoy = new Date();
            var dd = hoy.getDate();
            var mm = hoy.getMonth() + 1;
            var yyyy = hoy.getFullYear();
            dd = (dd < 9) ? "0" + dd : dd;
            mm = (mm < 9) ? "0" + mm : mm;
            date_today = yyyy + '-' + mm + '-' + dd;
            date_today2 = dd + '/' + mm + '/' + yyyy;

            shift_start_h = $("#shift_start_h").val();
            shift_start_m = $("#shift_start_m").val();
            shift_start_type = $("#shift_start_type").val();

            shift_end_h = $("#shift_end_h").val();
            shift_end_m = $("#shift_end_m").val();
            shift_end_type = $("#shift_end_type").val();

            if (shift_end_type == "AM" && shift_start_type == "PM") {
                date2 = hoy.setDate(hoy.getDate() + 1);
                date2 = new Date(date2);
                dd = (date2.getDate() < 9) ? "0" + date2.getDate() : date2.getDate();
                mm = ((date2.getMonth() + 1) < 9) ? "0" + (date2.getMonth() + 1) : (date2.getMonth() + 1);
                date2 = date2.getFullYear() + "-" + mm + '-' + dd;
            } else {
                date2 = date_today;
            }

            date1 = date_today + ' ' + shift_start_h + ":" + shift_start_m + ":00 " + shift_start_type;
            date2 = date2 + " " + shift_end_h + ":" + shift_end_m + ":00 " + shift_end_type;

            var fecha1 = moment(date1);
            var fecha2 = moment(date2);
            milisegundos = (fecha2.diff(fecha1));
            minutos = milisegundos / 1000 / 60;
            $("#working_time").val(minutos);
            return minutos;
        }

        window.onload = getSchedules();
    </script>
@endpush
