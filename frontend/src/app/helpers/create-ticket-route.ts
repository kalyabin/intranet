import {TicketCategoryInterface} from "../service/model/ticket-category.interface";
import {Location} from "vue-router";
import {ServiceInterface} from "../service/model/service.interface";

/**
 * Получить роут для создания нового тикета
 */
export const createTicketRouteHelper = (category: TicketCategoryInterface, service?: ServiceInterface): Location => {
    if (service && service.id) {
        return {
            name: 'cabinet_service_ticket_create',
            params: {
                service: service.id
            }
        };
    } else if (category && category.id) {
        return {
            name: 'cabinet_ticket_create',
            params: {
                category: category.id
            }
        };
    } else {
        return {
            name: 'cabinet_ticket_create_root'
        };
    }
};
