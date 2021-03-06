
import {SidebarMenuItem} from "./sidebar-menu-item.interface";

/**
 * Меню для менеджера
 */
export const managerSidebarMenuItems: Array<SidebarMenuItem> = [
    {
        route: {
            name: 'dashboard_index',
        },
        faIcon: 'fa-home',
        menuName: 'Главная страница',
    },
    {
        route: {
            name: 'manager_user_list',
        },
        faIcon: 'fa-users',
        menuName: 'Пользователи',
        role: 'ROLE_USER_MANAGEMENT',
    },
    {
        route: {
            name: 'manager_customer_list',
        },
        faIcon: 'fa-newspaper-o',
        menuName: 'Арендаторы',
        role: 'ROLE_USER_MANAGEMENT'
    },
    {
        route: {
            name: 'manager_service_list',
        },
        faIcon: 'fa-wrench',
        menuName: 'Дополнительные услуги',
        role: 'ROLE_SERVICE_MANAGEMENT',
    },
    {
        route: {
            name: 'manager_ticket_root',
        },
        faIcon: 'fa-question',
        menuName: 'Заявки',
        children: []
    },
    {
        route: {
            name: 'manager_room_list',
        },
        faIcon: 'fa-building',
        menuName: 'Переговорные комнаты',
        role: 'ROLE_RENT_MANAGEMENT'
    },
    {
        route: {
            name: 'dashboard_index',
        },
        faIcon: 'fa-shopping-basket',
        menuName: 'Управление складом',
        role: 'ROLE_STORAGE_MANAGEMENT',
    },
    {
        route: {
            name: 'dashboard_index',
        },
        faIcon: 'fa-file',
        menuName: 'Управление документами',
        role: 'ROLE_DOCUMENT_MANAGEMENT',
    },
];
