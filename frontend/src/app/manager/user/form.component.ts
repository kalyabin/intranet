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
import Vuex from 'vuex';

/**
 * Мета данные для формы
 */
interface FormState {
    rolesLabels: {[key: string]: string};
    rolesHierarchy: {[key: string]: string[]};
    rolesByUserType: {[key: string]: Array<string>};
    customers: Array<CustomerInterface>;
}

Component.registerHooks([
    'mounted'
]);

/**
 * Форма редактирования / создания пользователя
 */
@Component({
    template: require('./form.component.html'),
    store:  new Vuex.Store({
        state: <FormState>{
            rolesLabels: {},
            rolesHierarchy: {},
            rolesByUserType: {},
            customers: []
        },
        mutations: {
            fetchRolesData: (state: FormState, roles: RolesResponseInterface) => {
                state.rolesHierarchy = roles.hierarchy;
                state.rolesLabels = roles.labels;
                state.rolesByUserType = roles.roles;
            },
            addCustomer: (state: FormState, customer: CustomerInterface) => {
                state.customers.push(customer);
            },
            addCustomers: (state: FormState, customers: CustomerInterface[]) => {
                state.customers = state.customers.concat(customers);
            }
        },
        actions: {
            // подтяжка данных о доступных ролях для пользователей
            rolesData: (action) => {
                userManagerService.roles().then((response: RolesResponseInterface) => {
                    action.commit('fetchRolesData', response);
                });
            },
            // подтяжка контрагентов
            customers: (action) => {
                let pageNum = 0;
                let cnt = 0;
                let fetchCustomers = () => {
                    customerManagerService.list(pageNum).then((response: CustomerListInterface) => {
                        action.commit('addCustomers', response.list);
                        pageNum++;
                        cnt += response.list.length;
                        if (response.totalCount > cnt) {
                            fetchCustomers();
                        }
                    });
                };
                fetchCustomers();
            }
        }
    })
})
export default class UserManagerFormComponent extends Vue {
    /**
     * Свойство на вход
     */
    @Prop(Object) user: UserInterface;

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
    @Model() customerId: number = this.userData.customer && this.userData.customer.id ? this.userData.customer.id : 0;

    /**
     * Модель арендатора
     */
    @Model() customer: CustomerInterface = this.userData.customer ? this.userData.customer : {
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
        return this.$store.state.customers;
    }

    /**
     * Подписи для ролей
     */
    get rolesLabels(): {[key: string]: string} {
        return this.$store.state.rolesLabels;
    }

    /**
     * Иерархия ролей
     */
    get rolesHierarchy(): {[key: string]: string[]} {
        return this.$store.state.rolesHierarchy;
    }

    /**
     * Коды ролей по типу пульзователя
     */
    get rolesByUserType(): {[key: string]: Array<string>} {
        return this.$store.state.rolesByUserType;
    }

    mounted(): void {
        // запрос данных о доступных ролях
        this.$store.dispatch('rolesData');
        this.$store.dispatch('customers');

        this.fetchUserData(this.user);
    }

    /**
     * Получить дополнительные данные о пользователе (роли, доступные контрагенты)
     */
    fetchUserData(val: UserInterface): void {
        this.roles = [];
        if (val && val.id) {
            userManagerService.details(val.id).then((response: UserDetailsInterface) => {
                for (let role of response.roles) {
                    this.roles.push(role);
                }
            });
        }
    }

    @Watch('user')
    setUserData(val): void {
        // рестарт табов
        let tabs: TabsComponent = <TabsComponent>this.$refs['tabs'];
        tabs.currentTab = 0;

        this.userData = val ? val : {
            name: '',
            email: '',
            status: 1,
            userType: 'customer'
        };

        this.customerId = this.userData.customer && this.userData.customer.id ? this.userData.customer.id : 0;

        this.fetchUserData(val);
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
            this.$store.dispatch('customers').then(() => {
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
                request['status'] = this.userData.status;
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
