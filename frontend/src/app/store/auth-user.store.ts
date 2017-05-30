import Vuex from 'vuex';
import {UserInterface} from "../service/model/user.interface";
import Vue from "vue";
import {authUserService} from "../service/auth-user.service";
import {AuthInterface} from "../service/response/auth.interface";
import {TicketCategoryInterface} from "../service/model/ticket-category.interface";

Vue.use(Vuex);

/**
 * Интерфейс состояния пользователя
 */
export interface AuthUserStateInterface {
    isAuth: boolean;
    userData: UserInterface;
    isTemporaryPassword: boolean;
    roles: string[]
}

/**
 * Состояние текущего авторизованного пользователя
 */
export const authUserStore = new Vuex.Store({
    state: <AuthUserStateInterface>{
        // по умолчанию состояние пользователя - не известно: надо проверить статус авторизации
        isAuth: undefined,
        userData: null,
        isTemporaryPassword: false,
        roles: []
    },
    mutations: {
        isAuth: (state: AuthUserStateInterface, isAuth: boolean) => {
            state.isAuth = isAuth;
        },
        userData: (state: AuthUserStateInterface, userData: UserInterface) => {
            state.userData = userData;
        },
        isTemporaryPassword: (state: AuthUserStateInterface, isTemporaryPassword: boolean) => {
            state.isTemporaryPassword = isTemporaryPassword;
        },
        roles: (state: AuthUserStateInterface, roles: string[]) => {
            state.roles = roles;
        }
    },
    actions: {
        fetchData: (action) => {
            return new Promise((resolve, reject) => {
                authUserService.checkAuth().then((response: AuthInterface) => {
                    action.commit('isAuth', response.auth);
                    action.commit('userData', response.user);
                    action.commit('isTemporaryPassword', response.isTemporaryPassword);
                    action.commit('roles', response.roles);

                    resolve();
                }, () => reject());
            });
        }
    }
});
