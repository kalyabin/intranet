import {UserInterface} from "./user.interface";
import {TicketStatus} from "./ticket.interface";

/**
 * Модель элемента истории из тикетной системы
 */
export interface TicketHistoryInterface {
    /**
     * Идентификатор элемента истории
     */
    id: number;

    /**
     * Дата создания в формате Y-m-d H:i:s
     */
    createdAt: string;

    /**
     * Автор смены статуса
     */
    createdBy: UserInterface;

    /**
     * Статус на который был изменен тикет
     */
    status: TicketStatus;
}
