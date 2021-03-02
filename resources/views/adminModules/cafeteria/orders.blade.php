@extends('layouts.app')

@section('pageTitle', 'Pedidos Cafetería')

@section('content')
    <orders :types="{{ $cafeteria_types }}" :breakfast_categories="{{ json_encode($breakfast_categories) }}" :locations="{{ $locations }}" :user="{{ auth::user() }}" :users="{{ $users }}"></orders>
@endsection
@push('scripts')
@endpush
