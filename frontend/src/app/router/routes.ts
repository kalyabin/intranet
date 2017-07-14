import {SignIn} from "../page/sign-in/sign-in";
import {Dashboard} from "../page/dashboard";
import {RouteConfig} from "vue-router";
import {RestorePassword} from "../page/sign-in/restore-password";
import {NotFound} from "../page/error/not-found";
import {AccessDenied} from "../page/error/access-denied";
import {Index} from "../page/index";
import {ManagerUserList} from "../page/manager/user/list";
import {ManagerCustomerList} from "../page/manager/customer/list";
import {CustomerTicketList} from "../page/customer/ticket/list";
import {CustomerTicketForm} from "../page/customer/ticket/form";
import {CustomerTicketDetails} from "../page/customer/ticket/details";
import {ManagerServiceList} from "../page/manager/service/list";
import {CustomerServicePage} from "../page/customer/service/page";
import {CustomerServiceTicketDetails} from "../page/customer/service/ticket-details";
import {ManagerTicketList} from "../page/manager/ticket/list";
import {ManagerTicketDetails} from "../page/manager/ticket/details";
import {ManagerRoomList} from "../page/manager/rent/room-list";
import {ManagerRoomCalendar} from "../page/manager/rent/room-calendar";
import {CustomerRoomList} from "../page/customer/rent/room-list";
import {CustomerRoomCalendar} from "../page/customer/rent/room-calendar";

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
                path: '/auth/cabinet/ticket/:category',
                name: 'cabinet_ticket_list',
                component: CustomerTicketList,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/cabinet/ticket/:category/create',
                name: 'cabinet_ticket_create',
                component: CustomerTicketForm,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/cabinet/ticket/:category/:ticket',
                name: 'cabinet_ticket_details',
                component: CustomerTicketDetails,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Заявка'
                },
            },
            {
                path: '/auth/cabinet/service/:service',
                name: 'cabinet_service_page',
                component: CustomerServicePage,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Услуги'
                },
            },
            {
                path: '/auth/cabinet/service/:service/ticket/create',
                name: 'cabinet_service_ticket_create',
                component: CustomerTicketForm,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Услуги'
                }
            },
            {
                path: '/auth/cabinet/service/:service/ticket/:ticket',
                name: 'cabinet_service_ticket_details',
                component: CustomerServiceTicketDetails,
                meta: {
                    needType: 'customer',
                    pageTitle: 'Услуги'
                }
            },
            {
                path: '/auth/cabinet/rent/room',
                name: 'cabinet_room_list',
                component: CustomerRoomList,
                meta: {
                    needRole: 'ROLE_RENT_CUSTOMER',
                    pageTitle: 'Переговорные комнаты'
                }
            },
            {
                path: '/auth/cabinet/rent/room/:id',
                name: 'cabinet_room_calendar',
                component: CustomerRoomCalendar,
                meta: {
                    needRole: 'ROLE_RENT_CUSTOMER',
                    pageTitle: 'Переговорные комнаты'
                }
            }
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
                component: ManagerTicketList,
                meta: {
                    needType: 'manager',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/manager/ticket/:category',
                name: 'manager_ticket_list',
                component: ManagerTicketList,
                meta: {
                    needType: 'manager',
                    pageTitle: 'Заявки'
                }
            },
            {
                path: '/auth/manager/ticket/:category/:ticket',
                name: 'manager_ticket_details',
                component: ManagerTicketDetails,
                meta: {
                    needType: 'manager',
                    pageTitle: 'Заявка'
                },
            },
            {
                path: '/auth/manager/service',
                name: 'manager_service_list',
                component: ManagerServiceList,
                meta: {
                    needRole: 'ROLE_SERVICE_MANAGEMENT',
                    pageTitle: 'Дополнительные услуги'
                },
            },
            {
                path: '/auth/manager/rent/room',
                name: 'manager_room_list',
                component: ManagerRoomList,
                meta: {
                    needRole: 'ROLE_RENT_MANAGEMENT',
                    pageTitle: 'Переговорные комнаты'
                },
            },
            {
                path: '/auth/manager/rent/room/:id',
                name: 'manager_room_calendar',
                component: ManagerRoomCalendar,
                meta: {
                    needRole: 'ROLE_RENT_MANAGEMENT',
                    pageTitle: 'Переговорные комнаты'
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
