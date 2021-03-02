<template>
  <div id="news">
    <b-overlay :show="show" no-wrap></b-overlay>

    <div class="container-fluid" v-if="newsArray.length && !show">
      <b-row class="justify-content-center">
        <b-col md="2" lg="2" sm="12">
          <h2>Noticias</h2>
        </b-col>
        <b-col md="10" sm="12" lg="10">
          <b-row class="justify-content-between">
            <b-col md="10" sm="12" lg="10">
              <input type="text" class="form-control form-control-sm" placeholder="Buscar noticias..." v-model="search" @keyup.enter="newsGrid(1, search, filter)">
            </b-col>
            <b-col md="2" sm="12" lg="2">
              <create v-if="can('news-create')"></create>
            </b-col>
          </b-row>
        </b-col>
      </b-row>


      <div class="row mt-5" v-if="can('news-view')">
        <div class="col-md-3 col-sm-12 col-lg-3" v-for="(news, i) in newsArray" :key="i">
          <div v-if="newsArray.length > 0">
            <div class="card border-0 shadow">
              <img class="card-img img-fluid" v-if="news.extension == 'IMG' || news.extension == 'IMG' || news.extension == 'IMG'" :src="news.file" :alt="news.title"
                   height="200" width="300">
              <video class="card-img" v-if="news.extension == 'VID'" height="200" width="300" style="min-width:100%; object-fit: cover;">
                <source :src="news.file" type="video/mp4">
                Your browser does not support the video tag.
              </video>
              <div class="card-img-overlay">
                <button v-if="can('news-edit') && news.canShare" class="btn btn-sm btn-success" @click="shareModal(news.id)">
                  <i class="fas fa-share-alt"></i> Compartir
                </button>
                <button v-if="can('news-delete')" class="btn btn-sm btn-danger" @click="deleteNews(news.id)">
                  <i class="fas fa-trash-alt"></i> Eliminar
                </button>
              </div>
              <div class="card-body">
                <h4 class="card-title">
                  <text-highlight :queries="search">{{ news.title }}</text-highlight>
                </h4>
                <small class="text-muted cat">
                  <i class="far fa-clock text-info"></i> <span class="text-capitalize">{{ moment(news.created_at).format('DD-MMM-YYYY') }}</span>
                  <i class="fas fa-users text-info"></i> <span>{{ news.roles_count }}</span>
                  roles
                </small>
                <p class="card-text">
                  <span v-html="news.body.substring(0, 100) + '...'"></span>
                  <!-- <NewsDetails :id="news.id" :user="user"></NewsDetails>-->
                  <a href="#" @click.prevent="viewDetails(news.id, news)">Ver más detalles</a>
                </p>
              </div>
              <div class="card-footer text-muted d-flex justify-content-between bg-transparent border-top-0">
                <div class="views">
                  <i class="far fa-eye text-info"></i> {{ news.views }} <span v-text="news.views >= 2 || news.views === 0 ? 'vistas' : 'visto'"></span>
                </div>
                <div class="stats">
                  <i class="far fa-thumbs-up text-info"></i> {{ news.likes_count }} <span v-text="news.likes_count >= 2 || news.likes_count === 0 ? 'likes' : 'like'"></span>&nbsp;
                  <i class="far fa-comment text-primary"></i> {{ news.comments_count }} <span
                    v-text="news.comments_count >= 2 || news.comments_count === 0 ? 'comentarios' : 'comentario'"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="col-md-12 col-lg-12 col-sm-12 text-center py-3" v-if="pagination.total > 20">
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
              <li class="page-item" v-if="pagination.current_page > 1">
                <a class="page-link" href="#" tabindex="-1" @click.prevent="paginate(pagination.current_page - 1,search,filter)">
                  <i class="fas fa-angle-left"></i>
                </a>
              </li>
              <li class="page-item" v-for="page in pagesNumber" :key="page"
                  :class="[page === isActive ? 'active' : '']">
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

    <div class="container mt-3" v-else-if="!newsArray.length && !show">
      <div class="row text-center">
        <div class="col-md-12">
          <div class="container">
            <div class="row">
              <div class="col-md-12">
                <h3>No hay noticias por el momento...</h3>
                <p>¡Empieza a crear las tuyas!</p>
                <div class="row">
                  <div class="col-md-12">
                    <create></create>
                    <b-button variant="primary" size="sm" @click="resetSearch" v-if="search !== ''">
                      <i class="fas fa-undo"></i> Volver a todas las noticias
                    </b-button>
                  </div>
                  <div class="col-md-12">
                    <img src="images/svg/undraw_no_data_qbuo.svg" alt="empty page" height="500"
                         width="500"/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- modals -->
    <!--news view details modal-->
    <b-modal @hide="resetModal" centered no-close-on-backdrop scrollable size="xl" header-bg-variant="dark" header-close-content="" ref="newsDetails" hide-footer
             :title="'Usted esta leyendo: ' + news.title">
      <b-overlay :show="show" no-wrap></b-overlay>

      <div class="container" v-if="!show">
        <div class="row">
          <div class="col-md-12">
            <div class="row justify-content-between">
              <div class="col-md-8">
                <span><strong>Publicado por: </strong>{{ news.created_by }}, <small class="text-muted">{{ news.created_at }}</small></span>
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <hr>
              <div class="row justify-content-between">
                <div class="col-md-6">
                  <ul class="list-inline">
                    <li class="list-inline-item">
                      Compartido con roles:
                      <span class="text-info font-weight-bold ">{{ news.roles_name }}</span>
                      <span v-if="news.roles_count >= 1">
                        <a href="#" v-b-popover.hover.top="news.all_roles">
                          y {{ news.roles_count }} roles mas
                        </a>
                      </span>
                    </li>
                  </ul>
                </div>
                <div class="col-md-6" v-if="news.studios_name !== ''">
                  <ul class="list-inline">
                    <li class="list-inline-item">
                      Compartido con estudios:
                      <span class="text-info font-weight-bold ">{{ news.studios_name }}
                        <span v-if="news.share_count >= 1">
                          <a href="#" v-b-popover.hover.top="news.all_studios">
                            y {{ news.share_count }} estudios mas
                          </a>
                        </span>
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            <hr>
          </div>

          <div class="col-md-5">
            <img v-if="news.extension == 'IMG' || news.extension == 'IMG' || news.extension == 'IMG'" :src="news.file" :alt="news.title"
                 class="img-fluid shadow rounded">
            <vue-plyr ref="plyr" :options="playerOptions" class="shadow">
              <video v-if="news.extension == 'VID'" width="100%" height="500" controls style="min-width:100%; object-fit: cover;">
                <source :src="news.file" type="video/mp4">
                Your browser does not support the video tag.
              </video>
            </vue-plyr>
          </div>

          <div class="col-md-7">
            <p v-html="news.description"></p>
          </div>

          <div class="col-md-12">
            <hr>
            <div class="row">
              <div class="col-md-5">
                <b-button :variant="userLiked ? 'success' : 'info'" size="sm" @click.prevent="like(news.news_id)">
                  <i class="fas fa-thumbs-up"></i> <span v-text="userLiked ? 'Te gusta esta noticia' : 'Me gusta'"></span>
                </b-button>
                <b-button @click="commentModal(news.news_id)" variant="primary" size="sm">
                  <i class="fas fa-comment"></i> Comentar
                </b-button>
              </div>

              <div class="col-md-7" v-if="likes.length > 0">
                <ul class="list-inline">
                  <li class="list-inline-item text-info">
                    A <strong class="text-info">{{ firstLikes }}</strong> <span v-if="countLikes >= 1"> y
                     <a href="#" @click="whoLikes" v-b-popover.hover.top="tipLikes + ' les gusta esta noticia'">{{ countLikes }} personas</a> más </span> les gusta esta noticia
                  </li>
                </ul>
              </div>
            </div>

            <hr>
          </div>
        </div>

        <!--<div class="row" v-if="comment.news_id != null">
          <div class="col-lg-12 col-md-12 py-2">
            <div class="card border-0 shadow">
              <div class="card-body">
                <b-form-group label="Escriba su comentario: ">
                  <b-form-textarea id="textarea" v-model="comment.body" placeholder="Que estas pensando?..." rows="1" max-rows="20"></b-form-textarea>
                </b-form-group>
              </div>
              <div class="card-footer">
                <b-button @click.prevent="saveComment" type="button" class="float-right" size="sm" variant="success" :disabled="comment.body == ''">
                  <i class="fas fa-save"></i> Comentar
                </b-button>
              </div>
            </div>
          </div>
        </div>-->

        <div class="row" v-if="commentsData">
          <div class="col-lg-12 col-md-12">
            <div class="card border-0 shadow">
              <div class="card-body">
                <h4 class="card-title text-center pb-2">Comentarios más recientes</h4>

                <div class="container">
                  <div class="row">
                    <div class="col-lg-12 col-md-12" v-for="(commentx, i) in commentsData" :key="i">

                      <hr v-if="commentsData.length >= 1">

                      <div class="media">
                        <img :src="commentx.avatar" width="20" height="20" class="mr-3 rounded-circle zoom-img" alt="sdsd">
                        <div class="media-body">
                          <h6 class="mt-0 text-info">{{ commentx.name }}
                            <small class="text-muted"> &#8226; {{ commentx.studio }}</small>
                            <small class="text-muted"> &#8226; {{ commentx.date }}</small>
                          </h6>
                          <small>
                            {{ commentx.comment }} <span class="mx-2"><a href="#" @click.prevent="replyCommentBox(i)">Responder</a></span>
                          </small>
                          <div class="row">
                            <div class="col-md-12 col-lg-12 col-sm-12 col-xl-12">
                              <div class="py-2" v-if="replyCommentBoxes[i]">
                                <b-form-textarea id="textarea" v-model="comment.body" placeholder="Que estas pensando?..." rows="1" no-resize></b-form-textarea>
                                <b-button class="float-right mt-1" @click="replyModal(commentx.comment_id, commentx.news_id)" variant="success" size="sm">
                                  <i class="fas fa-comment"></i> Publicar
                                </b-button>
                              </div>
                            </div>
                          </div>
                          <div class="media my-3" v-for="reply in commentx.replies" :key="reply.id">
                            <img :src="reply.avatar" width="20" height="20" class="mr-3 rounded-circle zoom-img" alt="sdsd">
                            <div class="media-body">
                              <h6 :class="user.id === reply.logged_user_id ? 'text-info' : 'text-secondary'">{{ reply.name }}
                                <small class="text-muted"> &#8226; {{ commentx.studio }}</small>
                                <small class="text-muted">&#8226; {{ reply.date }}</small>
                              </h6>
                              <small>{{ reply.comment }}</small>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  <!--<infinite-loading v-if="commentsData.length >= 6" @distance="1" @infinite="infiniteHandler"></infinite-loading>-->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </b-modal>
    <!--news view details modal-->

    <!--post share modal-->
    <b-modal @hide="resetModal" size="lg" centered hide-header-close="" variant="dark" ref="modal-share" :title="'Usted esta compartiendo: ' + share.title">
      <b-form>
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
        <b-button @click.prevent="shareNews()" type="button" class="float-right" size="sm" variant="success">
          <i class="fas fa-share-alt"></i> Compartir
        </b-button>
      </template>
    </b-modal>
    <!--post share modal-->

    <b-modal @hide="resetComment" centered header-bg-variant="primary" header-close-content="" ref="comment-modal" id="comment-modal" :title="'Comentando en: ' + news.title">
      <div class="row">
        <div class="col-md-12">
          <b-form>
            <b-form-group label="Escriba su comentario: ">
              <b-form-textarea v-model="comment.body" rows="6" max-rows="20" placeholder="Escriba el comentario..."></b-form-textarea>
            </b-form-group>
          </b-form>
        </div>
      </div>
      <template v-slot:modal-footer>
        <b-button @click.prevent="saveComment" type="button" class="float-right" size="sm" variant="success" :disabled="comment.body == ''">
          <i class="fas fa-save"></i> Comentar
        </b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import create from "./partials/create";
