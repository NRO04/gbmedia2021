<template>
  <div id="container-statistics-resume">
    Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusantium aut
    consequuntur itaque voluptas autem repudiandae. Odit illo unde totam! Unde
    voluptatum itaque libero recusandae corporis, perspiciatis consectetur!
    Facilis, itaque perferendis.

    <div class="row justify-content-between mb-4">
      <div class="col-md-4">
        <b-form-group>
      
          <VueCtkDateTimePicker
            :no-value-to-custom-elem="true"
            v-model="search"
            autoClose
            dark
            range
            noShortcuts
            noLabel
            noButton
            color="blue"
            inputSize="sm"
            format="YYYY-MM-DD"
            formatted="ll"
            id="RangeDatePicker"
          >
            
            <b-form-input
              size="sm"
              v-model="search.start + ' / ' + search.end"
            ></b-form-input>
          
          </VueCtkDateTimePicker>
        </b-form-group>
      </div>
      <div class="col-md-4">
        <b-form-select
          v-model="search.selectedLocation"
          @change="GetAttendanceStats()"
          size="sm"
        >
          <b-form-select-option
            v-for="(location, i) in locations"
            :key="i"
            :value="location.id"
            >{{ location.name }}</b-form-select-option
          >
        </b-form-select>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-gradient-primary container-resume-1">
          <div
            class="card-body card-body pb-0 d-flex justify-content-between align-items-start"
          >
            <div>
              <div id="container-week-goal">
                Meta semanal estudio:
                <b id="studio-week-goal" class="text-bold">{{
                  overallData.studio_goal
                }}</b>
              </div>
              <div id="container-week-earnings">
                Total alcanzado:
                <b id="studio-week-earnings" class="text-bold">{{
                  overallData.reached_amount
                }}</b>
                <i class="fa fa-info-circle"></i>
                <i class="fa fa-info-circle"></i>
              </div>
              <div id="container-week-hour-billing">
                Facturación por hora:
                <b id="studio-week-hour-billing" class="text-bold">{{
                  overallData.earnings_per_hour
                }}</b>
              </div>
              <div id="container-week-reached">
                Alcanzado:
                <b id="studio-week-reached" class="text-bold">22.27%</b>
              </div>
              <div
                id="container-week-models"
                :title="
                  'Modelos activas en el estudio:' +
                  overallData.active_models +
                  '<br>Modelos facturando por ahora:' +
                  overallData.models_earning
                "
                v-b-tooltip.hover.html
              >
                Activas:
                <b id="studio-week-active-models" class="text-bold">{{
                  overallData.active_models
                }}</b>
                / Facturando:
                <b id="studio-week-billing-models" class="text-bold">{{
                  overallData.models_earning
                }}</b>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-gradient-info container-resume-1">
          <div
            class="card-body card-body pb-0 d-flex justify-content-between align-items-start"
          >
            <div>
              <div id="container-week-earnings1">
                Total Inasistencias:
                <b id="studio-week-earnings1" class="text-bold">
                  <!-- {{
                  overallData.unjustified
                }} -->
                </b>
              </div>
              <div id="container-week-earnings2">
                <i class="fas fa-plus"></i> Inasistencias:
                <b id="studio-week-earnings2" class="text-bold">
                  <!-- {{
                  most.nick + "(" + most.unjustified_days + ")"
                }} -->
                </b>
              </div>
              <div id="container-week-hour-billing2">
                Perdido Inasistencias:
                <b id="studio-week-hour-billing2" class="text-bold"
                  >
                  <!-- {{ lost.toFixed(2) }} -->
                  </b
                >
              </div>
              <div id="container-week-reached2" v-b-hover="hoverHandler">
                Resumen: <i class="fas fa-question-circle text-white"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- /.col-->
      <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-gradient-warning container-resume-1">
          <div
            class="card-body card-body pb-0 d-flex justify-content-between align-items-start"
          >
            <div>
              <div id="container-week-earnings3">
                <i class="fas fa-plus"></i> Ventas:
                <b id="studio-week-earnings3" class="text-bold">{{
                  best_selling.nick + "($" + best_selling.value + ")"
                }}</b>
              </div>
              <div id="container-week-earnings4">
                <i class="fas fa-minus"></i> Ventas:
                <b id="studio-week-earnings4" class="text-bold">{{
                  worst_selling.nick + "($" + worst_selling.value + ")"
                }}</b>
              </div>
              <div id="container-week-hour-billing3">
                <i class="fas fa-plus"></i> Rendimiento
                <b id="studio-week-hour-billing3" class="text-bold"
                  >$
                  {{ worst_selling.nick + "($" + worst_selling.value + ")" }}</b
                >
              </div>
              <div id="container-week-reached3">
                <i class="fas fa-minus"></i> Rendimiento:
                <b id="studio-week-hour-billing4" class="text-bold"
                  >$ {{ (most.goal * most.unjustified_days).toFixed(2) }}</b
                >
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /.col-->
      <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-gradient-danger">
          <div
            class="card-body card-body pb-0 d-flex justify-content-between align-items-start"
          >
            <div>
              <div class="text-value-lg">9.823</div>
              <div>Members online</div>
            </div>
            <div class="btn-group">
              <button
                class="btn btn-transparent dropdown-toggle p-0"
                type="button"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <svg class="c-icon">
                  <use
                    xlink:href="vendors/@coreui/icons/svg/free.svg#cil-settings"
                  ></use>
                </svg>
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#">Action</a
                ><a class="dropdown-item" href="#">Another action</a
                ><a class="dropdown-item" href="#">Something else here</a>
              </div>
            </div>
          </div>
          <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px">
            <canvas class="chart" id="card-chart4" height="70"></canvas>
          </div>
        </div>
      </div>

      <!-- /.col-->
    </div>

    <!-- <b-modal ref="my-modal" header-close-content="" header-bg-variant="dark" centered size="xl" hide-footer title="Resumen Inasistencias">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <b-table striped hover :items="items" :fields="fields" :tbody-tr-class="rowClass"></b-table>
          </div>
        </div>
      </div>
    </b-modal> -->
  </div>
