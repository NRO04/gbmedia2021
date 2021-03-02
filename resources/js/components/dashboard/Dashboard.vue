<template>
    <div id="container-dashboard">
        <div class="row" v-if="can('home-statistics') || user.setting_role_id === 11">
            <div class="col-12">
                <statistics-resume :user="user"></statistics-resume>
            </div>
<!--            <div class="col-12">
                <statistics-graph></statistics-graph>
            </div>-->
        </div>
        <div class="row">
            <div class="col-12 col-sm-6">
                <latest-news></latest-news>
            </div>
            <div class="col-12 col-sm-6">
                <Agenda></Agenda>
            </div>
            <div class="col-12 col-sm-12">
                <latest-tasks></latest-tasks>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "dashboard",
        components: {},
        props: [
            'user',
            'permissions',
            'current_tenant_id',
        ],
        methods: {
            can(permission_name) {
                return this.permissions.indexOf(permission_name) !== -1;
            },
        }
    }
</script>

<style scoped>

</style>
