<template>
  <div>
    <b-overlay :show="show" no-wrap></b-overlay>

    <div>
      <b-row v-if="trainings.length">
        <b-col md="6" lg="6" sm="6">
          <b-form-input v-model="search" @keyup="onGet(1, search, filter)" type="search" placeholder="Buscar capacitaciones..."></b-form-input>
        </b-col>
        <b-col md="6" lg="6" sm="6">
          <div class="float-md-right float-lg-right float-xl-right">
            <create></create>
          </div>
        </b-col>

        <b-col lg="4" md="4" sm="12" v-for="training in trainings" :key="training.id" class="pt-5 text-center my-auto">
          <div class="card shadow border-0">
            <img :src="training.image_url" :alt="training.title" class="card-img-top">
            <div class="card-header border-bottom-0">
              <h4>
                <text-highlight :queries="search">{{ training.title }}</text-highlight>
              </h4>
            </div>
            <div class="card-body">
              <p class="card-text">{{ truncateText(training.description) }}</p>
              <a :href="'/trainings/training/'+training.id" class="btn btn-sm btn-dark">
                <i class="fas fa-play"></i> Empezar capacitacion
              </a>
              <b-button size="sm" variant="warning" @click="EditTraining(training.id)" id="toggle-btn">
                <i class="fas fa-edit"></i>
              </b-button>
              <b-button size="sm" variant="danger" @click="DeleteTraining(training.id)">
                <i class="fas fa-trash"></i>
              </b-button>
              <b-button size="sm" variant="info" @click="UserCompleted(training.id)" id="complete-btn">
                <i class="fas fa-eye"></i>
              </b-button>
            </div>
            <div class="card-footer border-top-0 text-muted">
              Publicado en {{ moment(training.created_at).format("DD-MMM-YYYY") }} a las {{ moment(training.created_at).format("hh:mm") }}
            </div>
          </div>
        </b-col>

        <b-col md="12" sm="12" class="py-3 text-center" v-show="pagination.total > 15">
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
              <li class="page-item" v-if="pagination.current_page > 1">
                <a class="page-link" href="#" tabindex="-1" @click.prevent="paginate(pagination.current_page - 1,search,filter)">
                  <i class="fas fa-angle-left"></i>
                </a>
              </li>
              <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page === isActive ? 'active' : '']">
                <a class="page-link" href="#" @click.prevent="paginate(page, search, filter)" v-text="page"></a>
              </li>
              <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                <a class="page-link" href="#" @click.prevent="paginate(pagination.current_page + 1,search,filter)">
                  <i class="fas fa-angle-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        </b-col>
      </b-row>

      <b-row class="mt-5 text-center" v-else v-show="!show">
        <b-col md="12" lg="12" sm="12">
          <h3>No hay capacitaciones por el momento...</h3>
          <p>¡Empieza a crear capacitaciones para tu equipo de trabajo!</p>
          <b-row>
            <b-col>
              <create v-can="'training-create'"></create>
            </b-col>
          </b-row>
          <img :src="url" alt="empty page" height="500" width="500"/>
        </b-col>
      </b-row>
    </div>

    <b-modal ref="my-modal" header-bg-variant="primary" :title="'Editando: ' + training.title" no-close-on-backdrop header-close-content="" size="xl" @show="resetModal" @hidden="resetModal">
      <div class="container px-4">
        <div class="row">
          <div class="col-md-12-col-lg-12 col-xl-12">
            <b-form>
              <b-row>
                <b-col>
                  <b-form-group>
                    <b-form-checkbox id="auto_share" v-model="training.is_shared" :value="training.is_shared">
                      Compartir automaticamente
                    </b-form-checkbox>
                  </b-form-group>
                </b-col>
                <b-col>

                </b-col>
              </b-row>

              <b-form-group label="Escoger roles" class="required">
                <b-form-select v-model="role_ids" multiple :select-size="8">
                  <b-form-select-option v-for="role in rolesArray" :key="role.id" :value="role.id">
                    {{ role.name }}
                  </b-form-select-option>
                </b-form-select>
              </b-form-group>

              <b-form-group label="Titulo" class="required">
                <b-form-input id="title" v-model="training.title" trim></b-form-input>
              </b-form-group>

              <b-form-group label="Contenido" class="required">
                <b-form-textarea id="textarea-auto-height" rows="5" max-rows="10" v-model="training.description"></b-form-textarea>
              </b-form-group>

              <b-row>
                <b-col>
                  <b-form-group label="Imagen de portada" class="required">
                    <b-form-file @change="" v-model="cover" placeholder="Choose a file or drop it here..." drop-placeholder="Drop file here..."></b-form-file>
                  </b-form-group>
                </b-col>
                <b-col>
                  <div v-if="training.cover" class="float-right">
                    <b-img :src="training.image_url" width="100" height="100" class="rounded"></b-img>
                  </div>
                  <div v-else class="float-right">
                    <b-img :src="imagePreview" width="100" height="100" class="rounded"></b-img>
                  </div>
                </b-col>
              </b-row>

              <b-row>
                <b-col>
                  <b-form-group label="Video" class="required">
                    <b-form-file v-model="video" placeholder="Choose a file or drop it here..." drop-placeholder="Drop file here..."></b-form-file>
                  </b-form-group>
                </b-col>
              </b-row>

              <hr>

              <b-form-group v-if="questions.length > 0">
                <b-form-checkbox v-model="want_questions">
                  Editar preguntas?
                </b-form-checkbox>
              </b-form-group>

              <div v-else>
                <b-row class="text-center">
                  <b-col>
                    <h3>Esta capacitación no tiene test</h3>
                  </b-col>
                </b-row>
              </div>

              <b-card class="mb-1" v-if="want_questions">
                <b-card-body>
                  <b-row>
                    <b-col md="6" lg="6" xl="6" sm="12" v-for="(question, i) in questions" :key="question.id">
                      <b-card>
                        <b-card-text>
                          <b-form-group>
                            <b-input-group size="sm">
                              <b-input-group-prepend is-text>
                                <strong>Q. {{ i + 1 }}</strong>
                              </b-input-group-prepend>
                              <b-form-input v-model="question.question_title" class="form-control" :placeholder="'Escriba la pregunta ' + (i + 1)"></b-form-input>
                            </b-input-group>
                          </b-form-group>
                          <b-input-group size="sm" class="mb-2" v-for="(answer, j) in question.answers" :key="answer.id">
                            <b-input-group-prepend is-text>
                              <b-form-radio class="mr-n2" v-model="answer.correct_answer" :value="correctAnswer"></b-form-radio>
                            </b-input-group-prepend>
                            <b-form-input v-model="answer.option_title" :placeholder="'Escriba opcion ' + (j + 1)"></b-form-input>
                          </b-input-group>
                        </b-card-text>
                      </b-card>
                    </b-col>
                  </b-row>
                </b-card-body>
              </b-card>
            </b-form>
          </div>
        </div>
      </div>

      <template v-slot:modal-footer>
        <div class="w-100">
          <template>
            <b-button type="button" class="float-right" size="sm" variant="success" @click.prevent="UpdateTraining(training.id)">
              <i class="fas fa-save"></i> Actualizar
            </b-button>
          </template>
        </div>
      </template>
    </b-modal>

    <b-modal ref="users_completed" header-bg-variant="primary" hide-footer header-close-content="" title="Usuarios que vieron capacitación" size="lg">
      <b-table show-empty small stacked="md" :items="users" :fields="fields" :current-page="currentPage" :per-page="perPage" :busy="isBusy">
        <!-- A virtual column -->
        <template v-slot:cell(index)="data">
          {{ data.index + 1 }}
        </template>
        <template v-slot:table-busy>
          <div class="text-center text-danger my-2">
            <b-spinner class="align-middle"></b-spinner>
            <strong>Loading...</strong>
          </div>
        </template>
      </b-table>
    </b-modal>
  </div>
