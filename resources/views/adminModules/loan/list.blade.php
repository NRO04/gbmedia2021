@extends('layouts.app')
@section('content')

<div class="row">
	<div class="card col-lg-12 table-responsive">
		<div class="card-header">
			<span class="span-title">Préstamos @if($status == 1) <span class="text-danger">Finalizados</span> @endif</span>
			@if($status == 0)
				<a href='{{ asset("loans/list/1") }}' class="btn btn-m btn-dark float-right btn-sm ml-2"><i class="fa fa-list"></i> Finalizados</a>
				@can('loans-create')
					<button type="button" class="btn btn-m btn-success float-right btn-sm" data-toggle="modal" data-target="#modal-create" id=""><i class="fa fa-plus"></i> Crear</button>
				@endcan
			@else
				<a href='{{ asset("loans/list/0") }}' class="btn btn-m btn-primary float-right btn-sm ml-2" ><i class="fa fa-list"></i> Listado</a>
			@endif


		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-lg-12">
					<table class="table table-hover table-striped" id="loan-table">
				<thead>
					<tr>
						<th>Solicitante</th>
						<th>Fecha Préstamo</th>
						<th>Valor Inicial</th>
						<th>% Interés</th>
						<th>Debe</th>
						<th>Desea Pagar</th>
						<th>Resumen</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
				</div>
			</div>

		</div>
	</div>
</div>

<!-- Modal -->
<div id="modal-create" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Crear Préstamo</h4>
			</div>
			<div class="modal-body">
				<form action="{{ route('loans.store')}}" method="post" id="form-create-loan">
					@csrf
					<div class="form-group row">
						<label for="name" class="col-sm-4 col-form-label">Usuario</label>
						<div class="col-sm-7">
							<select class="form-control" name="user_id">
								@foreach($users as $user)
									<option value="{{ $user->id }}">{{ $user->first_name." ".$user->last_name." ".$user->second_last_name }}</option>
								@endforeach
								option
							</select>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-4 col-form-label"> Valor Préstamo</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" name="amount" id="amount" placeholder="100000"/>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-4 col-form-label"> % Interés</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" name="interest" id="interest" placeholder="3"/>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-4 col-form-label">Valor a Pagar</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" name="installment" id="installment" placeholder="50000"/>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-success" id="btn_create"> <i class="fa fa-plus"></i> Crear</button>
			</div>
		</div>
	</div>
</div>

<!--loan_installment-->
<div class="modal fade" id="loan_installment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel3" aria-hidden="true">
  <div class="modal-dialog modal-dialog-slideout modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Resumen Préstamo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        </button>
      </div>
      <div class="modal-body">
      		<div class="container">
      			<div class="row">
      				<div class="col-12">
      					<div class="row">
      						<div class="col-lg-11">
      							<div class="alert alert-danger text-center" style="display: none"  id="alert_terminated_loan" role="alert">
                                    <h5>El Préstamo se encuentra finalizado</h5>
                                </div>
      						</div>
      						<!--user info-->
      						<div class="col-lg-11">
      							<div class="card  table-responsive">
		      						<div class="card-header">
										<div class="float-right">
											@can('loans-edit')
											<button type="button" id="btn_create_installment" class="btn btn-success btn-sm"
                                                    data-toggle="modal" data-target="#modal-create-installment"> <i class="fa fa-dollar-sign"></i> Abonar</button>
											@endcan
										</div>
									</div>
		      						<div class="card-body">
										<table class="table table-hover table-striped">
											<thead>
												<tr>
													<th>Solicitante</th>
													<th>Fecha Préstamo</th>
													<th>Valor Inicial</th>
													<th>% Interés</th>
													<th>Debe</th>
													<th>Cuotas</th>
												</tr>
											</thead>
											<tbody id="user_info">
											</tbody>
										</table>
									</div>
								</div>
      						</div>
      						<!-- /user info-->
      						<div class="col-lg-11">
      							<div class="card  table-responsive">
		      						<div class="card-body">
										<table class="table table-hover">
											<thead>
												<tr>
													<th>Fechas</th>
													<th>Intereses</th>
													<th>Abonos</th>
													<th>Total</th>
												</tr>
											</thead>
											<tbody id="installment_info">

											</tbody>
										</table>
									</div>
								</div>
      						</div>

      					</div>
      				</div>
      			</div>
      		</div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="modal-create-installment" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Abonar</h4>
			</div>
			<div class="modal-body">
                <small class="text-warning">Los abonos realizados el 15 o el último dia del mes se guardaran en la quincena siguiente de la
                    nomina</small>
				<form action="{{ route('loans.store_installment')}}" method="post" id="form-create-installment">
					@csrf
					<input type="hidden" name="loan_id" id="loan_id">
					<div class="form-group row">
						<label class="col-sm-4 col-form-label">Valor a Abonar</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" name="amount_installment" id="amount_installment" placeholder="50000"/>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-success" id="btn_store_installment"><i class="fa fa-plus"></i> Crear</button>
			</div>
		</div>
	</div>
