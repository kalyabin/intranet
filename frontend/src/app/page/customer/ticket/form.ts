import Vue from "vue";
import Component from "vue-class-component";
import {ticketListStore} from "../../../store/ticket-list.store";
import {Model} from "vue-property-decorator";
import {TicketCategoryInterface} from "../../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../../store/ticket-categories.store";
import {pageMetaStore} from "../../../router/page-meta-store";
import {Location} from "vue-router";
import {TicketForm} from "../../../components/ticket/form";
import {TicketInterface} from "../../../service/model/ticket.interface";
import {router} from "../../../router/router";

/**
 * Форма создания нового тикета для арендатора
 */
@Component({
    template: require('./form.html'),
    store: ticketListStore,
    components: {
        'ticket-form': TicketForm
    }
})
export class CustomerTicketForm extends Vue {
    /**
     * Категория тикетной системы
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * Получить ссылку на список всех тикетов в категории
     */
    get categoryRoute(): Location {
        return {
            name: 'cabinet_ticket_list',
            params: <any> {
                category: this.category ? this.category.id : null
            }
        };
    }

    /**
     * Установка категории и услуги, через котору произошёл переход в тикетную систему
     */
    setCategory(category: TicketCategoryInterface): void {
        this.category = category;

        pageMetaStore.commit('setTitle', `Заявки: ${category.name}`);
        pageMetaStore.commit('setPageTitle', `Заявки: ${category.name}`);
        this.$store.dispatch('fetchList', this.category.id);
    }

    /**
     * Проверка прав и получение категории тикетной системы
     */
    beforeRouteEnter(to, from, next): void {
        // проверить возможность просмотра категории текущим пользователем
        ticketCategoriesStore.dispatch('checkCategory', to.params.category)
            .then((category: TicketCategoryInterface) => {
                next(vm => vm.setCategory(category));
            }, () => {
                next({name: '403'});
            });
    }

    /**
     * Переход между страницами списков заявок
     */
    beforeRouteUpdate(to, from, next): void {
        // проверить возможность просмотра категории текущим пользователем
        ticketCategoriesStore.dispatch('checkCategory', to.params.category)
            .then((category: TicketCategoryInterface) => {
                this.setCategory(category);
                next();
            }, () => {
                next({name: '403'});
            });
    }

    cancel(): void {
        router.push(this.categoryRoute);
    }

    saved(ticket: TicketInterface): void {
        this.$store.dispatch('addTicket', ticket)
            .then(() => router.push(this.categoryRoute));
    }
}
