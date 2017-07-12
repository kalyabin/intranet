/**
 * Статусы заявки
 */
import {RoomInterface} from "./room.interface";
import {CustomerInterface} from "./customer.interface";
export type RoomRequestStatus = 'pending' | 'approved' | 'declined' | 'cancelled';

/**
 * Модель заявки на аренду помещения
 */
export interface RoomRequestInterface {
    /**
     * Идентификатор
     */
    id?: number;

    /**
     * Время создания заявки в формате Y-m-d H:i:s
     */
    createdAt?: string;

    /**
     * Статус заявки
     */
    status?: RoomRequestStatus;

    /**
     * Заказанное помещение
     */
    room?: RoomInterface | number;

    /**
     * Заказчик
     */
    customer?: CustomerInterface | number;

    /**
     * Дата и время начала заявки в формате Y-m-d H:i:s
     */
    from?: string;

    /**
     * Дата и время окончания заявки в формате Y-m-d H:i:s
     */
    to?: string;

    /**
     * Комментарий менеджера
     */
    managerComment?: string;

    /**
     * Комментарий арендатора
     */
    customerComment?: string;
}
