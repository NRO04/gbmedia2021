@extends('layouts.app')
@section('pageTitle', 'Prospectos')
@push('styles')
<style>
    .imgM {
        width: 100%;
        height: 270px;
        border-radius: 5px;
        position: relative;
        box-shadow: $boxShadow2;
        .dataL {
            position: absolute;
            z-index: 10;
            //display: block;
            bottom: 20%;
            right: 5%;
            //width: 100%;
            //height: 100%;
        }
        .enlace {
            position: absolute;
            z-index: 10;
            bottom: 5%;
            right: 5%;
        }
    }
    .contenedorImg {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    .divImg {
        width: 270px;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 3rem;
        span {
            font-weight: 600;
            letter-spacing: 1px;
        }
    }
    .imgM {
        width: 100%;
        height: 270px;
        border-radius: 5px;
        position: relative;
        box-shadow: $boxShadow2;
        .dataL {
            position: absolute;
            z-index: 10;
            //display: block;
            bottom: 20%;
            right: 5%;
            //width: 100%;
            //height: 100%;
        }
        .enlace {
            position: absolute;
            z-index: 10;
            bottom: 5%;
            right: 5%;
        }
    }
}
</style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold">Recursos Humanos</li>
<li class="breadcrumb-item active"><a href="">Formato Entrevista</a></li>
@endsection
@section('content')
<div class="row">
    <div class="card border-secondary" style="width: 100%;">

            <h4 class="card-header card-primary">
                <div class="row">
                    <div class="col-sm">
                    Editar Prospecto <span class="badge bg-success">Modelo</span>
                    </div>
                    <div class="col-sm">
                        <a class="btn btn-success btn-sm float-right mx-1" onclick="showModalRhHistory()"><i class="fas fa-history"></i>&nbsp;Historial</a>
                        <a class="btn btn-info btn-sm float-right mx-1" onclick="ModalCite({{ $rh_interview_model->id }})"><i class="fas fa-bolt"></i>&nbsp;Citar</a>
                    </div>
                </div>
            </h4>

            <input name="id" id="rh_interview_id" type="hidden" value="{{ $rh_interview_model->id }}">
            <div class="card-body">

                <div class="alert alert-primary border-primary" role="alert">
                    <div class="row">
                        <div class="col-sm">
                            <B>Entrevistador</B>&nbsp;<span class="badge bg-success">{{ $rh_interview_model->RHInterviewToUser->userFullName() }}</span>&nbsp;
                        </div>
                        <div class="col-sm">
                            <center><B>Cargo </B>{{ $rh_interview_model->RHInterviewToRole->name }}</center>
                        </div>
                        <div class="col-sm" style="text-align: right;">
                            <i class="fas fa-calendar-alt"></i> <B>Fecha</B>&nbsp;<span class="badge bg-success">{{ $rh_interview_model->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <div class="list-group" id="list-tab" role="tablist">
                            <a class="list-group-item list-group-item-action active" id="personal-info-list" data-toggle="list" href="#personal-info" role="tab" aria-controls="home">Datos Personales</a>
                            <a class="list-group-item list-group-item-action" id="education-level-list" data-toggle="list" href="#education-level" role="tab" aria-controls="profile">Nivel Educativo</a>
                            <a class="list-group-item list-group-item-action" id="list-working-information" data-toggle="list" href="#list-messages" role="tab" aria-controls="messages">Información Laboral</a>
                            <a class="list-group-item list-group-item-action" id="list-Additional_information" data-toggle="list" href="#list-settings" role="tab" aria-controls="settings">Información Complementaria</a>
                            <a class="list-group-item list-group-item-action" id="list-model_img" data-toggle="list" href="#list-img-model" role="tab" aria-controls="img-model">Imagenes de modelo</a>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="tab-content" id="nav-tabContent">
                            <!-- Entrevistador -->
                            <div class="card border-secondary tab-pane fade show active" id="personal-info" role="tabpanel" aria-labelledby="personal-info-list">

                            <form id="form-edit-personal">
                                @csrf
                                <input name="id" type="hidden" value="{{ $rh_interview_model->id }}">
                                <div class="card-body form-row">

                                    <div class="form-group col-md-6">
                                        <label for="first_name" class="required">Nombre</label>
                                        <input type="text" class="form-control" name="first_name" value="{{ $rh_interview_model->first_name }}" id="first_name" placeholder="Ejemplo: Angel">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="middle_name">Segundo Nombre</label>
                                        <input type="text" class="form-control" name="middle_name" id="middle_name" value="{{ $rh_interview_model->middle_name }}" placeholder="Ejemplo: Alberto">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="last_name" class="required">Apellido</label>
                                        <input type="text" class="form-control" name="last_name" id="last_name" value="{{ $rh_interview_model->last_name }}" placeholder="Ejemplo: Perez">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="second_last_name">Segundo Apellido</label>
                                        <input type="text" class="form-control" name="second_last_name" id="second_last_name" value="{{ $rh_interview_model->second_last_name }}" placeholder="Ejemplo: Perez">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="birth_date">Fecha Nacimiento</label>
                                        <input type="date" class="form-control" name="birth_date" value="{{ $rh_interview_model->birth_date }}" id="birth_date">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="document_id">Tipo Documento</label>
                                        <select class="form-control" id="document_id" name="document_id">
                                            @foreach($document_types as $document_type)
                                                <option value="{{ $document_type->id }}" @if($document_type->id == $rh_interview_model->global_document_id) selected  @endif>{{ $document_type->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="document_number" class="required">Documento Numero</label>
                                        <input type="text" class="form-control only-numbers" name="document_number" id="document_number" value="{{ $rh_interview_model->document_number }}" placeholder="Ejemplo: 122154626">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="expiration_date">Fecha Vencimiento de Documento</label>
                                        <input type="date" class="form-control" name="expiration_date" value="{{ $rh_interview_model->expiration_date }}" id="expiration_date">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="blood_type_id" class="required">Grupo Sanguineo (RH)</label>
                                        <select class="form-control" id="blood_type_id" name="blood_type_id">
                                            @foreach($blood_types as $blood)
                                                <option value="{{ $blood->id }}" @if($blood->id == $rh_interview_model->blood_type_id ) selected  @endif>{{ $blood->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="email" class="required">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" value="{{ $rh_interview_model->email }}" placeholder="Ejemplo: algelalberto@gmail.com">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="mobile_number" class="required">Telefono</label>
                                        <input type="text" class="form-control only-numbers" name="mobile_number" id="mobile_number" value="{{ $rh_interview_model->mobile_number }}" placeholder="Ejemplo: 2136458874">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="department_id">Departamento</label>
                                        <select class="form-control" id="department_id" name="department_id">
                                            @foreach($department_list as $department)
                                                <option value="{{$department->id}}" @if($department->id == $rh_interview_model->RHInterviewToCity->CityToDepartment->id) selected  @endif>{{$department->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="city_id" class="required">Ciudad</label>
                                        <select class="form-control" name = "city_id" id = "city_id">
                                            @foreach($city_list as $city)
                                                <option value="{{$city->id}}" @if($city->id == $rh_interview_model->city_id) selected  @endif>{{$city->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="neighborhood" class="required">Barrio</label>
                                        <input type="text" class="form-control" name="neighborhood" id="neighborhood" value="{{ $rh_interview_model->neighborhood }}" placeholder="Ejemplo: La Vigia">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="address" class="required">Dirección</label>
                                        <input type="text" class="form-control" name="address" id="address" value="{{ $rh_interview_model->address }}" placeholder="Ejemplo: Cra 5ta # 98-65">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="lives_with">¿Con quién Vive?</label>
                                        <input type="text" class="form-control" name="lives_with" id="lives_with" value="{{ $rh_interview_model->lives_with }}" placeholder="Ejemplo: Mamá, Papá, Abuela">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="emergency_contact">Contacto de Emergencia</label>
                                        <input type="text" class="form-control" name="emergency_contact" id="emergency_contact" value="{{ $rh_interview_model->emergency_contact }}" placeholder="Ejemplo: Angela Barrera: Abuela">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="emergency_phone">Teléfono Contacto</label>
                                        <input type="text" class="form-control only-numbers" name="emergency_phone" id="emergency_phone" value="{{ $rh_interview_model->emergency_phone }}" placeholder="Ejemplo: 2136458874">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="he_has_children" style="margin-bottom: 12px;margin-right: 10px;">¿Tiene Hijos?</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="he_has_children" value='1' onclick="haveSon(this.value)" @if($rh_interview_model->he_has_children == '1') style="display: block;" checked @endif>
                                            <label class="form-check-label">Si</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="he_has_children" value='0' onclick="haveSon(this.value)" @if($rh_interview_model->he_has_children == '0') style="display: block;" checked @endif>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-9" id="option_son" @if($rh_interview_model->he_has_children == '1') style="display: block;" @else style="display: none;" @endif>
                                        <button type="button" class="btn btn-sm btn-success btn-sm" id="btnMinSon" value = "min" onclick="addSon(this.value)" style="margin-left: 8px;" @if($rh_interview_model->RHInterviewToSon()->count() <= 0) disabled @endif><i class="fas fa-minus"></i></button>
                                        <button type="button" class="btn btn-sm btn-success btn-sm" id="btnMaxSon" value = "plus" onclick="addSon(this.value)"><i class="fas fa-plus"></i></button>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <div id="son_list" class="row" @if($rh_interview_model->he_has_children == '1') style="display: block;" @else style="display: none;" @endif>
                                            @if($rh_interview_model->RHInterviewToSon()->count() >= 1)
                                                @foreach($rh_interview_model->RHInterviewToSon()->get() as $count => $son)
                                                    <div class="col-md-3" style="margin-bottom: 8px">
                                                        <input type="text" name="son[]" class="form-control child-form" placeholder="Hijo {{ $count+1 }}" value="{{$son->name}}">
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="col-md-3" style="margin-bottom: 8px">
                                                    <input type="text" name="son[]" class="form-control child-form" placeholder="Hijo 1">
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Disponibilidad</label>
                                        <br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="availability" value="morning"  @if(( $rh_interview_model->availability == '') || ($rh_interview_model->availability == 'morning') )  checked @endif>
                                            <label class="form-check-label" for="inlineRadio1">Mañana</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="availability" value="afternoon" @if( $rh_interview_model->availability == 'afternoon') checked @endif>
                                            <label class="form-check-label" for="inlineRadio2">Tarde</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="availability" value="night" @if( $rh_interview_model->availability == 'night') checked @endif>
                                            <label class="form-check-label" for="inlineRadio2">Noche</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="availability" value="anytime" @if( $rh_interview_model->availability == 'anytime') checked @endif>
                                            <label class="form-check-label" for="inlineRadio2">Cualquiera</label>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6" id="was_model-form">
                                        <label for="was_model">¿Ya ha trabajado como Modelo Webcam Antes?</label>
                                        </br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="was_model" id="was_model_yes" value="1" onclick="WasModelOption(this.value)" @if( $rh_interview_model->was_model == '1') checked @endif>
                                            <label class="form-check-label">Si</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="was_model" id="was_model_no" value="0" onclick="WasModelOption(this.value)" @if( $rh_interview_model->was_model == '0') checked @endif>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12" id="was_model_option" @if( $rh_interview_model->was_model == '1') style="display: block;" @else style="display: none;" @endif>
                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label for="which_study" class="required">¿En que estudio?</label>
                                                <input type="text" class="form-control" name="which_study" id="which_study" placeholder="Ejemplo: SexysStudio " value="{{ $rh_interview_model->which_study }}">
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="how_long" class="required">¿Por cuanto tiempo?</label>
                                                <input type="text" class="form-control" name="how_long" id="how_long" placeholder="Ejemplo: 4 meses "  value="{{ $rh_interview_model->how_long }}">
                                            </div>

                                            <div class="form-group col-md-5">
                                                <label for="work_pages" class="required">Páginas que trabajó</label>
                                                <input type="text" class="form-control" name="work_pages" id="work_pages" placeholder="Ejemplo: Jasmin, Chaturbate " value="{{ $rh_interview_model->work_pages }}">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="how_much" class="required">¿Cuánto facturaba?</label>
                                                <input type="text" class="form-control" name="how_much" id="how_much" placeholder="Ejemplo: 1 millon quincenal" value="{{ $rh_interview_model->how_much }}">
                                            </div>

                                            <div class="form-group col-md-8">
                                                <label for="retirement_reason" class="required">Razón de retiro</label>
                                                <input type="text" class="form-control" name="retirement_reason" id="retirement_reason" placeholder="Ejemplo: Viaje " value="{{ $rh_interview_model->retirement_reason }}">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </form>

                                <div class="card-footer">
                                    <div class="d-flex justify-content-end">
                                        <button onclick="editPersonal()" class="btn btn-success" style="margin-bottom: 14px;"> Aceptar</button>
                                    </div>
                                </div>

                            </div>


                            <!-- Datos Personales -->
                            <div class="card border-secondary tab-pane fade" id="education-level" role="tabpanel" aria-labelledby="education-level-list">

                                <form id="form-edit-education">
                                    @csrf
                                    <input name="id" type="hidden" value="{{ $rh_interview_model->id }}">
                                    <div class="card-body form-row">
                                        <div for="edu_level" class="form-group col-md-12">
                                            <label>Nivel Estudio</label>
                                            <br>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="edu_level" value="primaria" onclick="showProfession(this.id)" @if( $rh_interview_model->edu_level == 'primaria') checked @endif checked>
                                                <label class="form-check-label">Primaria</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="edu_level" value="bachillerato" onclick="showProfession(this.id)" @if( $rh_interview_model->edu_level == 'bachillerato') checked @endif>
                                                <label class="form-check-label">Bachillerato</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="edu_level" value="carrera tecnica" onclick="showProfession(this.id)" @if( $rh_interview_model->edu_level == 'carrera tecnica') checked @endif>
                                                <label class="form-check-label">Carrera Tecnica</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="edu_level" value="universidad" onclick="showProfession(this.id)" @if( $rh_interview_model->edu_level == 'universidad') checked @endif>
                                                <label class="form-check-label">Universidad</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="edu_level" value="postgrado" onclick="showProfession(this.id)" @if( $rh_interview_model->edu_level == 'postgrado') checked @endif>
                                                <label class="form-check-label">Postgrado</label>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Año de finalización</label>
                                            <input type="text" class="form-control" name="edu_final" id="final_edu" value="{{ $rh_interview_model->edu_final }}" placeholder="Ejemplo: 2020">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Nombre de la institución</label>
                                            <input type="text" class="form-control" name="edu_name_inst" id="nombre_inst_edu" value="{{ $rh_interview_model->edu_name_inst }}" placeholder="Ejemplo: Univalle">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label >Ciudad</label>
                                            <input type="text" class="form-control" name="edu_city" id="edu_city" value="{{ $rh_interview_model->edu_city }}" placeholder="Ejemplo: Cali">
                                        </div>

                                        <div class="form-group col-md-4" id="content_titulo" @if(is_null($rh_interview_model->edu_level)) style="display: none;" @endif>
                                            <label>Titulo obtenido</label>
                                            <input type="text" class="form-control" name="edu_title" id="edu_title" value="{{ $rh_interview_model->edu_title }}" placeholder="Ejemplo: Ingenieria en Sistemas de Información">
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label>Cursa Estudios actualmente</label>
                                            <br>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="edu_validate" value="1" onclick="showDetailStudy()" @if( $rh_interview_model->edu_validate == '1') checked @endif>
                                                <label class="form-check-label" for="edu_validate">Si</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="edu_validate" value="0" checked="" onclick="showDetailStudy()" @if( $rh_interview_model->edu_validate == '0') checked @endif>
                                                <label class="form-check-label" for="edu_validate">No</label>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-12" id="detail_study"  @if( $rh_interview_model->edu_validate == '1') style="display: block;" @else style="display: none;" @endif>
                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="edu_type_study" class="required">¿Que tipo de Estudios?</label>
                                                    <textarea class="form-control study" name="edu_type_study" id="edu_type_study" style="resize: none">{{ $rh_interview_model->edu_type_study }}</textarea>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="edu_time_final" class="required">¿Cuanto falta para finalizar?</label>
                                                    <textarea class="form-control study" name="edu_time_final" id="edu_time_final" style="resize: none">{{ $rh_interview_model->edu_time_final }}</textarea>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="edu_name_inst_current" class="required">Nombre de la institución</label>
                                                    <textarea class="form-control study" name="edu_name_inst_current" id="edu_name_inst_current" style="resize: none">{{ $rh_interview_model->edu_name_inst_current }}</textarea>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="edu_schedule" class="required">Horarios de dichos Estudios</label>
                                                    <textarea class="form-control study" name="edu_schedule" id="edu_schedule" style="resize: none">{{ $rh_interview_model->edu_schedule }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label for="edu_others">Otros conocimientos</label>
                                            <textarea class="form-control" name="edu_others" id="edu_others" style="resize: none">{{ $rh_interview_model->edu_others }}</textarea>
                                        </div>
                                    </div>

                                </form>

                                <div class="card-footer">
                                    <div class="d-flex justify-content-end">
                                        <button onclick="editEducation()" class="btn btn-success" style="margin-bottom: 14px;"> Aceptar</button>
                                    </div>
                                </div>

                            </div>

                            <div class="card border-secondary tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-working-information">

                                <form id="form-edit-working">
                                    @csrf
                                    <input name="id" type="hidden" value="{{ $rh_interview_model->id }}">

                                    <div class="card-body form-row">


                                        <div class="col-12">
                                            <button type="button" class="btn btn-sm btn-success btn-sm" id="btnMin" value = "min" onclick="addWork(this.value)" style="margin-left: 8px;" @if($rh_interview_model->RHInterviewToWorking()->count() <= 0) disabled @endif><i class="fas fa-minus"></i></button>
                                            <button type="button" class="btn btn-sm btn-success btn-sm" id="btnMax" value = "plus" onclick="addWork(this.value)"><i class="fas fa-plus"></i></button>
                                        </div>

                                        <div class="col-12">
                                            <table class="table tableForm table-hover">
                                                <thead>
                                                    <th>
                                                        <label>Empresa donde ha laborado</label>
                                                    </th>
                                                    <th>
                                                        <label>Tiempo Laborado</label>
                                                    </th>
                                                    <th>
                                                        <label>Cargo</label>
                                                    </th>
                                                    <th>
                                                        <label>Funciones-Motivo de Retiro</label>
                                                    </th>
                                                </thead>
                                                <tbody id="work_experience">
                                                    @if( $rh_interview_model->RHInterviewToWorking()->count() >= 1 )
                                                        @foreach( $rh_interview_model->RHInterviewToWorking()->get() as $count => $work )
                                                            <tr>
                                                                <td><input type="text" class="form-control works-info" name="works[{{$count}}][name_bussines]" value="{{$work->name_bussines}}"></td>
                                                                <td><input type="text" class="form-control works-info" name="works[{{$count}}][time_worked]" value="{{$work->time_worked}}"></td>
                                                                <td><input type="text" class="form-control works-info" name="works[{{$count}}][position]" value="{{$work->position}}"></td>
                                                                <td><input type="text" class="form-control works-info" name="works[{{$count}}][reason_withdrawal]" value="{{$work->reason_withdrawal}}"></td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td><input type="text" class="form-control  works-info" name="works[0][name_bussines]"></td>
                                                            <td><input type="text" class="form-control  works-info" name="works[0][time_worked]"></td>
                                                            <td><input type="text" class="form-control  works-info" name="works[0][position]"></td>
                                                            <td><input type="text" class="form-control  works-info" name="works[0][reason_withdrawal]"></td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>


                                        <div class="divTable table-responsive">
                                            <table class="table tableForm table-hover" id="datos">
                                                <thead>
                                                    <th>
                                                        <label ><i class="cil-user-follow"></i> Ha tenido personal a cargo</label>
                                                    </th>
                                                    <th>
                                                        <label ><i class="cil-clock"></i> Tiempo de desempleado</label>
                                                    </th>
                                                    <th>
                                                        <label ><i class="cil-calendar-check"></i> Actividades desarrolladas durante este tiempo</label>
                                                    </th>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="person_charge" value="1" onclick="personInCharge(this.value)" @if( $rh_interview_model->person_charge == '1') checked @endif>
                                                                <label class="form-check-label" for="person_charge">Si</label>
                                                            </div>

                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="person_charge" value="0" checked="" onclick="personInCharge(this.value)" @if( $rh_interview_model->person_charge == '0') checked @endif>
                                                                <label class="form-check-label" for="person_charge">No</label>
                                                            </div>

                                                            <div id="count_person" @if( $rh_interview_model->person_charge == '1') style="display: block;" @else style="display: none;" @endif>
                                                                <input type="text" style="margin-top: 10px;" class="form-control count_person only-numbers" name="count_person" placeholder="¿Cuántas personas?" value="{{ $rh_interview_model->count_person }}">
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <input class="form-control" type="text" name="unemployment_time" value="{{ $rh_interview_model->unemployment_time }}">
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control" name="developed_activities" style="resize: none">{{ $rh_interview_model->developed_activities }}</textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </form>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-end">
                                        <button onclick="editWorking()" class="btn btn-success" style="margin-bottom: 14px;"> Aceptar</button>
                                    </div>
                                </div>

                            </div>

                            <!-- Información Laboral -->
                            <div class="card border-secondary tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-Additional_information">

                                <form id="form-edit-additional">
                                    @csrf
                                    <input name="id" type="hidden" value="{{ $rh_interview_model->id }}">

                                    <div class="card-body form-row">

                                        <div class="form-group col-md-12">
                                            <label for="know_business">¿Conoce la empresa? ¿Qué información tiene de ella?</label>
                                            <textarea class="form-control" name="know_business" id="know_business" style="resize: none">{{ $rh_interview_model->know_business }}</textarea>
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label for="meet_us"><i class="cil-chevron-right"></i>¿Cómo se dio Cuenta de nosotros?</label>
                                            <br>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="meet_us"  value="facebook" checked="" onclick="EnableRecommended(this.value)" @if( $rh_interview_model->meet_us == 'facebook') checked @endif>
                                                <label class="form-check-label" for="meet_us">Facebook o Redes Sociales</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="meet_us" value="clasificados" onclick="EnableRecommended(this.value)" @if( $rh_interview_model->meet_us == 'clasificados') checked @endif>
                                                <label class="form-check-label" for="meet_us">Clasificados en Linea</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="meet_us" value="internet" onclick="EnableRecommended(this.value)" @if( $rh_interview_model->meet_us == 'internet') checked @endif>
                                                <label class="form-check-label" for="meet_us">La Internet o Página Web</label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="meet_us" value="recomendado" onclick="EnableRecommended(this.value)" @if( $rh_interview_model->meet_us == 'recomendado') checked @endif>
                                                <label class="form-check-label" for="meet_us">Recomendada(o) Por:</label>
                                            </div>

                                        </div>

                                        <div class="form-group col-md-12" id="recommended_name" style="padding-top: 5px; @if($rh_interview_model->meet_us != 'recomendado') display: none; @endif">
                                            <input type="text" class="form-control meet_us" name="recommended_name" value="{{ $rh_interview_model->recommended_name }}" >
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label for="strengths"><i class="cil-chevron-right"></i>¿Cuáles son sus mayores fortalezas?</label>
                                            <textarea class="form-control" name="strengths" id="strengths" style="resize: none">{{ $rh_interview_model->strengths }}</textarea>
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label for="exampleFormControlTextarea1"><i class="cil-chevron-right"></i>¿Qué aspectos de su personalidad considera podría mejorar?</label>
                                            <textarea class="form-control" name="personality" id="personality" style="resize: none">{{ $rh_interview_model->personality }}</textarea>
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label for="exampleFormControlTextarea1"><i class="cil-chevron-right"></i>¿Cómo se visualiza en un año? ¿Qué proyectos tiene?</label>
                                            <textarea class="form-control" name="visualize" id="visualize" style="resize: none">{{ $rh_interview_model->visualize }}</textarea>
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label for="exampleFormControlTextarea1"><i class="cil-chevron-right"></i>¿Cuál es su estado de salud actual?</label>
                                            <textarea class="form-control" name="health_state" id="health_state" style="resize: none">{{ $rh_interview_model->health_state }}</textarea>
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label for="exampleFormControlTextarea1"><i class="cil-chevron-right"></i>Aspiración Salarial</label>
                                            <input type="text" class="form-control only-numbers" name="wage_aspiration" id="wage_aspiration" value="{{ $rh_interview_model->wage_aspiration }}">
                                        </div>

                                        <div class="form-group col-md-12">
                                            <label for="exampleFormControlTextarea1"><i class="cil-chevron-right"></i>Observaciones</label>
                                            <textarea class="form-control" name="observations" id="observations" style="resize: none">{{ $rh_interview_model->observations }}</textarea>
                                        </div>
                                    </div>
                                </form>

                                <div class="card-footer">
                                    <div class="d-flex justify-content-end">
                                        <button onclick="editAdditional()" class="btn btn-success" style="margin-bottom: 14px;"> Aceptar</button>
                                    </div>
                                </div>

                            </div>
                            <div class="card border-secondary tab-pane fade" id="list-img-model" role="tabpanel" aria-labelledby="list-Additional_information">
                                <div class="gallery card-body form-row">

                                    <div class="form-group col-md-6">
                                        <div class="card-body">
                                            <div class="card text-center border-primary">
                                                <div class="card-header">
                                                    Rostro
                                                </div>
                                                <div class="row card-body" style="min-height: 250px; align-self: center;">
                                                    <a href="{{ asset($rh_interview_model->RHInterviewToImg->urlFace()) }}">
                                                        <img src="{{ asset($rh_interview_model->RHInterviewToImg->urlFace()) }}" style="width: 100%;">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <div class="card-body">
                                            <div class="card text-center border-primary">
                                                <div class="card-header">
                                                    Frente
                                                </div>
                                                <div class="row card-body" style="min-height: 250px; align-self: center;">
                                                    <a href="{{ asset($rh_interview_model->RHInterviewToImg->urlFront()) }}">
                                                        <img src="{{ asset($rh_interview_model->RHInterviewToImg->urlFront()) }}" style="width: 100%;">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group col-md-6">
                                        <div class="card-body">
                                            <div class="card text-center border-primary">
                                                <div class="card-header">
                                                    Lado
                                                </div>
                                                <div class="row card-body" style="min-height: 250px; align-self: center;">
                                                    <a href="{{ asset($rh_interview_model->RHInterviewToImg->urlSide()) }}">
                                                        <img src="{{ asset($rh_interview_model->RHInterviewToImg->urlSide()) }}" style="width: 100%;">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="card-body">
                                            <div class="card text-center border-primary">
                                                <div class="card-header">
                                                    Espalda
                                                </div>
                                                <div class="row card-body" style="min-height: 250px">
                                                    <a href="{{ asset($rh_interview_model->RHInterviewToImg->urlBack()) }}">
                                                        <img src="{{ asset($rh_interview_model->RHInterviewToImg->urlBack()) }}" class = "img-fluid">
                                                    </a>
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
</div>

<!-- Modal -->
<div class="modal fade cd-example-modal-xl" id="modal-rh-history" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dark" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalScrollableTitle">Historial</h5>
      </div>
      <div class="modal-body">
        <table class="table table-striped table-hover" id="table_interviews_history" style="width: 100%;">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Campo</th>
                    <th>Antes</th>
                    <th>Despues</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Salir</button>
      </div>
    </div>
  </div>
</div>

<!-- User Modal cite-->
<div id="modal-cite-user" class="modal fade" role="dialog">
	<div class="modal-dialog modal-dark">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Citar</h4>
			</div>
			<div class="modal-body">
				<form id="form_cite">
                    @csrf
					<input id="id_user" name="id" type="hidden" value="">
					<input id="option_user" name="cite" type="hidden" value="">
				</form>
			</div>
			<div class="modal-footer">
				<div class="col-sm">
					<button type="button" class="btn btn-success btn-block" onclick="citeModal(1)"><i class="fas fa-check"></i> Citar</button>
				</div>
				<div class="col-sm">
					<button type="button" class="btn btn-danger btn-block" onclick="citeModal(0)"><i class="fas fa-times"></i> No Citar</button>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@push('scripts')
<script>
    let id = document.getElementById("rh_interview_id").value;

    $(document).ready(function() {
        table = $('#table_interviews_history').DataTable({
            language: {
                url: '{{ asset("DataTables/Spanish.json") }}',
            },
            ajax: {
                method: "GET",
                url   : "{{ route('rh.interview.getInterviewHistory') }}",
                data: { id : id },
            },
            columns: [
                { data: "user" },
                { data: "field"},
                { data: "old"},
                { data: "new" },
                { data: "date"},
            ],
            processing: true,
            ordering: false,
            pageLength: 10,
            destroy: true,
        });

        var $gallery = new SimpleLightbox('.gallery a', {});

    });

    $('#aniimated-thumbnials').lightGallery({
    thumbnail:true
});

function ModalCite(id)
{
    $('#modal-cite-user').modal('show');
    document.getElementById("id_user").value = id;
}

function citeModal(cite)
{
    document.getElementById("option_user").value = cite;
    let form_cite = $("#form_cite").serialize();
    $.ajax({
        url: "{{ route('rh.interview.updateCite') }}",
        type: "POST",
        data: form_cite,
    }).done(function (res) {
        $('#modal-cite-user').modal('hide');
        table.ajax.reload();
        Toast.fire({
            icon: "success",
            title: "La entrevista fue creada exitosamente"
        });

    }).fail(function (res, textStatus, xhr) {
        let errors = res.responseJSON.errors;
        CallBackErrors(errors);
    });
}

    function showModalRhHistory()
    {
        table.ajax.reload();
        $('#modal-rh-history').modal('show');
    }

    function editPersonal()
    {
        ResetValidations();
        DisableModalActionButtons();
        let form_data = $("#form-edit-personal").serialize();
        let route =  "{{ route('rh.interview.editPersonal') }}";
        setInterviewForm(route, form_data);
    }
    function editEducation()
    {
        ResetValidations();
        DisableModalActionButtons();
        let form_data = $("#form-edit-education").serialize();
        let route =  "{{ route('rh.interview.editEducation') }}";
        setInterviewForm(route, form_data);
    }
    function editWorking()
    {
        ResetValidations();
        DisableModalActionButtons();
        let form_data = $("#form-edit-working").serialize();
        let route =  "{{ route('rh.interview.editWorking') }}";
        setInterviewForm(route, form_data);
    }
    function editAdditional()
    {
        ResetValidations();
        DisableModalActionButtons();
        let form_data = $("#form-edit-additional").serialize();
        let route =  "{{ route('rh.interview.editAdditional') }}";
        setInterviewForm(route, form_data);
    }


    function setInterviewForm(route, form_data)
    {
        $.ajax({
            url: route,
            type: "POST",
            data: form_data,
        })
        .done(function (res) {
            Toast.fire({
                icon: "success",
                title: "La entrevista fue creada exitosamente"
            });
        })
        .fail(function (res, textStatus, xhr) {
            let errors = res.responseJSON.errors;
            CallBackErrors(res);
        });
    }

    function adaptProfile(value)
    {
        document.getElementById("not_adapts_reason").value = '';
        document.getElementById("not_adapts_form").style.display = "";
        if( value == 1)
            document.getElementById("not_adapts_form").style.display = "none";
    }

    function EnableRecommended(value)
    {
        $('.form-control.meet_us').val('');

        document.getElementById("recommended_name").value = "";
        if (value == "recomendado")
        {
          document.getElementById("recommended_name").style.display = "";
        }
        else
        {
          document.getElementById("recommended_name").style.display = "none";
        }
    }

    function personInCharge(value)
    {
        $('.form-control.count_person').val('');
        if(value == '1'){
            document.getElementById("count_person").style.display = "block";
        }
        else
        {
            document.getElementById("count_person").style.display = "none";
        }
    }

    function addWork(value)
    {
        let data = document.getElementById('work_experience');
        let num = data.children.length;

        if(value == 'min')
        {
            if(num<=1){
                $('.form-control.works-info').val('');
                document.getElementById("btnMin").disabled = true;
            }
            else
            {
                data.removeChild(data.lastChild);
            }
        }
        else
        {
            document.getElementById("btnMin").disabled = false;
            data.insertAdjacentHTML('beforeend', "<tr><td><input type='text' class='form-control works-info' name='works["+num+"][name_bussines]'></td><td><input type='text' class='form-control works-info' name='works["+num+"][time_worked]'></td><td><input type='text' class='form-control works-info' name='works["+num+"][position]'></td><td><input type='text' class='form-control works-info' name='works["+num+"][reason_withdrawal]'></td></tr>");
        }
    }

    function showDetailStudy()
    {
        $('.form-control.study').val('');
        if($("input[name='edu_validate']:radio").is(':checked') && $("input:radio[name='edu_validate']:checked").val() == 1)
        {
            document.getElementById("detail_study").style.display = "block";
        }else{
            document.getElementById("detail_study").style.display = "none";
        }
    }

    function showProfession(value)
    {
        document.getElementById("content_titulo").style.display = "block";
    }

    function haveSon(value)
    {
        var sonList = document.getElementById('son_list');
        if (value == 1)
        {
            document.getElementById("option_son").style.display = "";
            document.getElementById("son_list").style.display = "";
            sonList.innerHTML = "<div class='col-md-3' style='margin-bottom: 8px'><input type='text' name='son[]' class='form-control child-form' placeholder='Hijo 1'></div>";
        }
        else
        {
            document.getElementById("btnMinSon").disabled = true;
            document.getElementById("option_son").style.display = "none";
            document.getElementById("son_list").style.display = "none";
            sonList.innerHTML = "";
        }
    }

    function addSon(value)
    {
      var sonList = document.getElementById('son_list');
      var numSon = sonList.children.length;
      if(value == 'min')
      {
        if(numSon<=1)
        {
            $('.form-control.child-form').val('');
            document.getElementById("btnMinSon").disabled = true;
        }
        else
        {
            sonList.removeChild(sonList.lastChild);
        }
      }
      else
      {
        document.getElementById("btnMinSon").disabled = false;
        sonList.insertAdjacentHTML('beforeend', "<div class='col-md-3 works-info' style='margin-bottom: 8px'><input type='text' name='son[]' class='form-control child-form' placeholder='Hijo "+(numSon+1)+"'></div>");
      }
    }

    $('#department').on('change', function () {
        let department_id = $(this).val();
        if( department_id != '' )
        {
            $.ajax({
                url: "{{ route('rh.interview.getCities') }}",
                type: 'GET',
                data: {department_id},
            }).done(function (res)
            {
                $('#city_id').html("");
                let options = '';
                $.each(res, function (i, city) {
                    let city_id = city.id;
                    let city_name = city.name;
                    options += '<option value="' + city_id + '">' + city_name + '</option>';
                });
                $('#city_id').append(options);
            });
        }
    });


    function WasModelOption(value)
    {
      if (value == "1"){
        document.getElementById("was_model_option").style.display = "";
      }else{
        document.getElementById("was_model_option").style.display = "none";
      }
      ClearModelOption();
    }

    function ClearModelOption()
    {
        document.getElementById("which_study").value = "";
        document.getElementById("how_long").value = "";
        document.getElementById("work_pages").value = "";
        document.getElementById("how_much").value = "";
        document.getElementById("retirement_reason").value = "";
    }

</script>
@endpush
