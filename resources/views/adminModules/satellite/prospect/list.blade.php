@extends("layouts.app")
@section('content')
    <prospect :permissions="{{ json_encode($user_permission) }}"></prospect>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
