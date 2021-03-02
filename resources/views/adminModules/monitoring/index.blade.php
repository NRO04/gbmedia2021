@extends('layouts.app')
@section('pageTitle', 'Monitoreo')

@section('content')
    <monitoring :user="{{ auth()->user() }}" :permissions="{{ json_encode($user_permission) }}"></monitoring>
@endsection


@push("scripts")
    <script>
        $(document).ready(function () {
            collapseMenu();
        });
    </script>
@endpush