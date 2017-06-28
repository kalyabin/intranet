import {ServiceActivatedInterface} from "../service/model/service-activated.interface";
import {ServiceInterface} from "../service/model/service.interface";
import Vuex from "vuex";
import {extendedServicesService} from "../service/extended-services-service";

/**
 * Интерфейс сторейджа для дополнительных услуг
 */
export interface ExtendedServiceState {
    /**
     * Список активированных услуг
     */
    activatedList: ServiceActivatedInterface[];

    /**
     * Список доступных услуг (актуальных)
     */
    actualList: ServiceInterface[];
}

export const extendedServiceStore = new Vuex.Store<ExtendedServiceState>({
    state: {
        activatedList: [],
        actualList: []
    },
    mutations: {
        /**
         * Заполнить список всех актуальных услуг для арендаторов
         */
        actualList: (state: ExtendedServiceState, list: ServiceInterface[]) => {
            state.actualList = list;
        },
        /**
         * Очистить список актуальных услуг для арендатор
         */
        clearActualList: (state: ExtendedServiceState) => {
            state.actualList.splice(0, state.actualList.length);
        },
        /**
         * Заполнить список активированных услуг у арендатора
         */
        activatedList: (state: ExtendedServiceState, list: ServiceActivatedInterface[]) => {
            state.activatedList = list;
        },
        /**
         * Добавить в список активированную услугу арендатора
         */
        addActivatedItem: (state: ExtendedServiceState, item: ServiceActivatedInterface) => {
            state.activatedList.push(item);
        },
        /**
         * Удалить из списка активированных услуг арендатора
         */
        removeActivatedItem: (state: ExtendedServiceState, service: ServiceInterface) => {
            for (let item of state.activatedList) {
                if (item.service.id == service.id) {
                    let index = state.activatedList.indexOf(item);
                    if (index > -1) {
                        state.activatedList.splice(index, 1);
                    }
                }
            }
        },
        /**
         * Очистить список активированных услуг
         */
        clearActivatedList: (state: ExtendedServiceState) => {
            state.activatedList.splice(0, state.activatedList.length);
        }
    },
    actions: {
        /**
         * Заполнить список активированных услуг
         */
        fetchActivatedList: (action) => {
            return new Promise((resolve) => {
                extendedServicesService.activatedList().then((response) => {
                    action.commit('activatedList', response.list);
                    resolve()
                }, () => resolve());
            });
        },
        /**
         * Заполнить список доступных услуг
         */
        fetchActualList: (action) => {
            return new Promise((resolve) => {
                extendedServicesService.actualList().then((response) => {
                    action.commit('actualList', response.list);
                    resolve();
                }, () => resolve());
            })
        },
        /**
         * Получить услугу по идентификатору
         */
        getServiceById: (action, id: string): Promise<ServiceInterface> => {
            return new Promise<ServiceInterface>((resolve, reject) => {
                for (let service of action.state.actualList) {
                    if (service.id == id) {
                        resolve(service);
                        return;
                    }
                }
                reject();
            });
        }
    }
});
