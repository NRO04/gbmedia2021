@extends('layouts.app')

@section('pageTitle', 'Locaciones')

@section('content')

<div class="row">
	<div class="card col-lg-12">
		<div class="card-header">
			<span class="span-title">Locaciones</span>
            @can('location-create')
                <button type="button" class="btn btn-m btn-success float-right btn-sm" data-toggle="modal" data-target="#modal-create-location" id="">
                    <i class="fa fa-plus"></i> Crear
                </button>
            @endcan
		</div>
		<div class="card-body">
			<table class="table table-hover table-striped" id="location-table">
				<thead>
					<tr>
						<th>Nombre</th>
						<th>Cantidad Cuartos</th>
						<th>Last Update</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal -->
<div id="modal-create-location" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Crear Locacion</h4>
			</div>
			<div class="modal-body">
				<form action="{{ route('location.create')}}" method="post" id="form-create-location">
					@csrf
					<div class="form-group row">
						<label for="name" class="col-sm-4 col-form-label">Nombre</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" name="name" id="name" placeholder="Example" />
						</div>
					</div>
					<div class="form-group row">
						<label for="name" class="col-sm-4 col-form-label"> Nro Cuartos</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" name="rooms" id="rooms" placeholder="5"/>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-success" id="btn_create">Crear</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div id="modal-update-location" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Modificar Locacion</h4>
			</div>
			<div class="modal-body">
				<form action="{{route('location.update')}}" method="post" id="form-update-location">
					@csrf
					<div id="div_edit_content">

					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-warning" id="btn_update">Editar</button>
			</div>
		</div>
	</div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    table = $("#location-table").DataTable({
        processing: true,
        serverSide: true,
        "lengthMenu": [[100, 200, 300, 400], [100, 200, 300, 400]],
        "language": {
            url: '{{ asset('DataTables/Spanish.json') }}',
        },
        ajax: {
            url: "{{ route('location.get_locations') }}",
            dataSrc: "data",
            type: "GET",
            dataType: 'json'
        },
        columns: [
            { data: "name" },
            { data: "rooms" },
            { data: "updated_at" },
            { data: "actions" },
        ],

        columnDefs: [
            {
                targets: [1, 2, 3],
                orderable: false,
            },
        ],
    });


    $("[data-toggle=\"tooltip\"]").tooltip();

    $("#btn_create").on("click", function() {
        ResetValidations();
        $.ajax({
            url: "{{ route('location.create') }}",
            type: "POST",
            data: $("#form-create-location").serialize(),
        })
            .done(function(res) {
                if (res.success) {
                    Toast.fire({
                        icon: "success",
                        title: "La Locación fue agregada exitosamente",
                    });
                    $("#modal-create-location").modal("hide");
                    ResetModalForm("#form-create-location");
                    table.draw();
                } else {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error, comuniquese con el ADMIN",
                    });
                }
            })
            .fail(function(res) {
                CallBackErrors(res);
            });

    });
});

function Edit(id) {
    $("#modal-update-location").modal("toggle");
    $.ajax({
        url: "/location/infoEdit/" + id,
        type: "GET",
    })
        .done(function(res) {
            $("#div_edit_content").html(res);
        });
}

function UpPosition() {
    alert("UpPosition");
}

$("#btn_update").on("click", function() {
    $.ajax({
        url: "{{ route('location.update') }}",
        type: "POST",
        data: $("#form-update-location").serialize(),
    })
        .done(function(res) {
            if (res.success){
                Toast.fire({
                    icon: "success",
                    title: "La Locación fue editada exitosamente",
                });
            }
            else
            {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error, comuniquese con el ADMIN",
                });

                $("#modal-update-location").modal("hide");
                table.draw();
            }
        })
        .fail(function(res) {
            CallBackErrors(res);
        });

});
</script>

@endpush
