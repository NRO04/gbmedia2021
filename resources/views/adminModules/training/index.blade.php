@extends('layouts.app')
@section('pageTitle', 'Capacitaciones')

@section('content')
    <training :user="{{ auth()->user() }}"></training>
@endsection
