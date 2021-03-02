@extends('layouts.app')
@section('pageTitle', 'Módulo Contratos')

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">{{ $info->title }}</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right"></div>
            </div>
            <div class="card-body">
                <div id="container-filters" class="row">
                    <div class="col-12">
                        <div class="row">
                            {!! $info->description !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection