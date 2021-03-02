@extends("layouts.app")
@section('content')
    <generate></generate>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
