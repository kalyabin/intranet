/**
 * Тип пользователя
 */
import {CustomerInterface} from "./customer.interface";
export type UserType = 'customer' | 'manager';

/**
 * Модель пользователя
 */
export interface UserInterface {
    /**
     * Идентификатор
     */
    id: number;

    /**
     * Статус
     */
    status: number;

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
