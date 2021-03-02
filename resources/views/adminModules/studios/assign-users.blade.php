@extends('layouts.app')

@section('pageTitle', 'Asignar Usuarios')

@section('content')
    <assign-users
        :user="{{ auth::user() }}"
        :users="{{ json_encode($users) }}"
        :assignments="{{ json_encode($studio_assignments) }}"
        :current_tenant_id="{{ tenant('id') }}"
        :permissions="{{ json_encode($user_permission) }}"
    >
    </assign-users>
@endsection
