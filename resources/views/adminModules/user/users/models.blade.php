@extends("layouts.app")

@section('pageTitle', 'Listado de Usuarios')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Listado de Modelos</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                </div>
            </div>
        </div>
        <div class="card-body">
            <user-models
                :user="{{ auth()->user() }}"
                :roles="{{ $roles }}"
                :permissions="{{ json_encode($user_permissions) }}"
                :locations="{{ json_encode($locations) }}"
                :departments="{{ $departments }}"
            >
            </user-models>
        </div>
    </div>
@endsection
@push("scripts")
    <script>

        $(document).ready(function(){
            collapseMenu();
        });

    </script>
@endpush
