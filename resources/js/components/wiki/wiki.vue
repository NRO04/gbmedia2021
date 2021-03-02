<template>
  <div id="wiki">
    <b-overlay :show="show" no-wrap></b-overlay>

    <div v-if="postsArray.length > 1 && !show">
      <div class="row">
        <div class="col-md-3">
          <div class="card border-0 py-3 shadow-sm">
            <div class="card-body text-center">
              <a href="wiki/">
                <span class="fa-stack fa-3x text-white">
                  <i class="text-dark far fa-circle fa-stack-2x"></i>
                  <i class="fab fa-wikipedia-w"></i>
                </span>
              </a>
              <h5 class="card-title">Wiki GB</h5>
            </div>
            <ul class="list-group list-group-flush px-2">
              <li class="list-group-item py-2 text-capitalize" v-for="(category, i) in allcategoriespost" :key="category.id">
                <a class="text-white collapsed cat_links" data-toggle="collapse" :href="'#collapseExample_'+i">
                  {{ category.category }}
                </a>
                <ul class="collapse" :id="'collapseExample_'+i">
                  <li class="py-1" v-for="post in category.title" :key="post.id">
                    <small>
                      <a class="text-muted" href="#" @click.prevent="postDetails(post.id)">{{ post.title }}</a>
                    </small>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>

        <div class="col-md-9">
          <div class="row">
            <div class="col-md-8">
              <input type="text" class="form-control form-control-sm w-75" placeholder="Buscar posts..." v-model="search" @keyup.enter="postsGrid(1, search, filter)" lazy>
            </div>
            <div class="col-md-4">
              <button v-if="can('wikicategories-create')" class="btn btn-sm btn-success float-right mx-2" @click="showCategories">
                <i class="fas fa-plus"></i> Crear categoria
              </button>
              <b-button v-if="can('wiki-create')" class="float-right" id="toggle-btn" variant="success" size="sm" @click="showModal">
                <i class="fas fa-plus"></i> Crear post
              </b-button>
            </div>
          </div>

          <div class="mt-4">
            <div class="row">
              <div class="col-md-4 col-lg-4 col-sm-12 col-xl-4" v-for="post in postsArray" :key="post.id">
                <div class="card shadow border-0">
                  <div class="card-header">
                    <span class="text-capitalize">
                      <span class="h6"><text-highlight :queries="search">{{ post.title }}</text-highlight></span>
                      <span class="float-right text-capitalize text-muted text-value-sm">
                        {{ post.category }}
                      </span>
                    </span>
                  </div>
                  <div class="card-body">
                    <small class="text-muted cat">
                      <i class="fas fa-clock text-white"></i> Por {{ post.author }}, {{ post.published }}
                      <i class="fas fa-users text-white"></i> {{ post.roles }} roles
                    </small>

                    <small class="text-muted cat float-right pt-1">
                      <i class="fas fa-share text-white"></i> {{ post.shares }} estudios
                    </small>

                    <span v-html="truncateText(post.body)"></span>
                    <a href="#" @click.prevent="postDetails(post.id)">Ver más detalles del post</a>
                  </div>
                  <div class="card-footer bg-transparent border-top-0">
                    <button @click="editPost(post.id)" v-if="can('wiki-edit')" class="btn btn-sm btn-dark"><i class="fas fa-edit"></i> Editar</button>
                    <button @click="shareModal(post.id)" v-if="can('wikishare-create') && post.canShare" class="btn btn-sm btn-dark mx-2"><i class="fas fa-share-alt"></i> Compartir</button>
                    <button @click="deletePost(post.id)" v-if="can('wiki-delete')" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Eliminar</button>
                  </div>
                </div>
              </div>

              <div class="col-md-12 col-lg-12 col-sm-12 text-center py-3" v-show="pagination.total >= 12">
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
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!postsArray.length && !show">
      <div class="container mt-3">
        <div class="row text-center">
          <div class="col-md-12">
            <div class="container">
              <div class="row">
                <div class="col-md-12">
                  <h3>No hay posts por el momento...</h3>
                  <p>¡Empieza a crear los tuyos!</p>
                  <div class="row">
                    <div class="col-md-12">
                      <button v-if="can('wiki-create')"class="btn btn-sm btn-success" @click="showModal">
                        <i class="fas fa-plus"></i>Crear post
                      </button>
                      <button class="btn btn-sm btn-primary mx-2" @click="showCategories" v-if="search === '' && can('wikicategories-view')">
                        <i class="fas fa-list-ol"></i> Ver listado de categorías
                      </button>
                      <button class="btn btn-sm btn-primary mx-2" @click="resetSearch" v-if="search !== ''">
                        <i class="fas fa-undo"></i> Volver al listado de wikis
                      </button>
                    </div>
                    <div class="col-md-12">
                      <img src="images/svg/undraw_no_data_qbuo.svg" alt="empty page" height="500" width="500"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="modals">
      <!-- create posts -->
      <div class="modal fade" id="exampleModal" :no-enforce-focus="true" ref="my-modal2" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel" v-text="editMode ? 'Editar post' : 'Crear post'"></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="container">
                <div class="row">
                  <div class="col-md-12">
                    <b-form>
                      <div class="row">
                        <div class="col-md-6">
                          <b-form-radio-group id="radio-group-1" v-model="selected_option" :options="options" name="radio-options"></b-form-radio-group>
                        </div>
                        <div class="col-md-6">
                          <b-form-checkbox id="checkbox-1" class="float-right" v-model="post.is_shared" name="checkbox-1" :value="1" :unchecked-value="0">Compartir
                            automaticamente
                          </b-form-checkbox>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 my-3" v-if="selected_option != null">
                          <b-form-select v-model="post.roles" multiple :select-size="10" v-if="selected_option === 1">
                            <b-form-select-option v-for="role in getRoles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                          </b-form-select>

                          <div class="row">
                            <div class="col-md-12 py-3" v-if="selected_option === 2">
                              <b-form-checkbox id="checkbox-2" name="checkbox-2" v-model="checkAll" v-if="!editMode" :checked="checkAll === true ? 'checked' : ''">Seleccionar todos</b-form-checkbox>
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
                          <b-form-group id="input-group-10" label="Categorias:" label-for="input-10">
                            <b-form-select :disabled="post.category_name != ''" v-model="post.wiki_category_id">
                              <template #first>
                                <b-form-select-option :value="null" disabled>-- Por favor, seleccione una categoria existente --</b-form-select-option>
                              </template>

                              <b-form-select-option v-for="category in allcategories" :key="category.id" :value="category.id">{{ category.name }}</b-form-select-option>
                            </b-form-select>
                          </b-form-group>
                        </div>

                        <div class="col-md-6">
                          <b-form-group id="input-group-20" label="Crear una nueva:" label-for="input-20">
                            <b-form-input id="input-20" :disabled="post.wiki_category_id != null" v-model="post.category_name" type="text" required placeholder="Escriba el tags del post..."></b-form-input>
                          </b-form-group>
                        </div>

                        <div class="col-md-12">
                          <b-form-group id="input-group-50" label="Crear tags:" label-for="input-50">
                            <b-form-input id="input-50" v-model="post.tag" type="text" required placeholder="Escriba el tags del post..."></b-form-input>
                          </b-form-group>
                        </div>

                        <div class="col-md-12">
                          <b-form-group id="input-group-30" label="Titulo:" label-for="input-30">
                            <b-form-input id="input-30" v-model="post.title" type="text" required placeholder="Escriba el titulo del post..."></b-form-input>
                          </b-form-group>
                        </div>

                        <div class="col-md-12">
                          <b-form-group id="input-group-40" label="Contenido:" label-for="input-40">
                            <editor :plugins="myPlugins" :toolbar="myToolbar1" :init="myInit" v-model.trim="post.body" api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"
                                    placeholder="Ingrese el contenido de la noticia que desea publicar"
                                    :initial-value="initialValue"
                            />
                          </b-form-group>
                        </div>
                      </div>
                    </b-form>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <b-button @click="editMode ? updatePost(post.id) : storePost()" type="button" class="float-right" size="sm" variant="success" :disabled="!isValid">
                <span v-if="!isCreating"><i class="fas fa-save"></i> Guardar</span>
                <span v-if="isCreating"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
              </b-button>
            </div>
          </div>
        </div>
      </div>

      <!-- create posts -->
      <b-modal @hide="resetModal" centered header-bg-variant="dark" no-close-on-backdrop header-close-content="" size="xl" ref="my-modal"
               :title="editMode ? 'Editar post' : 'Crear post'">
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <b-form>
                <div class="row">
                  <div class="col-md-6">
                    <b-form-radio-group id="radio-group-1" v-model="selected_option" :options="options" name="radio-options"></b-form-radio-group>
                  </div>
                  <div class="col-md-6">
                    <b-form-checkbox id="checkbox-1" class="float-right" v-model="post.is_shared" name="checkbox-1" :value="1" :unchecked-value="0">Compartir automaticamente
                    </b-form-checkbox>
                  </div>
                  <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 my-3" v-if="selected_option != null">
                    <b-form-select v-model="post.roles" multiple :select-size="10" v-if="selected_option === 1">
                      <b-form-select-option v-for="role in getRoles" :key="role.id" :value="role.id">{{ role.name }}</b-form-select-option>
                    </b-form-select>

                    <div class="row">
                      <div class="col-md-12 py-3" v-if="selected_option === 2">
                        <b-form-checkbox id="checkbox-2" name="checkbox-2" v-model="checkAll" v-if="!editMode" :checked="checkAll === true ? 'checked' : ''">Seleccionar todos
                        </b-form-checkbox>
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
                    <b-form-group id="input-group-1" label="Categorias:" label-for="input-1">
                      <b-form-select :disabled="post.category_name != ''" v-model="post.wiki_category_id">
                        <template #first>
                          <b-form-select-option :value="null" disabled>-- Por favor, seleccione una categoria existente --</b-form-select-option>
                        </template>

                        <b-form-select-option v-for="category in allcategories" :key="category.id" :value="category.id">{{ category.name }}</b-form-select-option>
                      </b-form-select>
                    </b-form-group>
                  </div>

                  <div class="col-md-6">
                    <b-form-group id="input-group-2" label="Crear una nueva:" label-for="input-2">
                      <b-form-input id="input-2" :disabled="post.wiki_category_id != null" v-model="post.category_name" type="text" required
                                    placeholder="Escriba el tags del post..."></b-form-input>
                    </b-form-group>
                  </div>

                  <div class="col-md-12">
                    <b-form-group id="input-group-5" label="Crear tags:" label-for="input-5">
                      <b-form-input id="input-5" v-model="post.tag" type="text" required placeholder="Escriba el tags del post..."></b-form-input>
                    </b-form-group>
                  </div>

                  <div class="col-md-12">
                    <b-form-group id="input-group-3" label="Titulo:" label-for="input-3">
                      <b-form-input id="input-3" v-model="post.title" type="text" required placeholder="Escriba el titulo del post..."></b-form-input>
                    </b-form-group>
                  </div>

                  <div class="col-md-12">
                    <b-form-group id="input-group-4" label="Contenido:" label-for="input-4">
                      <editor :plugins="myPlugins"
                              :toolbar="myToolbar1"
                              :init="myInit"
                              v-model.trim="editMode ? '' : post.body"
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
                <b-button @click="editMode ? updatePost(post.id) : storePost()" type="button" class="float-right" size="sm" variant="success" :disabled="!isValid">
                  <span v-if="!isCreating"><i class="fas fa-save"></i> Guardar</span>
                  <span v-if="isCreating"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                </b-button>
              </div>
            </div>
          </div>
        </template>
      </b-modal>
      <!-- create posts -->

      <!--post view details modal-->
      <b-modal size="xl" centered hide-header-close="" :no-enforce-focus="true" scrollable header-bg-variant="dark" variant="dark" ref="modal-details" hide-footer :title="'Usted esta leyendo: ' + details.title + ' (' + details.category + ')'">
        <b-overlay :show="showDetails" no-wrap></b-overlay>
        <div v-if="!showDetails">
          <div class="metadata">
            <span> Publicado por: <span class="text-info">{{ details.author }}</span></span>
            <span class="float-right text-info"><strong>{{ details.published }}</strong></span>
          </div>
          <hr>
          <div class="metadata">
            <div class="row">
              <div class="col-md-6">
                <ul class="list-inline">
                  <li class="list-inline-item">Publicado para:
                    <span class="font-weight-bold">{{ details.roles }}</span>
                    <span v-if="details.roles_count >= 1" class="ltext-info font-weight-bold">
                      y  <a href="#" v-b-popover.hover.top="details.all_roles">{{ details.roles_count }} roles mas</a>
                    </span>
                  </li>
                </ul>
              </div>
              <div class="col-md-6"  v-if="details.studios !== '' && can('wikishare-view')">
                <ul class="list-inline">
                  <li class="list-inline-item">Compartido con:
                    <span class="font-weight-bold">{{ details.studios }}</span>
                    <span v-if="details.shares_count >= 1" class="text-info font-weight-bold">
                      <a href="#" v-b-popover.hover.top="details.all_studios">y {{ details.shares_count }} estudios mas</a>
                    </span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <hr>
          <div class="copy_btn">
