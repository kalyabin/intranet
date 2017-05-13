import {ValidationInterface} from "./validation.interface";

/**
 * Ответ об изменении пароля
 */
export interface RestorePasswordInterface extends ValidationInterface{
    success: boolean;
}
