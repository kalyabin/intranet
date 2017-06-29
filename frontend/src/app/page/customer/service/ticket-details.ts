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
import {ServiceInterface} from "../../../service/model/service.interface";
import {extendedServiceStore} from "../../../store/extended-service.store";
import {Location} from "vue-router";
import {TicketHistory} from "../../../components/ticket/history";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Детальная страница заявки для арендатора на странице услуги
 */
@Component({
    template: require('./ticket-details.html'),
    store: ticketListStore,
    components: {
        'message-form': TicketMessageForm,
        'ticket-history': TicketHistory
    }
})
export class CustomerServiceTicketDetails extends Vue {
    /**
     * Категория тикета
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * Модель заявки
     */
    @Model() ticket: TicketInterface = null;

    /**
     * Услуга, через которую произошёл переход в тикет
     */
    @Model() service: ServiceInterface = null;

    /**
     * Показать форму заполнения сообщени
     */
    @Model() showMessageForm: boolean = false;

    /**
     * Детальная информация по тикету
     */
    @Model() details: TicketDetailsResponseInterface = null;

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
     * Установка услуги
     */
    setService(service: ServiceInterface): void {
        this.service = service;

        pageMetaStore.commit('setTitle', `Услуги: ${service.title}`);
        pageMetaStore.commit('setPageTitle', `Услуги: ${service.title}`);
    }

    /**
     * Получить ссылку на категорию тикетной системы
     */
    get categoryRoute(): Location {
        return {
            name: 'cabinet_service_page',
            params: {
                service: this.service ? this.service.id : null
            }
        };
    }

    /**
     * Проверка прав и получение детальной информации о тикете
     */
    beforeRouteEnter(to, from, next): void {
        ticketCategoriesStore.dispatch('checkCategory', to.params.service).then((category: TicketCategoryInterface) => {
            ticketService.ticketDetails(to.params.ticket)
                .then((response: TicketDetailsResponseInterface) => {
                    next(vm => {
                        vm.setData(response);
                        vm.setCategory(category);
                        extendedServiceStore.dispatch('getServiceById', to.params.service)
                            .then((service: ServiceInterface) => {
                                vm.setService(service);
                            }, () => {});
                    });
                }, () => next({name: '403'}));
        }, () => next({name: '403'}));
    }

    /**
     * Проверка прав и получение детальной информации о тикете
     */
    beforeRouteUpdate(to, from, next): void {
        ticketCategoriesStore.dispatch('checkCategory', to.params.service).then((category: TicketCategoryInterface) => {
            ticketService.ticketDetails(to.params.ticket)
                .then((response: TicketDetailsResponseInterface) => {
                    next(vm => {
                        vm.setData(response);
                        vm.setCategory(category);
                        extendedServiceStore.dispatch('getServiceById', to.params.service)
                            .then((service: ServiceInterface) => {
                                vm.setService(service);
                            }, () => {});
                    });
                }, () => next({name: '403'}));
        }, () => next({name: '403'}));
    }
}
