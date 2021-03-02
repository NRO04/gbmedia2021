<template>
    <div id="container-contracts">
        <div class="card col-12">
            <div class="card-header row">
                <div class="col-xs-12 mb-2">
                    <span class="span-title">Editar Información Módulo Contratos</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right">
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <b-form-group id="input-group-3" label="Titulo:" label-for="input-3" class="required">
                            <b-form-input v-model="formEditModuleInfo.title" type="text" name="title" required placeholder="Información del Modulo Contratos"></b-form-input>
                        </b-form-group>
                    </div>
                    <div class="col-12">
                        <b-form-group id="input-group-4" label="Descripción:" label-for="input-4">
                            <editor :plugins="plugins"
                                    :toolbar ="toolbar"
                                    :init="init"
                                    v-model.trim="formEditModuleInfo.description"
                                    api-key="n0p07k5quwjc2kzt8a973dp0yu64xzddwkyeyljsrkug60x3"
                                    placeholder="Información del Módulo"/>
                        </b-form-group>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-right">
                    <b-button type="button" size="sm" variant="warning" @click="editModuleInfo()">
                        <i class="fa fa-edit"></i> Editar
                    </b-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import * as helper from '../../../../../public/js/vue-helper.js'
    import Editor from "@tinymce/tinymce-vue";

    export default {
        name: "EditModuleInfo",
        props: [
            'info',
        ],
        components: {
            'editor': Editor
        },
        data() {
            return {
                formEditModuleInfo: new Form({
                    title: null,
                    description: null,
                }),
                toolbar: 'fontsizeselect | bold alignleft aligncenter alignright alignjustify | backcolor forecolor | \ bullist numlist | link | emoticons | image | code',
                plugins:  [
                    'emoticons advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                init:{
                    height: 500,
                    menubar: false,
                    skin: 'oxide-dark',
                    content_css: 'dark',
                }
            }
        },
        created() {
            this.formEditModuleInfo.title = this.info.title;
            this.formEditModuleInfo.description = this.info.description;
        },
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
            editModuleInfo() {
                helper.VUE_DisableModalActionButtons();
                helper.VUE_ResetValidations();

                axios.post(
                    route("contracts.edit_module_info"),
                    this.formEditModuleInfo,
                ).then((response) => {
                    let res = response.data;

                    if (res.success) {
                        Toast.fire({
                            icon: "success",
                            title: "Información modificada correctamente",
                        });
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al guardar la información. Por favor intente mas tarde.",
                            timer: 8000,
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                }).catch((res) => {
                    if (res.response.status === 422) {
                        Toast.fire({
                            icon: "error",
                            title: "Por favor corriga los campos marcados en rojo",
                            timer: 5000,
                        });
                        helper.VUE_CallBackErrors(res.response);
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: "Ha ocurrido un error al guardar la información. Por favor intente mas tarde.",
                            timer: 8000,
                        });
                    }

                    helper.VUE_EnableModalActionButtons();
                });
            },
        },
    }
</script>

<style scoped>

</style>
