@extends('layouts.app')
@section('pageTitle', 'Capacitaciones')

@section('content')
    <show :trainingId="{{ $training->id }}" :user="{{ auth()->user() }}"></show>
@endsection
