<template>
  <div id="statisticstable">
    <b-container fluid>
      <!-- User Interface controls -->
      <b-row class="justify-content-between">
        <b-col md="10" lg="10" xl="10" sm="12">
          <b-row>
            <b-col lg="4" md="4" sm="12" class="my-1">
              <b-form-group class="mb-0">
                <b-input-group size="sm">
                  <b-form-input v-model="filter" type="search" id="filterInput" placeholder="Type to Search"></b-form-input>
                </b-input-group>
              </b-form-group>
            </b-col>
            <b-col lg="2" md="2" sm="12" class="my-1">
              <b-form-select v-model="search.selectedLocation" @change="GetStatistics" size="sm">
                <b-form-select-option v-for="location in getAllLocations" :key="location.id" :value="location.id">
                  {{ location.name }}
                </b-form-select-option>
              </b-form-select>
            </b-col>
            <b-col lg="3" class="my-1">
              <b-row>
                <b-col sm="8" class="px-0">
                  <VueCtkDateTimePicker
                      :no-value-to-custom-elem="(true)"
                      v-model="search"
                      dark range
                      noShortcuts
                      noLabel
                      noButton
                      color="purple"
                      inputSize="sm"
                      format="YYYY-MM-DD"
                      formatted="ll"
                      autoClose
                      id="RangeDatePicker">
                    <b-form-input size="sm" v-model="search.start +' / '+ search.end"></b-form-input>
                  </VueCtkDateTimePicker>
                </b-col>
              </b-row>
            </b-col>
            <b-col lg="2" md="2" sm="12" class="my-1">
              <b-form-select v-model="stats.model_id" @change="GetPages()" size="sm">
                <template #first>
                  <b-form-select-option :value="null" disabled>-- Please select a model --</b-form-select-option>
                </template>
                <b-form-select-option v-for="model in modelPages" :key="model.id" :value="model.id">
                  {{ model.nick }}
                </b-form-select-option>
              </b-form-select>
              <!--  <b-button variant="success" type="button" size="sm" @click="GetPages(stats.model_id, stats.page_id)">Crawl</b-button>-->
            </b-col>
          </b-row>
        </b-col>

        <b-col md="1" lg="1" xl="1" sm="12">
          <b-row>
            <b-col sm="12" md="12" class="my-1">
              <b-form-group class="mb-0">
                <b-form-select v-model="perPage" id="perPageSelect" size="sm" :options="pageOptions"></b-form-select>
              </b-form-group>
            </b-col>
          </b-row>
        </b-col>
      </b-row>

      <hr>
      <!-- Main table element -->
      <b-table class="table-striped" show-empty small stacked="md" :items="items" :fields="fields"
               :current-page="currentPage" :per-page="perPage" :filter="filter"
               :filter-included-fields="filterOn" :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" :sort-direction="sortDirection" @filtered="onFiltered">
        <template #thead-top="data">
          <b-tr class="text-center">
            <b-th colspan="1"><span class="sr-only">Nick</span></b-th>
            <b-th colspan="7" variant="secondary">Dias semana</b-th>
            <b-th colspan="3" variant="primary">Meta / Lograda</b-th>
            <b-th colspan="1" variant="info">Total / Efectividad</b-th>
          </b-tr>
        </template>
        
        <template #cell(nick)="row">
          {{ row.item.nick }}
        </template>

        <template #cell(monday)="row">
          <b-button :variant="row.item.monday.variant" size="sm" @click="info(row.item, row.item.monday.date)" class="mr-1">
            {{ row.item.monday.value }}
          </b-button>
        </template>
        <template #cell(tuesday)="row">
          <b-button :variant="row.item.tuesday.variant" size="sm" @click="info(row.item, row.item.tuesday.date)" class="mr-1">
            {{ row.item.tuesday.value }}
          </b-button>
        </template>
        <template #cell(wednesday)="row">
          <b-button :variant="row.item.wednesday.variant" size="sm" @click="info(row.item, row.item.wednesday.date)" class="mr-1">
            {{ row.item.wednesday.value }}
          </b-button>
        </template>
        <template #cell(thursday)="row">
          <b-button :variant="row.item.thursday.variant" size="sm" @click="info(row.item, row.item.thursday.date)" class="mr-1">
            {{ row.item.thursday.value }}
          </b-button>
        </template>
        <template #cell(friday)="row">
          <b-button :variant="row.item.friday.variant" size="sm" @click="info(row.item, row.item.friday.date)" class="mr-1">
            {{ row.item.friday.value }}
          </b-button>
        </template>
        <template #cell(saturday)="row">
          <b-button :variant="row.item.saturday.variant" size="sm" @click="info(row.item, row.item.saturday.date)" class="mr-1">
            {{ row.item.saturday.value }}
          </b-button>
        </template>
        <template #cell(sunday)="row">
          <b-button :variant="row.item.sunday.variant" size="sm" @click="info(row.item, row.item.sunday.date)" class="mr-1">
            {{ row.item.sunday.value }}
          </b-button>
        </template>
        <template #cell(weekly_performance)="row">
          <span class="text-info font-weight-bold">
            {{ row.value }}
          </span>
        </template>
      </b-table>

      <b-row class="pt-5">
        <b-col sm="12" md="12" class="my-1">
          <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" size="sm" class="my-0 float-right"></b-pagination>
        </b-col>
      </b-row>

      <!-- Info modal -->
      <b-modal ref="stats-modal" centered hide-footer hide-header-close size="lg" :id="infoModal.id" :title-html="'Estadisticas ' + infoModal.title"  @hide="resetInfoModal">
        <div class="container">
          <div class="row">
            <div class="col-md-4" v-for="page in pages">
                <b-form-group :id="page.name" :label="page.name" :label-for="page.name">
                  <b-form-input :disabled="!can('statistics-edit')" v-model.number="page.value" :id="page.name" size="sm" :value="page.value" placeholder="ex: 10.50" trim></b-form-input>
                </b-form-group>
            </div>

            <div class="col-md-12">
              <b-button size="sm" class="float-right" variant="success" @click="SaveStatistics">
                <i class="fas fa-check"></i> Actualizar
              </b-button>
            </div>
          </div>
        </div>
      </b-modal>

      <b-modal ref="date-stats-modal" centered hide-footer hide-header-close size="lg" :title-html="'Estadisticas ' + infoModal.title" @hide="resetInfoModal">
        <div class="container">
          <div class="row">
            <div class="col-md-4" v-for="page in pages">
              <b-form-group :id="page.name" :label="page.name" :label-for="page.name">
                <b-form-input :disabled="!can('statistics-edit')" v-model.number="page.value" :id="page.name" size="sm" :value="page.value" placeholder="ex: 10.50" trim></b-form-input>
              </b-form-group>
            </div>

            <div class="col-md-12 pt-3">
              <b-form-group>
                <b-form-select v-model="stats.date" class="mb-3">
                  <b-form-select-option :value="null">Seleccione una fecha</b-form-select-option>
                  <b-form-select-option  v-for="(date, i) in dates" :value="date" :key="i">{{ date }}</b-form-select-option>
                </b-form-select>
              </b-form-group>
            </div>

            <div class="col-md-12">
              <b-button size="sm" class="float-right" variant="success" @click="SaveDateStatistics">
                <i class="fas fa-check"></i> Actualizar
              </b-button>
            </div>
          </div>
        </div>
      </b-modal>
    </b-container>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import moment from "moment";

