<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Title --}}
    <title>No tiene permiso para realizar esta acción</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ global_asset('images/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ global_asset('css/coreuipro.css') }}" rel="stylesheet">
    <link href="{{ global_asset('vendors/datatables.net-bs4/css/dataTables.bootstrap4.css')}}" rel="stylesheet">

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/push.js/0.0.11/push.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.2.1/simple-lightbox.min.css" integrity="sha512-qIQ+oWWu9Su/mIwrFFAWBp5Yv2bZ9wqC2BuzEmnfCwc0HIuVg6AjYPr+kUwY9KtCUCl8vxc73oWneeFCKoUfpw==" crossorigin="anonymous" /> --}}
    <link href="{{ global_asset('vendors/simpleLigthbox/simple-lightbox.min.css') }}" rel="stylesheet" >
    <link href="{{ global_asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ global_asset('css/gblaravel.css') }}" rel="stylesheet">
    <link href="{{ global_asset('vendors/ladda/css/ladda-themeless.min.css') }}" rel="stylesheet">
    <link href="{{ global_asset('vendors/datatables.net-select/css/select.dataTables.min.css') }}" rel="stylesheet">
    @stack('styles')

</head>
<body class="{{ auth()->user()->theme}}" id="body-id">

<div id="app">
    <div class="flex-center position-ref full-height">
        <div class="code">
            403
        </div>

        <div class="message" style="padding: 10px;">
            No tienes los permisos necesarios para realizar esta acción.
        </div>
    </div>
</div>
<!-- Scripts -->
<script src="{{ global_asset('js/app.js') }}"></script>
<script src="{{ global_asset('vendors/@coreui/coreui-pro/js/coreui.bundle.min.js') }}"></script>
<script src="{{ global_asset('vendors/@coreui/chartjs/js/coreui-chartjs.bundle.js') }}"></script>
<script src="{{ global_asset('vendors/@coreui/utils/js/coreui-utils.js') }}"></script>
@stack('scripts')
</body>
</html>
