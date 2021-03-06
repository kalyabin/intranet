import {UserInterface} from "../model/user.interface";

/**
 * Интерфейс ответа проверки авторизации
 */
export interface AuthInterface {
    /**
     * True, если авторизован
     */
    auth: boolean;

    /**
     * Модель пользователя, если авторизован
     */
    user: UserInterface;

    /**
     * Флаг временного пароля
     */
    isTemporaryPassword: boolean;

    /**
     * Массив ролей пользователя
     */
    roles: string[];
}
