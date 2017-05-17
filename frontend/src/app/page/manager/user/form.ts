import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import {UserDetailsInterface} from "../../../service/model/user-datails.interface";
import {UserInterface} from "../../../service/model/user.interface";
import {CustomerInterface} from "../../../service/model/customer.interface";
import {userManagerService} from "../../../service/user-manager.service";
import {ValidationInterface} from "../../../service/response/validation.interface";
import {customerManagerService} from "../../../service/customer-manager.service";
import {CustomerResponseInterface} from "../../../service/response/customer-response.interface";
import {UserRequestInterface} from "../../../service/request/user-request.interface";
import {UserResponseInterface} from "../../../service/response/user-response.interface";
import {customerListStore} from "../../../store/customer-list.store";
import {rolesListStore} from "../../../store/roles-list.store";
import {Tabs} from "../../../components/tabs";

/**
 * Форма редактирования / создания пользователя
 */
@Component({
    template: require('./form.html')
})
export class ManagerUserForm extends Vue {
    /**
     * Модель редактируемого пользователя
     */
    @Prop(Object) user: UserInterface;

    /**
     * Идентификатор редактируемого пользователя
     */
    protected userId: number = this.user ? this.user.id : 0;

    /**
     * Данные для редактирования
     */
    @Model() userData: UserInterface = this.user ? this.user : {
        name: '',
        email: '',
        status: 1,
        userType: 'customer'
    };

    /**
     * Пароль
     */
    @Model() password: string = '';

    /**
     * Роли пользователя
     */
    @Model() roles: string[] = [];

    /**
     * Идентификатор контрагента (арендатора, 0, если заносится новый арендатор)
     */
    @Model() customerId: number = this.user && this.user.customer ? this.user.customer.id : 0;

    /**
     * Модель арендатора
     */
    @Model() customer: CustomerInterface = this.user && this.user.customer ? this.user.customer : {
        name: '',
        currentAgreement: '',
        allowItDepartment: false,
        allowBookerDepartment: false
    };

    /**
     * Ожидание субмита формы
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Текст об ошибке
     */
    @Model() errorMessage: string = '';

    /**
     * Доступные арендаторы для привязки
     */
    get customers(): CustomerInterface[] {
        return customerListStore.state.list;
    }

    /**
     * Подписи для ролей
     */
    get rolesLabels(): {[key: string]: string} {
        return rolesListStore.state.rolesLabels;
    }

    /**
     * Иерархия ролей
     */
    get rolesHierarchy(): {[key: string]: string[]} {
        return rolesListStore.state.rolesHierarchy;
    }

    /**
     * Коды ролей по типу пульзователя
     */
    get rolesByUserType(): {[key: string]: Array<string>} {
        return rolesListStore.state.rolesByUserType;
    }

    mounted(): void {
        // запрос данных о доступных ролях
        rolesListStore.dispatch('fetchData');
        customerListStore.dispatch('fetchList');

        // получить роли пользователя, если редактируем пользователя
        this.roles = [];
        if (this.userId) {
            userManagerService.details(this.userId).then((response: UserDetailsInterface) => {
                for (let role of response.roles) {
                    this.roles.push(role);
                }

                this.$validator.errorBag.clear();
            });
        }
    }

    /**
     * Выбор родительской роли сбрасывает выбор его дочерних элементов
     */
    chooseParentRole(role: string): void {
        if (this.roles.indexOf(role) != -1) {
            for (let childRole of this.rolesHierarchy[role]) {
                let index = this.roles.indexOf(childRole);
                if (index != -1) {
                    this.roles.splice(index, 1);
                }
            }
        }
    }

    /**
     * Все роли в виде строки для валидации на пустоту
     */
    get rolesStr(): string {
        return this.roles.join();
    }

