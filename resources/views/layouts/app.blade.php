<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Title --}}
    <title>@yield('pageTitle') | GB Media Group</title>

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
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    @stack('styles')

</head>
<body class="c-app c-dark-theme" id="body-id">
<div class="b-overlay position-absolute d-none" id="global-spinner" style="top: 0px; left: 0px; bottom: 0px; right: 0px; z-index: 10;">
    <div class="position-absolute bg-light" style="top: 0px; left: 0px; bottom: 0px; right: 0px; opacity: 0.85; backdrop-filter: blur(2px);"></div>
    <div class="position-absolute" style="top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%);"><span aria-hidden="true" class="spinner-border"><!----></span></div>
</div>

<div id="app">
    @include('adminCoreUi.partials.sidebar')
    <div class="c-wrapper">
        <div class="col-12 text-center bg-success text-small p-1 @if (Auth::check() && Auth::user()->is_admin != 1) d-none @endif" style="max-height: 25px;">
            <b>NAVEGANDO COMO ADMIN</b>
        </div>
        @include('adminCoreUi.partials.header')

        <div class="c-body">
            <main class="c-main">
                <div class="container-fluid">
                    <div id="ui-view">
                        <div>
                            <vue-progress-bar></vue-progress-bar>
                            @yield('content')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
<!-- Scripts -->
@routes
<script src="{{ global_asset('js/app.js') }}"></script>
<script src="{{ global_asset('vendors/@coreui/coreui-pro/js/coreui.bundle.min.js') }}"></script>
<script src="{{ global_asset('vendors/@coreui/chartjs/js/coreui-chartjs.bundle.js') }}"></script>
<script src="{{ global_asset('vendors/@coreui/utils/js/coreui-utils.js') }}"></script>
<script src="{{ global_asset('vendors/datatables.net/js/jquery.dataTables.js')}}"></script>
<script src="{{ global_asset('vendors/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{ global_asset('vendors/datatables.net-select/js/dataTables.select.js')}}"></script>
<script src="{{ global_asset('vendors/@coreui/icons/js/svgxuse.min.js')}}"></script>
<script src="{{ global_asset('vendors/tinymce/jquery.tinymce.min.js')}}"></script>
<script src="{{ global_asset('vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{ global_asset('js/helper.js')}}"></script>
<script src="{{ global_asset('js/spartan-image.js')}}"></script>
<script src="{{ global_asset('vendors/simpleLigthbox/simple-lightbox.min.js') }}"></script>
<script src="{{ global_asset('vendors/ladda/js/spin.min.js')}}"></script>
<script src="{{ global_asset('vendors/ladda/js/ladda.min.js')}}"></script>
<script src="{{ global_asset('js/loading-buttons.js')}}"></script>
@stack('scripts')
</body>
</html>
