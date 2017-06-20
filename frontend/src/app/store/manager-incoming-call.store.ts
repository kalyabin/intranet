import Vuex from "vuex";
import {UserNotificationInterface} from "../service/model/user-notification.interface";

/**
 * Стейт входящего звонка для менеджера
 */
export interface ManagerIncomingCallState {
    notification: UserNotificationInterface;
}

/**
 * Сторейдж входящего звонка для менеджера с АТС.
 * Входящий звонок декларируется интерфейсом уведомления с типом incoming_call.
 * В один момент времени может быть только один входящий звонок
 */
export let managerIncomingCallStore = new Vuex.Store<ManagerIncomingCallState>({
    state: <ManagerIncomingCallState>{
        notification: null
    },
    mutations: {
        /**
         * Поместить входящий звонок
         */
        incomingCall: (state: ManagerIncomingCallState, notification: UserNotificationInterface) => {
            state.notification = notification;
        },
        /**
         * Удалить входящий звонок
         */
        removeIncomingCall: (state: ManagerIncomingCallState) => {
            state.notification = null;
        }
    }
});
