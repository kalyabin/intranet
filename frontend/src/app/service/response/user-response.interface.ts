import {ValidationInterface} from "./validation.interface";
import {UserInterface} from "../model/user.interface";

/**
 * Формат ответа на изменение или создание пользователя
 */
export interface UserResponseInterface extends ValidationInterface {
    success: boolean;
    user?: UserInterface;
}
