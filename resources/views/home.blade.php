@extends('layouts.app')

@section('content')
<div class="container">
<div class="b-overlay position-absolute" style="top: 0px; left: 0px; bottom: 0px; right: 0px; z-index: 10;"><div class="position-absolute bg-light" style="top: 0px; left: 0px; bottom: 0px; right: 0px; opacity: 0.85; backdrop-filter: blur(2px);"></div><div class="position-absolute" style="top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%);"><span aria-hidden="true" class="spinner-border"><!----></span></div></div>


    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
