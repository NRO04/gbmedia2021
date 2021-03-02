<template>
  <div>
    <b-button variant="success" @click="show=true">
      <i class="fas fa-plus"></i> Crear capacitación
    </b-button>

    <b-modal header-bg-variant="primary" v-model="show" no-close-on-backdrop header-close-content="" hide-footer size="xl" ref="my-modal" title="Crear capacitación">
      <b-container>
        <b-row>
          <b-col xl="12" lg="12" md="12" sm="12">
            <validation-observer ref="formData">
              <b-form>
                <b-container>
                  <b-row>
                    <b-col xl="6" lg="6" md="6" sm="12">
                      <b-form-group>
                        <b-form-checkbox id="auto_share" v-model="formData.is_shared" name="auto_share">
                          Compartir automaticamente
                        </b-form-checkbox>
                      </b-form-group>
                    </b-col>

                    <b-col xl="6" lg="6" md="6" sm="12">
                      <b-form-group>
                        <b-form-radio-group id="radio-group-roles" v-model="selected">
                          <b-form-radio :value="1">Escojer roles individuales</b-form-radio>
                          <b-form-radio :value="2">Seleccionar todos los roles</b-form-radio>
                        </b-form-radio-group>
                      </b-form-group>
                    </b-col>

                    <b-col xl="12" lg="12" md="12" sm="12">
                      <validation-provider name="role_ids[]" :rules="{ required: true }" v-slot="validationContext">
                        <b-row class="py-3" v-if="selected === 1">
                          <b-col>
                            <b-form-select v-model="role_ids" multiple :select-size="8" name="role_ids[]">
                              <b-form-select-option v-for="role in rolesArray" :key="role.id" :value="role.id">
                                {{ role.name }}
                              </b-form-select-option>
                            </b-form-select>
                          </b-col>
                        </b-row>

                        <b-row v-if="selected === 2" class="py-3">
                          <b-col>
                            <b-form-checkbox id="checkAll" v-model="checkAll" name="checkAll" class="text-warning">
                              Seleccionar todos
                            </b-form-checkbox>
                          </b-col>
                          <b-col md="4" v-for="role in rolesArray" :key="role.id">
                            <b-form-checkbox :id="'checkbox'+role.id" v-model="role_ids" :value="role.id" name="role_ids[]">
                              {{ role.name }}
                            </b-form-checkbox>
                          </b-col>
                        </b-row>
                        <b-form-invalid-feedback id="input-roles-live-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </validation-provider>
                    </b-col>

                    <b-col xl="12" lg="12" md="12" sm="12">
                      <validation-provider name="title" :rules="{ required: true, min: 3, max: 75}" v-slot="validationContext">
                        <b-form-group id="title" label="Título de la capcitacion">
                          <b-form-input v-model="formData.title" placeholder="Titulo de la capacitacion: Convierte en experto..." :state="getValidationState(validationContext)"></b-form-input>
                          <b-form-invalid-feedback id="input-title-live-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>

                      <validation-provider name="description" :rules="{ required: true, min: 20 }" v-slot="validationContext">
                        <b-form-group id="description" label="Descripcion de la capcitacion">
                          <b-form-textarea id="textarea" v-model="formData.description" placeholder="Descripción de la capacitación..." rows="6" max-rows="20" :state="getValidationState(validationContext)"></b-form-textarea>
                          <b-form-invalid-feedback id="input-description-live-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <b-col xl="8" lg="8" md="8" sm="12">
                      <validation-provider name="cover" :rules="{ required: true, image: true, size:2048 }" v-slot="validationContext">
                        <b-form-group id="cover" label="Cargar imagen para cover" label-for="cover" class="py-3">
                          <b-form-file v-model="cover" :state="getValidationState(validationContext)" @change="onFileChange" placeholder="Cargar imagen para cover" drop-placeholder="Drop file here..." name="cover"></b-form-file>
                          <b-form-invalid-feedback id="input-cover-live-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    
                    <b-col class="pt-3">
                      <div v-if="!showPreview" class="float-right">
                        <b-img src="/public/images/default/placeholder.png" width="100" height="100" class="rounded"></b-img>
                      </div>
                      <div v-else class="float-right">
                        <b-img :src="imagePreview" width="100" height="100" class="rounded"></b-img>
                      </div>
                    </b-col>

                    <b-col xl="12" lg="12" md="12" sm="12">
                      <validation-provider name="video" v-slot="validationContext">
                        <b-form-group id="video" label="Cargar video para capacitacion" label-for="video" class="py-3">
                          <b-form-file v-model="video" :state="getValidationState(validationContext)" @change="onVideoUpload" ref="video"
                                       placeholder="Cargar video para capacitacion" drop-placeholder="Drop file here..." name="video">
                          </b-form-file>
                          <b-form-invalid-feedback id="input-cover-live-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <b-col xl="12" lg="12" md="12" sm="12">
                      <b-row>
                        <b-col xl="6" lg="6" md="6" sm="12">
                          <b-form-group class="py-3">
                            <b-form-checkbox v-model="want_questions" @change="onTest" :disabled="more_parts">
                              ¿Desea crear un cuestionario?
                            </b-form-checkbox>
                          </b-form-group>
                        </b-col>
                        <b-col xl="6" lg="6" md="6" sm="12">
                          <b-form-group class="py-3">
                            <b-form-checkbox v-model="more_parts" :disabled="want_questions">
                              ¿Desea añadir más partes a las capacitacion?
                            </b-form-checkbox>
                          </b-form-group>
                        </b-col>
                      </b-row>

                      <b-card class="mb-1" v-if="want_questions">
                        <b-card-body>
                          <b-row>
                            <b-col md="6" lg="6" xl="6" sm="12" v-for="(question, i) in questions" :key="i">
                              <b-card>
                                <b-card-text>
                                  <b-form-group>
                                    <b-input-group size="sm">
                                      <b-input-group-prepend is-text>
                                        <strong>Q.{{ i + 1 }}</strong>
                                      </b-input-group-prepend>
                                      <b-form-input :name="'question_title_'+i" v-model="questions[i].question" class="form-control form-control-sm" size="sm" :placeholder="'Escriba la pregunta ' + (i + 1)"></b-form-input>
                                    </b-input-group>
                                  </b-form-group>
                                  <b-input-group size="sm" class="mb-2" v-for="(questionOption, j) in questionOptions" :key="j">
                                    <b-input-group-prepend is-text>
                                      <b-form-radio class="mr-n2" :name="'correct_option_'+i" v-model="questions[i].correctAnswer" :value="questionOption"></b-form-radio>
                                    </b-input-group-prepend>
                                    <b-form-input v-model="questions[i].options[j]" :name="'option_'+i+'+[]'" :placeholder="'Opcion ' + questionOption"></b-form-input>
                                  </b-input-group>
                                </b-card-text>
                              </b-card>
                            </b-col>
                          </b-row>
                        </b-card-body>
                      </b-card>
                    </b-col>

                    <b-col xl="12" lg="12" md="12" sm="12">
                      <b-form-group label="¿En cuantas partes desea dividir la capacitacion?" v-show="more_parts">
                        <b-form-radio-group id="radio-group-2" name="sessions">
                          <b-form-radio v-for="(part, i) in parts" :key="i" :value="part" v-model="formData.sessions">
                            {{ part }} parte(s)
                          </b-form-radio>
                        </b-form-radio-group>
                      </b-form-group>
                    </b-col>

                    <b-col v-if="parts">
                      <div role="tablist" v-for="(session, k) in formData.sessions" :key="k">
                        <b-card class="mb-1">
                          <b-card-header header-tag="header" class="p-1" role="tab">
                            <b-button block v-b-toggle="'accordion-'+session" variant="primary">Parte {{ session }}</b-button>
                          </b-card-header>
                          <b-collapse :id="'accordion-'+session" accordion="my-accordion" role="tabpanel">
                            <b-card-body>
                              <b-row>
                                <b-col md="6" lg="6" xl="6" sm="12" v-for="i in 10" :key="i">
                                  <b-card :header="'Pregunta '+ i" header-tag="header">
                                    <b-card-text>
                                      <b-form-group>
                                        <input type="text" class="form-control form-control-sm" :id="'question'+i" :placeholder="'Escriba la pregunta ' + i" @input="update(trainings, 'part'+k+'_question'+i, $event)" :ref="'part'+k+'_question'+i" name="question">
                                      </b-form-group>
                                      <b-input-group size="sm" class="mb-2" v-for="j in 4" :key="j">
                                        <b-input-group-prepend is-text>
                                          <b-form-radio class="mr-n2"></b-form-radio>
                                        </b-input-group-prepend>
                                        <input type="text" class="form-control form-control-sm" :ref="'part'+k+'_question'+i+'_option'+j" :placeholder="'Opcion ' + i" :id="'option'+i" @input="update(trainings, 'part'+k+'_question'+i+'_option'+j, $event)" name="option">
                                      </b-input-group>
                                    </b-card-text>
                                  </b-card>
                                </b-col>
                              </b-row>
                            </b-card-body>
                          </b-collapse>
                        </b-card>
                      </div>
                    </b-col>
                  </b-row>
                </b-container>

                <b-button variant="success" size="sm" class="float-right mt-5" @click.prevent="onCreate">
                  <i class="fas fa-save"></i> Crear capacitacion
                </b-button>
              </b-form>
            </validation-observer>
          </b-col>
        </b-row>
      </b-container>
    </b-modal>
  </div>
