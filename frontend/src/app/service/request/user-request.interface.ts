import {UserStatus, UserType} from "../model/user.interface";

/**
 * Запрос на изменение или создание пользователя
 */
export interface UserRequestInterface {
    /**
     * Имя пользователя
     */
    name: string;

    /**
     * E-mail пользователя
     */
    email: string;

    /**
     * Пароль пользователя, если требуется изменить пароль
     */
    password?: string;

    /**
     * Тип пользователя
     */
    userType: UserType;

    /**
     * Статус
     */
    status: UserStatus;

    /**
     * Массив ролей
     */
    role: Array<{code: string}>;

    /**
     * Идентификатор контрагента для userType = 'customer'
     */
    customer?: number;
}
