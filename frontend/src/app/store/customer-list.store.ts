import {CustomerInterface} from "../service/model/customer.interface";
import Vuex from "vuex";
import {customerManagerService} from "../service/customer-manager.service";
import {CustomerListInterface} from "../service/response/customer-list.interface";

/**
 * Список контрагентов для менеджера
 */
export interface CustomerListStateInterface {
    list: CustomerInterface[];
}

export const customerListStore = new Vuex.Store({
    state: <CustomerListStateInterface>{
        list: []
    },
    mutations: {
        /**
         * Добавить список контрагентов
         */
        addCustomers: (state: CustomerListStateInterface, customers: CustomerInterface[]) => {
            state.list = state.list.concat(customers);
        },
        /**
         * Добавить контрагента
         */
        addCustomer: (state: CustomerListStateInterface, customer: CustomerInterface) => {
            state.list.push(customer);
        },
        /**
         * Обновление контрагента
         */
        updateCustomer: (state: CustomerListStateInterface, customer: CustomerInterface) => {
            for (let i in state.list) {
                if (customer.id && state.list[i].id == customer.id) {
                    state.list[i] = customer;
                }
            }
        },
        /**
         * Удаление контрагента
         */
        removeCustomer: (state: CustomerListInterface, id: number) => {
            for (let i in state.list) {
                if (id && state.list[i].id == id) {
                    state.list.splice(parseInt(i), 1);
                }
            }
        },
        /**
         * Очистить весь стек контрагентов
         */
        clear: (state: CustomerListStateInterface) => {
            state.list = [];
        },
    },
    actions: {
        /**
         * Подтяжка контрагентов из API
         */
        fetchList: (action) => {
            return new Promise((resolve, reject) => {
                // защита от задвоения данных
                if (action.state.list.length > 0) {
                    return resolve();
                }
                let pageNum = 0;
                let cnt = 0;
                let fetchCustomers = () => {
                    customerManagerService.list(pageNum).then((response: CustomerListInterface) => {
                        action.commit('addCustomers', response.list);
                        pageNum++;
                        cnt += response.list.length;
                        if (response.totalCount > cnt) {
                            fetchCustomers();
                        } else {
                            resolve();
                        }
                    }).catch(() => reject());
                };
                fetchCustomers();
            });
        },
        /**
         * Получить контрагента по идентификатору
         */
        getCustomer: (action, customerId) => {
            return new Promise<CustomerInterface[]>((resolve, reject) => {
                action.dispatch('fetchList').then(() => {
                    for (let customer of action.state.list) {
                        if (customer.id == customerId) {
                            resolve([customer]);
                            return;
                        }
                    }

                    reject();
                });
            });
        },
        /**
         * Редактирование контрагента
         */
        updateCustomer: (action, customer: CustomerInterface) => {
            return new Promise((resolve) => {
                action.commit('updateCustomer', customer);
                resolve();
            });
        },
        /**
         * Удалить контрагента
         */
        removeCustomer: (action, customerId: number) => {
            return new Promise((resolve) => {
                action.commit('removeCustomer', customerId);
                resolve();
            });
        },
        /**
         * Добавить контрагента
         */
        addCustomer: (action, customer: CustomerInterface) => {
            return new Promise((resolve) => {
                action.commit('addCustomer', customer);
                resolve();
            });
        },
        /**
         * Очистка списка
         */
        clear: (action) => {
            return new Promise((resolve) => {
                action.commit('clear');
                resolve();
            });
        }
    }
});
