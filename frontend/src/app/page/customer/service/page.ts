import Vue from "vue";
import Component from "vue-class-component";
import {extendedServiceStore} from "../../../store/extended-service.store";
import {Model} from "vue-property-decorator";
import {ServiceInterface} from "../../../service/model/service.interface";
import {pageMetaStore} from "../../../router/page-meta-store";
import {ServiceTariffInterface} from "../../../service/model/service-tariff.interface";
import {extendedServicesService} from "../../../service/extended-services-service";
import $ from "jquery";
import {TicketCategoryInterface} from "../../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../../store/ticket-categories.store";
import {TicketTable} from "../../../components/ticket/table";
import {TicketForm} from "../../../components/ticket/form";
import {TicketInterface} from "../../../service/model/ticket.interface";
import {ticketListStore} from "../../../store/ticket-list.store";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
    'beforeRouteLeave',
]);

/**
 * Просмотр информации об услуги и пользование услугой
 */
@Component({
    template: require('./page.html'),
    components: {
        'ticket-table': TicketTable,
        'ticket-form': TicketForm
    }
})
export class CustomerServicePage extends Vue {
    /**
     * Модель услуги
     */
    @Model() service: ServiceInterface = null;

    /**
     * Категория тикетной системы для работы с услугой
     */
    @Model() ticketCategory: TicketCategoryInterface = null;

    /**
     * Раскрыть форму создания заявки
     */
    @Model() toggledTicketForm: boolean = false;

    /**
     * Выбранный тариф при активации услуги
     */
    @Model() selectedTariff: number = null;

    /**
     * Установка услуги
     */
    setService(service: ServiceInterface): void {
        this.service = service;
        pageMetaStore.commit('setPageTitle', `Услуги: ${service.title}`);
        this.fetchTicketCategory();
    }

    /**
     * Получить состояние услуги: активна или нет
     */
    get serviceIsActivated(): boolean {
        for (let item of extendedServiceStore.state.activatedList) {
            if (item.service.id == this.service.id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Возвращает true, если у услуги есть тарифы
     */
    get serviceHasTariff(): boolean {
        return this.service.tariff && this.service.tariff.length > 0;
    }

    /**
     * Запомнить категорию тикетной системы
     */
    fetchTicketCategory(): void {
        this.ticketCategory = null;
        ticketCategoriesStore.commit('clear');
        ticketCategoriesStore.dispatch('fetchList').then(() => {
            for (let category of ticketCategoriesStore.state.list) {
                if (category.customerRole == this.service.customerRole) {
                    this.ticketCategory = category;
                }
            }
        });
    }

    /**
     * Свернуть / развернуть описание
     */
    toggleDescription(): void {
        $(this.$refs['description']).slideToggle('fast');
    }

    /**
     * Активировать услугу
     */
    activateService(tariff?: ServiceTariffInterface): void {
        if (this.serviceIsActivated) {
            return;
        }

        this.$validator.validateAll().then(() => {
            pageMetaStore.commit('showPageLoader');
            let tariff = null;
            if (this.selectedTariff) {
                for (let item of this.service.tariff) {
                    if (item.id == this.selectedTariff) {
                        tariff = item;
                        break;
                    }
                }
            }
            extendedServicesService.activate(this.service, tariff).then((response) => {
                if (response.success) {
                    extendedServiceStore.commit('addActivatedItem', response.activated);
                    this.fetchTicketCategory();
                }
                pageMetaStore.commit('hidePageLoader');
            }, () => pageMetaStore.commit('hidePageLoader'));
        }, () => {});
    }

    /**
     * Деактивировать услугу
     */
    deactivateService(): void {
        if (this.serviceIsActivated) {
            pageMetaStore.commit('showPageLoader');
            extendedServicesService.deactivate(this.service).then((response) => {
                if (response.success) {
                    extendedServiceStore.commit('removeActivatedItem', response.activated);
                    this.fetchTicketCategory();
                }
                pageMetaStore.commit('hidePageLoader');
            }, () => pageMetaStore.commit('hidePageLoader'));
        }
    }

    beforeRouteEnter(to, from, next): void {
        extendedServiceStore.dispatch('fetchActivatedList').then(() => {
            extendedServiceStore.dispatch('getServiceById', to.params.service).then((service: ServiceInterface) => {
                next(vm => vm.setService(service));
            }, () => next('404'));
        });
    }

    beforeRouteUpdate(to, from, next): void {
        extendedServiceStore.dispatch('fetchActivatedList').then(() => {
            extendedServiceStore.dispatch('getServiceById', to.params.service).then((service: ServiceInterface) => {
                this.setService(service);
                next();
            }, () => next('404'));
        });
    }

    beforeRouteLeave(to, from, next): void {
        ticketCategoriesStore.commit('clear');
        next();
    }

    /**
     * Был создан новый тикет
     */
    ticketSaved(ticket: TicketInterface): void {
        ticketListStore.dispatch('addTicket', ticket).then(() => {
            this.toggledTicketForm = false;
        });
    }
}
