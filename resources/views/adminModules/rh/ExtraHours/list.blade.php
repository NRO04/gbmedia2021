@extends('layouts.app')

@push('styles')
<style>
</style>
@endpush
@section('content')
<extra :daytime_hours   = "{{ $daytime_hours }}"
       :night_hours     = "{{ $night_hours }}"
       :id_user         = "{{ $id_user }}"
       :user_name       = "'{{ $user_name }}'"
       :current_date    = "'{{ $current_date }}'"
       :list_user       = "{{ json_encode($list_user)}}"
       :ranks           = "{{ $ranks }}"
       :yesterday_date  = "'{{ $yesterday_date }}'"
       :permissions="{{ json_encode($user_permission) }}">
</extra>
@endsection @push('scripts')
<script>
    $(document).ready(function(){
        collapseMenu();
    });
</script>
@endpush
