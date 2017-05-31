import {TicketStatus} from "../service/model/ticket.interface";

/**
 * Фильтр для подсветки статусов тикетов
 */
export const ticketStatusColorFilter = (value: TicketStatus) => {
    let defaultClass = 'label ';
    switch (value) {
        case 'new':
            return `${defaultClass} label-info`;

        case 'in_progress':
            return `${defaultClass} label-primary`;

        case 'answered':
            return `${defaultClass} label-success`;

        case 'wait':
            return `${defaultClass} label-warning`;

        case 'closed':
            return `${defaultClass} label-default`;

        case 'reopened':
            return `${defaultClass} label-danger`;

        default:
            return value;
    }
};
