import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {TicketInterface} from "../../service/model/ticket.interface";
import {TicketHistoryInterface} from "../../service/model/ticket-history.interface";
import {TicketMessageInterface} from "../../service/model/ticket-message.interface";
import {ticketListStore} from "../../store/ticket-list.store";
import * as moment from "moment";
import {TicketDetailsResponseInterface} from "../../service/response/ticket-details-response.interface";
import {ticketService} from "../../service/ticket.service";
import {ticketCategoriesStore} from "../../store/ticket-categories.store";
import {pageMetaStore} from "../../router/page-meta-store";
import {TicketMessageForm} from "./message-form";
import {XPanel} from "../../components/x-panel";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Детальная страница заявки
 */
@Component({
    template: require('./details.html'),
    store: ticketListStore,
    components: {
        'message-form': TicketMessageForm
    }
})
export class TicketDetails extends Vue {
    /**
     * Категория тикета
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * Модель заявки
     */
    @Model() ticket: TicketInterface = null;

    /**
     * История изменений по тикету
     */
    @Model() history: Array<{
        createdAt: string,
        type: 'message' | 'status',
        item: TicketHistoryInterface | TicketMessageInterface
    }> = [];

    /**
     * Показать форму заполнения сообщени
     */
    @Model() showMessageForm: boolean = false;

    /**
     * Обновление данных после создания сообщения
     */
    updateData(): void {
        let messagePanel = <XPanel>this.$refs['message-panel'];
        messagePanel.visible = true;
        messagePanel.toggle();

        ticketService.ticketDetails(this.category.id, this.ticket.id)
            .then((response: TicketDetailsResponseInterface) => {
                this.setData(response);
            }, () => {});
    }

    /**
     * Установка и формирование данных
     */
    setData(response: TicketDetailsResponseInterface): void {
        this.ticket = response.ticket;
        this.history = [];

        // формирование истории
        for (let item of response.messages) {
            this.history.push({
                createdAt: item.createdAt,
                type: 'message',
                item: item
            });
        }
        for (let item of response.history) {
            this.history.push({
                createdAt: item.createdAt,
                type: 'status',
                item: item
            });
        }

        // сортировка истории по дате и типу
        this.history = this.history.sort((itemA: {
            createdAt: string,
            type: 'message' | 'status',
            item: TicketHistoryInterface | TicketMessageInterface
        }, itemB: {
            createdAt: string,
            type: 'message' | 'status',
            item: TicketHistoryInterface | TicketMessageInterface
        }) => {
            let dateA = moment(itemA.createdAt);
            let dateB = moment(itemB.createdAt);

            // статус заявки "Новая" всегда должна идти первой
            if (itemB.type == 'status' && itemB.item['status'] == 'new') {
                return 1;
            } else if (itemA.type == 'status' && itemA.item['status'] == 'new') {
                return -1;
            }

            if (dateB.isBefore(dateA)) {
                // А младше B
                return 1;
            } else if (dateB.isAfter(dateA)) {
                // B младше A
                return -1;
            } else if (dateB.isSame(dateA) && itemA.type == 'message') {
                // сообщения идут после статуса
                return 1;
            } else if (dateB.isSame(dateA) && itemA.type == 'status') {
                // статусы идут перед сообщений
                return -1;
            } else {
                return 0;
            }
        });
    }

    /**
     * Установка категории
     */
    setCategory(category: TicketCategoryInterface): void {
        this.category = category;

        pageMetaStore.commit('setTitle', `#${this.ticket.number} - ${category.name}`);
        pageMetaStore.commit('setPageTitle', `${category.name}: #${this.ticket.number}`);
    }

    /**
     * Проверка прав и получение детальной информации о тикете
     */
    beforeRouteEnter(to, from, next): void {
        ticketCategoriesStore.dispatch('checkCategory', to.params.category).then((category: TicketCategoryInterface) => {
            ticketService.ticketDetails(to.params.category, to.params.ticket)
                .then((response: TicketDetailsResponseInterface) => {
                    next(vm => {
                        vm.setData(response);
                        vm.setCategory(category);
                    });
                }, () => next({name: '403'}));
        }, () => next({name: '403'}));
    }

    /**
     * Проверка прав и получение детальной информации о тикете
     */
    beforeRouteUpdate(to, from, next): void {
        ticketCategoriesStore.dispatch('checkCategory', to.params.category).then((category: TicketCategoryInterface) => {
            ticketService.ticketDetails(to.params.category, to.params.ticket)
                .then((response: TicketDetailsResponseInterface) => {
                    next(vm => {
                        vm.setData(response);
                        vm.setCategory(category);
                    });
                }, () => next({name: '403'}));
        }, () => next({name: '403'}));
    }
}