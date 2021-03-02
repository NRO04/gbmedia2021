<template>
    <div id="container-latest-news">
        <div class="card card-accent-info news-card">
            <b-overlay :show="show" no-wrap></b-overlay>

            <div class="card-header">
                <div class="row">
                    <div class="col-8">
                        <h5 class="mb-0">
                            <i class="far fa-newspaper"></i>
                            Última Noticia
                        </h5>
                    </div>
                    <div class="col-4 text-right">
                        <small class="text-muted"><i class="fa fa-clock"></i> {{ latest_new_creation }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body row pt-5">
                <div id="container-news-image" class="col-12 mb-4 px-2">
                    <div v-if="latest_new_file_extension == 'IMG'" class="container-inner-news-image">
                        <img :src="this.latest_new_file" alt="Imagen" class="news-image">
                    </div>
                </div>
                <div class="col-12">
                    <div id="container-news-title"><h5>{{ latest_new_title }}</h5></div>
                    <div id="container-news-content" class="text-justify">
                        {{ latest_new_body }}
                        <p class="text-right mt-2">
                            <a :href="projectRoute('news.index')">Ver más...</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    // import * as helper from '../../../public/js/vue-helper.js'

    export default {
        name: "LatestNews",
        created()
        {
            this.getLastNew();
        },
        data() {
            return {
                latest_new_title: null,
                latest_new_body: null,
                latest_new_file: null,
                latest_new_file_extension: null,
                latest_new_creation: null,
                show: true,
            }
        },
        methods: {
            getLastNew() {
                let url = route('home.get_user_last_new');

                axios.get(url).then((response) => {
                    let data = response.data;

                    this.latest_new_title = data.title;
                    this.latest_new_body = data.body;
                    this.latest_new_file = data.file;
                    this.latest_new_file_extension = data.extension;
                    this.latest_new_creation = data.creation;
                    this.show = false;

                }).catch((response) => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error al obtener la información. Por favor, intente mas tarde.",
                    });

                    this.show = false;
                });
            },
            projectRoute(route_name, params) {
                return route(route_name, params)
            },
        }
    }
</script>

<style scoped>
    .container-inner-news-image {
        text-align: center;
    }
    .news-image {
        max-height: 240px;
    }
    .news-card {
        min-height: 493px;
    }
</style>
