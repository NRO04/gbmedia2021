<template>
  <div id="resume-1">
    <div class="row">
      <div class="col-md-12">
        <div class="card text-white bg-gradient-primary container-resume-1">
          <div class="card-body card-body pb-0 d-flex justify-content-between align-items-start">
            <div>
              <div id="container-week-goal">Meta semanal estudio: <b id="studio-week-goal" class="text-bold">$10.000</b></div>
              <div id="container-week-earnings">
                Total alcanzado: <b id="studio-week-earnings" class="text-bold">2.993</b> <i class="fa fa-info-circle"></i> <i class="fa fa-info-circle"></i>
              </div>
              <div id="container-week-hour-billing">
                Facturación por hora: <b id="studio-week-hour-billing" class="text-bold">$122.5</b>
              </div>
              <div id="container-week-reached">
                Alcanzado: <b id="studio-week-reached" class="text-bold">22.27%</b>
              </div>
              <div id="container-week-models" title="Modelos activas en el estudio: 30<br>Modelos facturando por ahora: 25" v-b-tooltip.hover.html>
                Activas: <b id="studio-week-active-models" class="text-bold">30</b> / Facturando: <b id="studio-week-billing-models" class="text-bold">25</b>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Resume1",
  data() {
    return{

    }
  },
  methods:{
      
  },

  created() {
  }
}
</script>

<style scoped>
.container-resume-1 {
  min-height: 160px;
}
</style>
