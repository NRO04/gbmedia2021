@extends('layouts.app')

@section('pageTitle', 'Info APP')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Tareas</li>
    <li class="breadcrumb-item active"><a href="">Info APP</a></li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-8 mb-2">
                    <span class="span-title">Info APP ControlCuartos</span>
                </div>
                <div class="col-xs-12 col-sm-4 text-sm-right">
                </div>
            </div>
            <div class="card-body row">
                <div class="col-12 col-sm-6">
                    <div class="card card-accent-info">
                        <div class="card-header"><b>Código APP</b></div>
                        <div class="card-body text-justify">
                            <p>El código del sub estudio para la App es: <span id="span-code"><b>{{ tenant('rooms_control_code') }}</b></span></p>
                            <p>Este es el código para utilizar en la App ControlCuartos en su estudio. Este código debe ingresarlo la primera vez que inicie la App y cada vez que necesite reiniciarla.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="card card-accent-info">
                        <div class="card-header"><b>Manual de Uso</b></div>
                        <div class="card-body text-justify">
                            <p>La App ControlCuartos le permite llevar un control de las entregas y recibos de los cuartos de su estudio a sus modelos, facilitando así el proceso y registrando cada movimiento para que pueda observarlo posteriormente en la plataforma.</p>
                            <div class="col-12 row">
                                <a id="btn-download" class="btn btn-success btn-sm" href="{{ global_asset('assets/manual/ControlCuartos_App_Manual.pdf') }}" target="_blank">
                                    <i class="fa fa-download"></i> Descargar manual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let table = null;
        let room_number = null;
        let location_id = '';

        $(document).ready(function () {

        });

    </script>
@endpush
