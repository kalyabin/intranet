import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import * as moment from "moment";
import {CustomerInterface} from "../../service/model/customer.interface";
import {RoomInterface} from "../../service/model/room.interface";
import {roomRequestHelper} from "../../helpers/room-request-helper";
import {customerListStore} from "../../store/customer-list.store";
import {roomManagerService} from "../../service/room-manager.service";
import {UserType} from "../../service/model/user.interface";
import {authUserStore} from "../../store/auth-user.store";
import {UpdateRoomRequestInterface} from "../../service/request/create-room-request.interface";
import {roomCustomerService} from "../../service/room-customer.service";

/**
 * Форма создания заявки.
 *
 * В зависимости от типа пользователя дополняет или отключает дополнительные поля.
 */
@Component({
    template: require('./create-request-form.html')
})
export class RentCreateRequestForm extends Vue {
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

    /**
     * Получить тип пользователя, который пользуется формой
     */
    get userType(): UserType {
        return authUserStore.state.userData ? authUserStore.state.userData.userType : null;
    }

    /**
     * Получить итоговую сумму
     */
    get totalCost(): number {
        return roomRequestHelper.getTotalCost(this.room, this.timeFrom, this.timeTo);
    }

    mounted(): void {
        if (this.userType == 'manager') {
            customerListStore.dispatch('fetchList');
        }
        this.date = this.from.format('YYYY-MM-DD');
        this.dateFormatted = this.from.format('DD.MM.YYYY');
        let timeFrom = this.from.format('HH:mm');
        let timeTo = this.to.format('HH:mm');
        this.timeFrom = timeFrom != timeTo ? timeFrom : '';
        this.timeTo = timeFrom != timeTo ? timeTo : '';
    }

    submit(): void {
        this.errorMessage = '';
        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;

            let request: UpdateRoomRequestInterface = {
                room: this.room.id,
                from: this.date + ' ' + this.timeFrom,
                to: this.date + ' ' + this.timeTo,
                customerComment: this.customerComment
            };
            if (this.userType === 'manager') {
                request.customer = this.customer;
                roomManagerService.createRequest(request).then((response) => {
                    if (response.success) {
                        this.$emit('created', response.request);
                    } else {
                        this.errorMessage = response.firstError;
                    }
                    this.awaitOfSubmit = false;
                });
            } else {
                roomCustomerService.createRequest(request).then((response) => {
                    if (response.success) {
                        this.$emit('created', response.request);
                    } else {
                        this.errorMessage = response.firstError;
                    }
                    this.awaitOfSubmit = false;
                });
            }
        }, () => {});
    }
}
