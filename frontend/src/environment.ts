/**
 * Конфигурация для DEV-окружения
 */
export const environment = {
    // Базовый URL для API (берется из настроек Symfony)
    backendApiUrl: '/api/',
    // количество милисекунд, в течение которых перезагружать состояние авторизации
    reloadAuthStateInterval: 180000
};
