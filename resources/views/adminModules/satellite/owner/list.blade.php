@extends("layouts.app")
@section('content')
    <owner-main :permissions="{{ json_encode($user_permission) }}"></owner-main>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
