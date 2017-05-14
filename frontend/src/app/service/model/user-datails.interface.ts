import {UserInterface, UserStatus} from "./user.interface";

/**
 * Детальная информация о пользователей
 */
export interface UserDetailsInterface {
    user: UserInterface;
    roles: string[];
    status: UserStatus;
}
