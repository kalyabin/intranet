import {ServiceInterface} from "./service.interface";
import {ServiceTariffInterface} from "./service-tariff.interface";

/**
 * Модель активированной услуги у арендатора
 */
export interface ServiceActivatedInterface {
    /**
     * Модель активированной услуги
     */
    service: ServiceInterface;

    /**
     * Выбранный тариф
     */
    tariff?: ServiceTariffInterface;

    /**
     * Дата активации услуги в формате Y-m-d H:i:s
     */
    createdAt: string;
}
