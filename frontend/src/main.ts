import {router} from "./app/router/router";
import Vue from "vue";
import VeeValidate from 'vee-validate';
import VueRouter from "vue-router";
import {validateConfiguration} from "./app/validation/config";
import LoaderComponent from "./app/loader.component";
import LoginFormComponent from "./app/user/login-form.component";
import RememberPasswordFormComponent from "./app/user/remember-password-form.component";
import {NeedRoleDirective} from "./app/directive/need-role.directive";
import ModalWindowComponent from "./app/widgets/modal-window.component";
import UserManagerFormComponent from "./app/manager/user/form.component";
import XPanelComponent from "./app/widgets/x-panel.component";
import TabsComponent from "./app/widgets/tabs.component";
import TabPaneComponent from "./app/widgets/tab-pane.component";
import Vuex from "vuex";

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
Vue.component('loader', LoaderComponent);
Vue.component('login-form', LoginFormComponent);
Vue.component('remember-password-form', RememberPasswordFormComponent);
Vue.component('modal-window', ModalWindowComponent);
Vue.component('user-manager-form', UserManagerFormComponent);
Vue.component('x-panel', XPanelComponent);
Vue.component('tabs', TabsComponent);
Vue.component('tab-pane', TabPaneComponent);

// директивы
Vue.directive('need-role', NeedRoleDirective);

export const app = new Vue({
    template: '<router-view></router-view>',
    router: router
}).$mount('#app');
