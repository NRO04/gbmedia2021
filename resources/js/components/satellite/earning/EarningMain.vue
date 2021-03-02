<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="card table-responsive">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-3">
                                <span class="span-title">Ganancias</span>
                            </div>
                            <div class="col-lg-5">
                                <div class="row">
                                    <select v-model="view_type" class="col-lg-8 form-control">
                                        <option value="1">Propietarios que más Facturan</option>
                                        <option value="2">Han recibido pago</option>
                                        <option value="3">Ganancias por Regiones </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-if="view_type == ''" class="alert alert-success text-center font-weight-bold" role="alert">
                            Seleccione la opcion por la cual desea visualizar las ganancias de los propietarios
                        </div>
                        <div v-if="view_type == 1">
                            <Earning></Earning>
                        </div>
                        <div v-if="view_type == 2">
                            <pay-received></pay-received>
                        </div>
                        <div v-if="view_type == 3">
                            <earning-region></earning-region>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</template>

<script>
    import PayReceived from "./PayReceived";
    import EarningRegion from "./EarningRegion";
    export default {
        name: "EarningMain",
        components: {EarningRegion, PayReceived},
        data(){
            return{
                view_type : "",
            }
        }
    }
</script>

<style scoped>

</style>
