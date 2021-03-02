@extends('layouts.app')

@section('pageTitle', 'Inicio')

@section('content')
    <dashboard :user="{{ auth::user() }}" :current_tenant_id="{{ tenant('id') }}" :permissions="{{ json_encode($user_permission) }}"></dashboard>
@endsection

@push('scripts')
    <script src="{{ global_asset('js/main.js') }}"></script>
@endpush
