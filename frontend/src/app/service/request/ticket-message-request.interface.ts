/**
 * Запрос на создание сообщение по тикету
 */
export interface TicketMessageRequestInterface {
    /**
     * Текст сообщения
     */
    text: string;

    /**
     * Флаг необходимости закрытия тикета
     */
    closeTicket: boolean;
}
