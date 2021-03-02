@extends('layouts.app')

@section('pageTitle', 'Categorías')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Categorías Boutique</li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Listado de Categorías</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    @can('boutique-categories-create')
                        <a class="btn btn-success btn-sm" data-target="#modal-create" data-toggle="modal" id="btn-open-create-category-modal">
                            <i class="fa fa-plus"></i> Crear
                        </a>
                    @endcan
                    @can('boutique-products')
                        <a class="btn btn-info btn-sm" href="{{ route('boutique.products') }}">
                            <i class="fa fa-list"></i> Productos
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover" id="categories-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Creación</th>
                            <th>Productos <i data-toggle="tooltip" title="" data-original-title="Cantidad de productos registrados en la categoría" class="fa fa-question-circle"></i></th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @can('boutique-categories-create')
    <!-- Modal Create -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-create" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Crear Categoría</h4>
                </div>
                <div class="modal-body">
                    <form id="form-create">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="required">Nombre:</label>
                            <input class="form-control" id="name" name="name" placeholder="Disfraces" type="text"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-create" type="button">Crear</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('boutique-categories-edit')
    <!-- Modal Edit -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-edit" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Categoría</h4>
                </div>
                <div class="modal-body">
                    <form id="form-edit">
                        @csrf
                        <div class="form-group">
                            <label for="edit-name" class="required">Nombre:</label>
                            <input class="form-control" id="edit-name" name="name" placeholder="Disfraces" type="text"/>
                        </div>
                        <input type="hidden" id="edit-category-id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-warning" id="btn-edit" type="button">Editar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

@endsection @push('scripts')
    <script>
        let table = null;

        $(document).ready(function () {
            if ($("#categories-table").length > 0) {
                new Vue({
                    el: "#categories-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#categories-table").DataTable({
                                processing: true,
                                serverSide: true,
                                pageLength: 50,
                                language: {
                                    url: '{{ asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('boutique.get_categories') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                },
                                columns: [
                                    { data: "name" },
                                    { data: "created_at" },
                                    { data: "products_count" },
                                    { data: "actions" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [3],
                                        orderable: false,
                                    },
                                    {
                                        targets: [3],
                                        searchable: false,
                                    },
                                ],
                                fnDrawCallback: function() {
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        },
                    },
                });
            }
        });

        // CONTROL ACTIONS
        $("#btn-open-create-category-modal").on("click", function (e) {
            ResetModalForm("#form-create");
            $('#container-new-category').addClass('d-none');
            ResetValidations();
        });

        $("#btn-create").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = new FormData(document.getElementById('form-create'));

            $.ajax({
                url: "{{ route('boutique.save_category') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                $('#modal-create').modal('toggle');
                Toast.fire({
                    icon: "success",
                    title: "Agregado exitosamente",
                });

                ResetModalForm("#form-create");
                table.ajax.reload();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-create').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        function edit(id)
        {
            let category_name = $('#edit-category-' + id).data('name');
            $('#edit-name').val(category_name);
            $('#edit-category-id').val(id);

            $("#modal-edit").modal('show');
        }

        function remove(id)
        {
            SwalGB.fire({
                title: '¿Está seguro que desea eliminar la categoría?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('boutique.delete_category') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        },
                    }).done(function (res) {
                        table.ajax.reload();

                        Toast.fire({
                            icon: "success",
                            title: "Categoría eliminada exitosamente",
                        });
                    }).fail(function (res) {
                        let json = res.responseJSON;

                        if(!json.success) {
                            Toast.fire({
                                icon: "error",
                                title: json.msg,
                                timer: 10000,
                            });
                        }
                    });
                }
            });
        }

        $("#btn-edit").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = new FormData(document.getElementById('form-edit'));

            $.ajax({
                url: "{{ route('boutique.edit_category') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                $('#modal-edit').modal('hide');
                Toast.fire({
                    icon: "success",
                    title: "Categoría modificada exitosamente",
                });

                ResetModalForm("#form-edit");
                table.ajax.reload();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-create').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });
    </script>
@endpush
