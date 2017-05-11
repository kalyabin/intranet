import {backendService} from "./app/service/backend.service";
import Vue from "vue";
import {router} from "./app/router/router";

/**
 * Точка входа приложения
 */

require('./theme/css/nprogress.css');
require('./theme/css/animate.min.css');
require('./theme/css/custom.min.css');

backendService.makeRequest('POST', 'check_auth');

export const app = new Vue({
    template: '<router-view></router-view>',
    router: router
}).$mount('#app');
