@extends('layouts.app')

@section('pageTitle', 'Listado de Productos')

@section('breadcrumb')
    <li class="breadcrumb-item" style="font-weight: bold">Productos Boutique</li>
    <li class="breadcrumb-item active">Listado</li>
@endsection

@section('content')
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Listado de Productos</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                    @can('boutique-products-create')
                        <a class="btn btn-success btn-sm" data-target="#modal-create-product" data-toggle="modal" id="btn-open-create-product-modal">
                            <i class="fa fa-plus"></i> Crear
                        </a>
                    @endcan
                    @can('boutique-categories')
                        <a class="btn btn-info btn-sm" href="{{ route('boutique.categories') }}">
                            <i class="fa fa-list"></i> Categorías
                        </a>
                    @endcan
                    @can('boutique-export-excel-inventories')
                        <a class="btn btn-outline-success btn-sm" id="export-week-sales" href="{{ route('boutique.export_inventory') }}">
                            <i class="fa fa-file-excel"></i> Exportar
                        </a>
                    @endcan
                    @can('boutique-logs')
                        <a class="btn btn-outline-warning btn-sm ml-2" id="btn-open-boutique-logs" onclick="logs()">
                            <i class="fa fa-history"></i> Logs
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div id="container-filters" class="row">
                    <div class="col-12">
                        <div class="row">
                            <label for="filter-category" class="col-12 col-sm-3 col-md-2 col-lg-2 col-xl-1 pt-2">Categoría:</label>
                            <div class="col-12 col-sm-9 col-md-10 col-lg-3 col-xl-2 my-1">
                                <select id="filter-category" class="form-control form-control-sm">
                                    <option value="">Todas</option>
                                    @foreach($categories AS $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="filter-stock" class="col-12 col-sm-3 col-md-2 col-lg-1 col-xl-1 pt-2">Stock:</label>
                            <div class="col-12 col-sm-9 col-md-10 col-lg-2 col-xl-2 my-1">
                                <select id="filter-stock" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="1">En inventario</option>
                                    <option value="2">Agotados</option>
                                </select>
                            </div>
                            <label for="filter-location" class="col-12 col-sm-3 col-md-2 col-lg-2 col-xl-1 pt-2">Locación:</label>
                            <div class="col-12 col-sm-9 col-md-10 col-lg-2 col-xl-2 my-1">
                                <select id="filter-location" class="form-control form-control-sm">
                                    <option value="">Todas</option>
                                    @foreach($locations AS $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-right mt-4">
                    @can('boutique-total-money')
                        <h5 class="text-muted">Total: <span id="total-inventory" class="text-success text-bold">$0</span></h5>
                    @endcan
                </div>
                <hr>
                <table class="table table-hover" id="products-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Imagen</th>
                            <th>Precio Unitario</th>
                            <th>Precio Mayorista</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @can('boutique-products-create')
    <!-- Modal Create Product -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-create-product" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Crear Producto</h4>
                </div>
                <div class="modal-body">
                    <form id="form-create-product">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="required">Nombre:</label>
                            <input class="form-control" id="name" name="name" placeholder="Media Algodón Flores" type="text"/>
                        </div>
                        <div class="form-group">
                            <label for="image">Imagen</label>
                            <div id="image"></div>
                        </div>
                        <div class="form-group">
                            <label for="nationality" class="required">Nacionalidad:</label>
                            <select name="nationality" id="nationality" class="form-control">
                                <option value="Nacional" selected>Nacional</option>
                                <option value="Internacional">Internacional</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="select-category" class="required">Categoría:</label>
                            <select name="category" id="select-category" class="form-control">
                                @foreach($categories AS $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                                <option value="other">Otro</option>
                            </select>
                            <div id="container-new-category" class="d-none">
                                <div class="form-group mt-2">
                                    <input class="form-control" id="new_category" name="new_category" placeholder="Rellene este campo para crear una nueva categoría" type="text" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="barcode">Código de Barras:</label>
                            <input class="form-control" id="barcode" name="barcode" placeholder="5174123358708" type="text"/>
                        </div>
                        <div class="form-group row">
                            <label for="stock-alarm" class="col-12 col-md-4 pt-2">Alarma Inventario:</label>
                            <input class="form-control col-12 col-md-2" id="stock-alarm" name="stock_alarm" type="number" min="1" value="0"/>
                        </div>
                        <div class="form-group row">
                            <label for="location-alarm" class="col-12 col-md-4 pt-2">Alarma Sede:</label>
                            <input class="form-control col-12 col-md-2" id="location-alarm" name="location_alarm" type="number" min="1" value="0"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-create-product" type="button">Crear</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('boutique-products-sell')
    <!-- Modal Sell Product -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-sell" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Vender Producto</h4>
                </div>
                <div class="modal-body">
                    <form id="form-sell-product" class="row">
                        @csrf
                        <div class="col-12 col-md-4 mb-3 b-r-1" style="border-color: #4c4f54">
                            <img class="img-fluid" alt="Imagen del Producto" id="image-product">
                        </div>
                        <div class="col-12 col-md-8">
                            <h5><span id="span-product-name"></span> | Precio: <span class="text-success" id="span-product-price"></span></h5>
                            <hr>
                            <div class="form-group row">
                                <label class="col-12">Locación:</label>
                                <div class="col-12">
                                    @foreach($locations AS $location)
                                        <div class="form-check form-check-inline mr-1">
                                            <input id="location-{{ $location->id }}" type="radio" value="{{ $location->id }}" name="location_id" class="checkbox-location mr-1">
                                            <label for="location-{{ $location->id }}" class="form-check-label mr-1">{{ $location->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="sk-wave d-none loader" style="margin: 0 !important; height: auto !important;">
                                    <div class="sk-rect sk-rect1"></div>
                                    <div class="sk-rect sk-rect2"></div>
                                    <div class="sk-rect sk-rect3"></div>
                                    <div class="sk-rect sk-rect4"></div>
                                    <div class="sk-rect sk-rect5"></div>
                                </div>
                            </div>
                            <div class="form-group row d-none" id="container-quantity">
                                <label for="product-quantity" class="col-12">Cantidad:</label>
                                <div class="col-12">
                                    <div id="status-quantity" class="d-none"></div>
                                    <select name="quantity" id="product-quantity" class="col-3 form-control d-none"></select>
                                </div>
                            </div>
                            <div class="d-none" id="container-sell-to">
                                <label for="" class="col-12 row">Verder a:</label>
                                <div class="form-group row">
                                    <div class="col-12 row">
                                        <div class="col-6">
                                            <label>
                                                <input type="radio" name="selected_user" id="for-model" value="models" class="radio-for">
                                                <span class="label-text">Modelos</span>
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <label>
                                                <input type="radio" name="selected_user" id="for-user" value="users" class="radio-for">
                                                <span class="label-text">Otro cargo</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="d-none" id="container-for-models">
                                        <hr>
                                        <label for="select-for-model">Modelos</label>
                                        <select class="col-12 col-sm-8 form-control" name="user_id" id="select-for-model">
                                            <option value="">Seleccione...</option>
                                            @foreach($models AS $model)
                                                <option value="{{ $model->id }}">{{ $model->nick }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-none" id="container-for-users">
                                        <hr>
                                        <label for="select-for-user">Usuarios</label>
                                        <select class="col-12 col-sm-8 form-control" name="user_id" id="select-for-user">
                                            <option value="">Seleccione...</option>
                                            @foreach($users AS $user)
                                                <option value="{{ $user->id }}">{{ $user->roleUserShortName() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {{--<div class="form-group row d-none" id="container-users">
                                <label for="select-user" class="col-12">Verder a:</label>
                                <div class="col-12 col-sm-8">
                                    <select name="user_id" id="select-user" class="form-control">
                                        <option value="">Seleccione...</option>
                                        @foreach($users AS $user)
                                            <option value="{{ $user->id }}">{{ $user->show_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>--}}
                        </div>
                        <input type="hidden" id="input-product-id" name="product_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-sell-product" type="button">Vender</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('boutique-products-edit')
    <!-- Modal Edit -->
    <div aria-hidden="true" aria-labelledby="modalCreateAlarmLabel" class="modal fade overflow-auto" id="modal-edit" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Producto</h4>
                </div>
                <div class="modal-body">
                    <form id="form-edit" class="row">
                        <div class="col-12 col-lg-7 b-r-1" style="border-color: rgb(76, 79, 84);">
                            @csrf
                            <div class="form-group">
                                <label for="edit-name" class="required">Nombre:</label>
                                <input class="form-control" id="edit-name" name="name" placeholder="Media Algodón Flores" type="text"/>
                            </div>
                            <div class="form-group">
                                <label for="edit-price" class="required">Precio Unitario:</label>
                                <input class="form-control col-12 col-sm-4" id="edit-price" name="unit_price" placeholder="" type="number" value="0"/>
                            </div>
                            <div class="form-group">
                                <label for="edit-wholesaler-price">Precio Mayorista:</label>
                                <input class="form-control col-12 col-sm-4" id="edit-wholesaler-price" name="wholesaler_price" placeholder="" type="number" value="0"/>
                            </div>
                            <div class="form-group">
                                <label for="edit-image">Imagen</label>
                                <div id="edit-image"></div>
                            </div>
                            <div class="form-group">
                                <label for="edit-nationality" class="required">Nacionalidad:</label>
                                <select name="nationality" id="edit-nationality" class="form-control">
                                    <option value="Nacional" selected>Nacional</option>
                                    <option value="Internacional">Internacional</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-category" class="required">Categoría:</label>
                                <select name="category" id="edit-category" class="form-control">
                                    @foreach($categories AS $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit-barcode">Código de Barras:</label>
                                <input class="form-control col-12 col-md-6" id="edit-barcode" name="barcode" placeholder="5174123358708" type="text"/>
                            </div>
                            <div class="form-group">
                                <label for="edit-stock-alarm" class="col-12 col-md-4 row">Alarma Inventario:</label>
                                <input class="form-control col-12 col-md-2" id="edit-stock-alarm" name="stock_alarm" type="number" min="1" value="0"/>
                            </div>
                            <div class="form-group">
                                <label for="edit-location-alarm" class="col-12 col-sm-4 pt-2 row">Alarma Sede:</label>
                                <input class="form-control col-12 col-md-2" id="edit-location-alarm" name="location_alarm" type="number" min="1" value="0"/>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <label class="text-muted">Inventario</label>
                            <div class="form-group" id="container-locations-stock">
                                <div class="col-12">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Locación</th>
                                                <th>Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label for="edit-quantity" class="col-12 text-right">
                                    Nueva Cantidad:&nbsp;
                                    <i class="fa fa-info-circle text-warning" data-toggle="tooltip" title="Tenga en cuenta que la cantidad se modificará en la locación base ({{ $base_location_name }})"></i>
                                </label>
                                <input class="form-control col-12 col-xs-4 col-sm-4 col-md-8 col-lg-4 float-right" id="edit-quantity" name="quantity" type="number" min="0" value="0"/>
                            </div>
                        </div>
                        <input type="hidden" name="id" id="edit-product-id">
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

    @can('boutique-products-return')
    <!-- Modal Return Product -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-return-product" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Regresar Producto</h4>
                </div>
                <div class="modal-body">
                    <form id="form-return-product">
                        @csrf
                        <div class="form-group row" id="container-return-locations-stock">
                            <div class="col-12">
                                <h5 id="label-return-product-name"></h5>
                                <hr>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Locación</th>
                                            <th>Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-12 col-sm-6">
                                <label for="select-return-product-location">Locación:</label>
                                <select class="form-control" name="from_location_id" id="select-return-product-location">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="sk-wave small-sk-wave d-none loader">
                                    <div class="sk-rect sk-rect1"></div>
                                    <div class="sk-rect sk-rect2"></div>
                                    <div class="sk-rect sk-rect3"></div>
                                    <div class="sk-rect sk-rect4"></div>
                                    <div class="sk-rect sk-rect5"></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 d-none" id="container-return-product-quantity">
                                <label for="select-return-product-quantity">Cantidad:</label>
                                <select class="form-control" name="quantity" id="select-return-product-quantity"></select>
                            </div>
                        </div>
                        <p>
                            <small><i class="fa fa-info-circle text-warning"></i> Tenga en cuenta que las cantidades a regresar se sumarán a la locación base ({{ $base_location_name }})</small>
                        </p>
                        <input type="hidden" id="input-return-product-id" name="product_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-return-product" type="button">Continuar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('boutique-products-transfer')
    <!-- Modal Transfer Product -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-transfer-product" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-primary" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Trasladar Producto</h4>
                </div>
                <div class="modal-body">
                    <form id="form-transfer-product">
                        @csrf
                        <div class="form-group row" id="container-transfer-locations-stock">
                            <div class="col-12">
                                <h5 id="label-transfer-product-name"></h5>
                                <hr>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Locación</th>
                                            <th>Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-12 col-sm-6">
                                <label for="select-transfer-product-location">Trasladar a:</label>
                                <select class="form-control" name="to_location_id" id="select-transfer-product-location">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="sk-wave small-sk-wave d-none loader">
                                    <div class="sk-rect sk-rect1"></div>
                                    <div class="sk-rect sk-rect2"></div>
                                    <div class="sk-rect sk-rect3"></div>
                                    <div class="sk-rect sk-rect4"></div>
                                    <div class="sk-rect sk-rect5"></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 d-none" id="container-transfer-product-quantity">
                                <label for="select-transfer-product-quantity">Cantidad:</label>
                                <select class="form-control" name="quantity" id="select-transfer-product-quantity"></select>
                            </div>
                        </div>
                        <p>
                            <small><i class="fa fa-info-circle text-warning"></i> Tenga en cuenta que las cantidades a trasladar se tomarán de la locación base ({{ $base_location_name }})</small>
                        </p>
                        <input type="hidden" id="input-transfer-product-id" name="product_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                    <button class="btn btn-sm btn-success" id="btn-transfer-product" type="button">Continuar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('boutique-logs')
    <!-- Modal Product Logs -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-product-logs" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-warning modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Historial del Producto <span id="span-product-logs-name"></span></h4>
                </div>
                <div class="modal-body">
                    <table class="table table-hover table-striped" id="product-logs-table">
                        <thead>
                            <tr>
                                <th>Acción</th>
                                <th>Cant. Anterior</th>
                                <th>Cant. Nueva</th>
                                <th>Hecho Por</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-secondary" data-dismiss="modal" type="button">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('boutique-logs')
    <!-- Modal Boutique Logs -->
    <div aria-hidden="true" class="modal fade overflow-auto" id="modal-logs" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-warning modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Historial Movimientos Boutique</h4>
                </div>
                <div class="modal-body">
                    <div class="col-12">
                        <i class="fa fa-info-circle text-warning"></i> Aquí se muestra el historial general de movimientos realizados en boutique.
                    </div>
                    <hr>
                    <table class="table table-hover table-striped" id="logs-table">
                        <thead>
                            <tr>
                                <th>Acción</th>
                                <th>Hecho Por</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-secondary" data-dismiss="modal" type="button">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endcan

@endsection @push('scripts')
    <script>
        let locations = [];
        let base_location_name = '{{ $base_location_name }}';
        let category_id = null;
        let stock = null;
        let location_id = null;

        let no_stock = false;
        let table = null;
        let logs_table = null;
        let products_logs_table = null;

        $(document).ready(function () {
            collapseMenu();

            if ($("#products-table").length > 0) {
                new Vue({
                    el: "#products-table",
                    data: {
                        dataTable: null,
                    },
                    mounted()
                    {
                        this.getData();
                    },
                    methods: {
                        getData: function () {
                            table = $("#products-table").DataTable({
                                processing: true,
                                serverSide: false,
                                pageLength: 50,
                                language: {
                                    url: '{{ global_asset("DataTables/Spanish.json") }}',
                                },
                                ajax: {
                                    url: '{{ route('boutique.get_products') }}',
                                    dataSrc: "data",
                                    type: "GET",
                                    data: function(data) {
                                        data.category_id = category_id;
                                        data.stock = stock;
                                        data.location_id = location_id;
                                    },
                                    beforeSend: function() {
                                        $('html,body').animate({
                                            scrollTop: $('html').offset().top
                                        }, 200);

                                        $('#global-spinner').removeClass('d-none');
                                    },
                                    complete: function (res) {
                                        let total = res.responseJSON.total;
                                        $('#total-inventory').text(total);

                                        $('html,body').animate({
                                            scrollTop: $('html').offset().top
                                        }, 1);

                                        $('#global-spinner').addClass('d-none');
                                    },
                                },
                                columns: [
                                    { data: "name" },
                                    { data: "image" },
                                    { data: "unit_price" },
                                    { data: "wholesaler_price" },
                                    { data: "quantity" },
                                    { data: "total" },
                                    { data: "category_name" },
                                    { data: "actions" },
                                ],
                                columnDefs: [
                                    {
                                        targets: [1, 4, 7],
                                        orderable: false,
                                    },
                                    {
                                        targets: [7],
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
        $("#btn-open-create-product-modal").on("click", function (e) {
            ResetModalForm("#form-create-product");
            $("#image").html('');

            $("#image").spartanMultiImagePicker({
                fieldName: 'image',
                maxCount: 1,
                groupClassName: 'col-xs-12',
                maxFileSize: 5000000,
                onExtensionErr: function(index, file){
                },
                onSizeErr: function(index, file){
                    alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
                }
            });

            $('#container-new-category').addClass('d-none');
            ResetValidations();
        });

        $("#select-category").on("click", function (e) {
            let selected = $(this).val();

            if(selected === 'other') {
                $('#container-new-category').removeClass('d-none');
            } else {
                $('#container-new-category').addClass('d-none');
                $('#new_category').val('');
            }
        });

        $("#btn-create-product").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = new FormData(document.getElementById('form-create-product'));

            $.ajax({
                url: "{{ route('boutique.save_product') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                $('#modal-create-product').modal('toggle');
                Toast.fire({
                    icon: "success",
                    title: "Agregado exitosamente",
                });

                ResetModalForm("#form-create-product");
                table.ajax.reload();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-create-product').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            });
        });

        $(".checkbox-location").on("change", function (e) {
            no_stock = false;

            let product_id = $('#input-product-id').val();
            let location_id = $(this).val();

            $('.loader').removeClass('d-none');
            $('#container-quantity').addClass('d-none');
            $('#container-sell-to').addClass('d-none');
            $('#status-quantity').addClass('d-none');
            $('#status-quantity').html('');
            $('#product-quantity').addClass('d-none');
            $('#product-quantity').html('');

            $.ajax({
                url: "{{ route('boutique.get_product_location_quantity') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id,
                    location_id,
                },
            })
            .done(function (res) {
                let options = '';
                let quantity = res.quantity;

                $('.loader').addClass('d-none');
                $('#container-quantity').removeClass('d-none');

                if(res == '' || res.quantity <= 0) {
                    $('#status-quantity').removeClass('d-none');
                    $('#status-quantity').html('<b class="text-warning">No hay stock disponible del producto en la locación seleccionada.</b>');
                    no_stock = true;
                    return;
                }

                $('#container-sell-to').removeClass('d-none');

                for (let i = 1; i <= quantity; i++) {
                    $('#product-quantity').removeClass('d-none');
                    options += "<option value='" + i + "'>" + i + "</option>";
                }

                $('#product-quantity').append(options);
            })
            .fail(function (res, textStatus, xhr) {
                $('.loader').removeClass('d-none');
            });
        });

        $("#btn-sell-product").on("click", function (e) {
            let sell_to = $(".radio-for:checked").val();
            let user = "";

            if(sell_to === 'users') {
                user = $('#select-for-user').val();
            }
            else if (sell_to === 'models')
            {
                user = $('#select-for-model').val();
            }

            let location_selected = $('input[name="location_id"]:checked').is(':checked');

            if(!location_selected) {
                Toast.fire({
                    icon: "warning",
                    title: "Debe seleccionar la locación",
                });

                return;
            }

            if(no_stock) {
                Toast.fire({
                    icon: "info",
                    title: "No hay stock disponible del producto en la locación seleccionada.",
                    timer: 5000
                });

                return;
            }

            if(user === "") {
                Toast.fire({
                    icon: "warning",
                    title: "Debe seleccionar el usuario o modelo a quien se le va a vender el producto",
                });

                return;
            }

            SwalGB.fire({
                title: '¿Está seguro que desea vender el producto?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    DisableModalActionButtons();

                    let form_data = $('#form-sell-product').serialize();

                    $.ajax({
                        url: '{{ route('boutique.sell_product') }}',
                        type: 'POST',
                        data: form_data,
                    }).done(function (res) {
                        if(res.success) {
                            Toast.fire({
                                icon: "success",
                                title: "Venta registrada exitosamente",
                            });

                            $('#modal-sell').modal('hide');
                            EnableModalActionButtons();
                            table.ajax.reload();
                        } else {
                            switch (res.code) {
                                case 1: // No owner assigned to selected user
                                    Toast.fire({
                                        icon: "error",
                                        title: res.msg,
                                        timer: 8000,
                                    });
                                break;

                                case 2: // User is blocked to buy this amount
                                    Toast.fire({
                                        icon: "error",
                                        title: res.msg,
                                        timer: 8000,
                                    });
                                break;

                                case 3: // No enough products in the location
                                    Toast.fire({
                                        icon: "error",
                                        title: res.msg,
                                        timer: 8000,
                                    });

                                    $('#modal-sell').modal('hide');
                                    EnableModalActionButtons();
                                    table.ajax.reload();

                                break;

                                default: // Can't sell the product
                                    Toast.fire({
                                        icon: "error",
                                        title: 'No se ha podido registrar la venta. Por favor, intenta mas tarde.',
                                    });
                                break;
                            }
                        }
                    }).fail(function (res) {
                        let json = res.responseJSON;
                        if (!json.success) {
                            Toast.fire({
                                icon: "error",
                                title: json.msg,
                                timer: 10000,
                            });
                        }
                    }).always(function () {
                        EnableModalActionButtons();
                    });
                }
            });
        });

        $("#btn-edit").on("click", function (e) {
            ResetValidations();
            DisableModalActionButtons();

            let form_data = new FormData(document.getElementById('form-edit'));

            $.ajax({
                url: "{{ route('boutique.edit_product') }}",
                type: "POST",
                data: form_data,
                contentType: false,
                processData: false
            })
            .done(function (res) {
                $('#modal-edit').modal('hide');

                Toast.fire({
                    icon: "success",
                    title: "Modificado exitosamente",
                });

                ResetModalForm("#form-edit");
                table.ajax.reload();
            })
            .fail(function (res, textStatus, xhr) {
                $('#modal-edit').animate({ scrollTop: 0 }, 500);
                CallBackErrors(res);
            }).always(function () {
                EnableModalActionButtons();
            });
        });

        $('#select-return-product-location').on('change', function () {
            let product_id = $('#input-return-product-id').val();
            let location_id = $(this).val();

            $('#select-return-product-quantity').html('');
            $('#container-return-product-quantity').addClass('d-none');
            $('.loader').removeClass('d-none');

            if(location_id != "") {
                $.ajax({
                    url: "{{ route('boutique.get_product_location_quantity') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id,
                        location_id,
                    },
                })
                .done(function (res) {
                    let quantity = res.quantity;
                    let options = '';

                    for (let i = 1; i <= quantity; i++) {
                        options += "<option value='" + i + "'>" + i + "</option>";
                    }

                    $('#select-return-product-quantity').append(options);

                    $('.loader').addClass('d-none');
                    $('#container-return-product-quantity').removeClass('d-none');
                })
                .fail(function (res, textStatus, xhr) {
                    $('.loader').removeClass('d-none');
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información",
                    });
                });
            } else {
                $('.loader').addClass('d-none');
            }
        });

        $('#select-transfer-product-location').on('change', function () {
            let product_id = $('#input-transfer-product-id').val();
            let location_id = $(this).val();

            $('#container-transfer-product-quantity').removeClass('d-none');
        });

        $('#btn-return-product').on('click', function () {
            let location_id = $('#select-return-product-location').val();
            let location_name = $('#select-return-product-location option:selected').text();
            let quantity = $('#select-return-product-quantity').val();

            if(location_id == "" || parseInt(quantity) <= 0 || quantity == null) {
                SwalGB.fire({
                    title: '¡Atención!',
                    text: 'Debe seleccionar la locación y la cantidad que desea regresar',
                    icon: 'warning',
                    showCancelButton: false,
                });

                return;
            }

            let form_data = $('#form-return-product').serialize();

            SwalGB.fire({
                title: '¿Está seguro que desea regresar ' + quantity + ' unidad(es) del producto de ' + location_name + ' a ' + base_location_name + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    DisableModalActionButtons();

                    $.ajax({
                        url: '{{ route('boutique.return_product') }}',
                        type: 'POST',
                        data: form_data,
                    }).done(function (res) {
                        if (res.code === 1) {
                            Toast.fire({
                                icon: "error",
                                title: res.msg,
                            });
                        } else {
                            Toast.fire({
                                icon: "success",
                                title: "Producto regresado correctamente",
                            });

                            $('#modal-return-product').modal('hide');

                            table.ajax.reload();
                        }
                    }).fail(function (res) {
                        let json = res.responseJSON;
                        if (!json.success) {
                            Toast.fire({
                                icon: "error",
                                title: json.msg,
                                timer: 10000,
                            });
                        }
                    }).always(function () {
                        EnableModalActionButtons();
                    });
                }
            });
        });

        $('#btn-transfer-product').on('click', function () {
            let location_id = $('#select-transfer-product-location').val();
            let location_name = $('#select-transfer-product-location option:selected').text();
            let quantity = $('#select-transfer-product-quantity').val();

            if(location_id == "" || parseInt(quantity) <= 0 || quantity == null) {
                SwalGB.fire({
                    title: '¡Atención!',
                    text: 'Debe seleccionar la locación y la cantidad que desea transferir',
                    icon: 'warning',
                    showCancelButton: false,
                });

                return;
            }

            let form_data = $('#form-transfer-product').serialize();

            SwalGB.fire({
                title: '¿Está seguro que desea transferir ' + quantity + ' unidad(es) del producto de ' + base_location_name + ' a ' + location_name + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    DisableModalActionButtons();

                    $.ajax({
                        url: '{{ route('boutique.transfer_product') }}',
                        type: 'POST',
                        data: form_data,
                    }).done(function (res) {
                        if (res.code === 1) {
                            Toast.fire({
                                icon: "error",
                                title: res.msg,
                            });
                        } else {
                            Toast.fire({
                                icon: "success",
                                title: "Producto transferido correctamente",
                            });

                            $('#modal-transfer-product').modal('hide');

                            table.ajax.reload();
                        }
                    }).fail(function (res) {
                        let json = res.responseJSON;
                        if (!json.success) {
                            Toast.fire({
                                icon: "error",
                                title: json.msg,
                                timer: 10000,
                            });
                        }
                    }).always(function () {
                        EnableModalActionButtons();
                    });
                }
            });
        });

        $('.radio-for').on('click', function () {
            $('#container-for-models').addClass('d-none');
            $('#container-for-users').addClass('d-none');

            let selected = $(this).val();

            if(selected === 'users') {
                $('#container-for-users').removeClass('d-none');
                $('#select-for-user').prop('disabled', false);
                $('#select-for-model').prop('disabled', true);
            }
            else if (selected === 'models')
            {
                $('#container-for-models').removeClass('d-none');
                $('#select-for-model').prop('disabled', false);
                $('#select-for-user').prop('disabled', true);

            }
        });

        $("#filter-category").on("change", function (e) {
            $('#filter-stock, #filter-location').val('');
            category_id = $('#filter-category').val();

            stock = null;
            location_id = null;
            table.ajax.reload();
        });

        $("#filter-stock").on("change", function (e) {
            $('#filter-category, #filter-location').val('');
            stock = $('#filter-stock').val();

            category_id = null;
            location_id = null;
            table.ajax.reload();
        });

        $("#filter-location").on("change", function (e) {
            $('#filter-category, #filter-stock').val('');
            location_id = $('#filter-location').val();

            category_id = null;
            stock = null;
            table.ajax.reload();
        });

        $('#btn-open-boutique-logs').on('click', function () {

        });

        /*$("#filter-category, #filter-stock, #filter-location").on("change", function (e) {
            console.log($(this).attr('id'));

            $('#filter-category')

            category_id = $('#filter-category').val();
            location_id = $('#filter-location').val();

            table.ajax.reload();
        });*/

        function sellProduct(id)
        {
            no_stock = false;

            $('.checkbox-location').prop('checked', false);
            $('.loader').addClass('d-none');
            $('#container-quantity').addClass('d-none');
            $('#container-sell-to').addClass('d-none');
            $('#select-user').val('');
            $('#status-quantity').addClass('d-none');
            $('#status-quantity').html('');
            $('#product-quantity').addClass('d-none');
            $('#product-quantity').html('');

            $.ajax({
                url: '{{ route('boutique.get_product') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            }).done(function (res) {
                let product_id = res.id;
                let product_name = res.name;
                let product_price = res.unit_price_format;
                let product_image = res.image;

                $('#input-product-id').val(product_id);
                $('#span-product-name').text(product_name);
                $('#span-product-price').text(product_price);
                $('#image-product').prop('src', product_image);

                $('#modal-sell').modal('show');
            }).fail(function (res) {
                $('#modal-sell').modal('hide');
                ResetModalForm('#form-sell-product');

                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error al obtener el producto. Por favor, intente mas tarde.",
                });
            });
        }

        function editProduct(id)
        {
            ResetValidations();
            $("#edit-image").html('');
            $('#container-locations-stock table tbody').html('');

            $.ajax({
                url: '{{ route('boutique.get_product') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            }).done(function (res) {
                let product_id = res.id;
                let product_name = res.name;
                let nationality = res.nationality;
                let category_id = res.boutique_category_id;
                let barcode = res.barcode;
                let product_price = res.unit_price;
                let product_price_format = res.unit_price_format;
                let product_wholesaler_price = res.wholesaler_price;
                let stock_alarm = res.stock_alarm;
                let location_alarm = res.location_alarm;
                let product_image = res.image;
                let product_inventory = res.locations_inventory;

                $('#edit-product-id').val(product_id);
                $('#edit-name').val(product_name);
                $('#edit-price').val(product_price);
                $('#edit-wholesaler-price').val(product_wholesaler_price);
                $('#edit-nationality').val(nationality);
                $('#edit-category').val(category_id);
                $('#edit-barcode').val(barcode);
                $('#edit-stock-alarm').val(stock_alarm);
                $('#edit-location-alarm').val(location_alarm);

                $("#edit-image").spartanMultiImagePicker({
                    fieldName: 'image',
                    maxCount: 1,
                    groupClassName: 'col-xs-12',
                    placeholderImage: {image: product_image , width: '100%'},
                    maxFileSize: 5000000,
                    onExtensionErr: function(index, file){
                    },
                    onSizeErr: function(index, file){
                        alert('El archivo que intenta subir es muy grande. Máximo: 5MB');
                    }
                });

                let items = '';

                $.each(product_inventory, function (i, inventory) {
                    items += '<tr>';

                    items +=
                        '<td>' + inventory.name + '</td>' +
                        '<td>' + inventory.quantity + '</td>';

                    items += '</tr>';
                });

                $('#container-locations-stock table tbody').append(items);

                $('#modal-edit').modal('show');
            }).fail(function (res) {
                $('#modal-edit').modal('hide');

                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error al obtener el producto. Por favor, intente mas tarde.",
                });
            });
        }

        function logsProduct(id)
        {
            $('#span-product-logs-name').text('');

            if(products_logs_table != null) {
                products_logs_table.destroy();
            }

            products_logs_table = $("#product-logs-table").DataTable({
                processing: true,
                serverSide: false,
                pageLength: 50,
                lengthMenu: [[50, 100, 150, 200], [50, 100, 150, 200]],
                language: {
                    url: '{{ asset("DataTables/Spanish.json") }}',
                },
                order: [[ 4, "desc" ]],
                ajax: {
                    url: '{{ route('boutique.get_product_logs') }}',
                    dataSrc: "data",
                    type: "GET",
                    data: function(data) {
                        data.product_id = id;
                    },
                    complete: function (res) {
                        let product_name = res.responseJSON.product_name;
                        $('#span-product-logs-name').text(' | ' + product_name);
                    }
                },
                columns: [
                    { data: "action" },
                    { data: "old_inventory_quantity" },
                    { data: "new_inventory_quantity" },
                    { data: "user" },
                    { data: "date" },
                ],
                columnDefs: [
                    {
                        targets: [0, 1, 2, 3],
                        orderable: false,
                    },
                ],
                fnDrawCallback: function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            $('#modal-product-logs').modal('show');
        }

        function logs()
        {
            if(logs_table != null ){
                logs_table.destroy();
            }

            logs_table = $("#logs-table").DataTable({
                processing: true,
                serverSide: false,
                pageLength: 50,
                lengthMenu: [[50, 100, 150, 200], [50, 100, 150, 200]],
                language: {
                    url: '{{ asset("DataTables/Spanish.json") }}',
                },
                order: [[ 2, "desc" ]],
                ajax: {
                    url: '{{ route('boutique.get_logs') }}',
                    dataSrc: "data",
                    type: "GET",
                    complete: function (res) {
                        // let product_name = res.responseJSON.product_name;
                        // $('#span-product-logs-name').text(' | ' + product_name);
                    }
                },
                columns: [
                    { data: "action" },
                    { data: "user" },
                    { data: "date" },
                ],
                columnDefs: [
                    {
                        targets: [0, 1],
                        orderable: false,
                    },
                ],
                fnDrawCallback: function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            $('#modal-logs').modal('show');
        }

        function deleteProduct(id)
        {
            SwalGB.fire({
                title: '¿Está seguro que desea inactivar este producto?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--hex-exito, #2ecc71)',
                cancelButtonColor: 'var(--hex-peligro, #ff5252)',
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '{{ route('boutique.delete_product') }}',
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id
                        },
                    }).done(function (res) {
                        Toast.fire({
                            icon: "success",
                            title: "Inactivado exitosamente",
                        });

                        table.ajax.reload();
                    }).fail(function (res) {
                        let json = res.responseJSON;
                        if (!json.success) {
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

        function returnProduct(id)
        {
            EnableModalActionButtons();

            $('#container-return-locations-stock table tbody').html('');
            $('#select-return-product-location').html('<option value="">Seleccione...</option>');
            $('#container-return-product-quantity').addClass('d-none');

            $.ajax({
                url: '{{ route('boutique.get_product') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            }).done(function (res) {
                let product_id = res.id;
                let product_name = res.name;
                let product_inventory = res.locations_inventory;

                $('#input-return-product-id').val(product_id);
                $('#label-return-product-name').text(product_name);

                let items = '';
                let items_locations = '';

                $.each(product_inventory, function (i, inventory) {
                    items += '<tr>';

                    items +=
                        '<td>' + inventory.name + '</td>' +
                        '<td>' + inventory.quantity + '</td>';

                    items += '</tr>';

                    if(inventory.quantity > 0 && inventory.base !== 1) {
                        items_locations += '<option value="' + inventory.id + '">' + inventory.name + '</option>';
                    }
                });

                $('#container-return-locations-stock table tbody').append(items);
                $('#select-return-product-location').append(items_locations);

                $('#modal-return-product').modal('show');
            }).fail(function (res) {
                $('#modal-return-product').modal('hide');

                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error al obtener el producto. Por favor, intente mas tarde.",
                });
            });
        }

        function transferProduct(id)
        {
            EnableModalActionButtons();

            $('#container-transfer-locations-stock table tbody').html('');
            $('#select-transfer-product-location').html('<option value="">Seleccione...</option>');
            $('#select-transfer-product-quantity').html('');
            $('#container-transfer-product-quantity').addClass('d-none');

            $.ajax({
                url: '{{ route('boutique.get_product') }}',
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id
                },
            }).done(function (res) {
                let product_id = res.id;
                let product_name = res.name;
                let product_inventory = res.locations_inventory;

                $('#input-transfer-product-id').val(product_id);
                $('#label-transfer-product-name').text(product_name);

                let items = '';
                let items_locations = '';
                let base_location_quantity = '';
                let items_base_locations = '';

                $.each(product_inventory, function (i, inventory) {
                    items += '<tr>';

                    items +=
                        '<td>' + inventory.name  + (inventory.quantity <= 0 && inventory.base === 1 ? ' <i class="fas fa-exclamation-triangle text-warning" data-toggle="tooltip" title="No hay productos disponibles en la locación base para trasladar"></i>' : '') + '</td>' +
                        '<td>' + inventory.quantity + '</td>';

                    items += '</tr>';

                    if(inventory.base !== 1) {
                        items_locations += '<option value="' + inventory.id + '">' + inventory.name + '</option>';
                    }

                    // Get base location quantity
                    if(inventory.base === 1) {
                        base_location_quantity = inventory.quantity;
                    }
                });

                for (let i = 1; i <= base_location_quantity; i++) {
                    items_base_locations += "<option value='" + i + "'>" + i + "</option>";
                }

                $('#select-transfer-product-quantity').append(items_base_locations);

                $('#container-transfer-locations-stock table tbody').append(items);
                $('#select-transfer-product-location').append(items_locations);

                $('#modal-transfer-product').modal('show');
            }).fail(function (res) {
                $('#modal-transfer-product').modal('hide');

                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error al obtener el producto. Por favor, intente mas tarde.",
                });
            });
        }
    </script>
@endpush
