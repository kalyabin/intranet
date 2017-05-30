import {TicketInterface} from "../model/ticket.interface";
import {TicketHistoryInterface} from "../model/ticket-history.interface";
import {TicketMessageInterface} from "../model/ticket-message.interface";

/**
 * Детальная информация о тикете
 */
export interface TicketDetailsResponseInterface {
    /**
     * Модель тикета
     */
    ticket: TicketInterface;

    /**
     * История изменений по тикету
     */
    history: TicketHistoryInterface[];

    /**
     * Сообщения по тикету
     */
    messages: TicketMessageInterface[];
}
