import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import {TicketCategoryInterface} from "../../../service/model/ticket-category.interface";
import {TicketInterface} from "../../../service/model/ticket.interface";
import {ticketListStore} from "../../../store/ticket-list.store";
import {TicketDetailsResponseInterface} from "../../../service/response/ticket-details-response.interface";
import {ticketService} from "../../../service/ticket.service";
import {ticketCategoriesStore} from "../../../store/ticket-categories.store";
import {pageMetaStore} from "../../../router/page-meta-store";
import {TicketMessageForm} from "../../../components/ticket/message-form";
import {XPanel} from "../../../components/x-panel";
import {Location} from "vue-router";
import {TicketHistory} from "../../../components/ticket/history";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Детальная страница заявки для менеджера
 */
@Component({
    template: require('./details.html'),
    store: ticketListStore,
    components: {
        'message-form': TicketMessageForm,
        'ticket-history': TicketHistory
    }
})
export class ManagerTicketDetails extends Vue {
    /**
     * Категория тикета
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * Модель заявки
     */
    @Model() ticket: TicketInterface = null;

    /**
     * История по тикету
     */
    @Model() details: TicketDetailsResponseInterface = null;

    /**
     * Показать форму заполнения сообщения
     */
    @Model() showMessageForm: boolean = false;

    /**
     * Обновление данных после создания сообщения
     */
    updateData(): void {
        let messagePanel = <XPanel>this.$refs['message-panel'];
        messagePanel.visible = true;
        messagePanel.toggle();

        ticketService.ticketDetails(this.ticket.id)
            .then((response: TicketDetailsResponseInterface) => {
                this.setData(response);
            }, () => {});
    }

    /**
     * Установка и формирование данных
     */
    setData(response: TicketDetailsResponseInterface): void {
        this.ticket = response.ticket;
        this.details = response;
    }

    /**
     * Установка категории
     */
    setCategory(category: TicketCategoryInterface): void {
        this.category = category;

        pageMetaStore.commit('setTitle', `#${this.ticket.number} - ${category.name}`);
        pageMetaStore.commit('setPageTitle', `Заявки`);
    }

    /**
     * Получить ссылку на категорию тикетной системы
     */
    get categoryRoute(): Location {
        if (this.category && this.category.id) {
            return {
                name: 'manager_ticket_list',
                params: <any> {
                    category: this.category.id
                }
            };
        } else {
            return {
                name: 'manager_ticket_root'
            };
        }
    }

    /**
     * Проверка прав и получение детальной информации о тикете
     */
    beforeRouteEnter(to, from, next): void {
        let categoryId = to.params.category ? to.params.category : to.params.service;
        ticketCategoriesStore.dispatch('checkCategory', categoryId).then((category: TicketCategoryInterface) => {
            ticketService.ticketDetails(to.params.ticket)
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
        let categoryId = to.params.category ? to.params.category : to.params.service;
        ticketCategoriesStore.dispatch('checkCategory', categoryId).then((category: TicketCategoryInterface) => {
            ticketService.ticketDetails(to.params.ticket)
                .then((response: TicketDetailsResponseInterface) => {
                    next(vm => {
                        vm.setData(response);
                        vm.setCategory(category);
                    });
                }, () => next({name: '403'}));
        }, () => next({name: '403'}));
    }
}
