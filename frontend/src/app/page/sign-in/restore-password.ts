import Vue from "vue";
import Component from "vue-class-component";
import $ from 'jquery';
import {Model} from "vue-property-decorator";
import {authUserService} from "../../service/auth-user.service";
import {RestorePasswordInterface} from "../../service/response/restore-password.interface";

/**
 * Форма восстановления пароля
 */
@Component({
    template: require('./restore-password.html')
})
export class RestorePassword extends Vue {
    /**
     * Флаг ожидания ответа от API
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Флаг успешного выполнения запроса
     */
    @Model() success: boolean = false;

    /**
     * Новый пароль пользователя
     */
    @Model() password: string = '';

    /**
     * Текст об ошибке
     */
    @Model() errorMessage: string = '';

    /**
     * Перед началом работы компонента добавить класс login
     */
    mounted(): void {
        $('body').addClass('login');
    }

    /**
     * После выхода с роута убрать класс login
     */
    beforeRouteLeave(to, from, next): void {
        $('body').removeClass('login');
        next();
    }

    /**
     * Проверить необходимые параметры перед запуском компонента
     */
    beforeRouteEnter(to, from, next): void {
        let checkerId = parseInt(to['params']['checkerId'] || 0);
        let checkerCode = to['params']['checkerCode'] || '';
        if (checkerId < 1 || isNaN(checkerId) || !checkerCode) {
            next('404');
        }
        next();
    }

    changePassword(event): void {
        event.preventDefault();

        this.errorMessage = '';

        if (this.awaitOfSubmit) {
            return;
        }

        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;

            let checkerId = parseInt(this.$route.params['checkerId']);
            let checkerCode = this.$route.params['checkerCode'];

            authUserService
                .restorePassword(checkerId, checkerCode, this.password)
                .then((response: RestorePasswordInterface) => {
                    this.awaitOfSubmit = false;
                    this.success = response.success;
                    if (!response.valid && response.validationErrors) {
                        this.errorMessage = response.validationErrors[Object.keys(response.validationErrors)[0]];
                    }
                });
        }).catch(() => {});
    }
}
