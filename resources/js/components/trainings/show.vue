<template>
  <div class="training">
    <div class="row">
      <div class="col-md-10 offset-md-1">
        <div class="card border-0 shadow">
          <div class="card-header border-bottom-0">
            <span>
              <span class="h4">Viendo: {{ training.title }}</span>
              <span class="float-right text-muted">
                Publicado en {{ moment(training.created_at).format("DD-MMM-YYYY") }} a las {{ moment(training.created_at).format("hh:mm") }}
              </span>
            </span>
          </div>
          <div class="card-body">
            <b-row>
              <b-col md="12" lg="12" sm="12" v-if="!userCompleted">
                <b-alert show variant="danger">
                  Debe finalizar el video en su totalidad, para que quede registrado como concluido a su jefe directo. No olvide dar clic al botón finalizar capacitación.
                </b-alert>
              </b-col>
              <b-col md="12" lg="12" sm="12" v-if="userCompleted">
                <b-alert show variant="success">
                  ¡Felicitaciones, Ya has completado esta capacitacion! ({{ moment(completed_date).format("DD-MMM-YYYY") }}
                    a las {{ moment(completed_date).format("hh:mm") }})
                </b-alert>
              </b-col>
              
              <b-col md="7" lg="7" sm="12" class="py-2">
                <vue-plyr :emit="['ended']" @ended="endedAction" ref="plyr" :options="playerOptions" class="shadow">
                  <video :poster="training.image_url" width="100%" height="100%" :src="training.video_url">
                    <source :src="training.video_url" type="video/mp4"/>
                  </video>
                </vue-plyr>
              </b-col>

              <b-col md="5" lg="5" sm="12">
               <b-row class="align-content-center align-items-center justify-content-center">
                 <b-col>
                   <p class="card-text">
                     {{ training.description }}
                   </p>
                 </b-col>
               </b-row>
              </b-col>
            </b-row>
          </div>
          <div v-if="showButton">
            <div class="card-footer border-top-0" v-if="training.has_test !== 0">
              <b-button variant="success" v-if="!userCompleted" class="float-right" @click.prevent="onFinish(training.id)"><i class="fas fa-check"></i> Finalizar capacitación</b-button>
              <b-button id="toggle-btn" v-if="userCompleted && !test_completed" class="float-right" variant="success" v-else @click="toggleModal(training.id)"><i class="fas fa-check"></i> Realizar prueba</b-button>
            </div>
            <div class="card-footer border-top-0" v-if="training.has_test === 0">
              <b-button variant="success" class="float-right" @click.prevent="onFinish(training.id)"><i class="fas fa-check"></i> Finalizar capacitación</b-button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <b-modal ref="my-modal" header-bg-variant="primary" centered  no-close-on-backdrop header-close-content="" size="lg" :title="'Realizando prueba para: '+training.title">
      <timer v-if="timePeriod > 0" :time-period="timePeriod"/>

      <b-row class="pt-3">
          <b-col md="12" sm="12" lg="12">
            <div class="card shadow border-0">
              <div class="card-header border-bottom-0">
                <strong>{{currentQuestionNo}}.</strong> {{getQuestions.question_title}}
              </div>
              <div class="card-body">
                <form>
                  <div class="form-group">
                    <b-list-group>
                      <b-list-group-item v-for="answer in getQuestions.answers" :key="answer.id">
                        <div class="custom-control custom-radio">
                          <input type="radio" :id="answer.id" name="option" class="custom-control-input" :value="answer.id" v-model="option">
                          <label class="custom-control-label" :for="answer.id"> {{answer.option_title}}</label>
                        </div>
                      </b-list-group-item>
                    </b-list-group>
                  </div>
                </form>
              </div>
            </div>
          </b-col>
        </b-row>

      <template v-slot:modal-footer>
        <div class="w-100">
          <p class="float-left">
            <span class="text-muted">
              {{getTotalQuestions}} preguntas en total
            </span>
          </p>
          <template v-if="currentQuestionNo + 1 > totalQuestions">
            <b-button size="sm" variant="success" class="float-right" @click="finishQuestionnaire">
              Finalizar prueba
            </b-button>
          </template>
          <template v-else>
            <b-button type="button" :disabled="option == null" class="float-right" size="sm" variant="success" @click="loadNextQuestion">
              Enviar pregunta Nro {{ currentQuestionNo }}
            </b-button>
          </template>
        </div>
      </template>
    </b-modal>
  </div>
