export default {
    state: {
        training: {},
        userComplete: {},
        trainingComplete: {},
        trainingQuestionnaire: [],
        currentQuestion: [],
        timePeriod: 10,
        currentTime: null,
        timerObj: null,
        events: [],
        models: [],
        users: [],
        roles: [],
        videographers: [],
        photographers: [],
        agendas: [],
        days: [],
        locations: [],
        AllLocations: [],
        attendances: [],
        attendance: {},
        completed: false,
        completed_date: null,
        test_completed: false,
    },
    getters: {
        training: state => {
            return state.training
        },
        userCompleted: state => {
            return state.userComplete
        },
        trainingCompleted: state => {
            return state.trainingComplete
        },
        trainingQuestionnaire: state => {
            return state.trainingQuestionnaire
        },
        getTimer: state => {
            return state.timePeriod
        },
        getCurrentTime: state => {
            return state.currentTime
        },
        getCurrentQuestions: state => {
            return state.currentQuestion
        },
        getEvents: state => {
            return state.events
        },
        getModels: state => {
            return state.models
        },
        getUsers: state => {
            return state.users
        },
        getRoles: state => {
            return state.roles
        },
        getPhotographers: state => {
            return state.photographers
        },
        getVideographers: state => {
            return state.videographers
        },
        getAgendas: state => {
            return state.agendas
        },
        getDays: state => {
            return state.days
        },
        getLocations: state => {
            return state.locations
        },
        getAttendances: state => {
            return state.attendances
        },
        getAllLocations: state => {
            return state.AllLocations
        },
        completed: state => {
            return state.completed
        },
        completed_date: state => {
            return state.completed_date
        },
        test_completed: state => {
            return state.test_completed
        }
    },
    actions: {
        GET_TRAINING({commit}, id) {
            // let url = `/trainings/show/${id}`
            const url = route('training.show', {id : id})
            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_TRAINING", res.training)
                commit("SET_COMPLETED", res.completed)
                commit("SET_COMPLETED_DATE", res.completed_date)
                commit("SET_TEST_COMPLETED", res.test_completed)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        EDIT_TRAINING({commit}, id) {
            // let url = `/trainings/edit/${id}`
            const url = route('training.edit', {id : id})
            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_TRAINING", res.training)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        GET_USER_COMPLETED_TRAINING({commit}, id) {
            // let url = `/trainings/completedUser/${id}`
            const url = route('training.completed_user', {id : id})

            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_USER_COMPLETED_TRAINING", res.userComplete)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        GET_COMPLETED_TRAINING({commit}, id) {
            // let url = `/trainings/completedTraining/${id}`
            const url = route('training.completed_training', {id : id})

            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_COMPLETED_TRAINING", res.trainingComplete)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        GET_TRAINING_TEST({commit}, id) {
            // let url = `/trainings/questionnaire/${id}`
            const url = route('training.questionnaire', {id : id})

            axios.get(url).then((response) => {
                let res = response.data;
                if (response.data.questionnaire.length > 0) {
                    commit("SET_TRAINING_TEST", res.questionnaire)
                    commit("UPDATE_NEXT_QUESTION", 0)
                    commit("SET_TIMER", this.state.timePeriod)
                } else {
                    console.log("no questionnaire")
                }
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        SEND_TEST_ANSWERS(context, payload) {
            clearTimeout(context.state.timerObj)
            let trainingId = payload[0]
            let questionIds = payload[1]
            let answerIds = payload[2]
            let time = context.state.currentTime;

            // let url = "/trainings/finishTest/" + trainingId
            const url = route('training.finishTest', {trainingId})
            axios.post(url, {
                'questions': questionIds,
                'answers': answerIds
            }).then((response) => {
                console.log(response)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        TIMER_TICKS({commit}, payload) {
            commit("SET_CURRENT_TIME", payload[1]);
            commit("SET_TIMER_OBJ", payload[0])
        },

        DELETE_TRAINING(context, payload) {
            let url = "trainings/delete" + payload
            axios.get(url)
                .then((response) => {

                }).catch((errors) => {

            });
        },

        GET_MODELS({commit}, id) {
            // let url = `/user/getModels/${id}`
            const url = route("user.getModels", {id})
            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_MODEL", res.models)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        GET_USERS({commit}) {
            // let url = `/bookings/getUsers`
            const url = route("bookings.getUsers")
            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_USERS", res.users)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        GET_ROLES({commit}) {
            let url = route("news.roles")
            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_ROLES", res.roles)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        GET_AGENDAS({commit}, id) {
            // let url = 'bookings/agenda' + id
            const url = route("bookings.agenda", {id})
            axios.get(url)
                .then((response) => {
                    commit("SET_AGENDAS", response.data.bookings)
                }).catch((errors) => {
                console.log(errors)
            })
        },

        GET_FILMAKERS({commit}, id) {
            // let url = `/user/getFilmakers/${id}`
            const url = route("user.getFilmakers", {id})
            axios.get(url)
                .then((response) => {
                    commit("SET_VIDEOGRAPHERS", response.data.videographers)
                }).catch((errors) => {
                console.log(errors)
            })
        },

        GET_PHOTOGRAPHERS({commit}, id) {
            // let url = `/user/getPhotographers/${id}`
            const url = route("user.getPhotographers",{id})
            axios.get(url)
                .then((response) => {
                    commit("SET_PHOTOGRAPHERS", response.data.photographers)
                }).catch((errors) => {
                console.log(errors)
            })
        },

        GET_DAYS({commit}) {
            // let url = "/bookings/days"
            const url = route("bookings.getDays")
            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_DAYS", res.days)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        GET_LOCATIONS({commit}) {
            // let url = "/bookings/locations"
            const url = route("bookings.getLocations")
            axios.get(url).then((response) => {
                let res = response.data;
                commit("SET_LOCATIONS", res.locations)
            }).catch((errors) => {
                console.log(errors)
            }).finally(() => {
                console.log("Finally GET_TRAINING")
            })
        },

        async ADD_ATTENDANCES({ commit }, attendance){
            let url = "attendance/store"
            const response = await axios.post(url, attendance)
            commit('ADD_ATTENDANCE', response.data)
        },

        async UPDATE_ATTENDANCE({ commit }, updatedItem){
            let url = "attendance/update" + updatedItem.id
            const response = await axios.post(url, updatedItem)
            commit('UPDATE_ATTENDANCES', response.data)
        },
        
        async GET_ALL_LOCATIONS({commit}) {
            let url = "monitoring/locations"
            const response = await axios.get(url)
            commit("SET_ATTENDANCE_LOCATION", response.data)
        },

    },
    mutations: {
        SET_TRAINING(state, payload) {
            return state.training = payload
        },

        SET_USER_COMPLETED_TRAINING(state, userComplete) {
            return state.userComplete = userComplete
        },

        SET_COMPLETED_TRAINING(state, trainingComplete) {
            return state.trainingComplete = trainingComplete
        },

        SET_TRAINING_TEST(state, payload) {
            return state.trainingQuestionnaire = payload
        },

        SET_TIMER(state, payload) {
            state.timePeriod = payload;
        },

        SET_CURRENT_TIME(state, payload) {
            state.currentTime = payload;
        },

        SET_TIMER_OBJ(state, payload) {
            state.timerObj = payload;
        },

        UPDATE_NEXT_QUESTION(state, payload) {
            state.currentQuestion = state.trainingQuestionnaire[payload];
        },

        ADD_EVENT(state, event) {
            state.events.push(event)
        },

        SET_MODEL(state, models) {
            return state.models = models
        },

        SET_USERS(state, users) {
            return state.users = users
        },

        SET_ROLES(state, roles) {
            return state.roles = roles
        },

        SET_VIDEOGRAPHERS(state, videographers) {
            return state.videographers = videographers
        },

        SET_PHOTOGRAPHERS(state, photographers) {
            return state.photographers = photographers
        },

        SET_AGENDAS(state, agendas) {
            return state.agendas = agendas
        },

        SET_DAYS(state, days) {
            return state.days = days
        },

        SET_LOCATIONS(state, locations) {
            return state.locations = locations
        },

        UPDATE_ATTENDANCES(state, updatedItem) {
            const index = state.attendances.findIndex(attendance => attendance.id === updatedItem.id)
            if (index !== -1){
                return state.attendances.splice(index, 1, updatedItem)
            }
        },

        ADD_ATTENDANCES(state, attendance) {
            return state.attendances.unshift(attendance)
        },

        SET_ATTENDANCE_LOCATION(state, locations){
            return state.AllLocations = locations
        },

        SET_COMPLETED(state, completed){
            return state.completed = completed
        },
        
        SET_COMPLETED_DATE(state, completed_date){
            return state.completed_date = completed_date
        },

        SET_TEST_COMPLETED(state, test_completed){
            return state.test_completed = test_completed
        }
    }
}