/**
 * Элемент меню для левой колонки
 */
export interface SideBarMenuItem {
    route: any;
    faIcon?: string;
    menuName: string;
    role?: string;
    children?: SideBarMenuItem[];
}

/**
 * Меню для арендатора
 */
export const customerSideBarMenu: Array<SideBarMenuItem> = [
    {
        route: {
            name: 'cabinet_index',
        },
        faIcon: 'fa-home',
        menuName: 'Главная страница',
    },
    {
        route: {
            name: 'cabinet_ticket_root',
        },
        faIcon: 'fa-question',
        menuName: 'Заявки',
        children: []
    },
];

/**
 * Меню для менеджера
 */
export const managerSideBarMenu: Array<SideBarMenuItem> = [
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
            name: 'manager_ticket_root',
        },
        faIcon: 'fa-question',
        menuName: 'Заявки',
        children: []
    },
    {
        route: {
            name: 'dashboard_index',
        },
        faIcon: 'fa-building',
        menuName: 'Служба аренды',
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
