/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import "./validation";

import VueApexCharts from 'vue-apexcharts'
Vue.use(VueApexCharts)

Vue.component('apexchart', VueApexCharts)

//vuex state managament
import Vuex from 'vuex'
import storeData from "./store/index"
import Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

import VueChatScroll from 'vue-chat-scroll'
import {AlertError, Form, HasError} from 'vform';
import TextHighlight from 'vue-text-highlight';
import VueEllipseProgress from 'vue-ellipse-progress';
import VueTimepicker from 'vue2-timepicker'
import 'vue2-timepicker/dist/VueTimepicker.css'
import VueProgressBar from 'vue-progressbar';
import {BootstrapVue, IconsPlugin} from 'bootstrap-vue';
import VueClipboard from 'vue-clipboard2';
import {Datetime} from 'vue-datetime'
import 'vue-datetime/dist/vue-datetime.css'
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';
import {extend, localize, ValidationObserver, ValidationProvider} from "vee-validate";
import es from "vee-validate/dist/locale/es.json";
import * as rules from "vee-validate/dist/rules";
import InfiniteLoading from 'vue-infinite-loading';

Vue.use(Vuex)
const store = new Vuex.Store(
    storeData
)

Vue.use(VueChatScroll)

window.Form = Form;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

// Firing events
let Fire = new Vue();
window.Fire = Fire;

window.events = new Vue()
window.flash = function (message){
    window.events.$emit('flash', message)
}

const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    onOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

const SwalGB = Swal.mixin({
    customClass: {
        confirmButton: 'btn btn-sm btn-success mx-2',
        cancelButton: 'btn btn-sm btn-danger',
        confirmButtonColor: '#05f281!important',
        cancelButtonColor: '#dd0400!important',
    },
    title: "¿Está seguro?",
    text: "",
    reverseButtons: true,
    buttonsStyling: false,
    confirmButtonText: "<i class='fas fa-check'></i> Aceptar",
    cancelButtonText: "<i class='fas fa-times'></i> Cancelar",
    showCancelButton: true,
    allowOutsideClick: false,
    backdrop: `
            rgb(24 25 36 / 87%)
            left top
            no-repeat
        `
});

window.Toast = Toast;
window.SwalGB = SwalGB;
window.Dropzone = Dropzone;

Vue.component('text-highlight', TextHighlight);

Vue.use(VueEllipseProgress);

Vue.component('vue-timepicker', VueTimepicker);

const options = {
    color: '#01ff70',
    failedColor: '#DE1738',
    thickness: '5px',
    transition: {
        speed: '0.5s',
        opacity: '0.8s',
        termination: 300
    },
    autoRevert: true,
    location: 'top',
    inverse: false
};

Vue.use(VueProgressBar, options);

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

VueClipboard.config.autoSetContainer = true
Vue.use(VueClipboard)
Vue.component('pagination', require('laravel-vue-pagination'));
Vue.use(Datetime)
Vue.component('VueCtkDateTimePicker', VueCtkDateTimePicker);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/news.vue -> <example-component></example-component>
 */
const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Object.keys(rules).forEach(rule => {
    extend(rule, rules[rule])
});
localize("es", es);
Vue.component("ValidationObserver", ValidationObserver);
Vue.component("ValidationProvider", ValidationProvider);

Vue.use(InfiniteLoading, {
    props: {
        spinner: 'waveDots',
    },
    slots: {
        noMore: 'No more message'
    },
});

Vue.config.productionTip = false

import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

Vue.component('v-select', vSelect)

import SimpleLightboxox from 'vue-simple-lightbox'
Vue.use(SimpleLightboxox)

//Filters
Vue.filter('round', function (value, accuracy, keep) {
    if (typeof value !== 'number') return value;
    var fixed = value.toFixed(accuracy);
    return keep ? fixed : +fixed;
});

Vue.filter('pesos', function (value) {
    let val = (value / 1).toFixed(0).replace('.', ',')
    return "$" + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
});

Vue.filter('dolares', function (value) {
    let val = (value / 1).toFixed(2).replace('.', ',')
    return "$ " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
});

/*Vue.directive('can',
    function(_elm, binding, vnode) {
        let permissions = []
        let url = "/role/permissions"
        axios.get(url).then((response) => {
            permissions = response.data
            if(permissions.indexOf(binding.value) !== -1){
                return vnode.elm.parentElement.appendChild(vnode.elm)
            }else{
                return vnode.elm.parentElement.removeChild(vnode.elm)
            }
        })
    }
)*/

Vue.directive('can',
    function(_elm, binding, vnode) {
        let permission = null
        let url = "/role/permissions/" + binding.value
        axios.get(url).then((response) => {
            permission = response.data
            if(permission){
                return vnode.elm.parentElement.appendChild(vnode.elm)
            }else{
                return vnode.elm.parentElement.removeChild(vnode.elm)
            }
        })
    }
)

// Vue application
const app = new Vue({
    el: '#app',
    store,
});
