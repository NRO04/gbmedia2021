@extends("layouts.app")
@section('pageTitle', 'Contabilidad')

@section('content')
    <accounting-main></accounting-main>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
