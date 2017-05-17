import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import {authUserService} from "../../service/auth-user.service";
import {RememberPasswordInterface} from "../../service/response/remember-password.interface";

/**
 * Форма напоминания пароля
 */
@Component({
    template: require('./remember-password-form.html')
})
export default class RememberPasswordForm extends Vue {
    /**
     * Флаг ожидания получения ответа от API
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Текст ошибки если есть
     */
    @Model() errorMessage: string = '';

    /**
     * E-mail пользователя для напоминания пароля
     */
    @Model() email: string = '';

    /**
     * Успешное заполнение формы
     */
    @Model() success: boolean = false;

    remember(event): void {
        event.preventDefault();

        this.errorMessage = '';

        if (this.awaitOfSubmit) {
            return;
        }

        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;

            authUserService.rememberPassword(this.email).then((response: RememberPasswordInterface) => {
                this.awaitOfSubmit = false;
                this.success = response.success;
                if (!response.valid && response.validationErrors) {
                    // показать первую ошибку
                    this.errorMessage = response.validationErrors[Object.keys(response.validationErrors)[0]];
                }
            });
        }).catch(() => {});
    }
}
