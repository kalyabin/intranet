/**
 * Интерфейс ответа свалидированной формы от API
 */
export interface ValidationInterface {
    /**
     * Флаг субмита формы
     */
    submitted: boolean;

    /**
     * Флаг успешной валидации формы
     */
    valid: boolean;

    /**
     * Тексты ошибок
     */
    validationErrors: {[key: string]: string}
}
