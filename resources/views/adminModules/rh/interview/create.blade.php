@extends('layouts.app')
@section('pageTitle', 'Prospectos')
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold">Recursos Humanos</li>
<li class="breadcrumb-item active"><a href="">Formato Entrevista</a></li>
@endsection
@section('content')

<div class="row">
    <div class="card border-secondary" style="width: 100%;">

    <h4 class="card-header">
	    <div class="row">
            <div class="col-sm">
                Crear <span class="badge badge-primary">Prospecto</span>
            </div>
        </div>
    </h4>

            <div class="card-body">

                    <div class="alert alert-primary border-primary" role="alert">
                        <div class="row">
                            <div class="col-sm"><B>Nombre Entrevistador</B> {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                            <div class="col-sm" style="text-align: right;"><i class="fas fa-calendar-alt"></i> <B>Fecha</B> <span class="badge bg-success">{{ $date }}</span></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                            <div class="list-group" id="list-tab" role="tablist">
                                <a class="list-group-item list-group-item-action active" id="personal-info-list" data-toggle="list" href="#personal-info" role="tab" aria-controls="home">Datos Personales</a>
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="tab-content" id="nav-tabContent">
                                <!-- Entrevistador -->
                                <div class="card border-secondary tab-pane fade show active" id="personal-info" role="tabpanel" aria-labelledby="personal-info-list">

                                <form id="form-create">
                                    @csrf
                                    <input name="user_interviewer_id" type="hidden" value="{{ Auth::user()->id }}">

                                    <div class="card-body form-row">

                                        <div class="form-group col-md-12">
                                            <label for="setting_role_id" class="required">Cargo para el que aplica</label>
                                            <select class="form-control" id="setting_role_id" name="setting_role_id">
                                                <option value>Seleccione el cargo...</option>
                                                @foreach ($setting_roles as $role)
                                                    <option value="{{ $role->id}}" @if( $role->id == old('id') ) selected @endif>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="first_name" class="required">Nombre</label>
                                            <input type="text" class="form-control" name="first_name" id="first_name" placeholder="Ejemplo: Angel" value="@isset($referred_prospect_data->first_name) {{ $referred_prospect_data->first_name }} @endisset">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="middle_name">Segundo Nombre</label>
                                            <input type="text" class="form-control" name="middle_name" id="middle_name" placeholder="Ejemplo: Alberto" value="@isset($referred_prospect_data->middle_name) {{ $referred_prospect_data->middle_name }} @endisset">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="last_name" class="required">Apellido</label>
                                            <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Ejemplo: Perez" value="@isset($referred_prospect_data->last_name) {{ $referred_prospect_data->last_name }} @endisset">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="second_last_name">Segundo Apellido</label>
                                            <input type="text" class="form-control" name="second_last_name" id="second_last_name" placeholder="Ejemplo: Perez" value="@isset($referred_prospect_data->second_last_name) {{ $referred_prospect_data->second_last_name }} @endisset">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="birth_date">Fecha Nacimiento</label>
                                            <input type="date" class="form-control" name="birth_date" id="birth_date">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="document_id">Tipo Documento</label>
                                            <select class="form-control" id="document_id" name="document_id">
                                                @foreach ($document_types as $dt)
                                                    <option value="{{ $dt->id}}" @if( $role->id == old('id') ) selected @endif>{{ $dt->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="document_number" class="required">Documento Numero</label>
                                            <input type="text" class="form-control only-numbers" name="document_number" id="document_number" placeholder="Ejemplo: 122154626">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="expiration_date">Fecha Vencimiento de Documento</label>
                                            <input type="date" class="form-control" name="expiration_date" id="expiration_date">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="blood_type_id" class="required">Grupo Sanguineo (RH)</label>
                                            <select class="form-control" id="blood_type_id" name="blood_type_id">
                                                <option value=" ">Seleccione el grupo sanguíneo...</option>
                                                @foreach ($blood_types as $blood)
                                                    <option value="{{ $blood->id}}">{{ $blood->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="email" class="required">Email</label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Ejemplo: algelalberto@gmail.com" value="@isset($referred_prospect_data->email) {{ $referred_prospect_data->email }} @endisset">
                                        </div>

                                            <div class="form-group col-md-6">
                                                <label for="mobile_number" class="required">Telefono</label>
                                                <input type="text" class="form-control only-numbers" name="mobile_number" id="mobile_number" placeholder="Ejemplo: 2136458874" value="@isset($referred_prospect_data->phone_number) {{ $referred_prospect_data->phone_number }} @endisset">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="department_id">Departamento</label>
                                                <select class="form-control" id="department_id" name="department_id">
                                                    <option value>Seleccione el departamento...</option>
                                                    @foreach ($department_list as $department)
                                                        <option value="{{ $department->id}}">{{ $department->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="city_id" class="required">Ciudad</label>
                                                <select class="form-control" name = "city_id" id = "city_id"></select>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="neighborhood" class="required">Barrio</label>
                                                <input type="text" class="form-control" name="neighborhood" id="neighborhood" placeholder="Ejemplo: La Vigia">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="address" class="required">Dirección</label>
                                                <input type="text" class="form-control" name="address" id="address" placeholder="Ejemplo: Cra 5ta # 98-65">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="lives_with">¿Con quién Vive?</label>
                                                <input type="text" class="form-control" name="lives_with" id="lives_with" placeholder="Ejemplo: Mamá, Papá, Abuela">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="emergency_contact">Contacto de Emergencia</label>
                                                <input type="text" class="form-control" name="emergency_contact" id="emergency_contact" placeholder="Ejemplo: Angela Barrera: Abuela">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="emergency_phone">Teléfono Contacto</label>
                                                <input type="text" class="form-control only-numbers" name="emergency_phone" id="emergency_phone" placeholder="Ejemplo: 2136458874">
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="he_has_children" style="margin-bottom: 12px;margin-right: 10px;"><i class="cil-chevron-right"></i> ¿Tiene Hijos?</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="he_has_children" value='1' onclick="haveSon(this.value)">
                                                    <label class="form-check-label">Si</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="he_has_children" value='0' onclick="haveSon(this.value)" checked>
                                                    <label class="form-check-label">No</label>
                                                </div>
                                            </div>

                                            <div class="col-9" id="option_son" style="display: none;">
                                                <button type="button" class="btn btn-sm btn-success btn-sm" id="btnMinSon" value = "min" onclick="addSon(this.value)" style="margin-left: 8px;" disabled><i class="fas fa-minus"></i></button>
                                                <button type="button" class="btn btn-sm btn-success btn-sm" id="btnMaxSon" value = "plus" onclick="addSon(this.value)"><i class="fas fa-plus"></i></button>
                                            </div>

                                            <div class="form-group col-md-12">
                                                <div class="row" id="son_list" style="display: none;">
                                                    <div class="col-md-3" style="margin-bottom: 8px">
                                                        <input type="text" name="son[]" class="form-control child-form" placeholder="Hijo 1">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label><i class="cil-chevron-right"></i> Disponibilidad</label>
                                                <br>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="availability" value="morning"  @if((old('availability') == '') || (old('availability') == 'mañana') )  checked @endif>
                                                    <label class="form-check-label" for="inlineRadio1">Mañana</label>
                                                </div>

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="availability" value="afternoon" @if(old('availability') == 'tarde') checked @endif>
                                                    <label class="form-check-label" for="inlineRadio2">Tarde</label>
                                                </div>

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="availability" value="night" @if(old('availability') == 'noche') checked @endif>
                                                    <label class="form-check-label" for="inlineRadio2">Noche</label>
                                                </div>

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="availability" value="anytime" @if(old('availability') == 'cualquiera') checked @endif>
                                                    <label class="form-check-label" for="inlineRadio2">Cualquiera</label>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6" id="was_model-form" style="display: none;">
                                                <label for="was_model">¿Ya ha trabajado como Modelo Webcam Antes?</label>
                                                </br>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="was_model" id="was_model_yes" value="1" onclick="WasModelOption(this.value)">
                                                    <label class="form-check-label">Si</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="was_model" id="was_model_no" value="0" onclick="WasModelOption(this.value)" checked>
                                                    <label class="form-check-label">No</label>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-12" id="was_model_option" style="display: none;">
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="which_study" class="required">¿En que estudio?</label>
                                                        <input type="text" class="form-control" name="which_study" id="which_study" placeholder="Ejemplo: SexysStudio " value="{{ old('what_study') }}">
                                                    </div>

                                                    <div class="form-group col-md-3">
                                                        <label for="how_long" class="required">¿Por cuanto tiempo?</label>
                                                        <input type="text" class="form-control" name="how_long" id="how_long" placeholder="Ejemplo: 4 meses "  value="{{ old('how_long') }}">
                                                    </div>

                                                    <div class="form-group col-md-5">
                                                        <label for="work_pages" class="required">Páginas que trabajó</label>
                                                        <input type="text" class="form-control" name="work_pages" id="work_pages" placeholder="Ejemplo: Jasmin, Chaturbate " value="{{ old('pages_job') }}">
                                                    </div>

                                                    <div class="form-group col-md-4">
                                                        <label for="how_much" class="required">¿Cuánto facturaba?</label>
                                                        <input type="text" class="form-control" name="how_much" id="how_much" placeholder="Ejemplo: 1 millon quincenal" value="{{ old('how_much') }}">
                                                    </div>

                                                    <div class="form-group col-md-8">
                                                        <label for="retirement_reason" class="required">Razón de retiro</label>
                                                        <input type="text" class="form-control" name="retirement_reason" id="retirement_reason" placeholder="Ejemplo: Viaje " value="{{ old('retiro_model') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>


                                    <div class="card-footer">
                                        <div class="d-flex justify-content-end">
                                            <button onclick="create()" id="btn-create" class="btn btn-success" style="margin-bottom: 14px;"> Aceptar</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
            </div>
    </div>
</div>

@endsection
@push('scripts')
<script>

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

    function adaptProfile(value)
    {
        document.getElementById("not_adapts_reason").value = '';
        document.getElementById("not_adapts_form").style.display = "";
        if( value == 1)
            document.getElementById("not_adapts_form").style.display = "none";
    }

    $('#setting_role_id').on('change', function () {

        let role_id = $(this).val();
        document.getElementById("was_model_yes").checked = false;
        document.getElementById("was_model_no").checked = false;
        document.getElementById("was_model-form").style.display = "none";

        if(role_id == 14){
            document.getElementById("was_model-form").style.display = "";
            document.getElementById("was_model_no").checked = true;
        }

        document.getElementById("was_model_option").style.display = "none";
        ClearModelOption();
    });

    $('#was_model').on('change', function () {
        let w_model_value = $(this).val();
    });

    $('#department_id').on('change', function () {

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

    function create(){

        ResetValidations();
        DisableModalActionButtons();

        let form_data = $("#form-create").serialize();
        $.ajax({
            url: "{{ route('rh.interview.create') }}",
            type: "POST",
            data: form_data,
        })
        .done(function (res) {
            Toast.fire({
                icon: "success",
                title: "La entrevista fue creada exitosamente"
            });
            location.href = res.url;
        })
        .fail(function (res, textStatus, xhr) {
            CallBackErrors(res);
        });
    };

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
        sonList.insertAdjacentHTML('beforeend', "<div class='col-md-3' style='margin-bottom: 8px'><input type='text' name='son[]' class='form-control child-form' placeholder='Hijo "+(numSon+1)+"'></div>");
      }

    }

    function getCantWork()
    {
        let d1 = document.getElementById('son_list');
        return d1.children.length;
    }

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
