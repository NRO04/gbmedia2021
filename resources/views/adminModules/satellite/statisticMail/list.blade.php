@extends("layouts.app")
@section('content')
    <statistic></statistic>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
