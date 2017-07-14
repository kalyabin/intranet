import {RoomInterface} from "../service/model/room.interface";
import Vuex from "vuex";
import {roomManagerService} from "../service/room-manager.service";
import {roomCustomerService} from "../service/room-customer.service";

/**
 * Список комнат для отображения
 */
export interface RoomListStateInterface {
    list: RoomInterface[];
}

export const roomListStore = new Vuex.Store<RoomListStateInterface>({
    state: <RoomListStateInterface>{
        list: []
    },
    mutations: {
        /**
         * Добавить новые элементы
         */
        addItems: (state: RoomListStateInterface, items: RoomInterface[]) => {
            state.list = state.list.concat(state.list, items);
        },
        /**
         * Добавить элемент
         */
        addItem: (state: RoomListStateInterface, item: RoomInterface) => {
            state.list.push(item);
        },
        /**
         * Замена элемента
         */
        updateItem: (state: RoomListStateInterface, item: RoomInterface) => {
            for (let i in state.list) {
                if (state.list[i].id == item.id) {
                    state.list[i] = item;
                }
            }
        },
        /**
         * Удалить элемент
         */
        removeItem: (state: RoomListStateInterface, id: number) => {
            for (let item of state.list) {
                if (id && item.id == id) {
                    let i = state.list.indexOf(item);
                    state.list.splice(i, 1);
                }
            }
        },
        /**
         * Очистка всего списка
         */
        clearList: (state: RoomListStateInterface) => {
            state.list.splice(0, state.list.length);
        }
    },
    actions: {
        /**
         * Заполнить менеджерский список
         */
        fetchManagerList: (action): Promise<boolean> => {
            return new Promise((resolve) => {
                roomManagerService.list().then((list: RoomInterface[]) => {
                    action.commit('addItems', list);
                    resolve(true);
                }, () => {
                    resolve(false);
                });
            });
        },
        /**
         * Заполнить пользовательский список
         */
        fetchCustomerList: (action): Promise<boolean> => {
            return new Promise((resolve) => {
                roomCustomerService.list().then((list: RoomInterface[]) => {
                    action.commit('addItems', list);
                    resolve(true);
                }, () => {
                    resolve(false);
                });
            });
        }
    }
});
