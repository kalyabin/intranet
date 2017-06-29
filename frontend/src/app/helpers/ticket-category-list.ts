import {TicketCategoryInterface} from "../service/model/ticket-category.interface";
import {ServiceInterface} from "../service/model/service.interface";
import {Location} from "vue-router";
import {authUserStore} from "../store/auth-user.store";

/**
 * Получить ссылку на список тикетов в категории
 */
export const ticketCategoryListHelper = (category: TicketCategoryInterface, service?: ServiceInterface): Location => {
    if (authUserStore.state.userData.userType == 'customer' && service) {
        return {
            name: 'cabinet_service_page',
            params: <any>{
                service: service.id
            }
        };
    } else {
        return {
            name: authUserStore.state.userData.userType == 'customer' ? 'cabinet_ticket_list' : 'manager_ticket_list',
            params: <any> {
                category: category ? category.id : null
            }
        };
    }
};
