import Vue from 'vue';
import Component from "vue-class-component";
import $ from 'jquery';
import {authUserService} from "./service/auth-user.service";
import {Model, Prop, Watch} from "vue-property-decorator";
import {LoginInterface} from "./interface/login.interface";
import {router} from "./router/router";

/**
 * Страница авторизации
 */
@Component({
    template: require('./login.component.html')
})
export default class LoginComponent extends Vue {
    /**
     * Текст об ошибке авторизации
     */
    @Model() errorMessage: string = '';

    /**
     * Логин пользователя
     */
    @Model() username: string = '';

    /**
     * Пароль пользователя
     */
    @Model() password: string = '';

    mounted(): void {
        $('body').addClass('login');
    }

    detached(): void {
        $('body').removeClass('login');
    }

    /**
     * Авторизация
     */
    login(event): void {
        event.preventDefault();

        this.errorMessage = '';

        this.$validator.validateAll().then(() => {
            authUserService.login(this.username, this.password).then((result: LoginInterface) => {
                if (!result.loggedIn && result.isLocked) {
                    this.errorMessage = 'Ваш аккаунт заблокирован';
                } else if (!result.loggedIn && result.isNeedActivation) {
                    this.errorMessage = 'Требуется активация аккаунта. Обратитесь к администратору.'
                } else if (!result.loggedIn) {
                    this.errorMessage = result.errorMessage;
                } else {
                    // переход на главную страницу
                    router.push({name: 'dashboard'});
                }
            });
        }).catch(() => {});
    }
}
