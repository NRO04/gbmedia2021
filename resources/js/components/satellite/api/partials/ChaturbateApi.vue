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
            <form @submit.prevent="SearchApi" @keydown="searchApi.onKeydown($event)" class="p-3">
              <b-form-group label="Escoja un fechas">
                <b-form-radio-group id="radio-group-2" v-model="option">
                  <b-form-radio :value="1">Venta total diaria</b-form-radio>
                  <b-form-radio :value="2">Estadisticas por modelo</b-form-radio>
                </b-form-radio-group>
              </b-form-group>

              <div v-if="option === 2">
                <b-form-group label="Escoja un fechas">
                  <b-form-radio-group id="radio-group-3" v-model="selected">
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
              </div>

              <div class="form-group row pt-3">
                <div class="col-md-10">
                  <b-form-select v-model="searchApi.studio_token" class="mb-3" size="sm">
                    <template #first>
                      <b-form-select-option :value="null" disabled>-- Seleccione un estudio --</b-form-select-option>
                    </template>
                    <b-form-select-option v-for="api_token in apis" :key="api_token.id" :value="{api_value:api_token.access_token, api_user:api_token.user}">{{ api_token.user }}</b-form-select-option>
                  </b-form-select>
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
    
    <div class="container" v-if="items.length !== 0">
        <div class="row">
          <div class="col-md-12 py-3">
            <h3>
              Resultados: <span v-text="option === 1 ? 'Venta total diaria' : 'Rango: '+ searchApi.fromDate + ' al ' + searchApi.toDate"></span>
            </h3>
          </div>
          <div class="col-md-12 col-lg-12">
            <b-table striped head-variant="dark"
                :items="items"
                :fields="fields"
                stacked="md"
                :busy="isBusy"
                show-empty>
              <template #table-busy>
                <div class="text-center text-info my-2">
                  <b-spinner class="align-middle"></b-spinner>
                  <strong>Loading...</strong>
                </div>
              </template>
            </b-table>
          </div>
        </div>
      </div>
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "Chaturbate",
  data() {
    return {
      api: new Form({
        user: null,
        token: null,
        type: 2
      }),

      searchApi: new Form({
        user: null,
        studio_token: null,
        model:null,
        date: moment().format("YYYY-MM-DD")
      }),

      date:{
        start: moment().isoWeekday(0).format("YYYY-MM-DD"),
        end: moment().isoWeekday(6).format("YYYY-MM-DD"),
      },

      isBusy: false,
      show: false,
      selected: 1,
      option: 1,
      apis: [],
      items: [],
      fields: []
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
          this.GetApis()
        }else{
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
      })
    },

    SearchApi(){
      const url = route("satellite.apiChaturbate");
      this.searchApi.option = this.option
      this.searchApi.selected = this.selected
      this.searchApi.user = this.api.user
      this.items = [];
      if (this.selected === 1){
        this.searchApi.fromDate = moment().format("YYYY-MM-DD")
        this.searchApi.toDate = moment().format("YYYY-MM-DD")
      }else{
        this.searchApi.fromDate = this.date.start
        this.searchApi.toDate = this.date.end
      }
      this.isBusy = true
      this.searchApi.post(url, this.searchApi).then((response) => {
        if (response.data.code === 200)
        {
          this.items = response.data.data.items
          this.fields = response.data.data.fields
          this.isBusy = false
        }
        else {
          SwalGB.fire({
            icon: response.data.icon,
            title: 'Oops...',
            text: response.data.msg,
            showCancelButton: false,
          })
          this.isBusy = false
        }
      }).catch((error) => {
        this.isBusy = false
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