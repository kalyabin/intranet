import Vuex from "vuex";
import {TicketInterface} from "../service/model/ticket.interface";
import {ticketService} from "../service/ticket.service";
import {ListInterface} from "../service/response/list.interface";
import * as moment from "moment";

/**
 * Состояние списка
 */
export interface TicketListStateInterface {
    list: TicketInterface[];
}

/**
 * Список тикетов в тикетной системе.
 *
 * В один момент в стейте может храниться список тикетов только одной категории.
 *
 * Для получения категорий тикетов необходимо использовать мутацию fetchList с указанием кода категории.
 */
export const ticketListStore = new Vuex.Store<TicketListStateInterface>({
    state: {
        list: []
    },
    mutations: {
        /**
         * Добавить список тикетов
         */
        addTickets: (state: TicketListStateInterface, tickets: TicketInterface[]) => {
            state.list = state.list.concat(tickets);
        },
        /**
         * Добавить тикет
         */
        addTicket: (state: TicketListStateInterface, ticket: TicketInterface) => {
            state.list.push(ticket);
        },
        /**
         * Обновление тикета в списке
         */
        updateTicket: (state: TicketListStateInterface, ticket: TicketInterface) => {
            for (let i in state.list) {
                if (ticket.id && state.list[i].id == ticket.id) {
                    state.list[i] = ticket;
                }
            }
        },
        /**
         * Удалить тикет из списка
         */
        removeTicket: (state: TicketListStateInterface, id: number) => {
            for (let i in state.list) {
                if (id && state.list[i].id == id) {
                    state.list.splice(parseInt(i), 1);
                }
            }
        },
        /**
         * Очистить весь стек тикетов
         */
        clear: (state: TicketListStateInterface) => {
            state.list = [];
        },
    },
    actions: {
        /**
         * Заполнить список
         */
        fetchList: (action, category: string = null, opened: boolean = true) => {
            return new Promise((resolve, reject) => {
                // защита от задвоения данных
                if (action.state.list.length > 0) {
                    return resolve();
                }

                let pageNum = 1;
                let cnt = 0;
                let fetchTickets = () => {
                    ticketService.list(category, opened, pageNum).then((response: ListInterface<TicketInterface>) => {
                        action.commit('addTickets', response.list);
                        pageNum++;
                        cnt += response.list.length;
                        if (response.totalCount > cnt) {
                            fetchTickets();
                        } else {
                            // отсортировать тикеты
                            action.state.list = action.state.list.sort((itemA: TicketInterface, itemB: TicketInterface) => {
                                // закрытие заявки идут ниже
                                if (itemA.currentStatus == 'closed' && itemA.currentStatus != itemB.currentStatus) {
                                    return 1;
                                } else if (itemB.currentStatus == 'closed' && itemB.currentStatus != itemA.currentStatus) {
                                    return -1;
                                }

                                let dateA = moment(itemA.updatedAt);
                                let dateB = moment(itemB.updatedAt);

                                if (dateA.isBefore(dateB)) {
                                    return -1;
                                } else if (dateB.isBefore(dateA)) {
                                    return 1;
                                } else {
                                    return 0;
                                }
                            });
                            resolve();
                        }
                    }).catch(() => reject());
                };
                fetchTickets();
            });
        },
        /**
         * Редактирование тикета
         */
        updateTicket: (action, ticket: TicketInterface) => {
            return new Promise((resolve) => {
                action.commit('updateTicket', ticket);
                resolve();
            });
        },
        /**
         * Добавить тикет
         */
        addTicket: (action, ticket: TicketInterface) => {
            return new Promise((resolve) => {
                action.commit('addTicket', ticket);
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
