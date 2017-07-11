import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import * as moment from "moment";
import {CustomerInterface} from "../../../service/model/customer.interface";
import {RoomInterface} from "../../../service/model/room.interface";
import {roomRequestHelper} from "../../../helpers/room-request-helper";

/**
 * Форма создания заявки
 */
@Component({
    template: require('./create-request-form.html')
})
export class ManagerRentCreateRequestForm extends Vue {
    /**
     * Начало действия заявки
     */
    @Prop(Object) from: moment.Moment;

    /**
     * Окончание действия заявки
     */
    @Prop(Object) to: moment.Moment;

    /**
     * Помещение для аренды
     */
    @Prop(Object) room: RoomInterface;

    /**
     * Режим работы
     */
    @Model() schedule: {from: string, to: string} | false = null;

    /**
     * Перерыв
     */
    @Model() scheduleBreak: {from: string, to: string} | false = null;

    /**
     * Дата заявки в формате YYYY-MM-DD
     */
    @Model() date: string = null;

    /**
     * Дата заявки в формате DD.MM.YYYY
     */
    @Model() dateFormatted: string = null;

    /**
     * Дата начала заявки в формате HH:mm
     */
    @Model() timeFrom: string = null;

    /**
     * Дата окончания заявки в формате HH:mm
     */
    @Model() timeTo: string = null;

    /**
     * Модель арендатора
     */
    @Model() customer: CustomerInterface = null;

    /**
     * Комментарий
     */
    @Model() customerComment: string = null;

    /**
     * Описание ошибки после субмита
     */
    @Model() errorMessage: string = '';

    /**
     * Флаг ожидания окончания субмита
     */
    @Model() awaitOfSubmit: boolean = false;

    mounted(): void {
        this.date = this.from.format('YYYY-MM-DD');
        this.dateFormatted = this.from.format('DD.MM.YYYY');
        this.timeFrom = this.from.format('HH:mm');
        this.timeTo = this.to.format('HH:mm');
        this.schedule = roomRequestHelper.getScheduleByDate(this.room, this.date);
        this.scheduleBreak = roomRequestHelper.getScheduleBreak(this.room);
    }

    submit(): void {
        this.errorMessage = '';
        this.$validator.validateAll().then(() => {
            alert('success!');
        }, () => {});
    }
}
