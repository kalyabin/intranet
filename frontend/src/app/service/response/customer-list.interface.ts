import {ListInterface} from "./list.interface";
import {CustomerInterface} from "../model/customer.interface";

/**
 * Список контрагентов
 */
export interface CustomerListInterface extends ListInterface {
    list: Array<CustomerInterface>;
}
