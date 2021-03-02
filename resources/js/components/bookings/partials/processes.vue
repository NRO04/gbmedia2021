<template>
      <span class="processes">
        <b-button variant="dark" size="sm" class="float-right mx-1" @click="showModal">
          <i class="fas fa-cogs"></i> <span class="mx-1">Procesos</span>
        </b-button>

          <b-modal id="modal-2" class="border-0" centered ref="modal-2" size="xl" header-bg-variant="dark" hide-footer header-close-content="" title="Estado de procesos" @show="resetModal" @hidden="resetModal">
            <b-overlay :show="show" no-wrap></b-overlay>

            <div v-if="!show">
              <div class="accordion" role="tablist">
                <b-card no-body class="mb-1 shadow border-0" v-for="(item, i) in items" :key="item.id" v-if="item.has_process">
                  <b-card-header header-tag="header" class="p-1" role="tab">
                    <b-button block v-b-toggle="'accordion-'+i" variant="dark">{{ item.date_range }}</b-button>
                  </b-card-header>
                  <b-collapse :id="'accordion-'+i" visible accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                      <table class="table table-striped">
                        <thead class="thead-default">
                          <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Sesión</th>
                            <th scope="col">Modelo</th>
                            <th scope="col">Editado</th>
                            <th scope="col">Revisado</th>
                            <th v-if="can('process-delete')" scope="col">Eliminar</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="booking in item.bookings" :key="booking.process_id" v-if="booking.cansee">
                            <td>{{ booking.session_date }}</td>
                            <td>{{ booking.name }}</td>
                            <td class="text-capitalize">{{ booking.booking_type }}</td>
                            <td>{{ booking.model }}</td>
                            <td v-if="booking.booking_type == 'video'">
                               <b-button v-if="booking.process_status == 0" variant="primary" size="sm" @click.prevent="()=>{ process.videoLink = true; process.process_status = 1, process.process_id = booking.process_id}">
                                  <i class="fas fa-link"></i>
                               </b-button>
                               <a v-else-if="booking.process_status == 1" :href="booking.attachment" class="btn btn-sm btn-success" target="_blank">
                                 <i class="fas fa-external-link-alt"></i> (En revision)
                               </a>
                            </td>
                            <td v-else>
                               <b-button v-if="booking.process_status == 0" variant="primary" size="sm" @click.prevent="updatePhotoProcess(booking.process_id)">
                                  <i class="fas fa-check"></i>
                              </b-button>
                             <span v-else-if="booking.process_status == 1" v-html="booking.process_status_msg"></span>
                            </td>
                            <td>
                              <b-button v-if="booking.process_status == 1 && can('process-edit')" variant="success" size="sm" @click.prevent="approveProcess(booking.process_id)">
                                <i class="fas fa-check"></i>
                              </b-button>
                              <span v-else-if="booking.process_status == 0" v-html="booking.process_status_msg"></span>
                              <span v-else-if="booking.process_status == 2" v-html="booking.process_status_msg"></span>
                              <span v-else-if="booking.process_status == 1" v-html="booking.process_status_msg"></span>
                            </td>
                            <td v-if="can('process-delete')">
                              <b-button variant="danger" size="sm" @click.prevent="deleteProcess(booking.process_id)">
                                <i class="fas fa-trash"></i>
                              </b-button>
                            </td>
                          </tr>
                        </tbody>
                      </table>

                      <div class="col-md-12" v-if="process.videoLink">
                        <div class="form-row">
                         <div class="col-md-10">
                           <div class="form-group">
                            <input type="text" v-model="process.attachment" class="form-control" placeholder="Ingrese el link para el video...">
                          </div>
                         </div>
                          <div class="col-md-2">
                              <div class="form-group">
                                <b-button variant="success" @click.prevent="updateVideoProcess()">
                                  <i class="fas fa-check"></i>
                                </b-button>
                              </div>
                          </div>
                        </div>
                      </div>
                    </b-card-body>
                  </b-collapse>
                </b-card>
            </div>
            </div>
            <div v-else class="text-center p-3">
              <h3>
                <i class="fas fa-folder-open fa-3x"></i> <br>
                No hay procesos de reservas aun
              </h3>
            </div>
          </b-modal>
      </span>
</template>

<script>
export default {
  name: "processes",
  props: ['user', 'bookingid', 'permissions'],

  data() {
    return {
      items: [],
      show: false,

      process: {
        process_status: 0,
        videoLink: false,
        attachment: null,
        process_id: null
      }
    }
  },

  methods: {
    can(permission_name) {
      return this.permissions.indexOf(permission_name) !== -1;
    },
    
    resetModal() {
      this.process.videoLink = false
      this.process.process_status = null
      this.process.attachment = null
      this.process.process_id = null
    },

    showModal() {
      this.$refs['modal-2'].show()
      this.getProcesses()
    },

    getProcesses() {
      // let url = "../../bookings/getProcesses"
      const url = route("bookings.getProcessesByDate")
      this.show = true
      axios.get(url).then((response) => {
        console.log(response)
        this.show = false
        this.items = response.data.data
      }).catch((error) => {
        console.log(error)
        this.show = false
      })
    },

    updateVideoProcess() {
      let id = this.process.process_id

      // let url = "../../bookings/updateProcess/" + id
      const url = route("bookings.updateProcess", {id})
      axios.put(url, this.process).then((response) => {
        Fire.$emit("updatedProcess")
        Toast.fire({
          icon: "success",
          title: "Reserva agendada correctamente!",
        })
      }).catch((error) => {
        console.log(error)
      })
    },

    updatePhotoProcess(id) {
      this.process.process_status = 1
      alert(id)

      // let url = "../../bookings/updateProcess/" + id
      const url = route("bookings.updateProcess", {id})
      axios.put(url, this.process).then((response) => {
        Fire.$emit("updatedProcess")
        Toast.fire({
          icon: "success",
          title: "Reserva agendada correctamente!",
        })
      }).catch((error) => {
        console.log(error)
      })
    },

    approveProcess(id) {
      this.process.process_status = 2

      // let url = "../../bookings/updateProcess/" + id
      const url = route("bookings.updateProcess", {id})
      axios.put(url, this.process).then((response) => {
        Fire.$emit("updatedProcess")
        Toast.fire({
          icon: "success",
          title: "Reserva agendada correctamente!",
        })
      }).catch((error) => {
        console.log(error)
      })
    },

    deleteProcess(id) {
      // let url = "../../bookings/deleteProcess/" + id
      const url = route("bookings.deleteProcess", {id})
      Swal.fire({
        title: "¿Está seguro?",
        text: "Eliminará esta noticia. ¡Esta acción no podrá ser revertida!",
        icon: "question",
        allowOutsideClick: false,
      }).then(result => {
        if (result.value) {
          axios.put(url, this.process).then((response) => {
            Fire.$emit("updatedProcess")
            Toast.fire({
              icon: "success",
              title: "Reserva agendada correctamente!",
            })
          }).catch((error) => {
            console.log(error)
          })
        }
      });
    }

  },

  mounted() {
    this.getProcesses()
    Fire.$on("updatedProcess", () => {
      this.process.videoLink = false
      this.getProcesses()
    });
  }
}
</script>

<style scoped>

</style>