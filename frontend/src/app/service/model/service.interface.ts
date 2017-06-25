import {ServiceTariffInterface} from "./service-tariff.interface";

/**
 * Модель дополнительный услуги для арендатора
 */
export interface ServiceInterface {
    id: string;
    isActive: boolean;
    title: string;
    description: string;
    customerRole: string;
    tariff: ServiceTariffInterface[];
}
