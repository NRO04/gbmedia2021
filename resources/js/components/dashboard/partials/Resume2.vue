<template>
<div id="resume_2">
  <div class="row">
    <div class="col-md-6">
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
    <div class="col-md-6">
      <div class="card text-white bg-gradient-info container-resume-1">
        <div class="card-body card-body pb-0 d-flex justify-content-between align-items-start">
          <div>
            <div id="container-week-earnings1">
              Total Inasistencias: <b id="studio-week-earnings1" class="text-bold">{{ overallData.unjustified }} / Semana</b>
            </div>
            <div id="container-week-earnings2">
              <i class="fas fa-plus"></i> Inasistencias: <b id="studio-week-earnings2" class="text-bold">{{ overallData.unjustified }} / Semana</b>
            </div>
            <div id="container-week-hour-billing2">
              Perdido Inasistencias: <b id="studio-week-hour-billing2" class="text-bold">$122.5</b>
            </div>
            <div id="container-week-reached2" v-b-hover="hoverHandler">
              Resumen: <i class="fas fa-question-circle text-white"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <b-modal ref="my-modal" header-close-content="" header-bg-variant="dark" centered size="xl" hide-footer title="Resumen Inasistencias">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <b-table striped hover :items="items" :fields="fields" :tbody-tr-class="rowClass"></b-table>
        </div>
      </div>
    </div>
  </b-modal>
</div>
</template>

<script>
export default {
  name: "Resume2",
  data() {
    return{
      overallData: {},
      fields: [
        {key: 'model_nick', label: 'Modelo', sortable: true, sortDirection: 'desc'},
        {key: 'model_worked', label: 'Trabajados', sortable: true, sortDirection: 'desc'},
        {key: 'model_justified', label: 'Justificadas', sortable: true, sortDirection: 'desc'},
        {key: 'model_unjustified', label: 'Injustificadas', sortable: true, sortDirection: 'desc'},
        {key: 'model_period', label: 'Periodo', sortable: true, sortDirection: 'desc'},
        {key: 'model_goal', label: 'Meta1', sortable: true, sortDirection: 'desc'},
        {key: 'lost_money', label: 'Dinero Perdido', sortable: true, sortDirection: 'desc'},
      ],
      items: []
    }
  },
  methods:{
    GetAttendanceStats(){
      const url = route("home.getAttendanceStats")
      axios.get(url).then((response) => {
        this.overallData = response.data.overall
        this.items = response.data.summaries
        console.log(this.items[0].model_nick);
      })
    },

    hoverHandler(isHovered) {
      if (isHovered) {
        this.$refs['my-modal'].show()
      } else {
        // Do something else
      }
    },

    rowClass(item, type) {
      if (!item || type !== 'row') return
      if (item.model_unjustified >= 1) return 'table-danger'
    }
  },

  created() {
    this.GetAttendanceStats()
  }
}
</script>

<style scoped>
.container-resume-1 {
  min-height: 160px;
}

#container-week-reached:hover{
    cursor: pointer!important;
}
</style>
