@extends('layouts.auth')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card-group">
                    <div class="card p-4">
                        <div class="card-body">
                            <h1>Iniciar Sesión</h1>
                            <hr>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <svg class="c-icon">
                                              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                                            </svg>
                                        </span>
                                    </div>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" autofocus placeholder="Correo Electrónico">
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <svg class="c-icon">
                                              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                                            </svg>
                                        </span>
                                    </div>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password" placeholder="Contraseña">
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button class="btn btn-info btn-block px-4" type="submit">{{ __('Entrar') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card text-white bg-primary py-5 d-md-down-none border-0" style="width:44%; background-color: #4c4f54 !important;">
                        <div
                            class="card-body text-center d-flex align-items-center justify-content-center align-center">
                            <div class="">
                                <div class="logo mb-4">
                                    @if(file_exists(public_path("../storage/app/public/" . tenant('studio_slug') . "/logo/" . tenant('studio_logo'))))
                                        <img src="{{ global_asset("../storage/app/public/" . tenant('studio_slug') . "/logo/" . tenant('studio_logo')) }}" alt="{{ tenant('studio_name') }}" class="img-fluid">
                                    @else
                                        <h1>{{ tenant('studio_name') }}</h1>
                                        <small class="text-muted text-bold">Estudio</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
