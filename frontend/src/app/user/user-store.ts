import Vuex from 'vuex';
import {UserInterface} from "../service/model/user.interface";
import Vue from "vue";

Vue.use(Vuex);

/**
 * Интерфейс состояния пользователя
 */
export interface UserStateInterface {
    isAuth: boolean;
    userData: UserInterface;
    isTemporaryPassword: boolean;
    roles: string[]
}

/**
 * Состояние текущего авторизованного пользователя
 */
export const userStore = new Vuex.Store({
    state: <UserStateInterface>{
        // по умолчанию состояние пользователя - не известно: надо проверить статус авторизации
        isAuth: undefined,
        userData: null,
        isTemporaryPassword: false,
        roles: []
    },
    mutations: {
        isAuth: (state: UserStateInterface, isAuth: boolean) => {
            state.isAuth = isAuth;
        },
        userData: (state: UserStateInterface, userData: UserInterface) => {
            state.userData = userData;
        },
        isTemporaryPassword: (state: UserStateInterface, isTemporaryPassword: boolean) => {
            state.isTemporaryPassword = isTemporaryPassword;
        },
        roles: (state: UserStateInterface, roles: string[]) => {
            state.roles = roles;
        }
    }
});
