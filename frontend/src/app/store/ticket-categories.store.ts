import {TicketCategoryInterface} from "../service/model/ticket-category.interface";
import Vuex from "vuex";
import {ticketService} from "../service/ticket.service";

/**
 * Список доступных пользвоателю категорий (очередей) для тикетной системы
 */
export interface TicketCategoriesStateInterface {
    list: TicketCategoryInterface[];
}

export const ticketCategoriesStore = new Vuex.Store<TicketCategoriesStateInterface>({
    state: {
        list: []
    },
    mutations: {
        addCategories: (state: TicketCategoriesStateInterface, list: TicketCategoryInterface) => {
            state.list = state.list.concat(list);
        },
        clear: (state: TicketCategoriesStateInterface) => {
            state.list = [];
        }
    },
    actions: {
        /**
         * Заполнить список доступных категорий для пользователя
         */
        fetchList: (action) => {
            return new Promise<TicketCategoryInterface[]>((resolve, reject) => {
                if (action.state.list.length > 0) {
                    return resolve(action.state.list);
                }
                ticketService.categories().then((response: TicketCategoryInterface[]) => {
                    action.commit('addCategories', response);
                    resolve(response);
                }, () => reject([]));
            });
        },
        /**
         * Проверить роль пользователя для просмотра категории.
         *
         * Стейт авторизованного пользователя authUserStore уже должен быть заполнен.
         */
        checkCategory: (action, categoryId: string) => {
            return new Promise<TicketCategoryInterface>((resolve, reject) => {
                action.dispatch('fetchList').then((categories: TicketCategoryInterface[]) => {
                    for (let category of categories) {
                        if (category.id == categoryId) {
                            return resolve(category);
                        }
                    }
                    return reject(null);
                }, () => reject(null))
            });
        },
        /**
         * Очистить список доступных категорий для пользователя
         */
        clear: (action) => {
            return new Promise((resolve) => {
                action.commit('clear');
                resolve();
            });
        }
    }
});
