<template>
 <span>
   <a href="#" @click.prevent="viewDetails">Ver más detalles</a>
   <!--news view details modal-->
   <b-modal @hide="resetModal" centered no-close-on-backdrop scrollable size="lg" header-bg-variant="primary" header-close-content="" ref="newsDetails" hide-footer :title="'Usted esta leyendo: ' + news.title">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <span><strong>Publicado por: </strong>{{ news.created_by }}, {{ moment(news.created_at).fromNow() }}</span>
          </div>

          <div class="col-md-12 my-2">
            <span><strong>Compartido con: </strong> {{ news.roles_count }} roles</span>
            <ul class="list-inline">
              <li class="text-info font-weight-bold list-inline-item" v-for="(role, i) in news.roles" :key="i">{{ role.role_name }}</li>
            </ul>
            <hr>
          </div>

           <div class="col-md-12">
            <img :src="news.file" :alt="news.title" class="img-fluid shadow rounded">
          </div>
          
          <div class="col-md-12 my-2">
            <p>{{ news.description }}</p>
            <hr>
          </div>

          <div class="col-md-12">
            <div class="row">
              <div class="col-md-5">
                 <b-button :variant="userLiked ? 'success' : 'info'" size="sm" @click.prevent="like">
                   <i class="fas fa-thumbs-up"></i> <span v-text="userLiked ? 'Te gusta esta noticia' : 'Me gusta'"></span>
                 </b-button>
                  <b-button @click="commentModal()" variant="primary" size="sm">
                    <i class="fas fa-comment"></i> Comentar
                  </b-button>
              </div>
              
              <div class="col-md-7" v-if="likes.length">
                 <ul class="list-inline">
                   <li class="list-inline-item">
                     A <strong class="text-info">{{ firstLikes }}</strong> <span v-if="countLikes > 3"> y
                     <a href="#" @click="whoLikes">{{ countLikes }} personas</a> más </span> le gusta esta noticia
                   </li>
                 </ul>
              </div>
            </div>

            <hr>
          </div>
        </div>
                        
        <div class="row" v-if="commentsData.length">
          <div class="col-lg-12 col-md-12">
              <div class="card border-0 shadow">
                  <div class="card-body">
                      <h4 class="card-title text-center pb-2">Comentarios más recientes</h4>

                    <div class="container">
                      <div class="row">
                        <div class="col-lg-12 col-md-12" v-for="(comment, i) in commentsData" :key="i">

                            <hr v-if="commentsData.length >= 1">
                          
                            <div class="media">
                               <a class="mr-3" href="#">
                                <img :src="'/assets/img/avatars/' + comment.avatar" width="30" height="30" class="mr-3 rounded-circle" alt="sdsd">
                               </a>
                                <div class="media-body">
                                  <h6 class="mt-0 text-info">{{ comment.name }}</h6>
                                  {{ comment.comment }} <br><small class="text-muted help-block">{{ moment(comment.created_at).fromNow() }}</small>
                                  <div class="row">
                                    <div class="col-md-12 col-lg-12 col-sm-12 col-xl-12">
                                      <b-button class="float-right" @click="replyModal(comment.comment_id)" variant="link" size="sm">
                                        <i class="fas fa-comment"></i> responder
                                      </b-button>
                                    </div>
                                  </div>
                                  <div class="media mt-1 mb-4" v-for="reply in comment.replies">
                                    <a class="mr-3" href="#">
                                      <img :src="'/assets/img/avatars/' + comment.avatar" width="30" height="30" class="mr-3 rounded-circle" alt="sdsd">
                                    </a>
                                    <div class="media-body">
                                      <h6 class="mt-0 text-primary">{{ reply.name }}</h6>
                                      {{ reply.comment }}
                                    </div>
                                  </div>
                                </div>
                            </div>
                        </div>

                        <infinite-loading v-if="commentsData.length >= 6" @distance="1" @infinite="infiniteHandler"></infinite-loading>
                      </div>
                    </div>
                  </div>
              </div>
          </div>
        </div>
      </div>
    </b-modal>
   <!--news view details modal-->

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

   <b-modal centered header-bg-variant="primary" hide-footer header-close-content="" ref="likes-modal" id="likes-modal" :title="'Personas que les gusta: ' + news.title">
      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-12 col-lg-12">
            <ul class="list-group list-group-flush|">
              <li v-for="like in likes" class="list-group-item d-flex justify-content-between align-items-center py-1">
               {{ like.first_name + " " + like.last_name }}
                <div class="image-parent">
                    <img :src="'/assets/img/avatars/' + like.avatar" class="img-fluid rounded-circle" width="30" :alt="like.first_name">
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </b-modal>
 </span>
</template>

<script>
export default {
  name: "NewsDetails",
  props: ['id', 'user'],
  data() {
    return {
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
      likedata: {
        action: null,
        news_id: null
      },
      comment: {
        body: "",
        news_id: null,
        reply_id: null
      },
      moment: moment,
      userLiked: false,
      page: 1
    }
  },

  methods: {
    resetModal() {
      this.comment.news_id = null
      this.comment.reply_id = null
      this.commentsData = []
      this.page = 1
    },

    resetComment() {
      this.comment.body = ""
    },

    viewDetails() {
      this.$refs['newsDetails'].show()
      let url = "news/show/" + this.id

      axios.get(url).then((response) => {
        this.news = response.data.news
      })

      this.getLikes()
      this.getComments()
    },

    like() {
      let url = "news/storeLike"
      this.likedata.news_id = this.id
      this.likedata.action = "like"

      axios.post(url, this.likedata).then((response) => {
        Fire.$emit("newsLiked")
        if (responde.data.code === 200) {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        } else {
          Toast.fire({
            icon: response.data.icon,
            title: response.data.msg
          });
        }
      })
    },

    getLikes() {
      let url = "news/getLikes/" + this.id
      axios.get(url).then((response) => {
        this.likes = response.data.allLikes
        this.firstLikes = response.data.likes
        this.countLikes = response.data.count
        this.userLiked = response.data.user_liked
      })
    },

    whoLikes() {
      this.$refs['likes-modal'].show()
    },

    commentModal() {
      this.$refs['comment-modal'].show()
      this.comment.news_id = this.id
      this.comment.reply_id = 0
    },

    replyModal(id) {
      this.$refs['comment-modal'].show()
      this.comment.reply_id = id
      this.comment.news_id = this.id
    },

    saveComment() {
      let url = "news/storeComment"

      axios.post(url, this.comment).then((response) => {
        Fire.$emit("newsCommented")
        this.$refs['comment-modal'].hide()
        console.log(response)
      })
    },

    getComments() {
      let url = "news/getComments/" + this.id + "?page=" + this.page
      axios.get(url).then((response) => {
        this.commentsData = response.data.comments
      })
    },

    infiniteHandler($state) {
      let url = "news/getComments/" + this.id + "?page=" + this.page
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
    }
  },

  mounted() {
    Fire.$on("newsCommented", () => {
      this.getComments()
      this.page = 1
    });
    Fire.$on("newsLiked", () => {
      this.getLikes()
    });
  }
}
</script>

<style scoped>
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