@extends('layouts.app')
@section('pageTitle', 'Psicología')

@section('content')
    <psychology  :user="{{ auth()->user() }}" :bookingid="{{ $bookingid->id }}" :permissions="{{ json_encode($user_permission) }}"></psychology>
@endsection