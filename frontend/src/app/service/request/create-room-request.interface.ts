import {RoomRequestStatus} from "../model/room-request.interface";
/**
 * Запрос на создание заявки на бронирование помещения
 */
export interface UpdateRoomRequestInterface {
    /**
     * Идентификатор помещения
     */
    room: number;

    /**
     * Идентификатор арендатора, если заявку создаёт менеджер
     */
    customer?: number;

    /**
     * Статус заявки, если заявку редактирует менеджер
     */
    status?: RoomRequestStatus;

    /**
     * Дата начала бронирования
     */
    from: string;

    /**
     * Дата окончания бронирования
     */
    to: string;

    /**
     * Комментарий арендатора
     */
    customerComment?: string;

    /**
     * Комментарий менеджера
     */
    managerComment?: string;
}
