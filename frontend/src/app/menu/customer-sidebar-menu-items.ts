
import {SidebarMenuItem} from "./sidebar-menu-item.interface";

/**
 * Перечисление всех доп. услуг
 */
const extendedServices: Array<{
    code: string,
    name: string,
    role: string
}> = [
    {
        code: 'it-department',
        name: 'IT-аутсорсинг',
        role: 'ROLE_IT_CUSTOMER',
    },
    {
        code: 'booker-department',
        name: 'Бухгалтер',
        role: 'ROLE_BOOKER_CUSTOMER',
    },
    {
        code: 'maintaince-department',
        name: 'Хаус-мастер',
        role: 'ROLE_MAINTAINCE_CUSTOMER'
    }
];

const buildExtendedServices = (): SidebarMenuItem[] => {
    let result = [];
    for (let item of extendedServices) {
        result.push({
            route: {
                name: 'cabinet_service_page',
                params: {
                    service: item.code,
                }
            },
            role: item.role,
            menuName: item.name
        });
    }
    return result;
};

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
        faIcon: 'fa-question',
        menuName: 'Заявки',
        children: []
    },
    {
        faIcon: 'fa-tag',
        menuName: 'Услуги',
        children: buildExtendedServices(),
    },
    {
        route: {
            name: 'cabinet_room_list',
        },
        faIcon: 'fa-building',
        menuName: 'Переговорные комнаты',
        role: 'ROLE_RENT_CUSTOMER'
    },
    {
        faIcon: 'fa-file',
        menuName: 'Документы',
        children: [
            {
                route: {
                    name: 'cabinet_ticket_list',
                    params: {
                        category: 'finance-department'
                    }
                },
                menuName: 'Задать вопрос'
            }
        ],
    }
];