export default {
  props: ['user', 'permissions'],
  name: "statisticstable",
  data: () => {
    return {
      items: [],
      fields: [
        { key: 'nick', label: 'Modelo', sortable: true, sortDirection: 'desc' },
        { key: 'sunday', label: 'Domingo', class: 'text-center' },
        { key: 'monday', label: 'Lunes', class: 'text-center' },
        { key: 'tuesday', label: 'Martes', class: 'text-center' },
        { key: 'wednesday', label: 'Miercoles', class: 'text-center' },
        { key: 'thursday', label: 'Jueves', class: 'text-center' },
        { key: 'friday', label: 'Viernes', class: 'text-center' },
        { key: 'saturday', label: 'Sabado', class: 'text-center' },
        { key: 'daily_goal_one', label: '1ra', class: 'text-center' },
        { key: 'daily_goal_two', label: '2da', class: 'text-center' },
        { key: 'daily_goal_three', label: '3ra', class: 'text-center' },
        { key: 'weekly_performance', label: '% semana', class: 'text-center' }
      ],
      totalRows: 1,
      currentPage: 1,
      perPage: 25,
      pageOptions: [25, 50, 100],
      sortBy: '',
      sortDesc: false,
      sortDirection: 'asc',
      filter: null,
      filterOn: [],
      pages: [],
      infoModal: {
        id: 'info-modal',
        title: ''
      },
      search: {
        selectedLocation: 0,
        start: moment().isoWeekday(0).format("YYYY-MM-DD"),
        end: moment().isoWeekday(6).format("YYYY-MM-DD"),
      },
      monitor: null,
      show: false,
      date: null,
      model_id: null,
      stats:{
        model_id : null,
        date : moment().format('YYYY-MM-DD'),
      },
      modelPages : [],
      currentPages : [],
      dates : [],
      canEditPages: false
    }
  },

  methods: {
    ...mapActions(["GET_ALL_LOCATIONS"]),

    can(permission_name) {
      return this.permissions.indexOf(permission_name) !== -1;
    },
    
    info(item, date) {
      this.infoModal.title = "<span class='text-danger'>" + item.nick + "("+date +")</span>"
      this.$root.$emit('bv::show::modal', this.infoModal.id)
      console.log(item)
      this.date = date
      this.model_id = item.model_id
      this.GetModelPages(item.model_id, date)
    },

    resetInfoModal() {
      this.infoModal.title = ''
      this.infoModal.content = ''
      this.pages = []
      this.dates = []
      this.model_nick = null
      this.model_id = null
    },

    onFiltered(filteredItems) {
      this.totalRows = filteredItems.length
      this.currentPage = 1
    },

    GetStatistics(){
      const url = route('monitoring.getStatistics');
      axios.post(url, this.search).then((response) => {
        console.log(response)
        this.items = response.data.statistics
        this.fields = response.data.columns
        this.totalRows = this.items.length
      })
      this.GetModels()
    },

    GetModelPages(id, date){
      const url = route('monitoring.getPages')
      axios.post(url, {
        model_id : id,
        date: date
      }).then((response) => {
        this.pages = response.data
      })
    },

    SaveStatistics(){
      const startOfWeek = moment(this.date).startOf('week').isoWeekday(0).format("YYYY-MM-DD")
      const endOfWeek = moment(this.date).startOf('isoweek').isoWeekday(6).format("YYYY-MM-DD")

      const url = route('monitoring.saveStatistics')

      axios.post(url, {
        pages :  this.pages,
        model_id : this.model_id,
        date : this.date,
        start : startOfWeek,
        end : endOfWeek,
      }).then((response) => {
        if (response.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });

          this.GetStatistics()

        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
        }
      })
    },

    GetStreamate(){
      const url = route("monitoring.executeCrawler")
      axios.get(url).then((response) => {
        console.log(response)
      })
    },

    GetModels(){
      const id = this.search.selectedLocation
      const url = route("monitoring.getModels", {id})
      axios.get(url).then((response) => {
        console.log(response)
        this.modelPages = response.data
      })
    },

    GetPages(){
      this.$refs['date-stats-modal'].show()
      this.pages = []

      const id = this.stats.model_id
      const url = route('monitoring.AllPages', {id})
      axios.get(url).then((response) => {
        this.pages = response.data.pages,
        this.dates = response.data.dates,
        this.canEditPages = response.data.enabled,
        this.infoModal.title = "<span class='text-danger'>" + response.data.nick + "</span>"
      })
    },

    SaveDateStatistics(){
      const startOfWeek = moment(this.stats.date).startOf('week').isoWeekday(0).format("YYYY-MM-DD")
      const endOfWeek = moment(this.stats.date).startOf('isoweek').isoWeekday(6).format("YYYY-MM-DD")

      const url = route('monitoring.saveStatistics')

      axios.post(url, {
        pages :  this.pages,
        model_id : this.stats.model_id,
        date : this.stats.date,
        start : startOfWeek,
        end : endOfWeek,
      }).then((response) => {
        if (response.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });

          this.GetStatistics()

        } else if (response.data.code === 500) {
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
        }
      })
    },
  },

  computed: {
    ...mapGetters(["getAllLocations"]),

    sortOptions() {
      // Create an options list from our fields
      return this.fields
          .filter(f => f.sortable)
          .map(f => {
            return { text: f.label, value: f.key }
          })
    }
  },

  watch: {
    search: function () {
      const startOfWeek = moment(this.search.start).startOf('week').isoWeekday(0).format("YYYY-MM-DD")
      const endOfWeek = moment(this.search.start).startOf('isoweek').isoWeekday(6).format("YYYY-MM-DD")
      this.search.start = startOfWeek
      this.search.end = endOfWeek

      console.log("start of week: " + startOfWeek)
      console.log("end of week: " + endOfWeek)

      if (this.user.setting_location_id == 1) {
        this.search.selectedLocation = 2
      } else {
        this.search.selectedLocation = this.user.setting_location_id
      }
      this.GetStatistics()
    }
  },

  mounted() {
    this.GET_ALL_LOCATIONS()
    if (this.user.setting_location_id == 1) {
      this.search.selectedLocation = 2
    }
    else {
      this.search.selectedLocation = this.user.setting_location_id
    }
    
    this.GetStatistics()
    this.GetModels()
  }
}
</script>

<style scoped>

</style>