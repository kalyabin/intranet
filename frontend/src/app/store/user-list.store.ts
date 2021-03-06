import {UserInterface} from "../service/model/user.interface";
import Vuex from "vuex";
import {userManagerService} from "../service/user-manager.service";
import {ListInterface} from "../service/response/list.interface";

/**
 * Список пользователей для менеджера
 */
export interface UserListStateInterface {
    list: UserInterface[];
}

export const userListStore = new Vuex.Store({
    state: <UserListStateInterface>{
        list: []
    },
    mutations: {
        /**
         * Добавить массив пользователей
         */
        addUsers: (state: UserListStateInterface, users: UserInterface[]) => {
            state.list = state.list.concat(users)
        },
        /**
         * Добавить пользователя в стек
         */
        addUser: (state: UserListStateInterface, user: UserInterface) => {
            state.list.push(user);
        },
        /**
         * Очистить весь стек пользователей
         */
        clear: (state: UserListStateInterface) => {
            state.list = [];
        },
        /**
         * Удалить пользователя из стека по идентификатору
         */
        removeUser: (state: UserListStateInterface, id: number) => {
            for (let i in state.list) {
                if (state.list[i].id == id) {
                    state.list.splice(parseInt(i), 1);
                }
            }
        },
        /**
         * Обновить пользователя
         */
        updateUser: (state: UserListStateInterface, user: UserInterface) => {
            for (let i in state.list) {
                if (user.id && state.list[i].id == user.id) {
                    state.list[i] = user;
                }
            }
        }
    },
    actions: {
        /**
         * Заполнить весь список пользователей из API
         */
        fetchList: (action) => {
            return new Promise((resolve, reject) => {
                // защита от задвоения
                if (action.state.list.length > 0) {
                    return resolve();
                }

                let currentPage = 1;
                let cnt = 0;

                let fetchItems = () => {
                    userManagerService
                        .list(currentPage)
                        .then((response: ListInterface<UserInterface>) => {
                            action.commit('addUsers', response.list);
                            cnt += response.list.length;
                            currentPage++;
                            if (response.totalCount > cnt) {
                                // запросить еще порцию пользователей
                                fetchItems();
                            } else {
                                resolve();
                            }
                        }).catch(() => reject());
                };

                fetchItems();
            });
        },
        /**
         * Добавление пользователя
         */
        addUser: (action, user: UserInterface) => {
            return new Promise((resolve) => {
                action.commit('addUser', user);
                resolve();
            });
        },
        /**
         * Редактировать пользователя
         */
        updateUser: (action, user: UserInterface) => {
            return new Promise((resolve) => {
                action.commit('updateUser', user);
                resolve();
            });
        },
        /**
         * Удалить пользователя
         */
        removeUser: (action, userId: number) => {
            return new Promise((resolve) => {
                action.commit('removeUser', userId);
                resolve();
            });
        },
        /**
         * Получить пользователя по идентификатору
         */
        getUser: (action, userId: number) => {
            return new Promise<UserInterface>((resolve, reject) => {
                action.dispatch('fetchList').then(() => {
                    for (let user of action.state.list) {
                        if (user.id == userId) {
                            resolve(user);
                            return;
                        }
                    }

                    reject();
                }).catch(reject);
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
