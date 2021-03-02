@extends('layouts.app')

@section('pageTitle', 'Prospectos Referidos')

@section('content')
    <referred-models
        :user="{{ auth::user() }}"
        :roles="{{ json_encode($roles) }}"
        :current_tenant_id="{{ tenant('id') }}"
        :permissions="{{ json_encode($user_permission) }}"
        :departments="{{ $departments }}"
    >
    </referred-models>
@endsection
