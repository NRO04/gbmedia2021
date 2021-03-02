<template>
  <div id="monitoring">
    <div class="row">
      <div class="col-md-12 col-sm-12 col-lg-12">
        <div class="card shadow border-0">
          <div class="card-header">
            <b-row class="justify-content-between">
              <b-col md="7" lg="7" sm="12">
                 <span class="h5" v-if="optionSelected === 1">Registro de asistencia</span>
                 <span class="h5" v-else-if="optionSelected === 2">Reportes de monitoreo</span>
                 <span class="h5" v-else-if="optionSelected === 3">Registro de estadisticas</span>
              </b-col>
              <b-col md="2" lg="2" sm="12">
                  <b-form-select v-model="optionSelected" size="sm">
                    <b-form-select-option :value="1">Registro de asistencia</b-form-select-option>
                    <b-form-select-option :value="2">Reportes de monitoreo</b-form-select-option>
                    <b-form-select-option :value="3">Registro de estadisticas</b-form-select-option>
                  </b-form-select>
              </b-col>
            </b-row>
          </div>

          <div class="card-body">
            <div v-if="optionSelected === 1">
              <attendancetable :user="user" :permissions="permissions"></attendancetable>
            </div>
            <div v-else-if="optionSelected === 2">
              <monitoringtable :user="user" :permissions="permissions"></monitoringtable>
            </div>
            <div v-else-if="optionSelected === 3">
              <statisticstable :user="user" :permissions="permissions"></statisticstable>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import monitoringtable from "./partials/monitoringtable";
import Statisticstable from "./partials/statisticstable";
import Attendancetable from "./partials/attendancetable";

export default {
  props: ['user', 'permissions'],
  name: "monitoring",
  components: {
    Attendancetable,
    Statisticstable,
    monitoringtable
  },
  data: () => {
    return {
      optionSelected: 1,
    }
  }
}
</script>

<style scoped>

</style>