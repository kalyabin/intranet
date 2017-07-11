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
        'create-request': ManagerRentCreateRequestForm
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
     * Подключение календаря
     */
    configureFullCalendar(): void {
        $(this.$refs['calendar']).fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            dayRender: (date, cell) => {
                if (!roomRequestHelper.dayIsAvailable(this.room, date)) {
                    $(cell).addClass('fc-nonbusiness fc-bgevent');
                } else {
                    $(cell).removeClass('fc-nonbusiness');
                }
            },
            selectable: true,
            select: (start: moment.Moment, end: moment.Moment) => {
                this.createFrom = start;
                this.createTo = end;

                let createModal = <ModalWindow>this.$refs['create-request-modal'];
                createModal.show();

                this.viewCreateForm = true;
            }
        });
    }

    setData(room: RoomInterface, requests: RoomRequestInterface[]) {
        this.room = room;
        this.requests = requests;

        pageMetaStore.commit('setTitle', `Календарь для помещения ${this.room.title}`);
        pageMetaStore.commit('setPageTitle', `Переговорные комнаты`);

        this.configureFullCalendar();
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
