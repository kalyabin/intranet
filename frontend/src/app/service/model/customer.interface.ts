/**
 * Модель арендатора
 */
export interface CustomerInterface {
    /**
     * Идентификатор
     */
    id?: number;

    /**
     * Название
     */
    name: string;

    /**
     * Текущий договор
     */
    currentAgreement: string;
}
