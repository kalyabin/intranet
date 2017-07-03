/**
 * Тип помещений
 */
export type RoomType = 'meeting' | 'conference';

/**
 * Модель помещения для модуля аренды
 */
export interface RoomInterface {
    /**
     * Идентификатор (недоступно для новых помещений)
     */
    id?: number;

    /**
     * Тип
     */
    type: RoomType;

    /**
     * Заголовок
     */
    title: string;

    /**
     * Описание
     */
    description: string;

    /**
     * Местоположение помещения
     */
    address: string;

    /**
     * Оплата помещения в час
     */
    hourlyCost: number;

    /**
     * Еженеделеньое расписание работы помещения
     */
    schedule: Array<{
        /**
         * Порядковый день недели (1-7)
         */
        weekday: number,
        /**
         * Отрезки времени в формате HH:mm
         */
        schedule: Array<{from: string, to: string}>
    }>;

    /**
     * Ежедневные перерывы в работе помещения в формате HH:mm
     */
    scheduleBreak: Array<{
        from: string,
        to: string
    }>;

    /**
     * Массив праздничных дней в формате Y-m-d
     */
    holidays: string[];

    /**
     * Перерыв между заявками в минутах
     */
    requestPause: number;
}
