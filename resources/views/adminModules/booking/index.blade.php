@extends('layouts.app')
@section('pageTitle', 'Audiovisuales')

@section('content')
  @foreach($schedules as $schedule)
    @if($schedule->id == 1)
      <audiov  :user="{{ auth()->user() }}" :bookingid="{{ $bookingid->id }}" :permissions="{{ json_encode($user_permission) }}"></audiov>
    @endif
  @endforeach
@endsection
