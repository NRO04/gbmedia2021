@extends('layouts.app')
@section('pageTitle', 'Prospectos')
@push('styles')
<style type="text/css">
  .tabladebehoras td, th{
    width: 60px;
    height: 40px;
    text-align: center;
    border: 1px solid #B6B5B5;
    border-style:dashed;
  }
</style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item" style="font-weight: bold">Recursos Humanos</li>
<li class="breadcrumb-item active"><a href="">Prospectos</a></li>
@endsection
@section('content')
<div class="card">
  <h4 class="card-header">
    <div class="row">
      <div class="col-sm">
      Historial de vacaciones del Personal <span class="badge badge-primary">{{ $rank }}</span>
      </div>
    </div>
  </h4>

  <div class="card-body">
  <div class="row">
  <div class="col-sm-6">
    <div class="card">
      <div class="card-body">
        <label class="card-title">Seleccionar Fecha</label>
        <select class="form-control form-control-sm" onchange="loadURL()" name="select_range" id="select_range">
        @foreach($vacation_rank as $vac_rank)
            <option @if($rank == $vac_rank->rank) selected @endif>{{ $vac_rank->rank }}</option>
        @endforeach
        </select>
      </div>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="card">
      <div class="card-body">
        <label class="card-title">Seleccionar Rol</label>
        <select class="form-control form-control-sm" onchange="loadURL()" name="select_role" id="select_role">
          <option>All</option>
          @foreach($setting_roles as $roles)
              <option @if($role == $roles->id) selected @endif value="{{ $roles->id }}">{{ $roles->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  <!--primera quincena-->
  <div class="col-sm-12">
    <div class="card">
      <div class="card-body">
        <table class="tabladebehoras">
          <thead>
            <tr>
              <th style="width: 120px;text-align: left; padding-left: 3px">Usuarios</th>
              @for($i = 1; $i <= 15; $i++)
                @if($day == $i && $rank == $current_rank)
                <th style="border-right: 2px solid #FF4000; border-left: 2px solid #FF4000; background-color: #FF4000" >
                  {{ $i }}
                </th>
                @else
                <th>
                  {{ $i }}
                </th>
                @endif
              @endfor
            </tr>
          </thead>
          <tbody>
          @foreach($diferentUserVacationOne as $user)
          <tr>
            <td style="width: 120px;text-align: left;padding-left: 3px">{{ $user->rhVactionUserToUser->roleUserFullName()}}</td>
            @for($i=1; $i <= 15 ; $i++)
            @php($today_exist = false)
              @if($i <= 9)
                @php($day_m = "0".$i)
              @else
                @php($day_m = $i)
              @endif
              {{$exist_vac = false}}
              @foreach($vacationListPersonOne as $vac)
                @if($user->user_id == $vac->user_id && $vac->day == $day_m)
                  @if($vac->date == $today)
                    @php($today_exist = true)
                  @endif
                    @php($exist_vac = true)
                @endif
              @endforeach
              @if($exist_vac)
                @if($today_exist)
                  <td style="background: green; color: white; border: #FF4000 2px solid">
                    <p style="font-size: 9px;text-align: center;font-weight: bold;margin-bottom:0">Vacaciones</p>
                  </td>
                @else
                  <td style="background: green; color: white; ">
                    <p style="font-size: 9px;text-align: center;font-weight: bold;margin-bottom:0">Vacaciones</p>
                  </td>
                @endif
              @else
              <td></td>
              @endif
            @endfor
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!--fin primera quincena-->

  <!--segunda quincena-->
  <div class="col-sm-12">
    <div class="card">
      <div class="card-body">
        <table class="tabladebehoras">
          <thead>
            <tr>
              <th style="width: 120px;text-align: left; padding-left: 3px">Usuarios</th>
              @for($i = 16; $i <= $end_month; $i++)
                @if($day == $i && $rank == $current_rank)
                <th style="border-right: 2px solid #FF4000; border-left: 2px solid #FF4000; background-color: #FF4000" >
                  {{ $i }}
                </th>
                @else
                <th>
                  {{ $i }}
                </th>
                @endif
              @endfor
            </tr>
          </thead>
          <tbody>
          @foreach($diferentUserVacationTwo as $user)
          <tr>
            <td style="width: 120px;text-align: left;padding-left: 3px">{{ $user->rhVactionUserToUser->roleUserFullName()}}</td>
            @for($i = 16; $i <= $end_month; $i++)
              @php($today_exist = false)
                @if($i <= 9)
                  @php($day_m = "0".$i)
                @else
                  @php($day_m = $i)
                @endif
                @php($exist_vac = false)
                @foreach($vacationListPersonTwo as $vac)
                  @if($user->user_id == $vac->user_id && $vac->day == $day_m)
                    @if($vac->date == $today)
                      @php($today_exist = true)
                    @endif
                    @php($exist_vac = true)
                  @endif
                @endforeach
                @if($exist_vac)
                  @if($today_exist)
                    <td style="background: green; color: white; border: #FF4000 2px solid">
                      <p style="font-size: 9px;text-align: center;font-weight: bold;margin-bottom:0">Vacaciones</p>
                    </td>
                  @else
                    <td style="background: green; color: white; ">
                      <p style="font-size: 9px;text-align: center;font-weight: bold;margin-bottom:0">Vacaciones</p>
                    </td>
                  @endif
                @else
                <td></td>
                @endif
            @endfor
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- fin segunda quincena-->

</div>

  </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function(){
    collapseMenu();
});
function loadURL()
{
  let rank = document.getElementById("select_range").value;
  let role = document.getElementById("select_role").value;

  let url = "{{ route('rh.vacationRequest.staffVacations') }}"+"?rank="+rank+"&role="+role;
  window.location = url;
}
</script>
@endpush
