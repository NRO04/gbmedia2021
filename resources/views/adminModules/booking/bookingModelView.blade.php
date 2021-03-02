@extends('layouts.app')
@section('pageTitle', 'Bookings')

@section('content')
    <model-booking  :user="{{ auth()->user() }}" :seed="{{ $seed }}"></model-booking>
@endsection
