@extends("layouts.app")
@section('content')
    <birthday></birthday>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
