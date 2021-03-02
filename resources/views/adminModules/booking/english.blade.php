@extends('layouts.app')
@section('pageTitle', 'Inglés')

@section('content')
    <english  :user="{{ auth()->user() }}" :bookingid="{{ $bookingid->id }}" :permissions="{{ json_encode($user_permission) }}"></english>
@endsection