import LoginComponent from "../page/sign-in/sign-in";
import DashboardComponent from "../page/dashboard";
import {RouteConfig} from "vue-router";
import RestorePasswordComponent from "../page/sign-in/restore-password";
import PageNotFoundComponent from "../page-not-found.component";
import PageAccessDeniedComponent from "../page-access-denied.component";
import IndexComponent from "../page/index";
import UserManagerListComponent from "../page/manager/user/list";

/**
 * Правила роутинга
 */
export const routes: Array<RouteConfig> = [
    {
        path: '/',
        name: 'login',
        component: LoginComponent,
        meta: {
            needNotAuth: true
        }
    },
    {
        path: '/change-password/:checkerId/:checkerCode',
        name: 'restore-password',
        component: RestorePasswordComponent,
        meta: {
            needNotAuth: true
        }
    },
    {
        path: '/auth',
        name: 'dashboard',
        component: DashboardComponent,
        meta: {
            needAuth: true,
            pageTitle: 'Личный кабинет'
        },
        children: [
            {
                path: '/auth/index',
                name: 'auth_index',
                component: IndexComponent,
                meta: {
                    needAuth: true,
                    pageTitle: 'Личный кабинет'
                }
            },
            {
                path: '/auth/manager/user',
                name: 'user_manager_list',
                component: UserManagerListComponent,
                meta: {
                    needRole: 'USER_MANAGEMENT',
                    pageTitle: 'Управление пользователями'
                }
            },
        ]
    },
    {
        path: '/404',
        name: '404',
        component: PageNotFoundComponent
    },
    {
        path: '/403',
        name: '403',
        component: PageAccessDeniedComponent,
    }
];
