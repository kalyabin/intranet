import {ValidationInterface} from "./validation.interface";
import {ServiceInterface} from "../model/service.interface";
import {ServiceTariffInterface} from "../model/service-tariff.interface";
import {ServiceActivatedInterface} from "../model/service-activated.interface";

/**
 * Ответ о активации или деактивации услуги
 */
export interface ServiceActivationResponseInterface extends ValidationInterface {
    /**
     * Флаг успешного выполнения запроса
     */
    success: boolean;

    /**
     * Активированная услуга
     */
    activated?: ServiceActivatedInterface;

    /**
     * Деактивированная услуга
     */
    service?: ServiceInterface;
}
