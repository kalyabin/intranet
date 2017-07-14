import {RoomInterface} from "./room.interface";
import {RoomRequestInterface} from "./room-request.interface";

/**
 * Ответ для детального описания переговорки для арендатора
 */
export interface CustomerRoomDetailsInterface {
    /**
     * Комната
     */
    room: RoomInterface;

    /**
     * Список броней для комнаты для текущего арендатора
     */
    myRequests: RoomRequestInterface[];

    /**
     * Забронированные диапазоны времени другими арендаторами
     */
    reserved: {from: string, to: string}[];
}
