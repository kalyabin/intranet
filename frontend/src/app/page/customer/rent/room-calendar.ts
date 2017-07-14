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

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Календарь для комнаты
 */
@Component({
    template: require('./room-calendar.html')
})
export class CustomerRoomCalendar extends Vue {
    /**
     * Комната
     */
    @Model() room: RoomInterface = null;

    /**
     * Заявки текущего арендатора
     */
    @Model() myRequests: RoomRequestInterface[] = [];

    /**
     * Зарезервированные дни другими арендаторами
     */
    @Model() reserved: {from: string, to: string}[] = [];

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
    protected addRrequestToCalendar(request: RoomRequestInterface): void {
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
                // let request: RoomRequestInterface = <RoomRequestInterface>event.request;
                // this.openRequest(request);
            },
            select: (start: moment.Moment, end: moment.Moment) => {
            }
        });

        for (let request of this.myRequests) {
            this.addRrequestToCalendar(request);
        }

        for (let item of this.reserved) {
            this.addReservedToCalendar(item);
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
