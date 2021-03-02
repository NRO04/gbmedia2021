@extends("layouts.app")

@section('pageTitle', 'Listado de Usuarios')

@section('content')
    <user-main
        :user="{{ auth()->user() }}"
        :roles="{{ $roles }}"
        :permissions="{{ json_encode($user_permissions) }}"
        :locations="{{ json_encode($locations) }}"
        :departments="{{ $departments }}"
    >

    </user-main>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
