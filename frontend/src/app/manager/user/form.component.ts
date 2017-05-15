import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import {UserDetailsInterface} from "../../service/model/user-datails.interface";
import {UserInterface, UserStatus, UserType} from "../../service/model/user.interface";
import {CustomerInterface} from "../../service/model/customer.interface";
import {userManagerService} from "../../service/user-manager.service";
import {RolesResponseInterface} from "../../service/response/roles-response.interface";
import {ValidationInterface} from "../../service/response/validation.interface";
import {customerManagerService} from "../../service/customer-manager.service";
import {CustomerResponseInterface} from "../../service/response/customer-response.interface";
import {UserRequestInterface} from "../../service/request/user-request.interface";
import {UserResponseInterface} from "../../service/response/user-response.interface";
import TabsComponent from "../../widgets/tabs.component";
import {CustomerListInterface} from "../../service/response/customer-list.interface";

Component.registerHooks([
    'mounted'
]);

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
     * Статус редактируемого пользователя
     */
    @Model() status: UserStatus = 1;

    /**
     * Пароль: только для новых пользователей
     */
    @Model() password: string = '';

    /**
     * Роли пользователя
     */
    @Model() roles: string[] = [];

    /**
     * Идентификатор контрагента (арендатора, 0, если заносится новый арендатор)
     */
    @Model() customerId: number = 0;

    /**
     * Модель нового арендатора
     */
    @Model() customer: CustomerInterface = {
        name: '',
        currentAgreement: '',
        allowBookerDepartment: false,
        allowItDepartment: false
    };

    /**
     * Подписи для ролей
     */
    @Model() rolesLabels: {[key: string]: string} = {};

    /**
     * Иерархия ролей
     */
    @Model() rolesHierarchy: {[key: string]: string[]} = {};

    /**
     * Доступные арендаторы для привязки
     */
    @Model() customers: CustomerInterface[] = [];

    /**
     * Ожидание субмита формы
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Текст об ошибке
     */
    @Model() errorMessage: string = '';

    /**
     * Коды ролей по типу пульзователя
     */
    protected rolesByUserType: {[key: string]: Array<string>};

    /**
     * Запрос необходимых данных с бекенда
     */
    mounted(): void {
        // рестарт табов
        let tabs: TabsComponent = <TabsComponent>this.$refs['tabs'];
        tabs.currentTab = 0;

        let val = this.user;

        if (val && val.id) {
            // существующий пользователь, форма редактирования
            this.name = val.name;
            this.email = val.email;
            this.type = val.userType;
            // запрос дополнительных данных о пользователе
            userManagerService.details(val.id).then((response: UserDetailsInterface) => {
                this.status = response.status;
                this.roles = [];
                for (let role of response.roles) {
                    this.roles.push(role);
                }
            });
        } else {
            // новый пользователь, форма создания
            this.name = '';
            this.email = '';
            this.status = 1;
            this.password = '';
            this.type = 'customer';
            this.customerId = 0;
            this.roles = [];
        }

        userManagerService.roles().then((response: RolesResponseInterface) => {
            this.rolesLabels = response.labels;
            this.rolesHierarchy = response.hierarchy;
            this.rolesByUserType = response.roles;
        });

        let pageNum = 0;
        this.customers = [];
        let fetchCustomers = () => {
            customerManagerService.list(pageNum).then((response: CustomerListInterface) => {
                this.customers = this.customers.concat(response.list);
                pageNum++;
                if (response.totalCount > this.customers.length) {
                    fetchCustomers();
                } else {
                    // после получения всех контрагентов установить идентифиатор редактируемого контрагента
                    this.customerId = val.customer ? val.customer.id : 0;
                }
            });
        };
        fetchCustomers();
    }

    /**
     * Возвращает true, если для данного типа пользователей возможно отображать указанную роль
     */
    canViewRole(role: string): boolean {
        return !!(this.rolesByUserType[this.type] && this.rolesByUserType[this.type].indexOf(role) != -1);
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
            for (let customer of this.customers) {
                if (customer.id == val) {
                    this.customer = {
                        name: customer.name,
                        currentAgreement: customer.currentAgreement,
                        allowBookerDepartment: customer.allowBookerDepartment,
                        allowItDepartment: customer.allowItDepartment
                    };
                    return;
                }
            }
            // если контрагент не найден - установка его в 0
            this.customerId = 0;
        } else {
            this.customer = {
                name: '',
                currentAgreement: '',
                allowBookerDepartment: false,
                allowItDepartment: false
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
                name: this.name,
                email: this.email,
                userType: this.type,
                role: []
            };
            if (customerId) {
                request['customer'] = customerId;
            }
            for (let role of this. roles) {
                request.role.push({code: role});
            }
            if (!this.user) {
                // создание нового пользователя
                request['password'] = this.password;
                userManagerService
                    .create(<UserRequestInterface>request)
                    .then((response: UserResponseInterface) => {
                        this.awaitOfSubmit = false;
                        if (isValidResponse(response)) {
                            this.awaitOfSubmit = false;
                            this.$emit('user:new', response.user);
                        }
                    });
            } else {
                // редактирование существующего пользователя
                request['status'] = this.status;
                userManagerService
                    .update(this.user.id, <UserRequestInterface>request)
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
            if (this.type == 'manager') {
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
            userManagerService.remove(this.user.id).then((response: UserResponseInterface) => {
                this.awaitOfSubmit = false;
                if (response.success) {
                    this.$emit('user:remove', this.user.id);
                } else {
                    this.errorMessage = 'Не удалось удалить пользователя.';
                }
            });
        }
    }
}
