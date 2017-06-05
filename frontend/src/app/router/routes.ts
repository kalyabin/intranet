import {SignIn} from "../page/sign-in/sign-in";
import {Dashboard} from "../page/dashboard";
import {RouteConfig} from "vue-router";
import {RestorePassword} from "../page/sign-in/restore-password";
import {NotFound} from "../page/error/not-found";
import {AccessDenied} from "../page/error/access-denied";
import {Index} from "../page/index";
import {ManagerUserList} from "../page/manager/user/list";
import {ManagerCustomerList} from "../page/manager/customer/list";
import {TicketList} from "../page/ticket/list";
import {TicketForm} from "../page/ticket/ticket-form";
import {TicketDetails} from "../page/ticket/details";

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
        path: '/auth/cabinet',
        name: 'cabinet',
        component: Dashboard,
        meta: {
            needType: 'customer',
            pageTitle: 'Личный кабинет',
        },
        children: [
            {
                path: '/auth/cabinet/index',
                name: 'cabinet_index',
                component: Index
            },
            {
                path: '/auth/cabinet/ticket',
                name: 'cabinet_ticket_root',
                component: TicketList,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/cabinet/ticket/:category',
                name: 'cabinet_ticket_list',
                component: TicketList,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/cabinet/ticket/:category/create',
                name: 'cabinet_ticket_create',
                component: TicketForm,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/cabinet/ticket/create',
                name: 'cabinet_ticket_create_root',
                component: TicketForm,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/cabinet/ticket/:category/:ticket',
                name: 'cabinet_ticket_details',
                component: TicketDetails,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявка'
                },
            },
        ],
    },
    {
        path: '/auth/manager',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            needType: 'manager',
            pageTitle: 'Панель управления'
        },
        children: [
            {
                path: '/auth/manager/index',
                name: 'dashboard_index',
                component: Index
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
                path: '/auth/manager/ticket',
                name: 'manager_ticket_root',
                component: TicketList,
                meta: {
                    needType: 'manager',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/manager/ticket/:category',
                name: 'manager_ticket_list',
                component: TicketList,
                meta: {
                    needType: 'manager',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/manager/ticket/:category/:ticket',
                name: 'manager_ticket_details',
                component: TicketDetails,
                meta: {
                    needType: 'manager',
                    pageTitle: 'Заявка'
                },
            },
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
