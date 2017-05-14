import Vue from 'vue';
import Component from "vue-class-component";
import $ from "jquery";
import {UserInterface} from "./service/model/user.interface";
import {authUserService} from "./service/auth-user.service";
import {router} from "./router/router";
import {userStore} from "./user/user-store";
import {Model} from "vue-property-decorator";
import {SideBarMenuItem, sideBarMenus} from "./sidebar-menu";

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
     * Состояние меню
     */
    @Model() menuToggled: boolean = true;

    /**
     * Все возможные пункты меню с разбивкой по ролям
     */
    @Model() sideBarMenu: Array<SideBarMenuItem> = [];

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
        if (this.menuToggled) {
            $('body').addClass('nav-md');
        }
        $('body').addClass('footer_fixed');
        this.sideBarMenu = sideBarMenus;
    }

    beforeRouteLeave(to, from, next): void {
        $('body').removeClass('nav-md footer_fixed');
        next();
    }

    /**
     * Скрыть / раскрыть левое меню
     */
    toggleMenu(event): void {
        this.menuToggled = !this.menuToggled;

        $('body').toggleClass('nav-md nav-sm');
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
