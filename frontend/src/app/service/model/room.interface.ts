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
    schedule: Array<Array<{avail?: boolean, from: string, to: string}>>;

    /**
     * Ежедневные перерывы в работе помещения в формате HH:mm
     */
    scheduleBreak: Array<{
        avail?: boolean,
        from: string,
        to: string
    }>;

    /**
     * Массив праздничных дней в формате Y-m-d
     */
    holidays: string[];

    /**
     * Перенесённые праздничные дни в формате Y-m-d
     */
    workWeekends: string[];

    /**
     * Перерыв между заявками в минутах
     */
    requestPause: number;
}
