@extends("layouts.app")
@section('pageTitle', 'Satelite deudas')

@section('content')
    <Debts :permissions="{{ json_encode($user_permission) }}"></Debts>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
