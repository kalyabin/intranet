import {SignIn} from "../page/sign-in/sign-in";
import {Dashboard} from "../page/dashboard";
import {RouteConfig} from "vue-router";
import {RestorePassword} from "../page/sign-in/restore-password";
import {NotFound} from "../page/error/not-found";
import {AccessDenied} from "../page/error/access-denied";
import {Index} from "../page/index";
import {ManagerUserList} from "../page/manager/user/list";
import {ManagerCustomerList} from "../page/manager/customer/list";
import {ManagerTicketList} from "../page/manager/ticket/list";

/**
 * Правила роутинга
 */
export const routes: Array<RouteConfig> = [
    {
        path: '/',
        name: 'login',
        component: SignIn,
        meta: {
            needNotAuth: true
        }
    },
    {
        path: '/change-password/:checkerId/:checkerCode',
        name: 'restore-password',
        component: RestorePassword,
        meta: {
            needNotAuth: true
        }
    },
    {
        path: '/auth',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            needAuth: true,
            pageTitle: 'Личный кабинет'
        },
        children: [
            {
                path: '/auth/index',
                name: 'auth_index',
                component: Index,
                meta: {
                    needAuth: true,
                    pageTitle: 'Личный кабинет'
                }
            },
            {
                path: '/auth/manager/user',
                name: 'manager_user_list',
                component: ManagerUserList,
                meta: {
                    needRole: 'ROLE_USER_MANAGEMENT',
                    pageTitle: 'Управление пользователями'
                }
            },
            {
                path: '/auth/manager/customer',
                name: 'manager_customer_list',
                component: ManagerCustomerList,
                meta: {
                    needRole: 'ROLE_USER_MANAGEMENT',
                    pageTitle: 'Управление арендаторами'
                }
            },
            {
                path: '/auth/manager/ticket/:category',
                name: 'manager_ticket_list',
                component: ManagerTicketList,
                meta: {
                    needAuth: true,
                    pageTitle: 'Тикетная система'
                }
            }
        ]
    },
    {
        path: '/404',
        name: '404',
        component: NotFound
    },
    {
        path: '/403',
        name: '403',
        component: AccessDenied,
    }
];
