/**
 * Элемент меню для левой колонки
 */
export interface SideBarMenuItem {
    routeName: string;
    faIcon: string;
    menuName: string;
    role?: string;
}

export const sideBarMenus: Array<SideBarMenuItem> = [
    {
        routeName: 'auth_index',
        faIcon: 'fa-home',
        menuName: 'Главная страница',
    },
    {
        routeName: 'manager_user_list',
        faIcon: 'fa-users',
        menuName: 'Пользователи',
        role: 'ROLE_USER_MANAGEMENT',
    },
    {
        routeName: 'manager_customer_list',
        faIcon: 'fa-newspaper-o',
        menuName: 'Арендаторы',
        role: 'ROLE_USER_MANAGEMENT'
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-building',
        menuName: 'Служба аренды',
        role: 'ROLE_RENT_MANAGEMENT'
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-shopping-basket',
        menuName: 'Управление складом',
        role: 'ROLE_STORAGE_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-laptop',
        menuName: 'Заявки IT-аутсорсинг',
        role: 'ROLE_IT_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-calculator',
        menuName: 'Заявки SMART-бухгалтер',
        role: 'ROLE_BOOKER_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-money',
        menuName: 'Финансовые вопросы',
        role: 'ROLE_FINANCE_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-wrench',
        menuName: 'Служба эксплуатации',
        role: 'ROLE_MAINTAINCE_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-file',
        menuName: 'Управление документами',
        role: 'ROLE_DOCUMENT_MANAGEMENT',
    },
];
