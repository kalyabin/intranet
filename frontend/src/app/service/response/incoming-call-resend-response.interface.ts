import {ValidationInterface} from "./validation.interface";

/**
 * Ответ переотправки входящего звонка арендатору
 */
export interface IncomingCallResendResponseInterface extends ValidationInterface {
    success: boolean;
}
