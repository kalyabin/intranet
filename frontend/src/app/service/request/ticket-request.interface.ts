/**
 * Запрос на создание или редактирование тикета
 */
export interface TicketRequestInterface {
    /**
     * Категория
     */
    category: string;

    /**
     * Заголовок
     */
    title: string;

    /**
     * Сообщение
     */
    text: string;
}
