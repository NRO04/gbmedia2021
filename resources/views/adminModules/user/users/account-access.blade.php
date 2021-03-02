@extends("layouts.app")

@section('pageTitle', 'Accesos')

@section('content')
    <model-account-access
        :model="{{ auth()->user() }}"
        :location="{{ $location }}"
        :accounts="{{ json_encode($access) }}"
    >

    </model-account-access>
@endsection
@push("scripts")
@endpush