import VuePlyr from "vue-plyr"

export default {
  name: "news",
  props: ['user', 'permissions'],
  data() {
    return {
      //news arrays
      newsArray: [],
      news_details: {},
      //pagination
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

      //Variable
      show: false,
      moment: moment,

      news: {
        title: null,
        description: null,
        created_by: null,
        roles_count: null,
        created_at: null,
        file: null,
        roles: []
      },
      likes: [],
      commentsData: [],
      firstLikes: null,
      countLikes: null,
      show_count: null,
      tipLikes: null,
      likedata: {
        action: null,
        news_id: null
      },
      comment: {
        body: "",
        news_id: null,
        reply_id: null
      },
      userLiked: false,
      page: 1,
      replyCommentBoxes: [],

      selected_studios: null,
      select_studios: [],
      studios: [],
      share: {
        studios: [],
        news_id: 0,
      }
    };
  },

  components: {
    create: create,
    VuePlyr: VuePlyr
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
    playerOptions() {
      return {
        playsinline: true,
        volume: 0,
        controls: ["mute", "volume","play-large"],
        debug: false,
      };
    },

    player() {
      return this.$refs.plyr.player
    },
  },

  methods: {
    can(permission_name) {
      return this.permissions.indexOf(permission_name) !== -1;
    },
    
    resetSearch() {
      this.search = ""
      this.filter = "title"
      this.newsGrid(1, this.search, this.filter)
    },

    paginate(page, search, filter) {
      this.pagination.current_page = page;
      this.newsGrid(page, search, filter);
    },

    shareNews() {
      // let url = "news/share"
      const url = route('news.share')
      this.share.studios = this.select_studios
      axios.post(url, this.share).then((response) => {
        console.log(response)
      })
    },

    shareModal(id) {
      this.$refs['modal-share'].show()
      // let url = "news/show/" + id
      const url = route('news.show', {id : id})
      if (this.selected_studios === 2) {
        this.checkAllStudios = true;
      }

      axios.get(url).then((response) => {
        this.share = response.data.news
      })

      this.getStudios()
    },

    newsGrid(page, search, filter) {
      this.show = true
      let vm = this;
      const url = route('news.getNews', {
        page, search,  filter,
      })
      axios.get(url).then(function (response) {
        let res = response.data;
        vm.newsArray = res.news;
        vm.pagination = res.pagination;
        vm.show = false
      }).catch((error) => {
        console.log(error);
        this.show = false
      });
    },

    deleteNews(id) {
      SwalGB.fire({
        title: "¿Está seguro?",
        text: "Eliminará esta noticia. ¡Esta acción no podrá ser revertida!",
        icon: "question",
        allowOutsideClick: false,
      }).then(result => {
        if (result.value) {
          const url = route('news.delete', {id : id})
          // let url = "news/delete/" + id;
          axios.delete(url)
              .then(() => {
                Toast.fire("Deleted!", "La noticia ha sido eliminada.", "success");
                Fire.$emit("newsCreated");
              })
              .catch(() => {
                Toast.fire("Failed!", "La noticia no ha podido ser eliminada.", "info");
              });
        }
      });
    },

    resetModal() {
      this.comment.news_id = null
      this.comment.reply_id = null
      this.commentsData = []
      this.replyCommentBoxes = []
      this.page = 1
      this.show = false
    },

    resetComment() {
      this.comment.body = ""
    },

    viewDetails(id, item) {
      this.$refs['newsDetails'].show()
      // let url = "news/show/" + id
      const url = route('news.show', {id : id})
      this.show = true
      this.news_details = item;
      axios.get(url).then((response) => {
        this.news = response.data.news
        this.show = false
      })
      this.news_details.views= this.news_details.views + 1;

      this.getLikes(id)
      Fire.$on("newsLiked", () => {
        this.getLikes(id)
      });

      this.getComments(id)
      Fire.$on("newsCommented", () => {
        this.getComments(id)
        this.page = 1
      });
    },

    like(id) {
      // let url = "news/storeLike"
      this.likedata.news_id = id
      this.likedata.action = "like"
      const url = route('news.storeLike')

      axios.post(url, this.likedata).then((response) => {
        if (response.data.code === 200) {
          Fire.$emit("newsLiked");
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
          this.news_details.likes_count = this.news_details.likes_count + 1;
          this.replyCommentBoxes = []
        } else {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
      })
    },

    getLikes(id) {
      // let url = "news/getLikes/" + id
      const url = route('news.getLikes', {id : id})
      axios.get(url).then((response) => {
        this.likes = response.data.allLikes
        this.firstLikes = response.data.likes
        this.countLikes = response.data.count
        this.show_count = response.data.show_count
        this.userLiked = response.data.user_liked
        this.tipLikes = response.data.likes_tip
      })
    },

    whoLikes() {
      this.$refs['likes-modal'].show()
    },

    commentModal(id) {
      this.$refs['comment-modal'].show()
      this.comment.news_id = id
      this.comment.reply_id = 0
    },

    replyModal(comment_id, news_id) {
      this.comment.reply_id = comment_id
      this.comment.news_id = news_id

      // let url = "news/storeComment"
      const url = route('news.storeComment')
      axios.post(url, this.comment).then((response) => {
        Fire.$emit("newsCommented")
        this.comment.body = ""
        this.comment.news_id = null
        this.comment.reply_id = null
        this.news_details.comments_count = this.news_details.comments_count + 1;
        Vue.nextTick(() => {
          this.replyCommentBoxes = []
        });
      })
    },

    saveComment() {
      // let url = "news/storeComment"
      const url = route('news.storeComment')
      axios.post(url, this.comment).then((response) => {
        Fire.$emit("newsCommented")
        this.comment.body = ""
        this.comment.news_id = null
        this.comment.reply_id = null
        this.news_details.comments_count = this.news_details.comments_count + 1;
        Vue.nextTick(() => {
          this.replyCommentBoxes = []
          this.$refs['comment-modal'].hide()
        });
      })
    },

    getComments(id) {
      // let url = "news/getComments/" + id + "?page=" + this.page
      const url = route('news.getComments', {id, page: this.page})
      axios.get(url).then((response) => {
        this.commentsData = response.data.comments
      })
    },

    infiniteHandler($state) {
      // let url = "news/getComments/" + this.id + "?page=" + this.page
      const url = route('news.getComments', {id: this.id, page: this.page})
      setTimeout(() => {
        this.page++;
        axios.get(url).then((response) => {
          if (response.data.comments.length > 1) {
            response.data.forEach(comment => this.commentsData.push(comment));
            $state.loaded()
          } else {
            $state.complete()
          }
        }).catch((error) => {
          console.log(error)
        })
      }, 500)
    },

    replyCommentBox(index) {
      if (this.user) {
        if (this.replyCommentBoxes[index]) {
          Vue.set(this.replyCommentBoxes, index, 0);
        } else {
          Vue.set(this.replyCommentBoxes, index, 1);
        }
      }
    },

    getStudios() {
      // let url = "news/studios"
      const url = route('news.getStudios')
      axios.get(url).then((response) => {
        this.studios = response.data
      })
    },
  },

  created() {
    this.newsGrid(1, this.search, this.filter);
    Fire.$on("newsCreated", () => {
      this.newsGrid(1, this.search, this.filter);
    });
    // Echo.private('Activity-Monitor').listen('.Activity-Monitor', (e) => {
    //   this.newsGrid(1, this.search, this.filter);
    // });
  },

  mounted() {
    console.log('mounted')
  },
};
</script>

<style scoped>
.card-img {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.card-title {
  margin-bottom: 0.3rem;
}

.card-body {
  position: relative;
}

.cat {
  display: inline-block;
  margin-bottom: 1rem;
}

.fa-users {
  margin-left: 1rem;
}

.card-footer {
  font-size: 0.8rem;
  position: relative;
}

.btn-link {
  font-weight: 400;
  color: #ebedef;
  text-decoration: none;
}

.btn-link:hover {
  color: #2eb85c;
  text-decoration: none;
}

.image-parent {
  max-width: 40px;
}
</style>