</template>

<script>
import moment from "moment"
import "moment/locale/es"
import create from "./partials/create"

export default {
  name: "training",
  props: ['user'],

  data() {
    return {
      url: "images/svg/undraw_no_data_qbuo.svg",
      search: "",
      filter: "title",
      trainings: [],
      rolesArray: [],
      show: false,
      want_questions: false,
      questions: [],
      users: [],
      questionOptions: 4,
      correctAnswer: 1,
      moment: moment,
      pagination: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        from: 0,
        to: 0,
      },
      role_ids: [],
      offset: 3,
      editMode: false,
      imagePreview: null,
      cover: null,
      video: null,
      fields: [
        {key: 'index', label: '#', sortable: true},
        {key: 'first_name', label: 'Nombre', sortable: true},
        {key: 'last_name', label: 'Apellido', sortable: true},
        {key: 'date_completed', label: 'Capacitacion vista en...', sortable: true},
        {key: 'test_completed', label: 'Test Completado en...', sortable: true}
      ],
      currentPage: 1,
      perPage: 1,
      isBusy: false
    }
  },

  components: {
    create: create,
  },

  methods: {
    truncateText(text) {
      if (text.length > 100) {
        return `${text.substr(0, 200)}...`;
      }
    },

    paginate(page, search, filter) {
      this.pagination.current_page = page;
      this.onGet(page, search, filter);
    },

    onGet(page, search, filter) {
      // let url = "/trainings/getTrainings?page=" + page + "&search=" + search + "&filter=" + filter;
      let vm = this;
      this.show = true;
      const url = route('training.get_trainings', {
        page, search,  filter,
      })
      axios.get(url).then((response) => {
        vm.trainings = response.data.trainings;
        vm.pagination = response.data.pagination;

        console.log(response)

        this.show = false;
      }).catch((error) => {
        console.log(error);
        this.show = false;
      });
    },

    DeleteTraining(id) {
      SwalGB.fire({
        text: "Eliminará esta capacitación, esta acción no podrá ser revertida",
        icon: 'warning',
      }).then((result) => {
        if (result.isConfirmed) {
          this.$store.dispatch("DELETE_TRAINING", id);
        }
      })
    },

    EditTraining(id) {
      this.$refs['my-modal'].toggle('#toggle-btn')
      this.$store.dispatch("EDIT_TRAINING", id)
      this.getRoles()
      this.GetQuestions(id)
    },

    getRoles() {
      let vm = this;
      // let url = "/role/AllRoles";
      const url = route("role.all_roles")
      axios.get(url).then((response) => {
        vm.rolesArray = response.data.roles;
      }).catch((error) => {
        console.log(error);
      });
    },

    GetQuestions(id) {
      // let url = `/trainings/editQuestions/${id}`
      const url = route('training.editQuestions', {id : id})
      axios.get(url).then((response) => {
        if (response.data.questionnaire.length > 0) {
          this.questions = response.data.questionnaire
        } else {
          console.log("no questionnaire")
        }
      }).catch((errors) => {
        console.log(errors)
      }).finally(() => {
        console.log("Finally GET_TRAINING")
      })
    },

    resetModal() {
      this.training = null
      this.want_questions = false
    },

    onFileChange(event) {
      this.training.cover = event.target.files[0];
      let reader = new FileReader();

      reader.addEventListener("load", function () {
        this.imagePreview = reader.result;
      }.bind(this), false);
      if (this.training.cover) {
        if (/\.(jpe?g|png|gif)$/i.test(this.training.cover.name)) {
          reader.readAsDataURL(this.training.cover);
        }
      }
    },

    UpdateTraining(id){
      // let url = `/trainings/update/${id}`
      const url = route('training.update', {id : id})
      axios.post(url).then((response) => {
        if (response.status.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          Fire.$emit("AfterCreated")
          this.$Progress.finish()
        }
        else if (response.status.code === 500){
          SwalGB.fire({
            icon: response.data.icon,
            text: response.data.msg,
            title: "¡Error!",
            showCancelButton: false,
          })
        }
      }).catch((error) => {
        console.log(error)
      })
    },

    UserCompleted(id) {
      this.$refs['users_completed'].toggle('#complete-btn')
      this.isBusy = true
      // let url = "/trainings/completedTraining/" + id
      const url = route('training.completed_training', {id : id})

      axios.get(url).then((response) => {
        this.users = response.data.trainingComplete;
        this.isBusy = false
      }).catch((error) => {
        console.log(error);
        this.isBusy = false
      });
    },
  },

  computed: {
    isActive: function () {
      return this.pagination.current_page;
    },
    pagesNumber: function () {
      if (!this.pagination.to) {
        return [];
      }

      let from = this.pagination.current_page - this.offset;
      if (from < 1) {
        from = 1;
      }

      let to = from + this.offset * 2;
      if (to >= this.pagination.last_page) {
        to = this.pagination.last_page;
      }

      let pagesArray = [];
      while (from <= to) {
        pagesArray.push(from);
        from++;
      }
      return pagesArray;
    },

    training() {
        return this.$store.getters.training
    }
  },

  created() {
    this.onGet(1, this.search, this.filter);
    Fire.$on("AfterCreated", () => {
      this.onGet(1, this.search, this.filter);
    });
  },

  mounted() {
  },
};
</script>

<style scoped>
:focus {
  outline: none !important;
}

body.swal2-shown > [aria-hidden="true"] {
  filter: blur(10px);
}
</style>
