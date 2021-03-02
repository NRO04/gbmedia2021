@extends('layouts.app')

@section('pageTitle', 'Listado de Estudios')

@section('content')
    <studios
        :user="{{ auth::user() }}"
        :roles="{{ json_encode($roles) }}"
        :current_tenant_id="{{ tenant('id') }}"
        :permissions="{{ json_encode($user_permission) }}"
    >
    </studios>
@endsection
