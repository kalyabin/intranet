import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import {RoomRequestInterface, RoomRequestStatus} from "../../service/model/room-request.interface";
import {roomRequestHelper} from "../../helpers/room-request-helper";
import * as moment from "moment";
import {roomManagerService} from "../../service/room-manager.service";
import {UserType} from "../../service/model/user.interface";
import {authUserStore} from "../../store/auth-user.store";
import {roomCustomerService} from "../../service/room-customer.service";

/**
 * Форма редактирования заявки.
 *
 * В зависимости от типа пользователей подключаются или отключаются дополнительные поля.
 */
@Component({
    template: require('./update-request-form.html')
})
export class RentUpdateRequestForm extends Vue {
    @Prop(Object) request: RoomRequestInterface;

    @Model() requestInternal: RoomRequestInterface = this.request;

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
     * Получить тип пользователя
     */
    get userType(): UserType {
        return authUserStore.state.userData ? authUserStore.state.userData.userType : null;
    }

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

    /**
     * Получить описание статуса
     */
    get statusText(): string {
        return roomRequestHelper.getStatusLabel(this.request.status);
    }

    mounted(): void {
        let from = moment(this.request.from);
        let to = moment(this.request.to);

        this.dateFormatted = from.format('DD.MM.YYYY');
        this.timeFrom = from.format('HH:mm');
        this.timeTo = to.format('HH:mm');
    }

    /**
     * Для арендатора - субмит отмены заявки
     */
    cancelRequest(): void {
        if (this.userType != 'customer') {
            return;
        }
        if (confirm('Вы уверены, что хотите отменить заявку на бронирование переговорной?')) {
            this.errorMessage = '';
            this.awaitOfSubmit = true;
            roomCustomerService.cancelRequest(this.request.id).then((response) => {
                this.awaitOfSubmit = false;
                if (!response.success) {
                    this.errorMessage = 'Не удалось отменить заявку. Обратитесь к администратору.'
                } else {
                    this.$emit('canceled', response.request);
                }
            });
        }
    }

    /**
     * Для менеджера - субмит изменения заявки
     */
    submitUpdate(): void {
        if (this.userType != 'manager') {
            return;
        }
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
