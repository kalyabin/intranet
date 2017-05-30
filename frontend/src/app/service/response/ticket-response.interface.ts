import {ValidationInterface} from "./validation.interface";
import {TicketInterface} from "../model/ticket.interface";
import {UserInterface} from "../model/user.interface";

/**
 * Ответ на создание тикета
 */
export interface TicketResponseInterface extends ValidationInterface {
    /**
     * Успешное создание
     */
    success: boolean;

    /**
     * Созданный тикет
     */
    ticket: TicketInterface;

    /**
     * Назначенный менеджер. Ответ только для метода assign API
     */
    user?: UserInterface;
}
