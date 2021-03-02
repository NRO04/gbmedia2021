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
                <div class="card table-responsive">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-3">
                                <span class="span-title">Contabilidad </span>
                            </div>
                            <div class="col-lg-4">
                                <div class="row">
                                    <label class="mt-1  mr-2 ml-2">Tipo</label>
                                    <select v-model="view_accounting" class="col-lg-8 form-control" >
                                        <option value="1">Siigo VS Banco</option>
                                        <option value="2">Resumen Estadisticas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <a href="owner/create" target="_blank" type="button" class="btn btn-m btn-success float-right btn-sm"><i
                                    class="fa fa-plus"></i> Crear</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-if="view_accounting == ''" class="alert alert-success text-center font-weight-bold" role="alert">
                            Seleccione la opcion por la cual desea filtrar
                        </div>
                        <div v-if="view_accounting == 1">
                            <platform-in-maintenance></platform-in-maintenance>
                        </div>
                        <div v-if="view_accounting == 2">
                            <div class="row">
                                <div class="col-lg-4 d-flex">
                                    <label for="">Seleccione la fecha</label>
                                    <select class="form-control form-control-sm col-lg-6 ml-3" v-model="payment_date">
                                        <option :value="data.payment_date" v-for="(data, index) in dates">{{ data.payment_date }}</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <a :href="'/public/satellite/accounting/export/siigo?payment_date='+ payment_date" v-if="payment_date != ''"
                                       class="btn btn-success btn-sm" @click="exportSiigo">Exportar Siigo</a>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </b-overlay>
        </div>

    </div>
</template>

<script>
    import PlatformInMaintenance from "../../PlatformInMaintenance";
    export default {
        name: "AccountingMain",
        components: {PlatformInMaintenance},
        data(){
            return{
                view_accounting: 2,
                payment_date: "",
                isBusy: false,
                show: false,
                dates: [],
            }
        },
        created(){
            this.getDates();
        },
        methods:{
            getDates(){
                axios.get(route('satellite.accounting_dates')).then(response => {
                    console.log(response.data);
                    this.dates = response.data;
                })
            },
        }

    }
</script>

<style scoped>

</style>
