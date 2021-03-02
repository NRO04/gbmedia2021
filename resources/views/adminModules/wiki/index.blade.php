@extends('layouts.app')
@section('pageTitle', 'Wiki')

@section('content')
    <wiki :permissions="{{ json_encode($user_permission) }}"></wiki>
@endsection


@push('scripts')
    <script>
        $(document).ready(function () {
            $("#sidebar").removeClass("c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show");
            $("#sidebar").addClass("c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show c-sidebar-unfoldable");
        });
  </script>
@endpush
