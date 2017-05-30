import {router} from "./app/router/router";
import Vue from "vue";
import VeeValidate from 'vee-validate';
import VueRouter from "vue-router";
import {validateConfiguration} from "./app/validation/config";
import {Loader} from "./app/components/loader";
import {NeedRoleDirective} from "./app/directive/need-role.directive";
import Vuex from "vuex";
import {ModalWindow} from "./app/components/modal-window";
import {XPanel} from "./app/components/x-panel";
import {Tabs} from "./app/components/tabs";
import {TabPane} from "./app/components/tab-pane";

/**
 * Точка входа приложения
 */

require('./theme/css/nprogress.css');
require('./theme/css/animate.min.css');
require('./theme/scss/custom.scss');

// используемые расширения
Vue.use(VueRouter);
Vue.use(VeeValidate, validateConfiguration);
Vue.use(Vuex);

// подключаемые компоненты
Vue.component('loader', Loader);
Vue.component('modal-window', ModalWindow);
Vue.component('x-panel', XPanel);
Vue.component('tabs', Tabs);
Vue.component('tab-pane', TabPane);

// директивы
Vue.directive('need-role', NeedRoleDirective);

export const app = new Vue({
    template: '<router-view></router-view>',
    router: router
}).$mount('#app');
