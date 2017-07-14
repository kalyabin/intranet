import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import {RoomInterface} from "../../../service/model/room.interface";
import {roomCustomerService} from "../../../service/room-customer.service";
import {RoomRequestInterface} from "../../../service/model/room-request.interface";
import {CustomerRoomDetailsInterface} from "../../../service/model/customer-room-details.interface";
import $ from "jquery";
import {roomRequestHelper} from "../../../helpers/room-request-helper";
import * as moment from "moment";
import {pageMetaStore} from "../../../router/page-meta-store";
import {ModalWindow} from "../../../components/modal-window";
import {RentCreateRequestForm} from "../../../components/rent/create-request-form";
import {RentUpdateRequestForm} from "../../../components/rent/update-request-form";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Календарь для комнаты
 */
@Component({
    template: require('./room-calendar.html'),
    components: {
        'create-request': RentCreateRequestForm,
        'update-request': RentUpdateRequestForm
    }
})
export class CustomerRoomCalendar extends Vue {
    /**
     * Комната
     */
    @Model() room: RoomInterface = null;

    /**
     * Текущая заявка
     */
    @Model() request: RoomRequestInterface = null;

    /**
     * Заявки текущего арендатора
     */
    @Model() myRequests: RoomRequestInterface[] = [];

    /**
     * Зарезервированные дни другими арендаторами
     */
    @Model() reserved: {from: string, to: string}[] = [];

    /**
     * Сообщение о невозможности забронировать комнату
     */
    @Model() alertMessage: string = '';

    /**
     * Для создания заявки: дата начала действия
     */
    @Model() createFrom: moment.Moment = null;

    /**
     * Для создания заявки: дата окончания действия
     */
    @Model() createTo: moment.Moment = null;

    /**
     * Показать форму создания заявки
     */
    @Model() viewCreateForm: boolean = false;

    /**
     * Показать всплывающее уведомление
     */
    protected openAlert(message: string): void {
        this.alertMessage = message;
        let modal = <ModalWindow>this.$refs['alert-modal'];
        modal.show();
    }

    setData(details: CustomerRoomDetailsInterface) {
        this.room = details.room;
        this.myRequests = details.myRequests;
        this.reserved = details.reserved;

        pageMetaStore.commit('setTitle', `Календарь для помещения ${this.room.title}`);
        pageMetaStore.commit('setPageTitle', `Переговорные комнаты`);

        this.configureCalendar();
    }

    /**
     * Запись в календаре по модели заявки
     */
    protected getEventObject(request: RoomRequestInterface) {
        return {
            id: request.id,
            title: `Забронировано вами`,
            start: request.from,
            end: request.to,
            request: request
        };
    }

    /**
     * Добавить заявку в календарь
     */
    protected addRequestToCalendar(request: RoomRequestInterface): void {
        if (request.status !== 'cancelled' && request.status !== 'declined') {
            $(this.$refs['calendar']).fullCalendar('renderEvent', this.getEventObject(request), true);
        }
    }

    /**
     * Добавить недоступное время в календарь
     */
    protected addReservedToCalendar(reserved: {from: string, to: string}): void {
        $(this.$refs['calendar']).fullCalendar('renderEvent', {
            title: 'Забронировано',
            start: reserved.from,
            to: reserved.to
        }, true);
    }

    /**
     * Открыть редактирование заявки
     */
    protected openRequest(request: RoomRequestInterface) {
        this.request = request;
        let modal = <ModalWindow>this.$refs['update-request-modal'];
        modal.show();
    }

    /**
     * Конфигурирование календаря
     */
    protected configureCalendar(): void {
        $(this.$refs['calendar']).fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            eventLimit: true,
            dayRender: (date, cell) => {
                // если день недоступен для бронирования
                if (!roomRequestHelper.dayIsAvailable(this.room, date)) {
                    $(cell).addClass('fc-nonbusiness fc-bgevent');
                } else {
                    $(cell).removeClass('fc-nonbusiness');
                }
            },
            selectable: true,
            eventClick: (event) => {
                let request: RoomRequestInterface = <RoomRequestInterface>event.request;
                this.openRequest(request);
            },
            select: (start: moment.Moment, end: moment.Moment) => {
                if (!roomRequestHelper.dayIsAvailable(this.room, start)) {
                    this.openAlert(`${moment(start).format('DD.MM.YYYY')} помещение не работает`);
                    return;
                }

                this.createFrom = start;
                this.createTo = end;

                let createModal = <ModalWindow>this.$refs['create-request-modal'];
                createModal.show();

                this.viewCreateForm = true;
            }
        });

        for (let request of this.myRequests) {
            this.addRequestToCalendar(request);
        }

        for (let item of this.reserved) {
            this.addReservedToCalendar(item);
        }
    }

    /**
     * Создана заявка
     */
    createdRequest(request: RoomRequestInterface): void {
        // закрыть окно и очистить форму
        this.viewCreateForm = false;
        let createModal = <ModalWindow>this.$refs['create-request-modal'];
        createModal.hide();
        // добавить в календарь
        this.addRequestToCalendar(request);
    }

    /**
     * Отмена заявки
     */
    canceledRequest(request: RoomRequestInterface): void {
        let event = this.getEventObject(request);
        this.request = null;
        let updateModal = <ModalWindow>this.$refs['update-request-modal'];
        updateModal.hide();
        if (request.status === 'cancelled' || request.status == 'declined') {
            // удалить заявку из календаря
            $(this.$refs['calendar']).fullCalendar('removeEvents', event.id);
        }
    }

    beforeRouteEnter(to, from, next) {
        roomCustomerService.details(to.params.id).then((response) => {
            next(vm => vm.setData(response));
        }, () => {
            next({name: 404});
        });
    }

    beforeRouteUpdate(to, from, next) {
        roomCustomerService.details(to.params.id).then((response) => {
            this.setData(response);
        }, () => {
            next({name: 404});
        });
    }
}
