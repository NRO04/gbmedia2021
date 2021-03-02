<template>
<span class="finalized">
  <b-button variant="dark" size="sm" class="float-right" @click="showModal">
    <i class="fas fa-check"></i> <span class="mx-1">Finalizados</span>
  </b-button>

  <b-modal centered id="modal-1" ref="modal-1" size="xl" header-bg-variant="dark" hide-footer header-close-content="" title="Procesos finalizados">
    <b-overlay :show="show" no-wrap></b-overlay>

    <div class="container" v-if="!show">
      <div class="row">
        <div class="col-md-12">
        <div class="accordion" role="tablist">
          <b-card no-body class="shadow border-0 mb-1" v-for="(item, i) in items" :key="item.id" v-if="item.has_process">
            <b-card-header header-tag="header" class="p-1" role="tab">
              <b-button block v-b-toggle="'accordion-'+i" variant="dark">
                {{ item.date_range}}
              </b-button>
            </b-card-header>
            <b-collapse :id="'accordion-' + i" visible accordion="my-accordion" role="tabpanel">
              <b-card-body>
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th scope="col">Fecha reserva</th>
                      <th scope="col">Realizado por</th>
                      <th scope="col">Modelo</th>
                      <th scope="col">Tipo de sesion</th>
                      <th scope="col">Fecha de edicion</th>
                      <th scope="col">Fecha de revision</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="booking in item.bookings" :key="booking.id" v-if="booking.cansee">
                      <td v-text="booking.session_date"></td>
                      <td v-text="booking.name"></td>
                      <td v-text="booking.model"></td>
                      <td class="text-capitalize" v-text="booking.booking_type"></td>
                      <td v-text="booking.submitted_date"></td>
                      <td v-text="booking.review_date"></td>
                    </tr>
                  </tbody>
                </table>
              </b-card-body>
            </b-collapse>
          </b-card>
        </div>
      </div>
      </div>
    </div>

    <div class="container" v-else>
      <div class="row">
        <div class="col-md-12 text-center p-3">
          <h3>
              <i class="fas fa-folder-open fa-3x"></i> <br>
              No hay procesos de reservas aun
          </h3>
        </div>
      </div>
    </div>
  </b-modal>
</span>
</template>

<script>
export default {
  name: "finalized",

  data() {
    return {
      items: [],
      show: false
    }
  },

  methods: {
    resetModal() {

    },
    showModal() {
      this.$refs['modal-1'].show()
      this.getFinishedBookings()
    },
    getFinishedBookings(){
      // let url = "../../bookings/getAllFinishedBookings"
      const url = route("bookings.getBookingFinished")
      this.show = true
      axios.get(url).then((response) => {
        console.log(response)
        this.items = response.data.data
        this.show = false
      }).catch((error) => {
        console.log(error)
        this.show = false
      })
    }
  }
}
</script>

<style scoped>

</style>