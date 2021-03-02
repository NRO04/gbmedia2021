<template>
    <div id="container-latest-tasks">
        <div class="card card-accent-info">
            <b-overlay :show="show" no-wrap></b-overlay>

            <div class="card-header">
                <div class="row">
                    <div class="col-8">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt"></i>
                            Últimos Trabajos
                        </h5>
                    </div>
                </div>
            </div>
            <div class="card-body row">
                <div class="col-12">
                    <a :href="projectRoute('tasks.list')">
                        <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">Visto</th>
                                <th class="text-center">Creado Por</th>
                                <th>Título</th>
                                <th>Último comentario</th>
                                <th class="text-center">Tiempo</th>
                                <th class="text-center">Código</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(task, i) in tasks">
                                <td class="text-center">
                                    <i :class="'fas fa-bolt ' + (task.pulsing == 1 ? 'pulsing-active' : '')"></i>
                                </td>
                                <td class="text-center">
                                    <div class="c-avatar">
                                        <img class="c-avatar-img" :src="task.creator_image" alt="Creado Por">
                                    </div>
                                </td>
                                <td>
                                    <div v-html="task.title"></div>
                                    <div class="small text-muted" v-html="task.info"></div>
                                </td>
                                <td>
                                    <div v-html="task.last_comment_date"></div>
                                </td>
                                <td class="text-center">
                                    <div class="clearfix">
                                        <div class="float-left">
                                            <small class="text-muted" v-text="task.time"></small>
                                        </div>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div :class="'progress-bar ' + task.progress_percent_class" role="progressbar" :style="'width:' +  task.progress_percent + '%'" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" :id="'progress-' + task.id"></div>
                                        <input type="hidden" ref="created-at-date" name="created_at_date" :data-id="task.id" :key="task.created_at">
                                        <input type="hidden" ref="should-finish-date" name="should_finish_date" :data-id="task.id" :value="task.should_finish">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <b>{{ task.code }}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </a>
                </div>
                <div class="col-12">
                    <div id="container-news-content" class="text-justify">
                        <p class="text-right mt-2">
                            <a :href="projectRoute('tasks.list')">Ver todos</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "LatestTasks",
        data() {
            return {
                tasks: [],
                show: true,
            }
        },
        created() {
            this.getLatestTasks();

            setInterval(this.tasksTime, 1000)
        },
        methods: {
            getLatestTasks() {
                let url = route('home.get_user_latest_tasks');

                axios.get(url)
                .then((response) => {
                    this.tasks = response.data;
                    this.show = false;
                    this.tasksTime();
                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });
                    this.show = false;
                });
            },
            tasksTime() {
                let _this = this;
                let msecPerMinute = 1000 * 60;
                let msecPerHour = msecPerMinute * 60;
                let msecPerDay = msecPerHour * 24;

                for (let i in this.tasks) {
                    let id = this.tasks[i].id;

                    let now = new Date();

                    let date_now = now.getTime();
                    let should_finish_date = new Date(this.tasks[i].should_finish).getTime();
                    let created_at_date = new Date(this.tasks[i].created_at).getTime();

                    let interval = should_finish_date - date_now;
                    let minutes1 = (date_now / msecPerMinute) - (created_at_date / msecPerMinute);
                    let minutes2 = (should_finish_date / msecPerMinute) - (created_at_date / msecPerMinute);

                    let xc = (minutes1 * 100) / minutes2;
                    xc = 100 - xc;

                    // Days
                    let days = Math.floor(interval / msecPerDay);
                    interval = interval - (days * msecPerDay);

                    let  hours = Math.floor(interval / msecPerHour);
                    interval = interval - (hours * msecPerHour);

                    if (hours <= 9) hours = "0" + hours;

                    // Minutes
                    let minutes = Math.floor(interval / msecPerMinute);
                    interval = interval - (minutes * msecPerMinute);

                    if (minutes <= 9) minutes = "0" + minutes;

                    // Seconds
                    let seconds = Math.floor(interval / 1000);
                    if (seconds <= 9) seconds = "0" + seconds;

                    let progress_status = 0;
                    let time = 0;
                    let progress_class = '';

                    if (should_finish_date < date_now) {
                        time = "Caducado";
                        progress_status = -1;
                    } else {
                        time = days + "d " + hours + ":" + minutes + ":" + seconds;
                        progress_status = parseInt(days);
                    }

                    let final_progress_status = parseInt(progress_status);

                    if (progress_status >= 3) {
                        progress_class = 'bg-gradient-success';
                    }
                    if (progress_status <= 2 && progress_status >= 1) {
                        progress_class = 'bg-gradient-warning';

                    }
                    if (progress_status == 0) {
                        progress_class = 'bg-gradient-danger';
                    }

                    _this.tasks[i].time = time;
                    _this.tasks[i].progress_percent = xc;
                    _this.tasks[i].progress_percent_class = progress_class;
                }
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
        }
    }
</script>

<style scoped>
    a:hover {
        text-decoration: none;
    }
</style>
