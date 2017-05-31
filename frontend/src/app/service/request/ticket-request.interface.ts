/**
 * Запрос на создание или редактирование тикета
 */
export interface TicketRequestInterface {
    /**
     * Заголовок
     */
    title: string;

    /**
     * Сообщение
     */
    text: string;
}
