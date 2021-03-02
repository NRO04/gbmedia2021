<template>
<span class="timetable">
  <b-button variant="dark" size="sm" class="float-right" @click="showModal">
    <i class="fas fa-calendar"></i> <span class="mx-1">Cronograma</span>
  </b-button>

    <b-modal id="modal-3" ref="modal-3" centered size="xl" header-bg-variant="dark" hide-footer header-close-content="" :title="'Cronograma ' + booking_type" @hidden="resetModal">
      <b-overlay :show="show" no-wrap></b-overlay>

      <div class="container">
        <div class="row">
           <div class="col-md-4">
            <b-form>
                 <b-form-select class="mb-3 border-0 float-right" v-model="date_range" @change="getRangeBookings()">
                    <template #first>
                      <b-form-select-option :value="null" disabled>-- Seleccione el rango que desea ver --</b-form-select-option>
                    </template>
                    <b-form-select-option v-for="(range, i) in ranges" :key="i" :value="range.date_range">{{ range.date_range }}</b-form-select-option>
                 </b-form-select>
            </b-form>
          </div>
        </div>
      </div>
      
       <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <b-card no-body border-0 shadow>
              <b-tabs fill pills card>
                <b-tab v-for="location in data.locations" :title="location.location_name" :key="location.id" :active="location.id == 2">
                  <div class="container">
                    <div class="row">
                      <div class="col-md-12">
                          <table class="table table-striped">
                            <thead class="thead-default">
                              <tr>
                                <th scope="col"></th>
                                <th scope="col">Modelo</th>
                                <th scope="col">Total(<span class="text-info">{{ location.date_range }}</span>)</th>
                                <th scope="col">Pendientes(<span class="text-info">{{ location.date_range }}</span>)</th>
                                <th scope="col">Realizadas(<span class="text-info">{{ location.date_range }}</span>)</th>
                                <th scope="col">No realizadas(<span class="text-info">{{ location.date_range }}</span>)</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="user in location.users" :key="user.id">
                                <td>
                                  <div class="c-avatar">
                                    <img class="c-avatar-img" :src="user.avatar" :alt="user.nick">
                                  </div>
                                </td>
                                <td v-text="user.nick"></td>
                                <td v-html="user.total_bookings"></td>
                                <td v-html="user.pending_bookings"></td>
                                <td v-html="user.attended_bookings"></td>
                                <td v-html="user.nonattended_bookings"></td>
                              </tr>
                            </tbody>
                          </table>
                      </div>
                    </div>
                  </div>
                </b-tab>
              </b-tabs>
            </b-card>
          </div>
        </div>
      </div>
    </b-modal>
</span>
</template>

<script>
export default {
  name: "timetable",
  props: ['bookingid'],

  data() {
    return {
      data: [],
      ranges: [],
      show: false,
      booking_type: null,
      date_range: null
    }
  },

  methods: {
    resetModal() {
      this.date_range = null
    },
    showModal() {
      this.$refs['modal-3'].show()
      this.getBookings()
    },

    getRangeBookings(){
      this.show = true
      let date_range = this.date_range

      let url = "../../bookings/getAllBookings/" + this.bookingid
      axios.post(url, {date_range}).then((response) => {
        console.log(response)
        this.data = response.data.data
        this.show = false
      }).catch((error) => {
        console.log(error)
        this.show = false
      })
    },

    getBookings() {
      this.show = true
      let url = "../../bookings/getAllBookings/" + this.bookingid
      axios.post(url).then((response) => {
        console.log(response)
        this.data = response.data.data
        this.booking_type = response.data.data[0].booking
        this.ranges = response.data.ranges
        this.show = false
      }).catch((error) => {
        console.log(error)
        this.show = false
      })
    }
  },

  mounted() {

  }
}
</script>

<style scoped>
.c-avatar-img {
  width: 70%;
  height: auto;
  border-radius: 50em;
}
</style>