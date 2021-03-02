<template>
  <span id="create_news">
    <b-button variant="success" size="sm" id="toggle-btn" @click="createNews">
      <i class="fas fa-plus"></i> Crear
    </b-button>

    <b-modal @hidden="resetModal" centered size="lg" ref="create-news-modal" no-close-on-backdrop header-bg-variant="primary" header-close-content="" title="Crear noticia">
      <div class="container-fluid">
        <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12">
          <ValidationObserver ref="observer" v-slot="{ store }">
            <b-form class="py-3">
            <div class="row">
              <div class="col-md-4 col-lg-4 col-xl-4 col-sm-12">
                <b-form-checkbox id="is_shared" v-model="news.is_shared" name="is_shared" :value="1" :unchecked-value="0">
                  Compartir automaticamente
                </b-form-checkbox>
              </div>
              <div class="col-md-3 col-lg-3 col-xl-3 col-sm-12">
                <b-form-checkbox id="studio_shared" v-model="news.studio_shared" name="studio_shared" :value="1" :unchecked-value="0">
                  Crear y compartir
                </b-form-checkbox>
              </div>
              <div class="col-md-5 col-lg-5 col-xl-5 col-sm-12">
                <b-form-radio-group id="radio-group-2" v-model="role_radio" name="role_ids">
<!--                  <b-form-radio :value="'individual'" @click="clear">Rol especifico</b-form-radio>-->
                  <b-form-radio :value="'all'" @click="clear">Todos los roles</b-form-radio>
                </b-form-radio-group>
              </div>
              
              <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 my-3" v-if="role_radio != ''">
                <ValidationProvider name="role_ids" rules="required" v-slot="{ valid, errors }">
                  <b-form-group label="Escoja rol individual:" v-if="role_radio === 'individual'">
                    <b-form-select :state="errors[0] ? false : (valid ? true : null)" v-model="news.role_ids">
                      <b-form-select-option v-for="role in getRoles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                    </b-form-select>
                    <b-form-invalid-feedback id="inputLiveFeedback">{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </ValidationProvider>

                <ValidationProvider name="role_ids" rules="required" v-slot="{ valid, errors }" v-if="role_radio === 'all'">
                  <b-form-group label="Escoja roles:">
                    <b-form-select :state="errors[0] ? false : (valid ? true : null)" v-model="news.role_ids" multiple :select-size="8">
                      <b-form-select-option v-for="role in getRoles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                    </b-form-select>
                    <b-form-invalid-feedback id="inputLiveFeedback">{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </ValidationProvider>
              </div>

              <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12" :class="role_radio == '' ? 'mt-3 mb-3' : ''">
                <ValidationProvider rules="required" name="title" v-slot="{ valid, errors }">
                  <b-form-group label="Titulo: ">
                      <b-form-input :state="errors[0] ? false : (valid ? true : null)" id="news_title" v-model="news.title" placeholder="Escriba el titulo de noticia..."></b-form-input>
                      <b-form-invalid-feedback id="inputLiveFeedback">{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </ValidationProvider>
              </div>

              <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12" :class="role_radio == '' ? 'mb-3' : 'mb-3'">
                <ValidationProvider rules="required" name="body" v-slot="{ valid, errors }">
                  <b-form-group label="contenido: ">
                    <b-form-textarea :state="errors[0] ? false : (valid ? true : null)" v-model="news.body" rows="6" max-rows="20" placeholder="Escriba el contenido de su noticia..."></b-form-textarea>
                    <b-form-invalid-feedback id="inputLiveFeedback">{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </ValidationProvider>
              </div>

              <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12">
                <ValidationProvider rules="required|ext:jpg,png,jpeg,gif,mp4" name="media" v-slot="{ valid, errors }">
                  <b-form-file :state="errors[0] ? false : (valid ? true : null)" @change="GetImage" placeholder="Cargue o arrastre el archivo para la noticia..." drop-placeholder="Drop file here..."></b-form-file>
                  <b-form-invalid-feedback id="inputLiveFeedback">{{ errors[0] }}</b-form-invalid-feedback>
                </ValidationProvider>
              </div>
              
              <div class="col-md-12" v-if="imagePreview != null" :class="imagePreview != null ? 'my-3' : ''">
                <img :src="imagePreview" class="rounded img-thumbnail img-preview">
              </div>
            </div>
          </b-form>
          </ValidationObserver>
        </div>
      </div>

      <template v-slot:modal-footer>
          <div class="container">
            <div class="row">
              <div class="col-md-12">
                <b-button @click="store"  type="button" class="float-right" size="sm" variant="success" :disabled="!isValid">
                  <i class="fas fa-save"></i> Guardar
                </b-button>
              </div>
            </div>
          </div>
      </template>
    </b-modal>
  </span>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  name: "create",
  
  data() {
    return {
      news:{
        is_shared: 0,
        studio_shared: 0,
        title: "",
        body: "",
        role_ids: [],
        media: null
      },

      //variables
      role_radio: "",
      imagePreview: null
    }
  },

  methods:{
    resetModal() {
      this.role_radio = ""
      this.news.role_ids = []
      this.news.is_shared = 0
      this.news.studio_shared = 0
      this.news.media = null
      this.news.title = ""
      this.news.body = ""
      this.imagePreview = null
    },

    GetImage(event) {
      this.news.media = event.target.files[0];
      let reader = new FileReader();

      reader.addEventListener("load", function () {
        this.imagePreview = reader.result;
      }.bind(this), false);
      if (this.news.media) {
        if (/\.(jpe?g|png|gif)$/i.test(this.news.media.name)) {
          reader.readAsDataURL(this.news.media);
        }
      }
    },

    createNews() {
      this.$refs['create-news-modal'].toggle('#toggle-btn')
      this.$store.dispatch("GET_ROLES")
    },

    store(){
      this.$Progress.start()

      let formData = new FormData()
      formData.append('file', this.news.media)
      formData.append('role_ids', JSON.stringify(this.news.role_ids))
      formData.append('title', this.news.title)
      formData.append('body', this.news.body)
      formData.append('is_shared', this.news.is_shared)
      formData.append('studio_shared', this.news.studio_shared)

      const config = {
        headers: {
          'content-type': 'multipart/form-data'
        }
      }

      const url = "news/store"

      axios.post(url, formData, config).then((response) => {
        if (response.status === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          Fire.$emit("newsCreated")
          this.$Progress.finish()
          this.resetModal()
        }else{
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          this.$Progress.fail();
        }
      }).catch((error) => {
        this.$Progress.fail();
        if (error.response.status === 422) {
          this.$refs.observer.setErrors(error.response.data.errors)
          Toast.fire({
            icon: "error",
            title: "Por favor, complete los campos requeridos",
          });
        }
      })
    }
  },

  computed:{
    ...mapGetters(['getRoles']),

    isValid() {
      return (this.news.title && this.news.body && this.news.role_ids.length >= 1 && this.news.media !== null);
    },

    clear() {
      return this.role_radio === 'individual' ? this.news.role_ids = "" : this.news.role_ids = [];
    }
  },

  mounted(){

  }
}
</script>

<style scoped>

</style>