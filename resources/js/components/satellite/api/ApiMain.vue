<template>
  <div class="row">
    <div class="col-lg-12">
      <b-overlay :show="show" rounded="sm" variant="dark" opacity="0.8">
        <template #overlay>
          <div class="text-center">
            <b-icon icon="stopwatch" variant="info" font-scale="3" animation="fade"></b-icon>
            <p id="cancel-label">Espere por favor...</p>
          </div>
        </template>
        <div class="card">
          <div class="card-header">
            <div class="row justify-content-between">
              <div class="col-lg-3">
                <span class="span-title" v-if="view_api === 1">API Jasmin</span>
                <span class="span-title" v-else-if="view_api === 2">API Chaturbate</span>
                <span class="span-title" v-else>Buscar API</span>
              </div>
              <div class="col-lg-4">
                <b-form-select v-model="view_api" class="mb-3" size="sm">
                  <template #first>
                    <b-form-select-option :value="null" disabled>-- Seleccione tipo de api --</b-form-select-option>
                  </template>
                  <b-form-select-option :value="1">Jasmin</b-form-select-option>
                  <b-form-select-option :value="2">Chaturbate</b-form-select-option>
                </b-form-select>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div v-if="view_api == null" class="alert alert-success text-center font-weight-bold" role="alert">
              Seleccione la opcion por la cual desea filtrar el API
            </div>
            <div v-if="view_api == 1">
              <jasmin-api></jasmin-api>
            </div>
            <div v-if="view_api == 2">
              <chaturbate-api></chaturbate-api>
            </div>
          </div>
        </div>
      </b-overlay>
    </div>

  </div>
</template>

<script>
export default {
  name: "ApiMain",
  data() {
    return {
      view_api: null,
      isBusy: false,
      show: false,
    }
  }
}
</script>

<style scoped>

</style>
