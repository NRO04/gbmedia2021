@extends("layouts.app")
@section('pageTitle', 'Satelite ganancias')

@section('content')
    <earning-main></earning-main>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
