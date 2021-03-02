@extends('layouts.app')
@section('pageTitle', 'Mantenimiento')

@section('content')
    <maintenance
        :user="{{ auth()->user() }}"
        :permissions="{{ json_encode($user_permission) }}"
        :locations="{{ json_encode($locations) }}"
    >
    </maintenance>
@endsection
