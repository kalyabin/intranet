import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import {authUserService} from "../../service/auth-user.service";
import {router} from "../../router/router";
import {LoginInterface} from "../../service/model/login.interface";

/**
 * Форма авторизации
 */
@Component({
    template: require('./login-fom.html')
})
export default class LoginForm extends Vue {
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

    /**
     * Флаг ожидания авторизации
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Авторизация
     */
    login(event): void {
        event.preventDefault();

        this.errorMessage = '';

        if (this.awaitOfSubmit) {
            return;
        }

        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;
            authUserService.login(this.username, this.password).then((result: LoginInterface) => {
                if (result.loggedIn) {
                    authUserService.checkAuth().then(() => {
                        this.awaitOfSubmit = false;
                        router.push({name: 'dashboard'});
                    }, () => this.awaitOfSubmit = false);
                    return;
                }
                this.awaitOfSubmit = false;
                if (result.isLocked) {
                    this.errorMessage = 'Ваш аккаунт заблокирован';
                } else if (result.isNeedActivation) {
                    this.errorMessage = 'Требуется активация аккаунта. Обратитесь к администратору.'
                } else {
                    this.errorMessage = result.errorMessage;
                }
            }).catch(() => this.awaitOfSubmit = false);
        }).catch(() => {});
    }
}