<!--            <b-button variant="dark" size="sm" v-clipboard:copy="details.body" v-clipboard:success="onCopy" v-clipboard:error="onError">
              <i class="fas fa-copy"></i> {{ copiedText }}
            </b-button>-->

            <b-button variant="dark" size="sm" @click="ejecutar('content')">
              <i class="fas fa-copy"></i> {{ copiedText }}
            </b-button>

            <b-button v-if="can('wiki-delete')" variant="danger" size="sm" class="float-right" @click="deletePost(details.id)">
              <i class="fas fa-trash-alt"></i>
            </b-button>
            <b-button v-if="can('wikishare-create')" variant="info" size="sm" class="float-right mx-2" @click="shareModal(details.id)">
              <i class="fas fa-share-alt"></i>
            </b-button>
            <b-button v-if="can('wiki-edit')" variant="dark" size="sm" class="float-right" @click="editPost(details.id)">
              <i class="fas fa-edit"></i>
            </b-button>
          </div>
          <hr>
          <div class="tags" v-if="details.tags !== '' || details.tags !== null">
            <small class="text-dark badge badge-info">{{ details.tags }}</small>
            <hr>
          </div>
          <div v-html="details.body" id="content"></div>
          <div id="destino" contentEditable="true" style="display: none; background: transparent; background-color: white"></div>
        </div>
      </b-modal>
      <!--post view details modal-->

      <!--category create/edit modal-->
      <b-modal @hide="resetModal" centered scrollable hide-header-close="" size="lg" variant="dark" ref="modal-categories" hide-footer>
        <div class="container">
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xl-12 col-lg-12" id="categories_form" v-if="can('wikicategories-create')">
              <h6 v-text="title"></h6>
              <b-form>
                <b-form-group>
                  <b-row class="justify-content-between">
                    <b-col md="10" lg="10" sn="12">
                      <b-form-input size="sm" v-model.trim="category.name" placeholder="Nombre de la categoría"></b-form-input>
                    </b-col>
                    <b-col md="2" lg="2" sn="12">
                      <b-button size="sm" variant="success" @click="editMode && category.name != '' ? updateCategory() : createCategory()">
                          <span v-if="!isCreating">
                            <i class="fas fa-save"></i>
                            <span v-text="editMode && category.name != '' ? 'Guardar' : 'Crear'"></span>
                          </span>
                        <span v-if="isCreating">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span v-text="editMode && category.name != '' ? 'Guardando...' : 'Creando...'"></span>
                          </span>
                      </b-button>
                    </b-col>
                  </b-row>
                </b-form-group>
              </b-form>
            </div>

            <div class="col-md-12 col-sm-12 col-xl-12 col-lg-12" id="categories_table" v-if="allcategories.length">
              <hr v-if="allcategories.length">

              <h3>Listado categorias creadas</h3>
              <table class="table table-striped my-3">
                <thead>
                <tr>
                  <th scope="col">No.</th>
                  <th scope="col">Nombre de la categoria</th>
                  <th scope="col">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(category, index) in allcategories" :key="category.id">
                  <th scope="row">{{ index + 1 }}</th>
                  <td v-text="category.name"></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-warning" @click="editCategory(category.id)"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" @click="deleteCategory(category.id)"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>

            <div class="col-md-12 col-sm-12 col-xl-12 col-lg-12 mt-5 text-center" v-else>
              <h3>No hay categorias creadas</h3>
            </div>
          </div>
        </div>
      </b-modal>
      <!--category create/edit modal-->

      <!--post share modal-->
      <b-modal @hide="resetModal" size="lg" centered hide-header-close="" variant="dark" ref="modal-share" :title="'Usted esta compartiendo: ' + share.title">
        <b-overlay :show="showShare" no-wrap></b-overlay>

        <b-form v-if="!showShare">
          <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 pb-4">
              <b-form-checkbox id="is_shared" v-model="share.is_shared" name="is_shared" :value="1" :unchecked-value="0">
                Compartir automaticamente
              </b-form-checkbox>
            </div>

            <div class="col-md-8 col-lg-8 col-xl-8 col-sm-12">
              <b-form-radio-group id="radio-group-3" v-model="selected_studios" name="radio-sub-component-3">
                <b-form-radio :value="1">Seleccionar estudios</b-form-radio>
                <b-form-radio :value="2">Seleccionar todos los estudios</b-form-radio>
              </b-form-radio-group>
            </div>

            <div class="col-md-12 col-lg-12 col-xl-12 col-sm-12 my-3" v-if="selected_studios != null">
              <div class="row">
                <div class="col-md-12" v-if="selected_studios === 1">
                  <b-form-select v-model="share.studios" multiple :select-size="10">
                    <b-form-select-option v-for="studio in studios" :key="studio.id" :value="studio.id">{{ studio.studio_name }}</b-form-select-option>
                  </b-form-select>
                </div>

                <div class="col-md-4" v-for="(studio, i) in studios" v-if="selected_studios === 2">
                  <b-form-checkbox-group id="checkbox-group-2" v-model="select_studios">
                    <b-form-checkbox :value="studio.id">{{ studio.studio_name }}</b-form-checkbox>
                  </b-form-checkbox-group>
                </div>
              </div>
            </div>
          </div>
        </b-form>

        <template v-slot:modal-footer>
          <b-button @click.prevent="sharePost()" type="button" class="float-right" size="sm" variant="success">
            <i class="fas fa-share-alt"></i> Compartir
          </b-button>
        </template>
      </b-modal>
      <!--post share modal-->
    </div>
  </div>
