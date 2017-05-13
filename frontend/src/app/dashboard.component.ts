import Vue from 'vue';
import Component from "vue-class-component";
import $ from "jquery";
import {UserInterface} from "./service/model/user.interface";
import {authUserService} from "./service/auth-user.service";
import {router} from "./router/router";
import {userStore} from "./user/user-store";

Component.registerHooks([
    'mounted',
    'beforeRouteLeave'
]);

/**
 * Главная страница личного кабинета
 */
@Component({
    template: require('./dashboard.component.html')
})
export default class DashboardComponent extends Vue {
    /**
     * Флаг авторизованности
     */
    get isAuth(): boolean {
        if (!userStore.state.isAuth) {
            router.push({name: 'login'});
        }
        return userStore.state.isAuth;
    }

    /**
     * Данные пользователя
     */
    get userData(): UserInterface {
        return userStore.state.userData;
    }

    mounted(): void {
        $('body').addClass('nav-md');
        $('body').addClass('footer_fixed');
    }

    beforeRouteLeave(to, from, next): void {
        $('body').removeClass('nav-md');
        $('body').removeClass('footer_fixed');
        next();
    }

    /**
     * Логаут
     */
    logout(event): void {
        event.preventDefault();
        authUserService.logout();
        router.push({name: 'login'});
    }
}
