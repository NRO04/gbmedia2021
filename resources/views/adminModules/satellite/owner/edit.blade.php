@extends("layouts.app")
@section('content')
@section('pageTitle', 'Editar propietario')

<style>
        .sl-overlay{
            z-index: 1031 !important;
            background: none !important;
        }
    </style>
<div class="row">
    @if($owner->status == 2)
        @can('satellite-owner-disable')
        <div class="col-md-12">
            <div class="alert alert-danger row">
                <div class="col-lg-10 text-center"><h3>Propietario Vetado</h3></div>
                <div class="col-lg-2">
                    <form action="{{ route('satellite.update_status')}}" class="form-row" method="post" id="form-update-status">
                        @csrf
                        <input type="hidden" name="id" value="{{ $owner->id }}">
                        <input type="hidden" name="status" value="1">
                        <input type="hidden" name="user_manager" value="{{ $owner->user_manager }}">
                        <input type="hidden" name="status_comment" value="">
                    </form>
                    <button type="button" onclick="changeStatus()" class="btn btn-success btn-sm float-right"> <i class="fa fa-check"></i> Activar</button>
                </div>
            </div>
        </div>
        @endcan
    @endif

    <div class="col-md-12">
        <div class="card table-responsive">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-12">
                    <span class="span-title">Editar Propietario  <span class="text-danger">{{ $owner->owner }}</span></span>

                </div>
         </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3">
                    <div class="list-group" id="list-tab" role="tablist">
                        <a class="list-group-item list-group-item-action item-sm active" data-toggle="list" href="#personal_info" role="tab"
                           aria-controls="home">Datos Personales</a>

                        <a class="list-group-item list-group-item-action item-sm"  data-toggle="list" href="#payment_method_tab" role="tab" aria-controls="profile">Forma de Pago</a>
                        @can('satellite-owner-percent')
                        <a class="list-group-item list-group-item-action item-sm"  data-toggle="list" href="#commission" role="tab" aria-controls="messages">Porcentajes</a>
                        @endcan

                         <a class="list-group-item list-group-item-action item-sm" data-toggle="list" href="#account_status" role="tab" aria-controls="messages">Estado de Cuenta</a>
                        @can('satellite-owner-api')
                        <a class="list-group-item list-group-item-action item-sm"  data-toggle="list" href="#api" role="tab" aria-controls="settings">Api</a>
                        @endcan
                    </div>

                </div>
                <div class="col-lg-9">
                    <div class="tab-content" id="nav-tabContent">

                        <!--Informacion Personal-->
                        <div class="tab-pane fade active show" id="personal_info" role="tabpanel">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <form action="{{ route('satellite.update_personal_info')}}" class="form-row" method="post"
                                          id="form-update-personal-info" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $owner->id }}">
                                        <div class="form-group col-md-6">
                                            <label for="owner">Propietario</label>
                                            <input type="text" class="form-control" name="owner" id="owner" value="{{ $owner->owner}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="email">Email Principal</label>
                                            <input type="text" class="form-control" name="email" id="email" value="{{ $owner->email}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="first_name">Nombre</label>
                                            <input type="text" class="form-control" name="first_name" id="first_name" value="{{ $owner->first_name}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="second_name">Segundo Nombre</label>
                                            <input type="text" class="form-control" name="second_name" id="second_name" value="{{ $owner->second_name}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="last_name">Primer Apellido</label>
                                            <input type="text" class="form-control" name="last_name" id="last_name" value="{{ $owner->last_name}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="second_last_name">Segundo Apellido</label>
                                            <input type="text" class="form-control" name="second_last_name" value="{{ $owner->second_last_name}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="document_number">Nro Documento</label>
                                            <input type="text" class="form-control" name="document_number" id="document_number" value="{{ $owner->document_number}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="phone">Teléfono</label>
                                            <input type="text" class="form-control" name="phone" id="phone" value="{{ $owner->phone}}">
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label>Otros correos Conocidos:</label>
                                            <span class="small text-info">En caso que se haya comunicado desde otros correos</span>
                                            <input type="text" class="form-control" name="others_emails" id="others_emails" value="{{ $owner->others_emails}}">
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label>Correo para estadisticas:</label>
                                            <span class="small text-info">Al enviar estadisticas solo llegaran a los correos que ingrese aqui
                                                <span class="smal text-danger">(solo si diferentes del email principal)</span></span>
                                            <input type="text" class="form-control" name="statistics_emails" id="statistics_emails" value="{{ $owner->statistics_emails}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>RUT</label>
                                            @can('satellite-owner-rut-edit')
                                            <div class="col-sm-7 col-lg-6 inputFile pl-0">
                                                <span class="btnFile btn-dark btn-sm">
                                                    <span id="spanFile">Seleccionar Archivos</span>
                                                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
                                                </span>
                                                <input id="rut" name="rut[]" type="file">
                                            </div>
                                            @endcan
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Cámara y Comercio</label>
                                            @can('satellite-owner-chamber-commerce-edit')
                                            <div class="col-sm-7 col-lg-6 inputFile pl-0">
                                                <span class="btnFile btn-dark btn-sm">
                                                    <span id="spanFile">Seleccionar Archivos</span>
                                                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
                                                </span>
                                                <input id="chamber_commerce" name="chamber_commerce[]" type="file" multiple="">
                                            </div>
                                            @endcan
                                        </div>
                                        @can('satellite-owner-rut-view')
                                            <div class="col-md-6" id="div_rut"></div>
                                        @endcan

                                        @can('satellite-owner-chamber-commerce-view')
                                            <div class="col-md-6" id="div_chamber_commerce"></div>
                                        @endcan

                                        <div class="form-group col-md-6">
                                            <label>Composición Accionaria</label>
                                            @can('satellite-owner-shareholder-structure-edit')
                                            <div class="col-sm-7 col-lg-6 inputFile pl-0">
                                                <span class="btnFile btn-dark btn-sm">
                                                    <span id="spanFile">Seleccionar Archivos</span>
                                                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
                                                </span>
                                                <input id="shareholder_structure" name="shareholder_structure[]" type="file">
                                            </div>
                                            @endcan
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Certificación Bancaria</label>
                                            @can('satellite-owner-bank-certification-edit')
                                            <div class="col-sm-7 col-lg-6 inputFile pl-0">
                                                <span class="btnFile btn-dark btn-sm">
                                                    <span id="spanFile">Seleccionar Archivos</span>
                                                    <i class="fa fa-upload pl-1 pr-1" aria-hidden="true"></i>
                                                </span>
                                                <input id="bank_certification" name="bank_certification[]" type="file" multiple="">
                                            </div>
                                            @endcan
                                        </div>
                                        @can('satellite-owner-shareholder-structure-view')
                                        <div class="col-md-6" id="div_shareholder_structure"></div>
                                        @endcan
                                        @can('satellite-owner-bank-certification-view')
                                        <div class="col-md-6" id="div_bank_certification"></div>
                                        @endcan
                                        <div class="form-group col-md-6">
                                            <label for="first_name">Departamento</label>
                                            <select class="form-control" id="department" name="department">
                                                <option value>Seleccione el departamento...</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id}}"
                                                            @if($department->id == $owner->department_id) selected @endif>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="first_name">Ciudad</label>
                                            <select class="form-control" name = "city" id ="city">
                                                @foreach ($cities as $city)
                                                    <option value="{{ $city->id}}" @if($city->id == $owner->city_id) selected @endif>{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="address">Dirección</label>
                                            <input type="text" class="form-control" name="address" id="address" value="{{ $owner->address}}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="neighborhood">Barrio</label>
                                            <input type="text" class="form-control" name="neighborhood" id="neighborhood" value="{{ $owner->neighborhood}}">
                                        </div>
                                    </form>
                                    <div>
                                        @if($owner->status == 1)
                                            <button id="btn-update-personal-info" type="button"
                                                    class="btn btn-m btn-success float-right btn-sm"><i class="fa fa-check"></i> Guardar</button>
                                        @endif
                                    </div>
                                </div>
                                <!--/card-body-->
                            </div>
                        </div>

                        <!--Forma de Pago-->
                        <div class="tab-pane fade" id="payment_method_tab" role="tabpanel">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <form action="{{ route('satellite.update_payment_method')}}" class="form-row" method="post"
                                          id="form-update-payment-method" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $owner->id }}">
                                        <div class="form-group col-md-6">
                                            <label for="first_name">Forma de pago</label>
                                            <select class="form-control" id="payment_method" name="payment_method" onchange="paymentMethod()">
                                                @foreach ($payment_methods as $payment_method)
                                                    <option value="{{ $payment_method->id}}"
                                                            @if($payment_method->id == $owner->payment_method) selected @endif>{{ $payment_method->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6"></div>
                                        <div class="form-group col-md-6" id="div_bank">
                                            <label for="first_name">Nombre de Banco</label>
                                            <select class="form-control" id="bank" name="bank">
                                                @foreach ($banks as $bank)
                                                    <option value="{{ $bank->id}}" @if($bank->id == $owner->paymentInfo->bank) selected @endif>{{ $bank->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6" id="div_bank_usa">
                                            <label for="email">Nombre Banco Usa</label>
                                            <input type="text" class="form-control" name="bank_usa" id="bank_usa" value="{{ $owner->paymentInfo->bank_usa  }}">
                                        </div>
                                        <div class="form-group col-md-6" id="div_holder">
                                            <label for="holder">Titular</label>
                                            <input type="text" class="form-control" name="holder" id="holder" value="{{ $owner->paymentInfo->holder  }}">
                                        </div>
                                        <div class="form-group col-md-6" id="div_document_type">
                                            <label for="first_name">Tipo de Documento</label>
                                            <select class="form-control" id="document_type" name="document_type">
                                                <option></option>
                                                @foreach ($documents as $document)
                                                    <option value="{{ $document->id}}"
                                                            @if($document->id == $owner->paymentInfo->document_type) selected @endif>{{ $document->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6" id="div_payment_document_number">
                                            <label for="email">Numero de Documento</label>
                                            <input type="text" class="form-control" name="document_number"
                                                   id="payment_document_number" value="{{ $owner->paymentInfo->document_number }}">
                                        </div>
                                        <div class="form-group col-md-6" id="div_account_type">
                                            <label for="first_name">Tipo de Cuenta</label>
                                            <select class="form-control" id="account_type" name="account_type">
                                                <option value="1" @if($owner->paymentInfo->account_type == 1) selected @endif>Ahorros</option>
                                                <option value="2" @if($owner->paymentInfo->account_type == 2) selected @endif>Corriente</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6" id="div_account_number">
                                            <label for="email">Numero de Cuenta</label>
                                            <input type="text" class="form-control" name="account_number" i
                                                   d="account_number" value="{{ $owner->paymentInfo->account_number }}">
                                        </div>
                                        <div class="form-group col-md-6" id="div_city">
                                            <label for="email">Ciudad</label>
                                            <input type="text" class="form-control" name="city" id="payment_city" value="{{
                                            $owner->paymentInfo->city_id }}">
                                        </div>
                                        <div class="form-group col-md-6" id="div_address">
                                            <label for="email">Direccion Completa</label>
                                            <input type="text" class="form-control" name="address" id="payment_address" value="{{ $owner->paymentInfo->address }}">
                                        </div>
                                        <div class="form-group col-md-6" id="div_phone">
                                            <label for="email">Teléfono</label>
                                            <input type="text" class="form-control" name="phone" id="payment_phone" value="{{ $owner->paymentInfo->phone }}">
                                        </div>
                                        <div class="form-group col-md-6" id="div_country">
                                            <label for="email">Pais</label>
                                            <input type="text" class="form-control" name="country" id="country" value="{{ $owner->paymentInfo->country }}">
                                        </div>
                                    </form>
                                    <div>
                                        @if($owner->status == 1)
                                            <button id="btn-update-payment-method" type="button"
                                                    class="btn btn-m btn-success float-right btn-sm"><i class="fa fa-check"></i> Guardar</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Porcentajes-->
                        <div class="tab-pane fade" id="commission" role="tabpanel">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <form action="" class="form-row" method="post" id="form-update-payment-method">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $owner->id }}">

                                        <div class="form-group col-md-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <label class="col-lg-3">% Predeterminado:</label>
                                                        <div class="col-lg-4">
                                                            <input type="text" class="form-control" name="commission_percent"
                                                                   id="commission_percent" value="{{ $owner->commission_percent  }}">
                                                        </div>
                                                        <div class="col-lg-3">
                                                            @if($owner->status == 1)
                                                                @can('satellite-owner-percent-edit')
                                                                <button type="button" class="btn btn-success btn-sm" id="btn_percent">
                                                                    <i class="fa fa-check"></i> Guardar</button>
                                                                @endcan
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="form-group col-md-6">
                                            <div class="row">
                                                <label class="col-md-5">Comisiona para:</label>
                                                @if($owner->status == 1)
                                                    @can('satellite-owner-percent-create')
                                                    <button type="button" data-toggle="modal" data-target="#modal-add-commission"
                                                            class="btn btn-success btn-sm float-right"><i class="fa fa-plus"></i></button>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
                                        <div id="div_commission_for" class="col-lg-12">

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!--Estado cuenta-->
                        <div class="tab-pane fade" id="account_status" role="tabpanel">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <form action="{{ route('satellite.update_status')}}" class="form-row" method="post" id="form-update-status">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $owner->id }}">
                                        <div class="form-group col-md-6">
                                            <label for="first_name">Estado</label>
                                            <select class="form-control" id="status" name="status" >
                                                <option value="1" @if($owner->status == 1) selected @endif>Activo</option>
                                                @can('satellite-owner-disable')
                                                <option value="2" @if($owner->status == 2) selected @endif>Vetado</option>
                                                @endcan
                                                <option value="2" @if($owner->status == 3) selected @endif>Inactivo</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="first_name">Gerente de Cuenta</label>
                                            <select class="form-control" id="user_manager" name="user_manager" onchange="paymentMethod()">
                                                <option></option>
                                                @foreach ($users_support as $user_support)
                                                    <option value="{{ $user_support->id}}" @if($user_support->id == $owner->user_manager) selected @endif>{{ $user_support->first_name." ".$user_support->last_name." ".$user_support->second_last_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-12" id="div_status_comment" style="display: @if($owner->status == 1) none @endif">
                                            <label for="first_name">Comentario de Vetado</label>
                                            <textarea class="form-control" style="height: 150px"
                                                      name="status_comment">{{$owner->status_comment}}</textarea>
                                        </div>
                                        @can('satellite-owner-convert')
                                            @if($owner->status == 1 && $owner->is_user == 1)
                                                <div class="col-lg-6">
                                                    <input type="checkbox" name="convert">
                                                    <label for="first_name" class="ml-2 mb-2">Independizar</label>
                                                </div>
                                            @endif
                                        @endcan
                                    </form>
                                    <div class="col-md-12">
                                        @if($owner->status == 1)
                                            <button type="button" onclick="changeStatus()" class="btn btn-success btn-sm float-right"> <i class="fa fa-check"></i> Guardar</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Api-->
                        <div class="tab-pane fade" id="api" role="tabpanel">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <form action="{{ route('satellite.update_status')}}" class="form-row" method="post" id="form-update-api">
                                        @csrf
                                        <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                                        <div class="form-group col-md-6">
                                            <label for="">Api Pagos en</label>
                                            <select name="tenant" id="" class="form-control form-control-sm col-lg-6">
                                                <option value="0"></option>
                                                @foreach($tenants as $tenant)
                                                    <option value="{{ $tenant->id }}"  @if($tenant->owner_id  == $owner->id) selected
                                                        @endif>{{
                                                    $tenant->studio_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="">Api Chaturbate</label>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-warning">En mantenimiento</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="col-md-12">
                                        @if($owner->status == 1)
                                            <button type="button" onclick="changeStatus()" class="btn btn-success btn-sm float-right" id="btn_update_api">
                                                <i class="fa fa-check"></i> Guardar</button>
                                        @endif
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

<!-- embed documents -->
<div id="modal-embed-documents" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl modal-dark">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Documento</h4>
            </div>
            <div class="modal-body" >
                <embed id="embed-documents" src="" type="" style="height: 600px; width: 100%"></embed>
                {{-- <iframe id="iframe-documents" src="" type="" style="height: 500px; width: 100%"></iframe> --}}
            </div>
        </div>
    </div>
</div>

<!-- add commission -->
<div id="modal-add-commission" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg modal-dark">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Comisiona Para</h4>
            </div>
            <div class="modal-body row" >
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-lg-4">Propietario</div>
                        <div class="col-lg-2">% Comision</div>
                        <div class="col-lg-3">Tipo</div>
                        <div class="col-lg-3">Pagina</div>
                    </div>
                </div>
                <form action="" class="col-md-12 form-row" method="post" id="form-add-commission">
                    @csrf
                    <input type="hidden" name="owner_giver" value="{{ $owner->id }}">
                    <div class="col-md-12 mt-2">
                        <div class="row">
                            <div class="col-lg-4">
                                <select class="form-control" name="owner_receiver">
                                    @foreach ($list_owners as $list_owner)
                                        <option value="{{ $list_owner->id}}">{{ $list_owner->owner }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <input type="text" class="form-control" name="percent">
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control" name="type" id="type_add" onchange="showPage()">
                                    <option value="1">Todas las paginas</option>
                                    <option value="2">Solo esta pagina</option>
                                    <option value="3">Todas excepto esta</option>
                                </select>
                            </div>
                            <div class="col-lg-3" id='div_page_add' style="display: none">
                                <select class="form-control" name="page">
                                    <option value=""></option>
                                    @foreach ($pages as $page)
                                        <option value="{{ $page->id}}">{{ $page->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-success" id="btn_add_commission"><i class="fa fa-plus"></i> Crear</button>
            </div>
        </div>
    </div>
</div>

<!-- update commission -->
<div id="modal-update-commission" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg modal-dark">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Comisiona Para</h4>
            </div>
            <div class="modal-body row" >
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-lg-4">Propietario</div>
                        <div class="col-lg-2">% Comision</div>
                        <div class="col-lg-3">Tipo</div>
                        <div class="col-lg-3">Pagina</div>
                    </div>
                </div>
                <form action="" class="col-md-12 form-row" method="post" id="form-update-commission">
                    @csrf
                    <input type="hidden" name="commission_id" id="commission_id" >
                    <div class="col-md-12 mt-2">
                        <div class="row">
                            <div class="col-lg-4">
                                <select class="form-control" name="owner_receiver" id="update_owner_receiver">
                                    @foreach ($list_owners as $list_owner)
                                        <option value="{{ $list_owner->id}}">{{ $list_owner->owner }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <input type="text" class="form-control" name="percent" id="update_percent">
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control" name="type" id="type_update" onchange="showPage()">
                                    <option value="1">Todas las paginas</option>
                                    <option value="2">Solo esta pagina</option>
                                    <option value="3">Todas excepto esta</option>
                                </select>
                            </div>
                            <div class="col-lg-3" id='div_page_update' style="display: none">
                                <select class="form-control" name="page" id="update_page">
                                    <option value=""></option>
                                    @foreach ($pages as $page)
                                        <option value="{{ $page->id}}">{{ $page->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-success" id="btn_update_commission"><i class="fa fa-save"></i> Modificar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#department').on('change', function () {
    let department = $(this).val();
    if(department != '')
    {
        $.ajax({
            url: "{{ route('satellite.get_cities') }}",
            type: 'GET',
            data: {department},
        })
        .done(function (res){
            $('#city').html("");
            let options = '';
            $.each(res, function(i, cities) {
                let city_id = cities.id;
                let city_name = cities.name;
                options += '<option value="' + city_id + '">' + city_name + '</option>';
            });
            $('#city').append(options);
        });
    }
    else
    {
        $('#city').html("");
    }
});

$("#btn-update-personal-info").on("click", function(){
    $("#btn-update-personal-info").prop('disabled' , false);
    ResetValidations();
    formData = new FormData(document.getElementById('form-update-personal-info'));
    $.ajax({
        url: '{{ route('satellite.update_personal_info')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
    .done(function(res) {
        if (res.success) {
            Toast.fire({
                icon: "success",
                title: "Se han modificado los datos personales exitosamente",
            });
            $("#btn-update-personal-info").prop('disabled' , false);
            $("#rut").val('');
            $("#chamber_commerce").val('');
            getDocuments();
            coincidenceWithBanned();
        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
            $("#btn-update-personal-info").prop('disabled' , false);
        }
    })
    .fail(function(res) {
        CallBackErrors(res);
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
        $("#btn-update-personal-info").prop('disabled' , false);
    });

});

$("#btn-update-payment-method").on("click", function(){
    $("#btn-update-payment-method").prop('disabled' , false);
    ResetValidations();
    formData = new FormData(document.getElementById('form-update-payment-method'));
    $.ajax({
        url: '{{ route('satellite.update_payment_method')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
    .done(function(res) {
        if (res.success) {
            Toast.fire({
                icon: "success",
                title: "Se ha modificado la forma de pago exitosamente",
            });
            $("#btn-update-payment-method").prop('disabled' , false);
            coincidenceWithBanned();
        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
            $("#btn-update-payment-method").prop('disabled' , false);
        }
    })
    .fail(function(res) {
        CallBackErrors(res);
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
        $("#btn-update-payment-method").prop('disabled' , false);
    });

});

$("#btn_add_commission").on("click", function(){
    $("#btn_add_commission").prop('disabled' , false);
    ResetValidations();
    formData = new FormData(document.getElementById('form-add-commission'));
    $.ajax({
        url: '{{ route('satellite.store_commission')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
    .done(function(res) {
        if (res.success) {
            Toast.fire({
                icon: "success",
                title: "Se ha creado la comision exitosamente",
            });
            $("#btn_add_commission").prop('disabled' , false);
            ResetModalForm("#form-add-commission");
            $("#modal-add-commission").modal("hide");
            getCommissions();
        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
            $("#btn_add_commission").prop('disabled' , false);
        }
    })
    .fail(function(res) {
        CallBackErrors(res);
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
        $("#btn_add_commission").prop('disabled' , false);
    });

});

$("#btn_update_commission").on("click", function(){
    $("#btn_update_commission").prop('disabled' , false);
    ResetValidations();
    formData = new FormData(document.getElementById('form-update-commission'));
    $.ajax({
        url: '{{ route('satellite.update_commission')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
    .done(function(res) {
        if (res.success) {
            Toast.fire({
                icon: "success",
                title: "Se ha modificado la comision exitosamente",
            });
            $("#btn_update_commission").prop('disabled' , false);
            $("#modal-update-commission").modal("hide");
            ResetModalForm("#form-update-commission");
            getCommissions();
        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
            $("#btn_update_commission").prop('disabled' , false);
        }
    })
    .fail(function(res) {
        CallBackErrors(res);
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
        $("#btn_update_commission").prop('disabled' , false);
    });

});

$("#btn_update_api").on("click", function(){
    $("#btn_update_api").prop('disabled' , false);

    formData = new FormData(document.getElementById('form-update-api'));
    $.ajax({
        url: '{{ route('satellite.update_api')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
        .done(function(res) {
            if (res.success) {
                Toast.fire({
                    icon: "success",
                    title: "Se ha modificado el Api exitosamente",
                });
                $("#btn_update_api").prop('disabled' , false);
            } else {
                Toast.fire({
                    icon: "error",
                    title: "Ha ocurrido un error, comuniquese con el ADMIN",
                });
                $("#btn_update_api").prop('disabled' , false);
            }
        })
        .fail(function(res) {
            Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
            $("#btn_update_api").prop('disabled' , false);
        });

});

$("#btn_percent").on("click", function(){
    $("#btn_percent").prop('disabled' , false);
    ResetValidations();
    owner_id = '{{ $owner->id }}';
    commission_percent = $("#commission_percent").val();
    $.ajax({
        url: '{{ route('satellite.update_percent')}}',
        type: 'POST',
        data: {"_token": "{{ csrf_token() }}", 'owner_id' : owner_id, 'commission_percent' : commission_percent },
    })
    .done(function(res) {
        if (res.success) {
            Toast.fire({
                icon: "success",
                title: "Se ha modificado porcentaje de comision exitosamente",
            });
            $("#btn_percent").prop('disabled' , false);
        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
            $("#btn_percent").prop('disabled' , false);
        }
    })
    .fail(function(res) {
        CallBackErrors(res);
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
        $("#btn_percent").prop('disabled' , false);
    });

});

function changeStatus()
{
    $("#btn_update_status").prop('disabled' , false);
    ResetValidations();
    formData = new FormData(document.getElementById('form-update-status'));
    owner_id = '{{ $owner->id }}';
    $.ajax({
        url: '{{ route('satellite.update_status')}}',
        type: 'POST',
        processData: false,
        contentType: false,
        data: formData,
    })
    .done(function(res) {
        if (res.success) {
            Toast.fire({
                icon: "success",
                title: "Informacion modificada exitosamente",
            });
            $("#btn_update_status").prop('disabled' , false);
            if ($("#status").val() == 2 || res.convert == true)
            {
                location.reload();
            }


        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
            $("#btn_update_status").prop('disabled' , false);
        }
    })
    .fail(function(res) {
        CallBackErrors(res);
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
        $("#btn_update_status").prop('disabled' , false);
    });
}

function getDocuments()
{
    owner_id = '{{ $owner->id }}';
    $.ajax({
        url: '{{ route('satellite.get_documents')}}',
        type: 'GET',
        data: {'owner_id': owner_id},
    })
    .done(function(res) {
        $("#div_rut").html(res.rut);
        $("#div_chamber_commerce").html(res.chamber_commerce);
        $("#div_shareholder_structure").html(res.shareholder_structure);
        $("#div_bank_certification").html(res.bank_certification);
        zoomImageGB();
    });
}

function embedDocuments(url, type)
{
    if (type == "pdf")
    {
        $("#modal-embed-documents").modal("show");
        $("#embed-documents").attr("src" , "../../../../storage/app/public/GB/satellite/owner/"+url);
    }
    else
    {
        window.location.href = "../../../../storage/app/public/GB/satellite/owner/"+url;
    }
}

function paymentMethod()
{
    payment_method = $("#payment_method").val();

    if (payment_method == 1)
    {
        $("#div_bank").css("display", "none");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "none");
        $("#div_document_type").css("display", "none");
        $("#div_payment_document_number").css("display", "none");
        $("#div_account_type").css("display", "none");
        $("#div_account_number").css("display", "none");
        $("#div_city").css("display", "none");
        $("#div_address").css("display", "none");
        $("#div_phone").css("display", "none");
        $("#div_country").css("display", "none");
    }

    if (payment_method == 2)
    {
        $("#div_bank").css("display", "");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "");
        $("#div_payment_document_number").css("display", "");
        $("#div_account_type").css("display", "");
        $("#div_account_number").css("display", "");
        $("#div_city").css("display", "");
        $("#div_address").css("display", "none");
        $("#div_phone").css("display", "none");
        $("#div_country").css("display", "none");
    }
    if (payment_method == 3)
    {
        $("#div_bank").css("display", "none");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "");
        $("#div_payment_document_number").css("display", "");
        $("#div_account_type").css("display", "none");
        $("#div_account_number").css("display", "none");
        $("#div_city").css("display", "");
        $("#div_address").css("display", "");
        $("#div_phone").css("display", "");
        $("#div_country").css("display", "none");
    }
    if (payment_method == 4)
    {
        $("#div_bank").css("display", "none");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "none");
        $("#div_payment_document_number").css("display", "none");
        $("#div_account_type").css("display", "none");
        $("#div_account_number").css("display", "none");
        $("#div_city").css("display", "none");
        $("#div_address").css("display", "none");
        $("#div_phone").css("display", "none");
        $("#div_country").css("display", "none");
    }
    if (payment_method == 5)
    {
        $("#div_bank").css("display", "none");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "");
        $("#div_payment_document_number").css("display", "");
        $("#div_account_type").css("display", "none");
        $("#div_account_number").css("display", "none");
        $("#div_city").css("display", "");
        $("#div_address").css("display", "");
        $("#div_phone").css("display", "");
        $("#div_country").css("display", "none");
    }
    if (payment_method == 6)
    {
        $("#div_bank").css("display", "");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "");
        $("#div_payment_document_number").css("display", "");
        $("#div_account_type").css("display", "");
        $("#div_account_number").css("display", "");
        $("#div_city").css("display", "");
        $("#div_address").css("display", "none");
        $("#div_phone").css("display", "none");
        $("#div_country").css("display", "none");
    }
    if (payment_method == 7)
    {
        $("#div_bank").css("display", "none");
        $("#div_bank_usa").css("display", "");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "");
        $("#div_payment_document_number").css("display", "");
        $("#div_account_type").css("display", "");
        $("#div_account_number").css("display", "");
        $("#div_city").css("display", "");
        $("#div_address").css("display", "none");
        $("#div_phone").css("display", "none");
        $("#div_country").css("display", "none");
    }
    if (payment_method == 8)
    {
        $("#div_bank").css("display", "none");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "");
        $("#div_payment_document_number").css("display", "");
        $("#div_account_type").css("display", "none");
        $("#div_account_number").css("display", "none");
        $("#div_city").css("display", "");
        $("#div_address").css("display", "");
        $("#div_phone").css("display", "");
        $("#div_country").css("display", "");
    }
    if (payment_method == 9)
    {
        $("#div_bank").css("display", "");
        $("#div_bank_usa").css("display", "none");
        $("#div_holder").css("display", "");
        $("#div_document_type").css("display", "");
        $("#div_payment_document_number").css("display", "");
        $("#div_account_type").css("display", "");
        $("#div_account_number").css("display", "");
        $("#div_city").css("display", "");
        $("#div_address").css("display", "none");
        $("#div_phone").css("display", "none");
        $("#div_country").css("display", "none");
    }
}

function getCommissions()
{
    owner_id = '{{ $owner->id }}';
    $.ajax({
        url: '{{ route('satellite.get_commissions')}}',
        type: 'GET',
        data: {'owner_id': owner_id},
    })
    .done(function(res) {
        $("#div_commission_for").html(res);
    });
}

function modalUpdate(json)
{
    $("#commission_id").val(json.id);
    $("#update_owner_receiver").val(json.owner_receiver.id);
    $("#update_percent").val(json.percent);
    $("#type_update").val(json.type);
    $("#update_page").val(json.page);
    showPage();
}

function removeCommission(id)
{
    $.ajax({
        url: '{{ route('satellite.remove_commission')}}',
        type: 'POST',
        data: { "_token": "{{ csrf_token() }}", 'id' : id },
    })
    .done(function(res) {
        if (res) {
            Toast.fire({
                icon: "success",
                title: "Se ha eliminado la comision exitosamente",
            });
            getCommissions();
        } else {
            Toast.fire({
                icon: "error",
                title: "Ha ocurrido un error, comuniquese con el ADMIN",
            });
        }
    })
    .fail(function(res) {
        Toast.fire({
                icon: "error",
                title: "Verifique la informacion de los campos",
            });
    });
}

$("#status").on("change", function(){
    if ($(this).val() == 2)
        $("#div_status_comment").css("display", "block");
    else
        $("#div_status_comment").css("display", "none");

});

function showPage()
{
    if ($("#type_add").val() == 1)
        $("#div_page_add").css("display", "none");
    else
        $("#div_page_add").css("display", "");

    if ($("#type_update").val() == 1)
        $("#div_page_update").css("display", "none");
    else
        $("#div_page_update").css("display", "");
}

function coincidenceWithBanned()
{
    owner_id = '{{ $owner->id }}';
    status = '{{ $owner->status }}';
    if (status == 1)
    {
        $.ajax({
            url: '{{ route('satellite.coincidence_banned')}}',
            type: 'GET',
            data: {'owner_id' : owner_id },
        })
        .done(function(res) {
           $.each(res, function(i, item){
                $(".invalid-"+i).remove();
                if (item) {
                    addStyleBanned(i);
                }
           });
        });
    }
}

function addStyleBanned(id)
{
    $("#"+id).addClass('is-invalid');
    $("#"+id).after("<div class='text-danger invalid-"+id+"'>Coincidencia con un propietario vetado</div>");
}

window.onload = getDocuments();
window.onload = paymentMethod();
window.onload = getCommissions();
window.onload = coincidenceWithBanned();
</script>
@endpush