</template>

<script>
import moment from "moment";

export default {
  name: "Resume2",
  props: ["user"],
  data() {
    return {
      overallData: {},
      most: {},
      lost: {},
      best_selling: {},
      worst_selling: {},
      fields: [
        {
          key: "model_nick",
          label: "Modelo",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "model_worked",
          label: "Trabajados",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "model_justified",
          label: "Justificadas",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "model_unjustified",
          label: "Injustificadas",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "model_period",
          label: "Periodo",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "model_goal",
          label: "Meta1",
          sortable: true,
          sortDirection: "desc",
        },
        {
          key: "lost_money",
          label: "Dinero Perdido",
          sortable: true,
          sortDirection: "desc",
        },
      ],
      items: [],
      options: [],
      locations: [],
      selected: 1,
      search: {
        selectedLocation: 1,
        start: moment().isoWeekday(0).format("YYYY-MM-DD"),
        end: moment().isoWeekday(6).format("YYYY-MM-DD"),
      },
    };
  },

  watch: {
    search: function () {
      const startOfWeek = moment(this.search.start)
        .startOf("week")
        .isoWeekday(0)
        .format("YYYY-MM-DD");
      const endOfWeek = moment(this.search.start)
        .startOf("isoweek")
        .isoWeekday(6)
        .format("YYYY-MM-DD");
      this.search.start = startOfWeek;
      this.search.end = endOfWeek;
      this.search.selectedLocation = this.user.setting_location_id;
      console.log("start of week: " + startOfWeek);
      console.log("end of week: " + endOfWeek);
      this.GetAttendanceStats();
    },
  },

  methods: {
    GetAttendanceStats() {
      const url = route("home.getAttendanceStats");
      axios.post(url, this.search).then((response) => {
        this.overallData = response.data.overall;
        this.most = response.data.most;
        this.best_selling = response.data.best_selling;
        this.worst_selling = response.data.worst_selling;
        this.lost = response.data.lost_money;
        this.items = response.data.summaries;
      });
    },

    hoverHandler(isHovered) {
      if (isHovered) {
        this.$refs["my-modal"].show();
      } else {
        // Do something else
      }
    },

    rowClass(item, type) {
      if (!item || type !== "row") return;
      if (item.model_unjustified >= 1) return "table-danger";
    },

    Locations() {
      const url = route("home.getLocations");
      axios.get(url).then((response) => {
        this.locations = response.data;
      });
    },
  },

  created() {
    this.GetAttendanceStats();
    this.Locations();
  },
};
</script>

<style scoped>
.container-resume-1 {
  min-height: 160px;
}

#container-week-reached:hover {
  cursor: pointer !important;
}
</style>

