import VueRouter, {Route} from "vue-router";
import {routes} from './routes';
import {authUserStore} from "../store/auth-user.store";
import {pageMetaStore} from "./page-meta-store";

/**
 * Конфигурация роутера
 */

export const router = new VueRouter({
    mode: 'history',
    routes: routes
});

/**
 * На каждый URL можно проходить только авторизованным пользователям.
 * В противном случае пользователь должен попасть на страницу авторизации.
 */
router.beforeEach((to: Route, from: Route, next) => {
    authUserStore.dispatch('fetchData').then(() => {
        let isAuth = authUserStore.state.isAuth;
        let needAuth = !!(to.meta && to.meta['needAuth']);
        let needNotAuth = !!(to.meta && to.meta['needNotAuth']);
        let needRole = to.meta && to.meta['needRole'] ? to.meta['needRole'] : '';

        // проверка необходимых ролей
        let hasRole = (role: string): boolean => {
            let roles = authUserStore.state.roles;
            return !!(roles && roles.indexOf(role) != -1);
        };

        if (isAuth && needNotAuth) {
            // авторизованному пользователю на этой странице делать нечего
            next({name: 'dashboard'});
        } else if (!isAuth && (needAuth || needRole)) {
            // требуется авторизовация
            next({name: 'login'});
        } else if (needRole && !hasRole(needRole)) {
            // роль для просмотра страницы не совпадает
            next({name: '403'});
        } else {
            // во всех остальных случаях даем пользователю перейти на страницу
            let pageTitle = to.meta.pageTitle || '';
            let title = to.meta.title || '';
            pageMetaStore.commit('setPageTitle', pageTitle);
            pageMetaStore.commit('setTitle', title);
            next();
        }
    });
});
