<template>
  <div class="msg">
    <div class="alert alert-primary alert-dismissible fade show" role="alert" v-if="getMessage">
      {{ getMessage }}
      <button type="button" class="close" data-dismiss="alert" aria-label="close">
        <span>&times;</span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "Message",
  data() {
    return {
      hide: true
    }
  },
  computed: {
    getMessage() {
      return this.$store.getters["trainings/getMessage"]
    }
  }
}
</script>

<style scoped>

</style>