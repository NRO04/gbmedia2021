@extends('layouts.app')
@section('pageTitle', 'Editar Información Módulo Contratos')

@section('content')
    <edit-module-info
        :info="{{ $info }}"
    >
    </edit-module-info>
@endsection
