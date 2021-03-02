<template>
    <div id="container-model-account-access">
        <div class="card col-12">
            <div class="card-header row">
                <div class="col-xs-12 col-sm-6 mb-2">
                    <span class="span-title">Accesos</span>
                </div>
                <div class="col-xs-12 col-sm-6 text-sm-right"></div>
            </div>
            <div class="card-body">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-6 col-lg-3">
                            <div class="card">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="bg-gradient-info p-2 mfe-3">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="text-value text-info">Usuario de la Modelo</div>
                                        <div class="text-muted font-weight-bold small" v-text="model.nick"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="bg-gradient-info p-2 mfe-3">
                                        <i class="fa fa-envelope"></i>
                                    </div>
                                    <div>
                                        <div class="text-value text-info">Email o Hangouts</div>
                                        <div class="text-muted font-weight-bold small" v-text="model.email"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="bg-gradient-info p-2 mfe-3">
                                        <i class="fa fa-key"></i>
                                    </div>
                                    <div>
                                        <div class="text-value text-info">Clave</div>
                                        <div class="text-muted font-weight-bold small" v-text="model.hangouts_password"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="bg-gradient-info p-2 mfe-3">
                                        <i class="fa fa-home"></i>
                                    </div>
                                    <div>
                                        <div class="text-value text-info">Locación</div>
                                        <div class="text-muted font-weight-bold small" v-text="location.name"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pagina</th>
                            <th>Nick</th>
                            <th>Email</th>
                            <th>Clave</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(data, i) in accounts">
                            <td>{{ data.page }}</td>
                            <td>{{ data.nick }}</td>
                            <td>{{ data.access }}</td>
                            <td>{{ data.password }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ModelAccountAccess",
        props: [
            'model',
            'accounts',
            'location',
        ],
    }
</script>

<style scoped>

</style>
