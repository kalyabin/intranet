import {ListInterface} from "./list.interface";
import {UserInterface} from "../model/user.interface";

/**
 * Список пользователей
 */
export interface UserListInterface extends ListInterface {
    list: Array<UserInterface>;
}
