import {TicketInterface} from "../service/model/ticket.interface";
import {ServiceInterface} from "../service/model/service.interface";
import {Location} from "vue-router";
import {authUserStore} from "../store/auth-user.store";

/**
 * Получить ссылку на просмотр тикета
 */
export const ticketDetailsRouteHelper = (ticket: TicketInterface, service?: ServiceInterface): Location => {
    if (authUserStore.state.userData.userType == 'customer' && service) {
        return {
            name: 'cabinet_service_ticket_details',
            params: <any>{
                service: service.id,
                ticket: ticket.id
            }
        };
    } else {
        return {
            name: authUserStore.state.userData.userType == 'customer' ? 'cabinet_ticket_details' : 'manager_ticket_details',
            params: <any>{
                category: ticket.category,
                ticket: ticket.id
            }
        };
    }
};
