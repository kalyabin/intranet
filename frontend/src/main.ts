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
import {ticketStatusFilter} from "./app/filter/ticket-status.filter";
import {dateFormatFilter} from "./app/filter/date-format.filter";
import {NeedUserTypeDirective} from "./app/directive/need-user-type.directive";
import {pageMetaStore} from "./app/router/page-meta-store";
import {ticketStatusColorFilter} from "./app/filter/ticket-status-color.filter";
import {Dropdown} from "./app/components/dropdown";
import {UserNotificationMessage} from "./app/components/user-notification/message";
import {CustomScrollbarDirective} from "./app/directive/custom-scrollbar.directive";
import {FlashNotifications} from "./app/components/user-notification/flash-notifications";
import {ManagerIncomingCallWindow} from "./app/components/user-notification/manager-incoming-call-window";
import {CalendarChooser} from "./app/components/calendar-chooser";
import {TimePicker} from "./app/components/time-picker";
import {UserType} from "./app/service/model/user.interface";
import {authUserStore} from "./app/store/auth-user.store";

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
Vue.component('dropdown', Dropdown);
Vue.component('notification-message', UserNotificationMessage);
Vue.component('flash-notifications', FlashNotifications);
Vue.component('manager-incoming-call-window', ManagerIncomingCallWindow);
Vue.component('calendar-chooser', CalendarChooser);
Vue.component('time-picker', TimePicker);

// директивы
Vue.directive('need-role', NeedRoleDirective);
Vue.directive('need-user-type', NeedUserTypeDirective);
Vue.directive('custom-scrollbar', CustomScrollbarDirective);

// используемые фильтры
Vue.filter('ticketStatus', ticketStatusFilter);
Vue.filter('ticketStatusColor', ticketStatusColorFilter);
Vue.filter('dateFormat', dateFormatFilter);

export const app = new Vue({
    template: require('./main.html'),
    router: router,
    computed: {
        userType: (): UserType => {
            return authUserStore.state.userData ? authUserStore.state.userData.userType : null
        },
        pageLoader: (): boolean => {
            return pageMetaStore.state.pageLoader;
        },
    },
}).$mount('#app');
