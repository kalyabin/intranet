/**
 * Тип уведомления
 */
import {UserInterface} from "./user.interface";
import {TicketInterface} from "./ticket.interface";
import {TicketMessageInterface} from "./ticket-message.interface";
export type UserNotificationType = 'ticket_new' | 'ticket_message' | 'ticket_manager_set' | 'ticket_closed';

/**
 * Модель системного уведомления для пользователя
 */
export interface UserNotificationInterface {
    id: number;

    /**
     * Дата уведомления в формате Y-m-d H:i:s
     */
    createdAt: string;

    /**
     * Тип уведомления
     */
    type: UserNotificationType;

    /**
     * Прочитано уведомление или нет
     */
    isRead: boolean;

    /**
     * Автор уведомления (пусто, если автор был удален, либо он априори отсутствует)
     */
    author?: UserInterface;

    /**
     * Заявка для отображения уведомления (пусто, если тип уведомления не привязан к заявке)
     */
    ticket?: TicketInterface;

    /**
     * Сообщение по заявке (пусто, если тип уведомления не привязан к заявке или сообщению)
     */
    ticketMessage?: TicketMessageInterface;

    /**
     * Установленный менеджер по заявке
     */
    ticketManager?: UserInterface;

    /**
     * Входящий номер телефона, если тип уведомления incoming_call
     */
    callerId: string;

    /**
     * Сопроводительный комментарий
     */
    comment: string;
}
