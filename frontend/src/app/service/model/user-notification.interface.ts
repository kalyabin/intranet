/**
 * Тип уведомления
 */
import {UserInterface} from "./user.interface";
import {TicketInterface} from "./ticket.interface";
import {TicketMessageInterface} from "./ticket-message.interface";
import {ServiceInterface} from "./service.interface";
import {ServiceTariffInterface} from "./service-tariff.interface";
import {CustomerInterface} from "./customer.interface";
import {RoomInterface} from "./room.interface";
export type UserNotificationType = 'ticket_new' | 'ticket_message' | 'ticket_manager_set' | 'ticket_closed' |
    'incoming_call' | 'service_activated' | 'service_deactivated' |
    'room_request_created' | 'room_request_cancelled' | 'room_request_updated';

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
     * Подключенная или отключенная услуга
     */
    service?: ServiceInterface;

    /**
     * Подключенный тариф
     */
    tariff?: ServiceTariffInterface;

    /**
     * Входящий номер телефона, если тип уведомления incoming_call
     */
    callerId: string;

    /**
     * Сопроводительный комментарий
     */
    comment?: string;

    /**
     * Дата и время заявки на бронирование комнаты
     */
    from?: string;

    /**
     * Модель арендатора для бронирования комнаты
     */
    customer?: CustomerInterface;

    /**
     * Модель комнаты для бронирования
     */
    room?: RoomInterface;
}
