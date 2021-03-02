@extends("layouts.app")
@section('content')
    <payroll
        :user="{{ auth()->user() }}"
        :permissions="{{ json_encode($user_permissions) }}"
    >
    </payroll>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush


