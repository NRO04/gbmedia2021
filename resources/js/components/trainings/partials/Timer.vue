<template>
  <div class="timer">
    <div class="row">
      <div class="col-md-12">
        <!--<strong><i class="fas fa-clock"></i> {{ label }}</strong>-->
        <b-progress height="1.5rem">
          <b-progress-bar :class="state" :style="{ width: tick + '%'}">
            <span><strong>{{ progressLabel }}</strong></span>
          </b-progress-bar>
        </b-progress>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Timer",
  props: ['timePeriod'],
  data() {
    return {
      label: '',
      progressLabel: '',
      state: '',
      tick: 0,
      timer: null
    }
  },

  mounted() {
    let tp = this.timePeriod;
    let i = 0;
    let sec = 59;
    let mins = tp - 1;
    let milisec = tp * 60000 / 1;
    milisec = milisec - tp * 1000;

    this.timer = setInterval(() => {
      i += 1000
      this.tick = i / milisec * 100;
      sec--;
      if (sec === 0) {
        mins = mins - 1;
        sec = 59;
      }

      if (mins === 0) {
        this.label = 'sólo quedan ' + sec + ' segundos';
        this.progressLabel = 'sólo quedan ' + sec + ' segundos';
      } else {
        this.label = 'Tiempo: ' + mins + ':' + sec
        this.progressLabel = 'Tiempo restante: ' + mins + ' minutos y ' + sec + ' segundos'
      }

      if (mins < 2) {
        this.state = 'bg-warning'
      }

      this.$store.dispatch("TIMER_TICKS", [this.timer, mins + ":" + sec])
    }, 1000);

    setTimeout(() => {
      clearTimeout(this.timer);
      this.label = "Se acabó el tiempo"
      this.progressLabel = "Se acabó el tiempo"
      this.state = 'bg-danger'

      SwalGB.fire({
        title: "¡Se acabó el tiempo!",
        icon: 'warning',
        showCancelButton: false,
      }).then((result) => {
        if (result.isConfirmed) {
          location.reload()
        }
      })
    }, milisec);
  }
}
</script>

<style scoped>

</style>