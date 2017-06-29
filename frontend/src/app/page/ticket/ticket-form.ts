import Vue from "vue";
import Component from "vue-class-component";
import {ticketListStore} from "../../store/ticket-list.store";
import {Model, Watch} from "vue-property-decorator";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../store/ticket-categories.store";
import {pageMetaStore} from "../../router/page-meta-store";
import {ticketService} from "../../service/ticket.service";
import {TicketRequestInterface} from "../../service/request/ticket-request.interface";
import {TicketResponseInterface} from "../../service/response/ticket-response.interface";
import {router} from "../../router/router";
import {Location} from "vue-router";
import {notificationStore} from "../../store/notification.store";
import {ServiceInterface} from "../../service/model/service.interface";
import {extendedServiceStore} from "../../store/extended-service.store";

/**
 * Форма создания нового тикета для арендатора
 */
@Component({
    template: require('./ticket-form.html'),
    store: ticketListStore
})
export class TicketForm extends Vue {
    /**
     * Категория тикетной системы
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * Если переход произошёл со страницы услуги
     */
    @Model() service: ServiceInterface = null;

    /**
     * Модель заполнеяемого тикета
     */
    @Model() ticket: TicketRequestInterface = {
        category: '',
        title: '',
        text: ''
    };

    /**
     * Текст сообщения из бекенда
     */
    @Model() errorMessage: string = '';

    /**
     * Ожидание окончания субмита
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Получение всех доступных категорий
     */
    get categories(): TicketCategoryInterface[] {
        return ticketCategoriesStore.state.list;
    }

    /**
     * Получить ссылку на список всех тикетов в категории
     */
    get categoryRoute(): Location {
        if (this.service) {
            return {
                name: 'cabinet_service_page',
                params: {
                    service: this.service.id
                }
            };
        }
        else if (this.category && this.category.id) {
            return {
                name: 'cabinet_ticket_list',
                params: {
                    category: this.category.id
                }
            };
        } else {
            return {
                name: 'cabinet_ticket_root'
            };
        }
    }

    /**
     * Установка категории и услуги, через котору произошёл переход в тикетную систему
     */
    setCategory(category: TicketCategoryInterface, service?: ServiceInterface): void {
        this.category = category;
        this.ticket.category = category.id;
        this.service = service;

        if (this.service) {
            pageMetaStore.commit('setPageTitle', `Услуги: ${this.service.title}`);
        }

        if (this.category) {
            pageMetaStore.commit('setTitle', `Создание заявки: ${category.name}`);

            this.$store.dispatch('fetchList', this.category.id);
        } else {
            pageMetaStore.commit('setTitle', `Создание заявки`);

            this.$store.dispatch('clear');
        }
    }

    /**
     * Проверка прав и получение категории тикетной системы
     */
    beforeRouteEnter(to, from, next): void {
        // проверить возможность просмотра категории текущим пользователем
        if (to.params.category) {
            ticketCategoriesStore.dispatch('checkCategory', to.params.category)
                .then((category: TicketCategoryInterface) => {
                    next(vm => vm.setCategory(category));
                }, () => {
                    next({name: '403'});
                });
        } else if (to.params.service) {
            extendedServiceStore.dispatch('getServiceById', to.params.service).then((service: ServiceInterface) => {
                ticketCategoriesStore.dispatch('checkCategory', service.id)
                    .then((category: TicketCategoryInterface) => {
                        next(vm => vm.setCategory(category, service));
                    }, () => {
                        next({name: '403'});
                    });
            });
        } else {
            next();
        }
    }

    /**
     * Переход между страницами списков заявок
     */
    beforeRouteUpdate(to, from, next): void {
        // проверить возможность просмотра категории текущим пользователем
        this.category = null;

        if (to.params.category) {
            ticketCategoriesStore.dispatch('checkCategory', to.params.category)
                .then((category: TicketCategoryInterface) => {
                    this.setCategory(category);
                    next();
                }, () => {
                    next({name: '403'});
                });
        } else if (to.params.service) {
            extendedServiceStore.dispatch('getServiceById', to.params.service).then((service: ServiceInterface) => {
                ticketCategoriesStore.dispatch('checkCategory', service.id)
                    .then((category: TicketCategoryInterface) => {
                        this.setCategory(category, service);
                        next();
                    }, () => {
                        next({name: '403'});
                    });
            }, () => next({name: '404'}));
        } else {
            next();
        }
    }

    /**
     * Субмит формы
     */
    submit(): void {
        this.$validator.validateAll().then(() => {
            // защита от двойного вскликивания
            if (this.awaitOfSubmit) {
                return;
            }
            this.awaitOfSubmit = true;

            pageMetaStore.commit('showPageLoader');
            ticketService.createTicket(this.ticket)
                .then((response: TicketResponseInterface) => {
                    if (response.success) {
                        this.$store.dispatch('addTicket', response.ticket)
                            .then(() => router.push(this.categoryRoute));
                        notificationStore.dispatch('flash', {
                            type: 'success',
                            text: `Заявка №${response.ticket.number} зарегистрирована в системе`
                        });
                    } else {
                        this.errorMessage = response.firstError;
                    }
                    this.awaitOfSubmit = false;
                    pageMetaStore.commit('hidePageLoader');
                }, () => {
                    this.awaitOfSubmit = false;
                    pageMetaStore.commit('hidePageLoader');
                });
        }, () => {});
    }
}