</template>

<script>
import moment from "moment";
import 'moment/locale/es'
import CreatePost from './partials/create'
import {mapGetters} from "vuex";
import Editor from "@tinymce/tinymce-vue";

export default {
  props: ['user', 'permissions'],

  data: () => {
    return {
      title: "",
      editMode: false,
      isCreating: false,
      selected: false,
      is_sharing: false,
      loading: false,
      moment: moment,
      show: false,
      showDetails: false,
      showShare: false,
      postsArray: [],
      allcategories: [],
      allcategoriespost: [],
      selected_roles: [],
      errors: [],
      options: [
        {text: 'Seleccionar rol', value: 1},
        {text: 'Seleccionar todos los roles', value: 2},
      ],
      selected_option: null,
      selected_studios: null,
      post: {
        id: 0,
        title: null,
        body: null,
        is_shared: 0,
        roles: [],
        wiki_category_id: null,
        tag: null,
        category_name: ""
      },
      myToolbar1: 'fontsizeselect | bold alignleft aligncenter alignright alignjustify | backcolor forecolor | \ bullist numlist | link | emoticons | image',
      myPlugins: [
        'emoticons advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
      ],
      myInit: {
        height: 500,
        menubar: false,
        statusbar: false,
        skin: 'oxide-dark',
        content_css: 'dark',
        images_upload_handler: function (blobInfo, success, failure, folderName) {
          var xhr, formData;
          xhr = new XMLHttpRequest();
          xhr.withCredentials = false;
          xhr.open('POST', route("wiki.uploadImages"));

          var token = document.head.querySelector('[name=csrf-token]').content;
          xhr.setRequestHeader('X-CSRF-Token', token);

          xhr.onload = function () {
            var json;

            if (xhr.status < 200 || xhr.status >= 300) {
              failure('HTTP Error: ' + xhr.status);
              return;
            }
            console.log(json);
            json = JSON.parse(xhr.responseText);

            if (!json || typeof json.location != 'string') {
              failure('Invalid JSON: ' + xhr.responseText);
              return;
            }
            success(json.location);

          };

          xhr.onerror = function () {
            failure('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
          };

          formData = new FormData();
          formData.append('file', blobInfo.blob(), blobInfo.filename());
          xhr.send(formData);
        },
      },
      initialValue: "",
      pagination: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        from: 0,
        to: 0,
      },
      offset: 3,
      search: "",
      filter: "title",
      details: {
        id: null,
        title: "",
        body: "",
        roles: [],
        is_shared: 0,
        author: "",
        category: "",
        tags: "",
        published: "",
        wiki_category_id: "",
      },
      role_radio: "",
      category: {
        id: null,
        name: ""
      },
      share: {
        id: 0,
        wiki_category_id: 0,
        studios: []
      },
      select_studios: [],
      studios: [],
      copiedText: "Copiar texto",
      roles: []
    };
  },

  components: {
    'CreatePost': CreatePost,
    'editor': Editor
  },

  computed: {
    ...mapGetters(['getRoles']),

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
    },
    checkAllStudios: {
      get: function () {
        return this.studios ? this.select_studios.length === this.studios.length : false;
      },
      set: function (value) {
        let selected = [];
        if (value) {
          this.studios.forEach((studio) => {
            selected.push(studio.id);
          });
        }
        this.select_studios = selected;
        this.share.studios = this.select_studios;
      },
    },
  },

  methods: {
    can(permission_name) {
      return this.permissions.indexOf(permission_name) !== -1;
    },

    resetSearch() {
      this.search = ""
      this.filter = "title"
      this.postsGrid(1, this.search, this.filter)
    },

    clearBody(){
        this.post.body = ""
        this.initialValue = ""
    },

    resetModal() {
      this.roles = []
      this.share.studios = []
      this.post.roles = []
      this.selected_roles = []
      this.selected_option = null
      this.selected_studios = null
      this.post.title = null
      this.post.body = null
      this.post.is_shared = 0
      this.post.wiki_category_id = null
      this.post.tag = null
      this.role_radio = ""
      this.category.name = ""
      this.editMode = false
      this.initialValue = ""
    },
    getCategories() {
      let vm = this
      axios.get(route("wiki.categorydata")).then(function (response) {
        let res = response.data;
        vm.allcategories = res.categories;
      }).catch((error) => {
        console.log(error);
      });
    },
    showModal() {
      // this.$refs['my-modal'].show()
      $("#exampleModal").modal('show')
      this.$store.dispatch("GET_ROLES")
      this.getCategories()
      this.resetModal()
    },
    storePost() {
      let url = route("wiki.store")
      this.isCreating = true
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
          this.isCreating = false
        }
      }).catch((error) => {
        if (error.response.status === 422) {
          this.errors = error.response.data.errors;
          Toast.fire({
            icon: "error",
            title: "Por favor, complete los campos requeridos",
          });

          this.isCreating = false
        }
      })
    },
    updatePost(id) {
      let url = route("wiki.updatepost", {id: id})
      this.isCreating = true

      axios.put(url, this.post).then((response) => {
        this.isCreating = false
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
          this.post = response.data.post
        }
        // Fire.$emit("AfterCreated")
      }).catch((error) => {
        console.log(error)
        this.isCreating = false
      })
    },

    onCopy: function (e) {
      this.copiedText = "¡Copiado!"

      setTimeout(() => {
        this.copiedText = "Copiar texto"
      }, 2500);
    },
    onError: function (e) {
      this.copiedText = "¡Texto no pude ser copiado!"
      setTimeout(() => {
        this.copiedText = "Copiar texto"
      }, 2500);
    },

    truncateText(text) {
      if (text.length > 100) {
        return `${text.substr(0, 100)}...`
      }
    },
    truncateTextLong(text) {
      if (text.length > 100) {
        return `${text.substr(0, 500)}...`
      }
    },

    paginate(page, search, filter) {
      this.pagination.current_page = page;
      this.postsGrid(page, search, filter);
    },
    postsGrid(page, search, filter) {
      let vm = this;
      this.show = true
      const url = route("wiki.wikidata", { page:page, search:search, filter:filter});
      //"/wiki/wikidata?page=" + page + "&search=" + search + "&filter=" + filter
      axios.get(url).then(function (response) {
        vm.postsArray = response.data.posts;
        vm.pagination = response.data.pagination;
        vm.show = false
      }).catch((error) => {
        console.log(error);
        vm.show = false
      });

      this.categoryposts()
    },
    postDetails(id) {
      this.$refs['modal-details'].show()
      let url = route("wiki.show", {id:id})
      this.showDetails = true
      axios.get(url).then((response) => {
        this.details = response.data.post
        this.showDetails = false
      })
    },
    showCategories() {
      this.$refs["modal-categories"].show()
      this.editMode = false;
      this.title = "Crear categoria";

      Fire.$on("CategoryCreated", () => {
        this.getCategories();
      });
      this.getCategories();
    },
    editPost(id) {
      $("#exampleModal").modal('show')
      this.$store.dispatch("GET_ROLES")
      this.getCategories()
      let url = route("wiki.editPost", {id:id})
      this.editMode = true
      axios.get(url).then((response) => {
        this.post.id = response.data.id
        this.post.title = response.data.title
        this.post.body = response.data.body
        this.post.is_shared = response.data.is_shared
        this.post.wiki_category_id = response.data.wiki_category_id
        this.post.tag = response.data.tags
        this.selected_option = 2
        this.post.roles = _.map(response.data.roles, function (role) {
          return role.id;
        });
      })
    },
    shareModal(id) {
      this.$refs['modal-share'].show()
      let url = route("wiki.show", {id:id})
      this.showShare = true
      if (this.selected_studios === 2)
      {
        this.checkAllStudios = true;
      }

      axios.get(url).then((response) => {
        this.showShare = false

        this.share.id = response.data.post.id
        this.share.wiki_category_id = response.data.post.category_id
        this.share.is_shared = 1
      })

      this.getStudios()
    },
    sharePost() {
      this.isCreating = true;
      this.share.studios = this.select_studios

      axios.post(route("wiki.share"), this.share).then((response) => {
        this.isCreating = false;
      }).catch((error) => {
        this.isCreating = false;
      })
    },
    deletePost(id) {
      let url = route("wiki.deletepost", {id:id})

      SwalGB.fire({
        title: "¿Está seguro?",
        text: "Eliminará esta noticia. ¡Esta acción no podrá ser revertida!",
        icon: "question",
      }).then(result => {
        if (result.value) {
          this.$Progress.start();
          axios.delete(url)
              .then(() => {
                Toast.fire("Deleted!", "El post ha sido eliminado.", "success");
                Fire.$emit("AfterCreated");
                this.$Progress.finish();
              })
              .catch(() => {
                Swal.fire("Failed!", "El post no ha podido ser eliminado.", "info");
              });
        }
      });
    },
    categoryposts() {
      let url = route("wiki.categoryPosts")
      axios.get(url).then((response) => {
        console.log(response)
        this.allcategoriespost = response.data.catposts
      })
    },
    createCategory() {
      let url = route("wiki.storecategory")
      this.isCreating = true

      axios.post(url, this.category).then((response) => {
        this.isCreating = false

        if (response.data.code === 403) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        } else if (response.data.code === 422) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        } else {
          Fire.$emit("CategoryCreated");
          this.category.name = ""

          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
      }).catch((error) => {
        console.log(error.response)
        this.isCreating = false
        if (error.response.status === 422) {
          Toast.fire({
            icon: 'error',
            title: "Ha ocurrido un error"
          });
        }
      })
    },
    editCategory(id) {
      let url = route("wiki.editCategory", {id:id})
      this.category.id = id
      this.editMode = true

      axios.get(url).then((response) => {
        this.category = response.data.category
      })
    },
    updateCategory() {
      let url = ("wiki.updatecategory", {id : this.category.id})
      this.isCreating = true

      axios.put(url, this.category).then((response) => {
        this.isCreating = false
        console.log(response)
        if (response.data.code === 403) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        } else if (response.data.code === 422) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        } else {
          Fire.$emit("CategoryCreated");
          this.category.name = ""
          this.editMode = false

          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
      }).catch((error) => {
        this.isCreating = false
        if (error.response.status === 422) {
          Toast.fire({
            icon: 'error',
            title: "Ha ocurrido un error"
          });
        }
      })
    },
    deleteCategory(id) {
      SwalGB.fire({
        text: "¡Eliminará esta categoria y todos los posts asociados a ella!",
        icon: "question"
      }).then((result) => {
        if (result.value) {
          axios.delete(route("wiki.deletecategory", {id:id})).then((response) => {
            Toast.fire({
              icon: response.data.icon,
              title: response.data.msg
            });
            Fire.$emit("CategoryCreated");
          })
              .catch((error) => {
                Toast.fire({
                  icon: response.data.icon,
                  title: response.data.msg
                });
              });
        }
      });
    },
    getStudios(){
      let url = route("wiki.getStudios")

      axios.get(url).then((response) => {
       this.studios = response.data
      })
    },
    
    ejecutar(element_id) {
      let aux = document.createElement("div");
      aux.setAttribute("contentEditable", true);
      aux.innerHTML = document.getElementById(element_id).innerHTML;
      aux.setAttribute("onfocus", "document.execCommand('selectAll', false, null)");
      document.body.appendChild(aux);
      aux.focus();
      document.execCommand("copy");
      document.body.removeChild(aux);

      this.copiedText = "¡Copiado!"
      setTimeout( () => {
        this.copiedText = "Copiar texto"
      }, 2500);
    }
  },

  created() {
    this.postsGrid(1, this.search, this.filter);
    this.getCategories();

    Fire.$on("AfterCreated", () => {
      this.postsGrid(1, this.search, this.filter);
    });
  },

  mounted() {

  }
};
</script>

<style scoped>
.list-group-item span {
  border: solid #222;
  border-width: 0 1px 1px 0;
  display: inline;
  cursor: pointer;
  padding: 3px;
  position: absolute;
  right: 0;
  margin-top: 10px;
}

.cat_links:hover {
  text-decoration: none;
}
</style>