    /**
     * Выбор контрагента по идентификатору
     */
    @Watch('customerId')
    onCustomerIdSet(val: number): void {
        if (val > 0) {
            customerListStore.dispatch('fetchList').then(() => {
                for (let customer of this.customers) {
                    if (customer.id == val) {
                        this.customer = customer;
                        return;
                    }
                }

                // если контрагент не найден - установка его в 0
                this.customerId = 0;
            });
        } else {
            this.customer = {
                name: '',
                currentAgreement: '',
                allowItDepartment: false,
                allowBookerDepartment: false
            };
        }
    }

    /**
     * Генерация случайного пароля
     */
    generatePassword(): void {
        let length = 8;
        let charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0, n = charset.length; i < length; ++i) {
            result += charset.charAt(Math.floor(Math.random() * n));
        }
        this.password = result;
    }

    /**
     * Субмит формы
     */
    submit(): void {
        this.errorMessage = '';

        // если был создан новый арендатор - он будет удален в случае ошибки создания пользователя
        let newCustomerHasBeenCreated = null;

        // проверка ответа от API и вывод ошибки
        let isValidResponse = (response: ValidationInterface): boolean => {
            if (!response.valid) {
                this.errorMessage = response.validationErrors[Object.keys(response.validationErrors)[0]];
                this.awaitOfSubmit = false;

                // если был создан новый контрагент - удалить его чтобы не задваивались данные
                if (newCustomerHasBeenCreated) {
                    customerManagerService.remove(newCustomerHasBeenCreated).then(() => {});
                    newCustomerHasBeenCreated = null;
                }

                return false;
            }
            return true;
        };

        // субмит пользователя с привязкой к контрагенту
        let submitUserData = (customerId?: number) => {
            let request = {
                name: this.userData.name,
                email: this.userData.email,
                userType: this.userData.userType,
                role: []
            };
            if (customerId) {
                request['customer'] = customerId;
            }
            for (let role of this. roles) {
                request.role.push({code: role});
            }
            if (!this.userId) {
                // создание нового пользователя
                request['password'] = this.password;
                userManagerService
                    .create(<UserRequestInterface>request)
                    .then((response: UserResponseInterface) => {
                        this.awaitOfSubmit = false;
                        if (isValidResponse(response)) {
                            this.awaitOfSubmit = false;
                            this.userId = response.user.id;
                            this.$emit('user:new', response.user);
                        }
                    });
            } else {
                // редактирование существующего пользователя
                request['status'] = this.userData.status;
                userManagerService
                    .update(this.userId, <UserRequestInterface>request)
                    .then((response: UserResponseInterface) => {
                        this.awaitOfSubmit = false;
                        if (isValidResponse(response)) {
                            this.awaitOfSubmit = false;
                            this.$emit('user:updated', response.user);
                        }
                    });
            }
        };

        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;

            // создание или редактирование пользователя
            if (this.userData.userType == 'manager') {
                submitUserData(null);
                return;
            }

            // субмит арендатора, после субмита арендатора - субмит пользователя
            if (this.customerId) {
                // редактирование существующего
                customerManagerService
                    .update(this.customerId, this.customer)
                    .then((response: CustomerResponseInterface) => {
                        if (isValidResponse(response)) {
                            submitUserData(response.customer.id);
                            customerListStore.commit('updateCustomer', response.customer);
                        }
                    });
            } else {
                // создание нового
                customerManagerService
                    .create(this.customer)
                    .then((response: CustomerResponseInterface) => {
                        if (isValidResponse(response)) {
                            newCustomerHasBeenCreated = response.customer.id;
                            submitUserData(response.customer.id);
                            customerListStore.commit('addCustomer', response.customer);
                        }
                    });
            }
        }).catch(() => {});
    }

    /**
     * Удаление пользователя
     */
    remove(): void {
        if (confirm('Пользователь будет удален безвозвратно. Вы уверены что хотите удалить пользователя?')) {
            this.awaitOfSubmit = true;
            this.errorMessage = '';
            userManagerService.remove(this.userId).then((response: UserResponseInterface) => {
                this.awaitOfSubmit = false;
                if (response.success) {
                    this.$emit('user:remove', this.userId);
                } else {
                    this.errorMessage = 'Не удалось удалить пользователя.';
                }
            });
        }
    }
}
