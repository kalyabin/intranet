import {UserInterface} from "./user.interface";
import {CustomerInterface} from "./customer.interface";

/**
 * Статусы тикетов
 */
export type TicketStatus = 'new' | 'in_progress' | 'answered' | 'wait' | 'closed' | 'reopened';

/**
 * Модель заявки в тикетной системе
 */
export interface TicketInterface {
    /**
     * Идентификатор тикета
     */
    id: number;

    /**
     * Номер тикета
     */
    number: string;

    /**
     * Дата создания тикета в формате Y-m-d H:i:s
     */
    createdAt: string;

    /**
     * Пользователь создавший тикет: может отсутствовать, если был удален
     */
    createdBy?: UserInterface;

    /**
     * Менеджер по тикету: может отсутствовать, если был удален либо еще не назначен
     */
    managedBy?: UserInterface;

    /**
     * Идентификатор категории тикетной системы
     */
    category: string;

    /**
     * Текущий статус тикета
     */
    currentStatus: TicketStatus;

    /**
     * Дата последнего вопроса по тикету от арендатора в формате Y-m-d H:i:s
     */
    lastQuestionAt?: string;

    /**
     * Дата последнего ответа от менеджера по тикету в формате Y-m-d H:i:s
     */
    lastAnswerAt?: string;

    /**
     * Дата автоматического закрытия тикета в формате Y-m-d H:i:s
     */
    voidedAt?: string;

    /**
     * Арендатор. Может отсутствовать, если был удалён
     */
    customer?: CustomerInterface;

    /**
     * Заголовок тикета
     */
    title: string;
}
