@extends('layouts.app')
@section('pageTitle', 'Maquillaje')

@section('content')
    <makeup  :user="{{ auth()->user() }}" :bookingid="{{ $bookingid->id }}" :permissions="{{ json_encode($user_permission) }}"></makeup>
@endsection