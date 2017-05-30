/**
 * Модель очереди тикетной системы
 */
export interface TicketCategoryInterface {
    /**
     * Идентификатор категории
     */
    id: string;

    /**
     * Название категории
     */
    name: string;

    /**
     * Роль менеджера для просмотра
     */
    managerRole: string;

    /**
     * Роль контрагента для просмотра
     */
    customerRole: string;
}