</div>

@endsection
@push('scripts')
<script>
    $(document).ready(function() {
    	status = {{ $status }};
        Mytable = $("#loan-table").DataTable({
            processing: true,
            serverSide: true,
            "lengthMenu": [[100, 200, 300, 400], [100, 200, 300, 400]],
            "language": {
                url: '{{ asset('DataTables/Spanish.json') }}',
            },
            ajax: {
                url: "{{ route('loans.get_loans') }}",
                dataSrc: "data",
                type: "GET",
                dataType: 'json',
                data: { 'status' : status }
            },
            columns: [
                { data: "user" },
                { data: "created_at" },
                { data: "amount" },
                { data: "interest" },
                { data: "amount_due" },
                { data: "installment" },
                { data: "actions" },
            ],

            columnDefs: [
                {
                    targets: [6],
                    orderable: false,
                },
            ],

        });
    });

    $("#btn_create").on("click", function() {
        ResetValidations();
        $("#btn_create").prop('disabled' , true);
        $.ajax({
            url: "{{ route('loans.store') }}",
            type: "POST",
            data: $("#form-create-loan").serialize(),
        })
        .done(function(res) {
            if (res.success) {
                Toast.fire({
                    icon: "success",
                    title: "El préstamo fue agregado exitosamente",
                });
                $("#modal-create").modal("hide");
                ResetModalForm("#form-create-loan");
                Mytable.draw();
                $("#btn_create").prop('disabled' , false);
            } else {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error, comuniquese con el ADMIN",
                });
            }
        })
        .fail(function(res) {
            CallBackErrors(res);
			$("#btn_create").prop('disabled' , false);
        });
    });

    $("#btn_store_installment").on("click", function() {
        ResetValidations();
        $("#btn_store_installment").prop('disabled' , true);
        $.ajax({
            url: "{{ route('loans.store_installment') }}",
            type: "POST",
            data: $("#form-create-installment").serialize(),
        })
        .done(function(res) {
            if (res.success) {
            	console.log(res);
            	if (res.terminated)
            	{
            		Toast.fire({
	                    icon: "success",
	                    title: "El préstamo ha sido finalizado",
	                });
            		$("#btn_create_installment").css("display", "none");
            		$("#alert_terminated_loan").css("display", "block");
            	}
            	else
            	{
            		Toast.fire({
	                    icon: "success",
	                    title: "El abono fue agregado exitosamente",
	                });
            	}

                $("#modal-create-installment").modal("hide");
                ResetModalForm("#form-create-installment");
                openLoanInstallments($("#loan_id").val());
                Mytable.draw();
                $("#btn_store_installment").prop('disabled' , false);

            } else {
            	$("#btn_store_installment").prop('disabled' , false);
                if (res.bigger)
                {
                	Toast.fire({
	                    icon: "error",
	                    title: "Upsss.. No puedes abonar esa cantidad",
	                });
                }
                else if(res.permission == false){
                	Toast.fire({
	                    icon: "error",
	                    title: "Upsss.. No tienes permiso para esto",
	                });
                }
                else
                {
                	Toast.fire({
	                    icon: "error",
	                    title: "Upsss.. Ha ocurrido un error, comuniquese con el ADMIN",
	                });
                }
            }
        })
        .fail(function(res) {
            CallBackErrors(res);
			$("#btn_store_installment").prop('disabled' , false);
        });
    });

    function openLoanInstallments(id)
   	{
   		$("#loan_installment").modal("show");
   		$("#loan_id").val(id);
   		$.ajax({
   			url: '{{ route('loans.get_loan_installments') }}',
   			type: 'GET',
   			dataType: 'json',
   			data: {'id': id},
   		})
   		.done(function(res) {
   			$("#user_info").html(res.user_info);
   			$("#installment_info").html(res.installment_info);
   			if (res.terminated)
   			{
   				$("#btn_create_installment").css("display", "none");
            	$("#alert_terminated_loan").css("display", "block");
   			}
   			else
   			{
   				$("#btn_create_installment").css("display", "block");
   				$("#alert_terminated_loan").css("display", "none");
   			}
   		})
   		.fail(function() {
   			Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error, comuniquese con el ADMIN",
                });
   		});
   	}

</script>

@endpush
