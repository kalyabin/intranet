/**
 * Модель тарифа дополнительной услуги
 */
export interface ServiceTariffInterface {
    id?: number;
    isActive: boolean;
    title: string;
    monthlyCost: number;
}
