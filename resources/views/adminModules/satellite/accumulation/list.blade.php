@extends("layouts.app")
@section('pageTitle', 'Acumulado')

@section('content')
    <Accumulation></Accumulation>
@endsection
@push("scripts")
    {{--<script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>--}}
@endpush
