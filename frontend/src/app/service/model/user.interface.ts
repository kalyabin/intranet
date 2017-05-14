/**
 * Тип пользователя
 */
import {CustomerInterface} from "../response/customer.interface";
export type UserType = 'customer' | 'manager';
export type UserStatus = 1 | 0 | -1;

/**
 * Модель пользователя
 */
export interface UserInterface {
    /**
     * Идентификатор
     */
    id?: number;

    /**
     * Статус
     */
    status: UserStatus;

    /**
     * Имя
     */
    name: string;

    /**
     * E-mail пользователя
     */
    email: string;

    /**
     * Тип
     */
    userType: UserType;

    /**
     * Модель арендатора, если тип пользователя = customer
     */
    customer?: CustomerInterface;
}
