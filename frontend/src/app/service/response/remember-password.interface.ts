import {ValidationInterface} from "./validation.interface";

/**
 * Ответ от API для восстановления пароля
 */
export interface RememberPasswordInterface extends ValidationInterface {
    /**
     * Запрошенный e-mail
     */
    email: string;

    /**
     * Успешное выполнение запроса
     */
    success: boolean;
}
