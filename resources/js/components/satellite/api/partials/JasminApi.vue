<template>
  <div class="container">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-lg-12">
            <form @submit.prevent="SaveApi" @keydown="api.onKeydown($event)" class="p-3">
              <div class="form-group row">
                <div class="col-md-5">
                  <input v-model="api.user" placeholder="Usuario" type="text" name="user" class="form-control form-control-sm" :class="{ 'is-invalid': api.errors.has('user') }">
                  <has-error :form="api" field="user"></has-error>
                </div>

                <div class="col-md-5">
                  <input v-model="api.token" placeholder="Token de acceso" type="text" name="token" class="form-control form-control-sm" :class="{ 'is-invalid': api.errors.has('token') }">
                  <has-error :form="api" field="token"></has-error>
                </div>

                <div class="col-md-2">
                  <button :disabled="api.busy" type="submit" class="btn btn-sm btn-success float-right">
                    <i class="fas fa-check"></i> Aceptar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <hr>

    <div class="container">
      <div class="row">
        <div class="col-md-12 py-3">
          <h3>Ejecutar API</h3>
        </div>
      </div>
    </div>
    
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-lg-12">
            <form @submit.prevent="SearchApi" class="p-3">
              <b-form-group label="Escoja un fechas">
                <b-form-radio-group id="radio-group-2" v-model="selected">
                  <b-form-radio :value="1">Seleccione una fecha</b-form-radio>
                  <b-form-radio :value="2">Seleccione un rango de fecha</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div class="form-group row">
                <div class="col-md-12" v-if="selected === 2">
                  <VueCtkDateTimePicker
                      :no-value-to-custom-elem="(true)"
                      v-model="date"
                      autoClose
                      dark  range
                      noLabel
                      noButton
                      color="purple"
                      inputSize="sm"
                      format="YYYY-MM-DD"
                      formatted="ll"
                      id="RangeDatePicker1">
                    <b-form-input size="sm" v-model="date.start +' to '+ date.end"></b-form-input>
                  </VueCtkDateTimePicker>
                </div>
                
                <div class="col-md-12" v-else>
                  <VueCtkDateTimePicker v-model="searchApi.date" dark onlyDate noLabel format="YYYY-MM-DD" formatted="ll" inputSize="sm"></VueCtkDateTimePicker>
                </div>
              </div>
              
              <div class="form-group row">
                <div class="col-md-5">
                  <b-form-select v-model="searchApi.studio_token" class="mb-3" size="sm">
                    <template #first>
                      <b-form-select-option :value="null" disabled>-- Seleccione un estudio --</b-form-select-option>
                    </template>
                    <b-form-select-option v-for="api_token in apis" :key="api_token.id" :value="api_token.access_token">{{ api_token.user }}</b-form-select-option>
                  </b-form-select>
                </div>

                <div class="col-md-5">
                  <b-form-input v-model="searchApi.model" size="sm" placeholder="Escriba el nick de la modelo..."></b-form-input>
                </div>

                <div class="col-md-2">
                  <button :disabled="searchApi.busy" type="submit" class="btn btn-sm btn-info float-right">
                    <i class="fas fa-arrow-right"></i> Enviar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <b-overlay :show="loading" rounded="sm" variant="dark" opacity="0.8">
      <template #overlay>
        <div class="text-center">
          <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
          <p id="cancel-label">Espere por favor...</p>
        </div>
      </template>
      <div class="container" v-if="Object.keys(drawTable).length !== 0">
      <div class="row">
        <div class="col-md-12 py-3">
          <h3>Resultados ({{ searchApi.fromDate + " al " + searchApi.toDate}})</h3>
        </div>
        <div class="col-md-12 col-lg-12">
          <table class="table">
            <thead class="thead-dark">
            <tr>
              <th scope="col">Modelo</th>
              <th scope="col">Valor</th>
              <th scope="col">Valor 2</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>{{ searchApi.model }}</td>
              <td>{{ drawTable.simple_total }}</td>
              <td>{{ drawTable.compound_total }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    </b-overlay>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "jasmin",
  data() {
    return {
      api: new Form({
        user: null,
        token: null,
        type: 1
      }),
      searchApi: new Form({
        studio_token: null,
        model:null,
        date: moment().format("YYYY-MM-DD")
      }),

      date:{
        start: moment().isoWeekday(0).format("YYYY-MM-DD"),
        end: moment().isoWeekday(6).format("YYYY-MM-DD"),
      },

      drawTable: {},
      
      isBusy: false,
      show: false,
      loading: false,
      selected: 1,
      apis: []
    }
  },

  computed: {
    isRequired() {
      if (this.api.user != null && this.api.token != null) {
        return true
      } else {
        return false
      }
    }
  },

  methods:{
    SaveApi(){
      const url = route("satellite.SaveApi");
      this.api.post(url, this.api).then((response) => {
        if (response.data.code == 200){
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }else{
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
      })
    },

    SearchApi(){
      const url = route("satellite.apiJasmin");
      this.loading = true
      if (this.selected === 1){
        this.searchApi.fromDate = moment().format("YYYY-MM-DD")
        this.searchApi.toDate = moment().format("YYYY-MM-DD")
      }else{
        this.searchApi.fromDate = this.date.start
        this.searchApi.toDate = this.date.end
      }
      this.searchApi.post(url, this.searchApi).then((response) => {
        this.drawTable = response.data
        this.loading = false
      })
    },

    GetApis(){
      const url = route("satellite.getApis", {id: this.api.type});
      axios.get(url).then((response) => {
        this.apis = response.data
      })
    }
  },

  mounted(){
    this.GetApis()
  }
}
</script>

<style scoped>

</style>