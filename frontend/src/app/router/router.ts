import VueRouter, {Route} from "vue-router";
import {routes} from './routes';
import {authUserService, AuthUserService} from "../service/auth-user.service";

/**
 * Конфигурация роутера
 */

export const router = new VueRouter({
    mode: 'history',
    routes: routes
});

let checkUserCanAccess = (authUserService: AuthUserService, to: Route, from: Route, next) => {
    let isAuth = authUserService.getIsAuth();

    if (isAuth && to.name == 'login') {
        // авторизованному пользователю на странице авторизации делать нечего
        next({name: 'dashboard'});
    } else if (!isAuth && to.name != 'login') {
        // неавторизованный пользователь должен попасть на страницу авторизации
        next({name: 'login'});
    } else {
        // во всех остальных случаях даем пользователю перейти на страницу
        next();
    }
};

/**
 * На каждый URL можно проходить только авторизованным пользователям.
 * В противном случае пользователь должен попасть на страницу авторизации.
 */
router.beforeEach((to: Route, from: Route, next) => {
    if (authUserService.needCheckAuth()) {
        // проверка авторизации
        authUserService.checkAuth().then(() => {
            checkUserCanAccess(authUserService, to, from, next);
        });
    } else {
        checkUserCanAccess(authUserService, to, from, next);
    }
});
