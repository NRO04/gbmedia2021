<template>
    <div id="container-statistics-graph">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0">Traffic</h4>
                        <div class="small text-muted">September 2019</div>
                    </div>
                    <div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
                        <div class="btn-group btn-group-toggle mx-3" data-toggle="buttons">
                            <label class="btn btn-outline-secondary">
                                <input id="option1" type="radio" name="options" autocomplete="off"> Day
                            </label>
                            <label class="btn btn-outline-secondary active">
                                <input id="option2" type="radio" name="options" autocomplete="off" checked=""> Month
                            </label>
                            <label class="btn btn-outline-secondary">
                                <input id="option3" type="radio" name="options" autocomplete="off"> Year
                            </label>
                        </div>
                        <button class="btn btn-primary" type="button">
                            <svg class="c-icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-cloud-download"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="c-chart-wrapper" style="height:300px;margin-top:40px;">
                    <canvas class="chart" id="main-chart" height="300"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="row text-center">
                    <div class="col-sm-12 col-md mb-sm-2 mb-0">
                        <div class="text-muted">Visits</div>
                        <strong>29.703 Users (40%)</strong>
                        <div class="progress progress-xs mt-2">
                            <div class="progress-bar bg-gradient-success" role="progressbar" style="width: 40%"
                                 aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md mb-sm-2 mb-0">
                        <div class="text-muted">Unique</div>
                        <strong>24.093 Users (20%)</strong>
                        <div class="progress progress-xs mt-2">
                            <div class="progress-bar bg-gradient-info" role="progressbar" style="width: 20%"
                                 aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md mb-sm-2 mb-0">
                        <div class="text-muted">Pageviews</div>
                        <strong>78.706 Views (60%)</strong>
                        <div class="progress progress-xs mt-2">
                            <div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 60%"
                                 aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md mb-sm-2 mb-0">
                        <div class="text-muted">New Users</div>
                        <strong>22.123 Users (80%)</strong>
                        <div class="progress progress-xs mt-2">
                            <div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 80%"
                                 aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md mb-sm-2 mb-0">
                        <div class="text-muted">Bounce Rate</div>
                        <strong>40.15%</strong>
                        <div class="progress progress-xs mt-2">
                            <div class="progress-bar" role="progressbar" style="width: 40%" aria-valuenow="40"
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "StatisticsGraph"
    }
</script>

<style scoped>

</style>
