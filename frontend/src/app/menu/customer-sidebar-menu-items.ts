
import {SidebarMenuItem} from "./sidebar-menu-item.interface";

/**
 * Меню для арендатора
 */
export const customerSidebarMenuItems: Array<SidebarMenuItem> = [
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
    {
        faIcon: 'fa-tag',
        menuName: 'Услуги',
        children: [
            {
                route: {
                    name: 'cabinet_service_page',
                    params: {
                        service: 'it-department',
                    },
                },
                role: 'ROLE_IT_CUSTOMER',
                menuName: 'IT-аутсорсинг'
            },
        ],
    }
];
