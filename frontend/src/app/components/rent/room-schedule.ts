import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import {RoomInterface} from "../../service/model/room.interface";
import {roomRequestHelper} from "../../helpers/room-request-helper";

/**
 * Вывод расписания переговорки
 */
@Component({
    template: require('./room-schedule.html')
})
export class RoomSchedule extends Vue {
    /**
     * Модель комнаты
     */
    @Prop(Object) room: RoomInterface;

    /**
     * Дата, на которую вывести расписание
     */
    @Prop(String) date: string;

    /**
     * Режим работы
     */
    @Model() schedule: {from: string, to: string} | false = null;

    /**
     * Перерыв
     */
    @Model() scheduleBreak: {from: string, to: string} | false = null;

    mounted(): void {
        this.schedule = roomRequestHelper.getScheduleByDate(this.room, this.date);
        this.scheduleBreak = roomRequestHelper.getScheduleBreak(this.room);
    }
}
