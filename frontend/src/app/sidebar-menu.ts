/**
 * Элемент меню для левой колонки
 */
export interface SideBarMenuItem {
    route: Object;
    faIcon: string;
    menuName: string;
    role?: string;
}

export const sideBarMenus: Array<SideBarMenuItem> = [
    {
        route: {
            name: 'auth_index',
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
            name: 'auth_index',
        },
        faIcon: 'fa-building',
        menuName: 'Служба аренды',
        role: 'ROLE_RENT_MANAGEMENT'
    },
    {
        route: {
            name: 'auth_index',
        },
        faIcon: 'fa-shopping-basket',
        menuName: 'Управление складом',
        role: 'ROLE_STORAGE_MANAGEMENT',
    },
    {
        route: {
            name: 'manager_ticket_list',
            params: {
                category: 'it-department',
            },
        },
        faIcon: 'fa-laptop',
        menuName: 'Заявки IT-аутсорсинг',
        role: 'ROLE_IT_MANAGEMENT',
    },
    {
        route: {
            name: 'manager_ticket_list',
            params: {
                category: 'booker-department',
            },
        },
        faIcon: 'fa-calculator',
        menuName: 'Заявки SMART-бухгалтер',
        role: 'ROLE_BOOKER_MANAGEMENT',
    },
    {
        route: {
            name: 'manager_ticket_list',
            params: {
                category: 'finance-department',
            },
        },
        faIcon: 'fa-money',
        menuName: 'Финансовые вопросы',
        role: 'ROLE_FINANCE_MANAGEMENT',
    },
    {
        route: {
            name: 'manager_ticket_list',
            params: {
                category: 'maintaince-department'
            },
        },
        faIcon: 'fa-wrench',
        menuName: 'Служба эксплуатации',
        role: 'ROLE_MAINTAINCE_MANAGEMENT',
    },
    {
        route: {
            name: 'auth_index',
        },
        faIcon: 'fa-file',
        menuName: 'Управление документами',
        role: 'ROLE_DOCUMENT_MANAGEMENT',
    },
];
