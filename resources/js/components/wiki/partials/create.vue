<template>
<span>
    <b-button id="toggle-btn" variant="success" size="sm" @click="showModal">
      <i class="fas fa-plus"></i> Crear post
    </b-button>

    <b-modal @hide="resetModal" centered header-bg-variant="dark" no-close-on-backdrop header-close-content="" size="xl" ref="my-modal" title="Crear post">
     <div class="container">
       <div class="row">
         <div class="col-md-12">
           <b-form>
              <div class="row">
                <div class="col-md-6">
                  <b-form-radio-group id="radio-group-1" v-model="selected_option" :options="options" name="radio-options"></b-form-radio-group>
                </div>
                <div class="col-md-6">
                  <b-form-checkbox id="checkbox-1" class="float-right" v-model="post.is_shared" name="checkbox-1" :value="1" :unchecked-value="0">Compartir automaticamente</b-form-checkbox>
                </div>
                <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 my-3" v-if="selected_option != null">
                    <b-form-select v-model="post.roles" multiple :select-size="10" v-if="selected_option === 1">
                        <b-form-select-option v-for="role in getRoles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                    </b-form-select>

                    <div class="row">
                      <div class="col-md-12 py-3" v-if="selected_option === 2">
                        <b-form-checkbox id="checkbox-2" name="checkbox-2" v-model="checkAll" :checked="checkAll === true ? 'checked' : ''">Seleccionar todos</b-form-checkbox>
                      </div>

                      <div class="col-md-4" v-for="role in getRoles" v-if="selected_option === 2">
                        <b-form-checkbox-group id="checkbox-group-2" v-model="post.roles">
                          <b-form-checkbox :value="role.id">{{ role.name }}</b-form-checkbox>
                        </b-form-checkbox-group>
                      </div>
                    </div>
                </div>
              </div>

              <div class="row py-3">
                <div class="col-md-6">
                  <b-form-group id="input-group-1" label="Categorias:" label-for="input-1" description="We'll never share your email with anyone else.">
                    <b-form-select :disabled="post.category_name != ''" v-model="post.wiki_category_id">
                      <template #first>
                        <b-form-select-option :value="null" disabled>-- Por favor, seleccione una categoria existente --</b-form-select-option>
                      </template>

                       <b-form-select-option v-for="category in allcategories" :key="category.id" :value="category.id">{{ category.name }}</b-form-select-option>
                    </b-form-select>
                  </b-form-group>
                </div>

                <div class="col-md-6">
                  <b-form-group id="input-group-2" label="Crear una nueva:" label-for="input-2" description="We'll never share your email with anyone else.">
                    <b-form-input id="input-2" :disabled="post.wiki_category_id != null" v-model="post.category_name" type="text" required placeholder="Escriba el tags del post..."></b-form-input>
                  </b-form-group>
                </div>

                <div class="col-md-12">
                  <b-form-group id="input-group-5" label="Crear tags:" label-for="input-5" description="We'll never share your email with anyone else.">
                    <b-form-input id="input-5" v-model="post.tag" type="text" required placeholder="Escriba el tags del post..."></b-form-input>
                  </b-form-group>
                </div>

                <div class="col-md-12">
                  <b-form-group id="input-group-3" label="Titulo:" label-for="input-3" description="We'll never share your email with anyone else.">
                    <b-form-input id="input-3" v-model="post.title" type="text" required placeholder="Escriba el titulo del post..."></b-form-input>
                  </b-form-group>
                </div>

                <div class="col-md-12">
                  <b-form-group id="input-group-4" label="Contenido:" label-for="input-4" description="We'll never share your email with anyone else.">
                    <editor :plugins="myPlugins"
                            :toolbar ="myToolbar1"
                            :init="myInit"
                            v-model.trim="post.body"
                            api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"
                            placeholder="Ingrese el contenido de la noticia que desea publicar"/>
                  </b-form-group>
                </div>
              </div>
           </b-form>
         </div>
       </div>
     </div>

      <template v-slot:modal-footer>
          <div class="container">
            <div class="row">
              <div class="col-md-12">
                <b-button @click.prevent="storePost" type="button" class="float-right" size="sm" variant="success" :disabled="!isValid">
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
import Editor from "@tinymce/tinymce-vue";
import {mapGetters} from "vuex";

export default {
  name: "create",

  components: {
    'editor': Editor
  },

  data: () => {
    return {
      allcategories: [],
      selected_roles: [],

      options: [
        {text: 'Seleccionar rol', value: 1},
        {text: 'Seleccionar todos los roles', value: 2},
      ],
      selected_option: null,

      post: {
        title: null,
        body: null,
        is_shared: 0,
        roles: [],
        wiki_category_id: null,
        tag: null,
        category_name: ""
      },

      myToolbar1: 'fontsizeselect | bold alignleft aligncenter alignright alignjustify | backcolor forecolor | \ bullist numlist | link | emoticons | image',
      myPlugins:  [
        'emoticons advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
      ],
      myInit:{
        height: 500,
        menubar: false,
      }
    }
  },

  computed: {
    ...mapGetters(['getRoles']),

    checkAll: {
      get: function () {
        return this.getRoles ? this.selected_roles.length === this.getRoles.length : false;
      },
      set: function (value) {
        let selected = [];
        if (value) {
          this.getRoles.forEach((role) => {
            selected.push(role.id);
          });
        }
        this.selected_roles = selected;
        this.post.roles = this.selected_roles;
      },
    },

    isValid() {
      return this.post.title && this.post.body && this.post.roles.length >= 1 && (this.post.wiki_category_id || this.post.category_name !== "");
    }
  },

  methods: {
    resetModal() {
      this.roles = []
      this.post.roles = []
      this.selected_roles = []
      this.selected_option = null
      this.post.title = null
      this.post.body = null
      this.post.is_shared = 0
      this.post.wiki_category_id = null
      this.post.tag = null
    },

    getCategories() {
      let vm = this
      axios.get("/wiki/categorydata").then(function (response) {
        let res = response.data;
        vm.allcategories = res.categories;
      }).catch((error) => {
        console.log(error);
      });
    },

    showModal() {
      this.$refs['my-modal'].show()
      this.$store.dispatch("GET_ROLES")
      this.getCategories()
    },

    storePost() {
      let url = "wiki/store"
      axios.post(url, this.post).then((response) => {
        if (response.data.code === 403) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        } else {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          Fire.$emit("AfterCreated")
        }
      }).catch((error) => {
        if (error.response.status === 422) {
          this.$refs.formData.setErrors(error.response.data.errors)
          Toast.fire({
            icon: "error",
            title: "Por favor, complete los campos requeridos",
          });
        }
      })
    }
  }
}
</script>

<style scoped>

</style>
