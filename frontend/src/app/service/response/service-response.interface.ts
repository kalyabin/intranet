import {ValidationInterface} from "./validation.interface";
import {ServiceInterface} from "../model/service.interface";

/**
 * Модель ответа при обновлении, создании или удалении услуги менеджером
 */
export interface ServiceResponseInterface extends ValidationInterface {
    success: boolean;
    service?: ServiceInterface;
}
