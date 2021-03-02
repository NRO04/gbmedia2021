@extends("layouts.app")

@section('pageTitle', 'Listado de Contratos')

@section('content')
    <contracts
        :user="{{ auth()->user() }}"
        :roles="{{ $roles }}"
        :global_documents="{{ $global_documents }}"
        :locations="{{ $locations }}"
    >
    </contracts>
@endsection
@push("scripts")
    <script>
        $(document).ready(function(){
            //collapseMenu();
        });
    </script>
@endpush
