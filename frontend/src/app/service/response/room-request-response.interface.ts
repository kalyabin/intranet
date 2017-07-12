import {ValidationInterface} from "./validation.interface";
import {RoomRequestInterface} from "../model/room-request.interface";

/**
 * Ответ на создание или редактирование заявки бронирования комнаты
 */
export interface RoomRequestResponseInterface extends ValidationInterface {
    success: boolean;
    request: RoomRequestInterface;
}
