import Vuex from "vuex";
import {RolesResponseInterface} from "../service/response/roles-response.interface";
import {userManagerService} from "../service/user-manager.service";

/**
 * Список ролей для менеджера
 */
export interface RolesListStateInterface {
    rolesLabels: {[key: string]: string};
    rolesHierarchy: {[key: string]: string[]};
    rolesByUserType: {[key: string]: Array<string>}
}

export const rolesListStore = new Vuex.Store({
    state: <RolesListStateInterface>{
        rolesLabels: {},
        rolesHierarchy: {},
        rolesByUserType: {}
    },
    mutations: {
        fetchRolesData: (state: RolesListStateInterface, response: RolesResponseInterface) => {
            state.rolesHierarchy = response.hierarchy;
            state.rolesLabels = response.labels;
            state.rolesByUserType = response.roles;
        }
    },
    actions: {
        fetchData: (action) => {
            return new Promise((resolve, reject) => {
                userManagerService.roles().then((response: RolesResponseInterface) => {
                    action.commit('fetchRolesData', response);
                    resolve();
                }).catch(() => reject());
            });
        }
    }
});
