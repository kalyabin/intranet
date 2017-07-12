import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import * as moment from "moment";
import {CustomerInterface} from "../../../service/model/customer.interface";
import {RoomInterface} from "../../../service/model/room.interface";
import {roomRequestHelper} from "../../../helpers/room-request-helper";
import {customerListStore} from "../../../store/customer-list.store";
import {roomManagerService} from "../../../service/room-manager.service";

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
    @Model() customer: number = null;

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

    /**
     * Список арендаторов
     */
    get customers(): CustomerInterface[] {
        return customerListStore.state.list;
    }

    mounted(): void {
        customerListStore.dispatch('fetchList');
        this.date = this.from.format('YYYY-MM-DD');
        this.dateFormatted = this.from.format('DD.MM.YYYY');
        let timeFrom = this.from.format('HH:mm');
        let timeTo = this.to.format('HH:mm');
        this.timeFrom = timeFrom != timeTo ? timeFrom : '';
        this.timeTo = timeFrom != timeTo ? timeTo : '';
        this.schedule = roomRequestHelper.getScheduleByDate(this.room, this.date);
        this.scheduleBreak = roomRequestHelper.getScheduleBreak(this.room);
    }

    submit(): void {
        this.errorMessage = '';
        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;
            roomManagerService.createRequest({
                room: this.room.id,
                customer: this.customer,
                from: this.date + ' ' + this.timeFrom,
                to: this.date + ' ' + this.timeTo,
                customerComment: this.customerComment
            }).then((response) => {
                if (response.success) {
                    alert('success!');
                } else {
                    this.errorMessage = response.firstError;
                }
                this.awaitOfSubmit = false;
            });
        }, () => {});
    }
}
