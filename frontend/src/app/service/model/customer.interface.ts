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

    /**
     * Есть доступ к услугам IT-аутсорсинга
     */
    allowItDepartment: boolean;

    /**
     * Есть доступ к услугам SMART-бухгалтера
     */
    allowBookerDepartment: boolean;
}
