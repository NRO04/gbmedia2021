<template>
    <div class="row">
        <div class="col-lg-5">
            <div class="row">
                <label class="mt-1  mr-2 ml-2">Opcion </label>
                <select v-model="view_type" class="col-lg-6 form-control form-control-sm">
                    <option value="1">Graficas</option>
                    <option value="2">Exportar Comparativos</option>
                </select>
            </div>
        </div>
        <div class="col-lg-12 mt-3">
            <div v-if="view_type == 1">
                <div class="row">

                    <label class="ml-2 mr-2">Rango</label>
                    <select v-model="range" class="col-lg-1 form-control form-control-sm">
                        <option value="1">Mensual</option>
                        <option value="2">Trimestral</option>
                        <option value="3">Semestral</option>
                        <option value="4">Anual</option>
                        <option value="5">Fecha</option>
                    </select>
                    <label class="ml-2 mr-2" v-if="range == 1">Mes</label>
                    <select v-model="month" v-if="range == 1" class="col-lg-2 form-control form-control-sm">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    <label class="ml-2 mr-2" v-if="range == 2">Trimestre</label>
                    <select v-model="trimester" v-if="range == 2" class="col-lg-2 form-control form-control-sm">
                        <option value="1">1er Trimestre</option>
                        <option value="2">2do Trimestre</option>
                        <option value="3">3er Trimestre</option>
                        <option value="4">4to Trimestre</option>
                    </select>
                    <label class="ml-2 mr-2" v-if="range == 3">Semestre</label>
                    <select v-model="semester" v-if="range == 3" class="col-lg-2 form-control form-control-sm">
                        <option value="1">1er Semestre</option>
                        <option value="2">2do Semestre</option>
                    </select>
                    <label class="ml-2 mr-2" v-if="range == 1 || range == 2 || range == 3 || range == 4">Año</label>
                    <select v-model="year" v-if="range == 1 || range == 2 || range == 3 || range == 4" class="col-lg-1 form-control form-control-sm">
                        <option value="2021">2021</option>
                        <option value="2020">2020</option>
                        <option value="2019">2019</option>
                        <option value="2108">2108</option>
                    </select>
                    <label class="ml-2 mr-2" v-if="range == 5">Fecha Inicio</label>
                    <input type="date" v-if="range == 5" v-model="start_date" class="col-lg-2 form-control form-control-sm">
                    <label class="ml-2 mr-2" v-if="range == 5">Fecha Fin</label>
                    <input type="date" v-model="end_date" v-if="range == 5" class="col-lg-2 form-control form-control-sm">

                    <div class="col-lg-2">
                        <button class="btn btn-success btn-sm ml-2" v-if="btnSearch"  @click="searchResults"><i class="fa fa-search"></i>
                            Buscar
                        </button>
                    </div>
                    <div v-if="total > 0" class=" col-lg-12  mb-0 pt-4 pb-1 font-weight-bold" role="alert">
                            <span class="text-success">Total Facturación: {{ total | dolares }}</span><br>
                            <span class="text-success">Promedio: {{ average | dolares}}</span>
                    </div>
                </div>

                <div id="chart">
                    <apexchart id="apex" type="bar" height="350" :options="chartOptions" :series="series" ></apexchart>
                </div>
            </div>
            <div v-if="view_type == 2">
                <platform-in-maintenance></platform-in-maintenance>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "EarningRegion",
        data(){
            return{
                view_type: 1,
                range: "",
                month: "",
                trimester: "",
                semester: "",
                start_date: "",
                end_date: "",
                year: "",
                total: 0,
                average: 0,
                series: [],
                chartOptions: {
                    chart: {
                        type: 'bar',
                        height: '500px',
                    },
                    plotOptions: {
                        bar: {
                            distributed: true,
                            horizontal: true,
                        }
                    },
                    colors: ['#008FFB', '#775dd0', '#ff4560', '#1bd884', '#A5978B', '#2b908f', '#f9a3a4', '#90ee7e',
                        '#feb019', '#00b1f2'
                    ],
                    dataLabels: {
                        enabled: true
                    },
                    xaxis: {
                        categories: [],
                    },
                    theme: {
                        mode: 'dark',
                        palette: 'palette1',
                        monochrome: {
                            enabled: false,
                            color: '#ee1a1c',
                            shadeTo: 'dark',
                            shadeIntensity: 0.65
                        },
                    },
                    tooltip: {
                        custom: function({series, seriesIndex, dataPointIndex, w})  {
                            let props = "";
                            console.log(w.config.region[w.globals.labels[dataPointIndex]]);
                            $.each(w.config.region[w.globals.labels[dataPointIndex]].title, function (i, prop) {
                                props += "<span style='padding: 10px'>" + prop.owner + " | $" + prop.total + "</span><br>";
                            });


                            return '<div class="arrow_box">' +
                                '<span >' + props /*series[seriesIndex][dataPointIndex]*/  + '</span>' +
                                '</div>'
                        }
                    }
                },
            }
        },
        computed:{
            btnSearch(){
                if (this.range == 1 && this.month != "" && this.year != ""){
                    return true;
                }
                else if (this.range == 2 && this.trimester != "" && this.year != ""){
                    return true;
                }
                else if (this.range == 3 && this.semester != "" && this.year != ""){
                    return true;
                }
                else if (this.range == 4 && this.year != ""){
                    return true;
                }
                else if (this.range == 5 && this.start_date != "" && this.end_date != "" && this.start_date < this.end_date){
                    return true;
                }
                else{
                    return false;
                }

            },
        },
        methods:{
            searchResults(){
                this.total = 0;
                axios.get(route('satellite.earning_graphic'), {
                    params: {
                        range: this.range,
                        month: this.month,
                        trimester: this.trimester,
                        semester: this.semester,
                        start_date: this.start_date,
                        end_date: this.end_date,
                        year: this.year,
                    }
                }).then(response => {
                    console.log(response.data);
                    this.series = [{
                        data: response.data.series
                    }];
                    let region = [];
                    this.total = response.data.total;
                    this.average = response.data.average;
                    this.chartOptions = {
                        chart: {
                            type: 'bar',
                            height: 'auto',
                        },
                        xaxis: {
                            categories: response.data.categories,
                        }, region: response.data.region};
                });




                //this.chartOptions.xaxis.categories.redraw();

          }
        }

    }
</script>

<style>
    .apexcharts-tooltip {
        background: #0A0B18 !important;
        color: #3AA2E7 !important;
        padding: 10px 5px !important;
    }
    .apexcharts-svg{
        background: #23242D !important;
    }
    #chart {
        margin: 15px auto;
        opacity: 0.9;
    }
    #apex {
        min-height: 350px !important;
    }

</style>
