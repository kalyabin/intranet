import {TicketStatus} from "../service/model/ticket.interface";

/**
 * Словесное описание статуса тикета
 */
export const ticketStatusFilter = (value: TicketStatus) => {
    switch (value) {
        case 'new':
            return 'Новая';

        case 'in_progress':
            return 'В обрабтке';

        case 'answered':
            return 'Поступил ответ';

        case 'wait':
            return 'Ожидает ответа';

        case 'closed':
            return 'Закрыта';

        case 'reopened':
            return 'Открыта повторно';

        default:
            return value;
    }
};
