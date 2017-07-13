import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import {RoomRequestInterface, RoomRequestStatus} from "../../../service/model/room-request.interface";
import {roomRequestHelper} from "../../../helpers/room-request-helper";
import * as moment from "moment";
import {roomManagerService} from "../../../service/room-manager.service";

/**
 * Форма редактирования заявки
 */
@Component({
    template: require('./update-request-form.html')
})
export class ManagerRentUpdateRequestForm extends Vue {
    @Prop(Object) request: RoomRequestInterface;

    @Model() requestInternal: RoomRequestInterface = this.request;

    /**
     * Режим работы
     */
    @Model() schedule: {from: string, to: string} | false = null;

    /**
     * Перерыв
     */
    @Model() scheduleBreak: {from: string, to: string} | false = null;

    /**
     * Сообщение об ошибке
     */
    @Model() errorMessage: string = '';

    /**
     * Ожидание субмита
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Время начала бронирования
     */
    @Model() timeFrom: string = '';

    /**
     * Время окончания бронирования
     */
    @Model() timeTo: string = '';

    /**
     * Отформатированная дата бронирования
     */
    @Model() dateFormatted: string = '';

    /**
     * Получить итоговую сумму
     */
    get totalCost(): number {
        return roomRequestHelper.getTotalCost(this.request.room, this.timeFrom, this.timeTo);
    }

    /**
     * Получить доступные статусы
     */
    get statuses(): Array<{id: RoomRequestStatus, name: string}> {
        let statuses: RoomRequestStatus[] = ['pending', 'approved', 'declined', 'cancelled'];
        let result = [];
        for (let status of statuses) {
            result.push({
                id: status,
                name: roomRequestHelper.getStatusLabel(status)
            });
        }
        return result;
    }

    mounted(): void {
        let from = moment(this.request.from);
        let to = moment(this.request.to);

        this.dateFormatted = from.format('DD.MM.YYYY');
        this.timeFrom = from.format('HH:mm');
        this.timeTo = to.format('HH:mm');

        this.schedule = roomRequestHelper.getScheduleByDate(this.request.room, from);
        this.scheduleBreak = roomRequestHelper.getScheduleBreak(this.request.room);
    }

    submit(): void {
        this.errorMessage = '';
        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;
            roomManagerService.updateRequest(this.request.id, {
                room: this.request.room.id,
                customer: this.request.customer.id,
                from: this.request.from,
                to: this.request.to,
                status: this.requestInternal.status,
                customerComment: this.request.customerComment,
                managerComment: this.requestInternal.managerComment,
            }).then((response) => {
                this.awaitOfSubmit = false;
                if (response.success) {
                    this.$emit('updated', response.request);
                } else {
                    this.errorMessage = response.firstError;
                }
            });
        }, () => { });
    }
}
