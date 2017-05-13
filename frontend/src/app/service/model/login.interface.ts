/**
 * Ответ авторизации пользователя
 */
export interface LoginInterface {
    /**
     * Флаг успешной авторизации
     */
    loggedIn: boolean;

    /**
     * Флаг заблокированности
     */
    isLocked: boolean;

    /**
     * Флаг необходимости активации
     */
    isNeedActivation: boolean;

    /**
     * Текст ошибки если есть
     */
    errorMessage: string;

    /**
     * Идентификатор пользователя, если успешно авторизовался
     */
    userId?: number;
}
