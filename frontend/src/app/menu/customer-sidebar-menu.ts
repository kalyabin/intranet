
import {SidebarMenuItem} from "./sidebar-menu-item";

/**
 * Меню для арендатора
 */
export const customerSidebarMenu: Array<SidebarMenuItem> = [
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
