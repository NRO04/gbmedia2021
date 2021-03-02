@extends("layouts.app")
@section('pageTitle', 'Contratos')

@section('content')
    <contract :permissions="{{ json_encode($user_permission) }}"></contract>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
