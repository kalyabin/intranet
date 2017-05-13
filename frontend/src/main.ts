import {router} from "./app/router/router";
import Vue from "vue";
import VeeValidate from 'vee-validate';
import VueRouter from "vue-router";
import {validateConfiguration} from "./app/validation/config";
import LoaderComponent from "./app/loader.component";
import LoginFormComponent from "./app/user/login-form.component";
import RememberPasswordFormComponent from "./app/user/remember-password-form.component";

/**
 * Точка входа приложения
 */

require('./theme/css/nprogress.css');
require('./theme/css/animate.min.css');
require('./theme/scss/custom.scss');

// используемые расширения
Vue.use(VueRouter);
Vue.use(VeeValidate, validateConfiguration);

// подключаемые компоненты
Vue.component('loader', LoaderComponent);
Vue.component('login-form', LoginFormComponent);
Vue.component('remember-password-form', RememberPasswordFormComponent);

export const app = new Vue({
    template: '<router-view></router-view>',
    router: router
}).$mount('#app');
