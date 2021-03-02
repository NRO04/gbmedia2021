@extends('layouts.app')

@section('pageTitle', 'Menú Cafetería')

@section('content')
    <cafeteria-menu :types="{{ $types }}" :weeks="{{ json_encode($weeks) }}" :permissions="{{ json_encode($user_permission) }}"></cafeteria-menu>
@endsection
@push('scripts')
@endpush
