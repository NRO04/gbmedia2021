<template>
<div>
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
          <ul class="list-group list-group-flush">
            <li v-for="like in likes" class="list-group-item d-flex justify-content-between align-items-center py-1" :key="like.id">
              {{ like.user }}
              <div class="image-parent">
                <img :src="'/assets/img/avatars/' + like.avatar" class="img-fluid rounded-circle" width="30" :alt="like.first_name">
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </b-modal>
</div>
  <!-- modals -->
</template>

<script>
export default {
name: "test"
}
</script>

<style scoped>

</style>