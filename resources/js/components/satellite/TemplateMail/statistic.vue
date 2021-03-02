<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="card table-responsive">
                <div class="card-header">
                    <div class="row">

                        <div class="col-lg-4">
                            <span class="span-title">Configuración Email Estadísticas </span>
                        </div>
                        <div class="col-lg-4">
                            <button class="btn btn-secondary btn-sm" data-target="#mail-preview" data-toggle="modal"> Vista Previa</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Datos del Email-->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card table-responsive">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <span class="span-title text-success">Datos del Email </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-6 pt-1">
                                            <label>Dirección de Email:</label>
                                            <input type="text" class="form-control" v-model="mail.subject" />
                                        </div>
                                        <div class="col-lg-6 pt-1">
                                            <label>Contraseña</label>
                                            <input type="password" class="form-control" v-model="mail.password"  />
                                        </div>
                                        <div class="col-lg-12 mt-2">
                                            <label>Asunto del mensaje:</label>
                                            <input type="text" class="form-control" v-model="mail.topic"  />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cuerpo del Mensaje-->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card table-responsive">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <span class="span-title text-success">Datos del Email </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-10 pt-1">
                                            <label>Texto Encabezado</label>
                                            <editor :plugins="myPlugins"
                                                    :toolbar ="myToolbar1"
                                                    :init="myInit"
                                                    v-model = "mail.header"
                                                    api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"/>
                                        </div>
                                        <div class="col-lg-4 mt-3">
                                            <label>Título Sección 1</label>
                                            <input type="text" class="form-control" v-model="mail.title1"  />
                                        </div>
                                        <div class="col-lg-10 pt-1">
                                            <label>Sección 1</label>
                                            <editor :plugins="myPlugins"
                                                    :toolbar ="myToolbar1"
                                                    :init="myInit"
                                                    v-model = "mail.section1"
                                                    api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"
                                                    placeholder="Ingrese el contenido de la noticia que desea publicar"/>
                                        </div>
                                        <div class="col-lg-4 mt-3">
                                            <label>Título Sección 2</label>
                                            <input type="text" class="form-control" v-model="mail.title2"  />
                                        </div>
                                        <div class="col-lg-10 pt-1">
                                            <label>Sección 2</label>
                                            <editor :plugins="myPlugins"
                                                    :toolbar ="myToolbar1"
                                                    :init="myInit"
                                                    v-model = "mail.section2"
                                                    api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"
                                                    placeholder="Ingrese el contenido de la noticia que desea publicar"/>
                                        </div>
                                        <div class="col-lg-4 mt-3">
                                            <label>Título Sección 3</label>
                                            <input type="text" class="form-control" v-model="mail.title3"  />
                                        </div>
                                        <div class="col-lg-10 pt-1">
                                            <label>Sección 3</label>
                                            <editor :plugins="myPlugins"
                                                    :toolbar ="myToolbar1"
                                                    :init="myInit"
                                                    v-model = "mail.section3"
                                                    api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"
                                                    placeholder="Ingrese el contenido de la noticia que desea publicar"/>
                                        </div>

                                        <div class="col-lg-4 mt-3">
                                            <label>Color de Fondo</label>
                                            <input type="color" class="form-control" v-model="mail.color1"  />
                                        </div>

                                        <div class="col-lg-4 mt-3">
                                            <label>Color secundario</label>
                                            <input type="color" class="form-control" v-model="mail.color2"  />
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Datos del Estudio-->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card table-responsive">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <span class="span-title text-success">Datos del Estudio </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-4 pt-1">
                                            <label>Nombre del Estudio</label>
                                            <input type="text" class="form-control" v-model="mail.studio"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Firma</label>
                                            <input type="text" class="form-control" v-model="mail.sign"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Web URL</label>
                                            <input type="text" class="form-control" v-model="mail.url_web"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Teléfono</label>
                                            <input type="text" class="form-control" v-model="mail.phone"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Celular</label>
                                            <input type="text" class="form-control" v-model="mail.cell"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Usuario Skype</label>
                                            <input type="text" class="form-control" v-model="mail.skype"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Usuario Facebook</label>
                                            <input type="text" class="form-control" v-model="mail.facebook"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Twitter</label>
                                            <input type="text" class="form-control" v-model="mail.twitter"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Instagram</label>
                                            <input type="text" class="form-control" v-model="mail.instagram"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Linkedin</label>
                                            <input type="text" class="form-control" v-model="mail.linkedin"  />
                                        </div>
                                        <div class="col-lg-4 pt-1">
                                            <label>Pinterest</label>
                                            <input type="text" class="form-control" v-model="mail.pinterest"  />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-success btn-sm float-right" @click="updateMail">Guardar</button>
                </div>
            </div>
        </div>

        <!-- Modal-->
        <div id="mail-preview" class="modal fade" role="dialog">
            <div class="modal-dialog modal-dark modal-xl">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"> Vista previa del Email</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <span class="text-info text-small ml-2"> <i class="fa fa-info-circle"></i> RECUERDE GUARDAR LOS CAMBIOS DEL FORMULARIO
                                PARA
                            ACTUALIZAR LA
                            PLANTILLA</span>

                            <div class="col-lg-12 mt-4 p-4" :style="'background:'+ mail.color1 ">
                                <div v-html="mail.logo"></div>
                                <div class="mt-3" v-html="mail.header"></div>
                            </div>
                            <div class="col-lg-12">
                                <div class="row justify-content-center">
                                    <div class="col-lg-3 mt-4 p-2">
                                        <p :style="'border-radius: 25px; font-weight: bold; background:'+ mail.color2 "
                                           class="text-center p-2">{{
                                            mail.title1 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div v-html="mail.section1"></div>
                            </div>
                            <div class="col-lg-12">
                                <div class="row justify-content-center">
                                    <div class="col-lg-3 mt-4 p-2">
                                        <p :style="'border-radius: 25px; font-weight: bold; background:'+ mail.color2 "
                                           class="text-center p-2">{{
                                            mail.title2 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div v-html="mail.section2"></div>
                            </div>
                            <div class="col-lg-12">
                                <div class="row justify-content-center">
                                    <div class="col-lg-3 mt-4 p-2">
                                        <p :style="'border-radius: 25px; font-weight: bold; background:'+ mail.color2 "
                                           class="text-center p-2">{{
                                            mail.title3 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div v-html="mail.section3"></div>
                            </div>
                            <div class="col-lg-12 mt-4 p-4" :style="'background:'+ mail.color1 + '; border-top: 3px solid'+  mail.color2 ">
                                <div class="row">
                                    <div class="col-lg-8 pl-4">
                                        <p class="mb-0">{{ mail.sign }}</p>
                                        <p class="mb-0">{{ mail.url_web }}</p>
                                        <p class="mb-0">Teléfono: {{ mail.phone }}</p>
                                        <p class="mb-0">Celular: {{ mail.cell }}</p>
                                        <p class="mb-0">&copy; {{ mail.studio }}. Todos los derechos reservados. </p>
                                    </div>
                                    <div class="col-lg-3">
                                        <a v-if="mail.facebook" target="_blank"><img src='/public/assets/social_network/facebook.png'
                                                                                     style='height: 30px'></a>
                                        <a v-if="mail.twitter" href="" target="_blank"><img src='/public/assets/social_network/twitter.png' style='height: 30px'></a>
                                        <a v-if="mail.instagram" href="" target="_blank"><img src='/public/assets/social_network/instagram.png' style='height: 30px'></a>
                                        <a v-if="mail.pinterest" href="" target="_blank"><img src='/public/assets/social_network/pinterest.png' style='height: 30px'></a>
                                        <a v-if="mail.linkedin" href=""
                                           target="_blank"><img src='/public/assets/social_network/linkedin.png' style='height: 30px'></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import Editor from "@tinymce/tinymce-vue";

    export default {
        name: "statistic",
        data() {
            return{
                mail: [],
                fields: [
                    { key: 'owner', label: 'Propietario', sortable: true },
                    { key: 'boutique', label: 'Deuda Boutique', sortable: true },
                    { key: 'pesos', label: 'Deuda Total Pesos', sortable: true },
                    { key: 'dolares', label: 'Deuda Total Dolares', sortable: true },
                    { key: 'pago', label: 'Estadisticas', sortable: true },
                    { key: 'blocked', label: 'Bloqueado'},

                ],
                totalRows: 1,
                currentPage: 1,
                perPage: 100,
                pageOptions: [100, 200, 300, { value: 400, text: "Show a lot" }],
                filter: null,
                filterOn: [],
                myToolbar1:
                    'fontsizeselect | bold alignleft aligncenter alignright alignjustify | backcolor forecolor | \ bullist numlist | link | emoticons | image | table',
                myPlugins:  [
                    'emoticons advlist autolink lists link image charmap print preview anchor', 'table',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                myInit:{
                    height: 300,
                    menubar: false,
                    skin: "oxide-dark", content_css: "dark",
                    images_upload_handler: function (blobInfo, success, failure,folderName) {
                        var xhr, formData;
                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', route('satellite.upload_image_mail'));

                        var token = document.head.querySelector('[name=csrf-token]').content;
                        xhr.setRequestHeader('X-CSRF-Token', token);

                        xhr.onload = function() {
                            var json;

                            if (xhr.status < 200 || xhr.status >= 300) {
                                failure('HTTP Error: ' + xhr.status);
                                return;
                            }
                            console.log(json);
                            json = JSON.parse(xhr.responseText);

                            if (!json || typeof json.location != 'string') {
                                failure('Invalid JSON: ' + xhr.responseText);
                                return;
                            }
                            success(json.location);

                        };

                        xhr.onerror = function () {
                            failure('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                        };

                        formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                        xhr.send(formData);
                    },
                }
            }
        },
        components: {
            'editor': Editor
        },
        computed: {
        },
        created() {
            this.getMail();
        },
        methods:{
            getMail(){
                axios.get(route('satellite.get_statistic_email')).then(response => {
                    console.log(response.data);
                    this.mail = response.data;
                })
            },
            updateMail(){
                axios.post(route('satellite.update_statistic_email'),
                    this.mail
                ).then(response => {
                    if(response.data.success)
                    {
                        Toast.fire({
                            icon: "success",
                            title: "Se ha guardado los datos exitosamente",
                        });
                    }
                    else{
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error, comuniquese con el ADMIN",
                        });
                    }

                })
                .catch(error => {
                    Toast.fire({
                        icon: "error",
                        title: "Ha ocurrido un error, comuniquese con el ADMIN",
                    });
                });
            },
        }
    }
</script>
