@extends("layouts.app")
@section('content')
    <payroll-boutique></payroll-boutique>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
