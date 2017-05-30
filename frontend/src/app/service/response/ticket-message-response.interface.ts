import {ValidationInterface} from "./validation.interface";
import {TicketInterface} from "../model/ticket.interface";
import {TicketMessageInterface} from "../model/ticket-message.interface";

/**
 * Ответ на создание сообщение по тикету
 */
export interface TicketMessageResponseInterface extends ValidationInterface {
    /**
     * Модель тикета
     */
    ticket: TicketInterface;

    /**
     * Модель созданного сообщения
     */
    message?: TicketMessageInterface;

    /**
     * Флаг успешного запроса
     */
    success: boolean;
}
