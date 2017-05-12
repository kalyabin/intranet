import {router} from "./app/router/router";
import Vue from "vue";
import VeeValidate from 'vee-validate';
import VueRouter from "vue-router";
import {validateConfiguration} from "./app/validation/config";

/**
 * Точка входа приложения
 */

require('./theme/css/nprogress.css');
require('./theme/css/animate.min.css');
require('./theme/css/custom.min.css');

Vue.use(VueRouter);
Vue.use(VeeValidate, validateConfiguration);

export const app = new Vue({
    template: '<router-view></router-view>',
    router: router
}).$mount('#app');
