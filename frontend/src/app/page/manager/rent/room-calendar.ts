import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import {RoomInterface} from "../../../service/model/room.interface";
import {RoomRequestInterface} from "../../../service/model/room-request.interface";
import {pageMetaStore} from "../../../router/page-meta-store";
import {roomManagerService} from "../../../service/room-manager.service";
import $ from "jquery";
import * as moment from "moment";
import {ModalWindow} from "../../../components/modal-window";
import {ManagerRentCreateRequestForm} from "./create-request-form";
import {ManagerRentUpdateRequestForm} from "./update-request-form";
import {roomRequestHelper} from "../../../helpers/room-request-helper";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Календарь переговорной комнаты для менеджера
 */
@Component({
    template: require('./room-calendar.html'),
    components: {
        'create-request': ManagerRentCreateRequestForm,
        'update-request': ManagerRentUpdateRequestForm
    }
})
export class ManagerRoomCalendar extends Vue {
    /**
     * Модель переговорки
     */
    @Model() room: RoomInterface = null;

    /**
     * Заявки
     */
    @Model() requests: RoomRequestInterface[] = [];

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
     * Всплывающее сообщение
     */
    @Model() alertMessage: string = '';

    /**
     * Заявка для редактирования
     */
    @Model() request: RoomRequestInterface = null;

    /**
     * Показать всплывающее уведомление
     */
    protected openAlert(message: string): void {
        this.alertMessage = message;
        let modal = <ModalWindow>this.$refs['alert-modal'];
        modal.show();
    }

    /**
     * Открыть редактирование заявки
     */
    protected openRequest(request: RoomRequestInterface) {
        this.request = request;
        let modal = <ModalWindow>this.$refs['update-request-modal'];
        modal.show();
    }

    protected getEventObject(request: RoomRequestInterface) {
        return {
            id: request.id,
            title: `Забронировано: ${request.customer.name}`,
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
     * Отредактировать заявку в календаре
     */
    protected updateRequestToCalendar(request: RoomRequestInterface): void {
        let event = this.getEventObject(request);
        if (request.status === 'cancelled' || request.status == 'declined') {
            // удалить заявку из календаря
            $(this.$refs['calendar']).fullCalendar('removeEvents', event.id);
        }
    }

    /**
     * Подключение календаря
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

        for (let request of this.requests) {
            this.addRequestToCalendar(request);
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
     * Обновить заявку
     */
    updatedRequest(request: RoomRequestInterface): void {
        this.request = null;
        let updateModal = <ModalWindow>this.$refs['update-request-modal'];
        updateModal.hide();
        this.updateRequestToCalendar(request);
    }

    setData(room: RoomInterface, requests: RoomRequestInterface[]) {
        this.room = room;
        this.requests = requests;

        pageMetaStore.commit('setTitle', `Календарь для помещения ${this.room.title}`);
        pageMetaStore.commit('setPageTitle', `Переговорные комнаты`);

        this.configureCalendar();
    }

    beforeRouteEnter(to, from, next) {
        roomManagerService.details(to.params.id).then((response) => {
            next(vm => vm.setData(response.room, response.requests));
        }, () => {
            next({name: 404});
        });
    }

    beforeRouteUpdate(to, from, next) {
        roomManagerService.details(to.params.id).then((response) => {
            this.setData(response.room, response.requests);
        }, () => {
            next({name: 404});
        });
    }
}
