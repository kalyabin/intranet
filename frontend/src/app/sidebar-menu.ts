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
        role: 'USER_MANAGEMENT',
    },
    {
        routeName: 'manager_customer_list',
        faIcon: 'fa-newspaper-o',
        menuName: 'Арендаторы',
        role: 'USER_MANAGEMENT'
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-building',
        menuName: 'Служба аренды'
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-shopping-basket',
        menuName: 'Управление складом',
        role: 'STORAGE_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-laptop',
        menuName: 'Заявки IT-аутсорсинг',
        role: 'IT_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-calculator',
        menuName: 'Заявки SMART-бухгалтер',
        role: 'BOOKER_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-money',
        menuName: 'Финансовые вопросы',
        role: 'FINANCE_MANAGEMENT',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-wrench',
        menuName: 'Служба эксплуатации',
    },
    {
        routeName: 'auth_index',
        faIcon: 'fa-file',
        menuName: 'Управление документами',
        role: 'DOCUMENT_MANAGEMENT',
    },
];
