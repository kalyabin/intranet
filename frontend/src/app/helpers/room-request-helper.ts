import {RoomInterface} from "../service/model/room.interface";
import * as moment from "moment";
import {RoomRequestStatus} from "../service/model/room-request.interface";

/**
 * Хелпер для работы с помещениями для аренды
 */
export class RoomRequestHelper {
    getMomentForTime(time: string): moment.Moment {
        return moment(moment().format('YYYY-MM-DD') + ' ' + time + ':00');
    }

    /**
     * Получить стоимость аренды комнаты на указанный срок
     */
    getTotalCost(room: RoomInterface, timeFrom: string, timeTo: string): number {
        if (timeFrom && timeTo) {

            let momentFrom = this.getMomentForTime(timeFrom);
            let momentTo = this.getMomentForTime(timeTo);

            let diff = momentTo.diff(momentFrom, 'minutes');
            return room.hourlyCost * (diff / 60);
        }
        return null;
    }

    /**
     * Получить подпись для статуса
     */
    getStatusLabel(status: RoomRequestStatus): string {
        switch (status) {
            case 'pending':
                return 'Ожидает подтверждения';

            case 'approved':
                return 'Подтверждена';

            case 'declined':
                return 'Отклонена';

            case 'cancelled':
                return 'Отменена арендатором';
        }

        return '';
    }

    /**
     * Проверка, доступен ли день для регистрации заявки или нет
     */
    dayIsAvailable(room: RoomInterface, date: string | Date | moment.Moment): boolean {
        let momentDate = moment(date);
        let dateFormatted = momentDate.format('YYYY-MM-DD');
        let weekday = momentDate.weekday();

        let schedule = room.schedule;
        let holidays = room.holidays;
        let workWeekends = room.workWeekends;
        if (workWeekends.indexOf(dateFormatted) !== -1) {
            // рабочий выходной
            return true;
        } else if (holidays.indexOf(dateFormatted) !== -1) {
            // праздичный день
            return false;
        } else if (schedule[weekday] && schedule[weekday][0] && !schedule[weekday][0].avail) {
            // выходной день
            return false;
        }
        return true;
    }

    /**
     * Получить режим работы на указанный день
     */
    getScheduleByDate(room: RoomInterface, date: string | Date | moment.Moment): {from: string, to: string} | false {
        let weekday = moment(date).weekday();
        if (room.schedule[weekday] && room.schedule[weekday][0] && !room.schedule[weekday][0].avail) {
            return false;
        } else if (room.schedule[weekday] && room.schedule[weekday][0]) {
            let item = room.schedule[weekday][0];
            return {
                from: item.from,
                to: item.to
            };
        } else {
            return {
                from: '00:00',
                to: '23:59'
            };
        }
    }

    /**
     * Получить перерыв в работе
     */
    getScheduleBreak(room: RoomInterface): {from: string, to: string} | false {
        if (room.scheduleBreak && room.scheduleBreak[0]) {
            return room.scheduleBreak[0];
        } else {
            return false;
        }
    }

    /**
     * Получить блокирующие время для установки времени начала действия заявки
     */
    getDisabledTimeFromRanges(room: RoomInterface, date: string | Date | moment.Moment): Array<string[]> {
        let result: Array<string[]> = [];

        let schedule = this.getScheduleByDate(room, date);
        if (!schedule) {
            // весь день не доступен
            return [['00:00', '23:59']];
        }

        if (schedule.from != '00:00') {
            result.push(['00:00', this.getMomentForTime(schedule.from).subtract(1, 'minute').format('HH:mm')]);
        }

        if (schedule.to != '24:00') {
            result.push([this.getMomentForTime(schedule.to).subtract(29, 'minutes').format('HH:mm'), '23:59']);
        }

        let scheduleBreak = this.getScheduleBreak(room);
        if (scheduleBreak) {
            result.push([scheduleBreak.from, this.getMomentForTime(scheduleBreak.to).subtract(29, 'minutes').format('HH:mm')]);
        }

        return result;
    }

    /**
     * Получить блокирующие время для установки времени окончания действия заявки
     */
    getDisabledTimeToRanges(room: RoomInterface, date: string | Date | moment.Moment): Array<string[]> {
        let result: Array<string[]> = [];

        let schedule = this.getScheduleByDate(room, date);
        if (!schedule) {
            // весь день недоступен
            return [['00:00', '23:59']];
        }

        if (schedule.from == '00:00') {
            result.push(['00:00', '00:29']);
        } else {
            result.push(['00:00', this.getMomentForTime(schedule.from).add(1, 'minutes').format('HH:mm')]);
        }

        if (schedule.to != '24:00') {
            result.push([this.getMomentForTime(schedule.to).add(1, 'minutes').format('HH:mm'), '23:59']);
        }

        let scheduleBreak = this.getScheduleBreak(room);
        if (scheduleBreak) {
            result.push([
                this.getMomentForTime(scheduleBreak.from).add(1, 'minutes').format('HH:mm'),
                this.getMomentForTime(scheduleBreak.to).add(29, 'minutes').format('HH:mm')
            ]);
        }

        return result;
    }
}

export const roomRequestHelper = new RoomRequestHelper();
