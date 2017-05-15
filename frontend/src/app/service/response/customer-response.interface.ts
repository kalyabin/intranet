import {ValidationInterface} from "./validation.interface";
import {CustomerInterface} from "../model/customer.interface";

/**
 * Ответ для создания или редактирования арендатора
 */
export interface CustomerResponseInterface extends ValidationInterface {
    customer: CustomerInterface;
    success: boolean;
}
