import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import {UserDetailsInterface} from "../../service/model/user-datails.interface";
import {UserInterface, UserStatus, UserType} from "../../service/model/user.interface";
import {CustomerInterface} from "../../service/response/customer.interface";
import {userManagerService} from "../../service/user-manager.service";

/**
 * Форма редактирования / создания пользователя
 */
@Component({
    template: require('./form.component.html')
})
export default class UserManagerFormComponent extends Vue {
    /**
     * Редактируемый пользователь
     */
    @Prop(Object) user: UserInterface;

    /**
     * Имя пользователя
     */
    @Model() name: string = '';

    /**
     * E-mail пользователя
     */
    @Model() email: string = '';

    /**
     * Тип пользователя
     */
    @Model() type: UserType = 'customer';

    /**
     * Пароль: только для новых пользователей
     */
    @Model() password: string = '';

    /**
     * Автогенерация пароля: только для новых пользователей
     */
    @Model() passwordAuto: boolean = false;

    /**
     * Статус пользователя
     */
    @Model() status: UserStatus = 1;

    /**
     * Роли пользователя
     */
    @Model() roles: string[] = [];

    /**
     * Идентификатор контрагента (арендатора)
     */
    @Model() customer: CustomerInterface = null;

    @Watch('user')
    onUserSet(val: UserInterface): void {
        if (val && val.id) {
            this.name = val.name;
            this.email = val.email;
            this.type = val.userType;
            this.customer = val.customer;
            // запрос дополнительных данных о пользователе
            userManagerService.details(val.id).then((response: UserDetailsInterface) => {
                this.status = response.status;
                this.roles = [];
                for (let role of response.roles) {
                    this.roles.push(role);
                }
            });
        } else {
            this.name = '';
            this.email = '';
            this.status = 1;
            this.password = '';
            this.passwordAuto = true;
            this.type = 'customer';
            this.customer = null;
        }
    }
}