</template>

<script>
export default {
  name: "create",
  data() {
    return {
      formData: {
        title: "",
        description: "",
        is_shared: false,
        sessions: 0,
      },
      show: false,
      selected: 1,
      rolesArray: [],
      selectedRoles: [],
      role_ids: [],
      cover: null,
      imagePreview: null,
      showPreview: false,
      video: null,
      want_questions: false,
      parts: 10,
      numberOfQuestions: 10,
      questionOptions: 4,
      questions: [],
      sessions: [],
      more_parts: false,
      trainings: {}
    }
  },

  methods: {
    update(obj, prop, event) {
      Vue.set(obj, prop, event.target.value);
    },
    
    onCreate() {
      this.$Progress.start()

      let formData = new FormData()
      formData.append('cover', this.cover)
      formData.append('video', this.video)
      formData.append('questions', JSON.stringify(this.questions))
      formData.append('role_ids', JSON.stringify(this.role_ids))
      formData.append('title', this.formData.title)
      formData.append('description', this.formData.description)
      formData.append('sessions', this.formData.sessions)
      formData.append('is_shared', this.formData.is_shared)
      formData.append('want_questions', this.want_questions)

      /*this.partquestions.push({ ...this.trainings });
       let questions = JSON.stringify(this.partquestions);*/

      const config = {
        headers: {
          'content-type': 'multipart/form-data'
        }
      }
      
      // const url = "/trainings/store"
      const url = route("training.store_training")

      axios.post(url, formData, config).then((response) => {
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
        this.$Progress.fail();
        if (error.response.status === 422) {
          this.$refs.formData.setErrors(error.response.data.errors)
          Toast.fire({
            icon: "error",
            title: "Por favor, complete los campos requeridos",
          });
        }
      })
    },

    formReset(event) {
      console.log(this.formData);
      event.target.reset();
    },

    getValidationState({dirty, validated, valid = null}) {
      return dirty || validated ? valid : null;
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

    onFileChange(event) {
      this.cover = event.target.files[0];
      let reader = new FileReader();

      reader.addEventListener("load", function () {
        this.showPreview = true;
        this.imagePreview = reader.result;
      }.bind(this), false);
      if (this.cover) {
        if (/\.(jpe?g|png|gif)$/i.test(this.cover.name)) {
          reader.readAsDataURL(this.cover);
        }
      }
    },

    onTest() {
      this.questions = [];
      for (let i = 0; i < this.numberOfQuestions; i++) {
        this.questions.push({
          question: '',
          options: [],
          correctAnswer: 0
        })
      }
    },

    onSessions(){
      this.sessions = [];
      this.questions = [];
      for (let i = 1; i <= this.formData.sessions; i++){
        for (let j = 0; j < this.numberOfQuestions; j++){
          this.questions.push({
            question: '',
            options: [],
            correctAnswer: 0
          })
        }
        
         this.sessions.push({
           session: i,
           questions: this.questions
         })
      }
    },

    onVideoUpload(e) {
      this.video = e.target.files[0];
    }
  },

  computed: {
    checkAll: {
      get: function () {
        return this.rolesArray ? this.selectedRoles.length === this.rolesArray.length : false;
      },

      set: function (value) {

        let selected = [];

        if (value) {
          this.rolesArray.forEach((role) => {
            selected.push(role.id);
          });
        }

        this.selectedRoles = selected;
        this.role_ids = this.selectedRoles;
      },
    },
  },

  mounted() {
    this.getRoles();
  }
}
</script>

<style scoped>

</style>
