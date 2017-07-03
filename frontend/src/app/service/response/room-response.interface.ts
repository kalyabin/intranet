import {ValidationInterface} from "./validation.interface";
import {RoomInterface} from "../model/room.interface";

/**
 * Ответ на создание или редактирование помещения
 */
export interface RoomResponseInterface extends ValidationInterface {
    /**
     * Доступно при удалении
     */
    id?: number;

    /**
     * Доступно при редактировании или добавлении
     */
    room?: RoomInterface;

    success: boolean;
}
