import * as io from "socket.io-client";

/**
 * Конфигурация для DEV-окружения
 */
export const environment = {
    // Базовый URL для API (берется из настроек Symfony)
    backendApiUrl: '/api/',
    // количество милисекунд, в течение которых перезагружать состояние авторизации
    reloadAuthStateInterval: 120000,
    // реквизиты подключения к comet-серверу
    comet: {
        url: 'http://' + location.hostname + ':3001/'
    }
};