</template>

<script>
import VuePlyr from "vue-plyr"
import moment from "moment"
import "moment/locale/es"
import Timer from "./partials/Timer"

export default {
  name: "show",
  props: ['trainingid', 'user'],
  data() {
    return {
      moment: moment,
      tick: 0,
      totalQuestions: 0,
      currentQuestionNo: 1,
      option:null,
      optionId:null,
      question: null,
      questionId: null,
      questions: [],
      currentQuestion:[],
      currentQuestionId:null,
      answerIds:[],
      questionIds:[],
      label:'Time is running...',
      showButton: false
    }
  },

  components:{
    VuePlyr: VuePlyr,
    Timer: Timer,
  },

  methods:{
    endedAction: function(event) {
      console.log('end')
      this.showButton = true;
    },

    onFinish(id) {
      // let url = "finish/" + id;
      const url = route('training.finish_training', {id : id})

      axios.post(url).then((response) => {
        Fire.$emit("finishedTraining");
      });
    },

    toggleModal(id) {
      this.$store.dispatch("GET_TRAINING_TEST", id)
      this.$refs['my-modal'].toggle('#toggle-btn')
    },

    preparingQuestions(questionId,option){
      this.questionIds.push(questionId);
      this.answerIds.push(option);
    },

    loadNextQuestion(){
      if(this.currentQuestionNo + 1 > this.totalQuestions){
        this.preparingQuestions(this.currentQuestionId,this.option);
        return false;
      }
      if(this.option == null){
        return false;
      }
      else
      {
        this.preparingQuestions(this.currentQuestionId,this.option);
        this.$store.commit("UPDATE_NEXT_QUESTION",this.currentQuestionNo);
        this.currentQuestionNo +=1;
        this.option = null;
      }
    },

    finishQuestionnaire(){
      this.$store.dispatch("SEND_TEST_ANSWERS",[this.trainingid,this.questionIds,this.answerIds]);

      SwalGB.fire({
        icon: 'success',
        text: "Test finalizado!",
        title: "¡Felicitaciones!",
        showCancelButton: false,
      }).then((result) => {
        if (result.isConfirmed) {
          this.$refs['my-modal'].hide()
          Fire.$emit("finishedTraining")
        }
      })
    }
  },

  computed:{
    playerOptions() {
      return {
        playsinline: true,
        volume: 50,
        controls: ["mute", "volume","play-large"],
        debug: false,
      };
    },

    player() {
      return this.$refs.plyr.player
    },

    training(){
         return this.$store.getters.training
    },

    userCompleted(){
      return this.$store.getters.completed
    },
    completed_date(){
      return this.$store.getters.completed_date
    },
    test_completed(){
      return this.$store.getters.test_completed
    },

    trainingCompleted(){
      return this.$store.getters.trainingCompleted
    },

    trainingQuestionnaire(){
      return this.$store.getters.trainingQuestionnaire
    },

    timePeriod(){
      return this.$store.getters.getTimer
    },

    getTotalQuestions(){
      this.totalQuestions = this.$store.getters.trainingQuestionnaire.length;
      return this.$store.getters.trainingQuestionnaire.length;
    },

    getQuestions(){
      this.currentQuestionId = this.$store.getters.getCurrentQuestions.id;
      return this.$store.getters.getCurrentQuestions;
    },
  },

  mounted() {
    this.$store.dispatch("GET_TRAINING", this.trainingid)
    /*this.$store.dispatch("GET_USER_COMPLETED_TRAINING", this.trainingid)
    this.$store.dispatch("GET_COMPLETED_TRAINING", this.trainingid)*/

    Fire.$on("finishedTraining", () => {
      this.$store.dispatch("GET_TRAINING", this.trainingid)
      /*this.$store.dispatch("GET_USER_COMPLETED_TRAINING", this.trainingid)
      this.$store.dispatch("GET_COMPLETED_TRAINING", this.trainingid)*/
    });
  }
}
</script>

<style scoped>
.plyr__poster {
  background-size: cover;
}
video {
  object-fit: cover;
}
</style>