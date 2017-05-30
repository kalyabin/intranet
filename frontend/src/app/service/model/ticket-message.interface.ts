import {UserInterface} from "./user.interface";

/**
 * Тип сообщения - вопрос или ответ
 */
type TicketMessageType = 'answer' | 'question';

/**
 * Модель сообщения в тикете
 */
export interface TicketMessageInterface {
    /**
     * Идентификатор сообщения
     */
    id: number;

    /**
     * Дата создания сообщения в формате Y-m-d H:i:s
     */
    createdAt: string;

    /**
     * Модель автора сообщения. Может отсутствовать, если пользователь был удалён
     */
    createdBy?: UserInterface;

    /**
     * Тип сообщения - вопрос или ответ
     */
    type: TicketMessageType;

    /**
     * Текст сообщения
     */
    text: string;
}
