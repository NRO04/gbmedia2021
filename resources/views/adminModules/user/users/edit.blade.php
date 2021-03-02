@extends("layouts.app")

@section('pageTitle', 'Editando Usuario')

@section('content')
    <edit-user
        :user="{{ $user }}"
        :studio_slug="{{ json_encode(tenant('studio_slug')) }}"
        :roles="{{ $roles }}"
        :permissions="{{ json_encode($user_permissions) }}"
        :locations="{{ json_encode($locations) }}"
        :departments="{{ $departments }}"
        :countries="{{ $countries }}"
        :blood_types="{{ $blood_types }}"
        :contract_types="{{ $contract_types }}"
        :documents_types="{{ $documents_types }}"
        :banks="{{ $banks }}"
        :all_eps="{{ $eps }}"
        :quarter_transportation_aid_value="{{ $quarter_transportation_aid_value }}"
        :user_contracts="{{ json_encode($user_contracts) }}"
        :have_contracts_access="{{  json_encode($have_contracts_access) }}"
    >
    </edit-user>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
