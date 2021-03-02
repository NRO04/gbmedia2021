@extends('layouts.app')

@section('content')
<div class="row">

	<div class="card col-lg-6 shadow offset-md-3">
		@if ($message = Session::get('success'))
		<div class="alert alert-success alert-block" id="alert-success">
			<strong>{{ $message }}</strong>
		</div>
		@endif
		<form action="/location/create" method="post">
				@csrf
		<div class="card-header">
			<span style="font-weight: bold">Settings / <a href="{{ asset('location/list')}}">Locacion</a> /</span> <span class="color-primary">Crear Locacion</span>
		</div>
		<div class="card-body">
				<div class="form-group row">
					<label for="name" class="col-sm-2 col-form-label">Nombre</label>
					<div class="col-sm-10">
						<input type="text" class="form-control @if($errors->has('name')) is-invalid @endif" name="name" id="name" placeholder="Please, enter a location name">
						@if($errors->has('name'))
							@foreach($errors->get('name') as $error)
								<div class="@if($errors->has('name')) invalid-feedback @endif">{{ $error }}</div>
							@endforeach
						@endif
					</div>
				</div>
		</div>
		<div class="card-footer border-top-0">
			<div class="form-group row my-2">
				<div class="col-md-8 offset-md-7 text-center">
					<button type="submit" class="btn btn-md btn-success">
						<i class="fa fa-save"></i> Guardar
					</button>
				</div>
			</div>
		</div>
		</form>
	</div>
</div>
@endsection
