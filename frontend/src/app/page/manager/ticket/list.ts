import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../../store/ticket-categories.store";
import {pageMetaStore} from "../../../router/page-meta-store";
import {Model} from "vue-property-decorator";
import {ticketListStore} from "../../../store/ticket-list.store";
import {TicketInterface} from "../../../service/model/ticket.interface";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Список тикетов.
 *
 * Внутрь компонента необходимо передавать категорию тикета.
 */
@Component({
    template: require('./list.html'),
    store: ticketListStore
})
export class ManagerTicketList extends Vue {
    /**
     * Категория тикетной системы
     */
    @Model() category: TicketCategoryInterface;

    /**
     * Список тикетов
     */
    get list(): TicketInterface[] {
        return this.$store.state.list as TicketInterface[];
    }

    /**
     * При выходе из компонента очистить список тикетов
     */
    beforeDestroy(): void {
        this.$store.commit('clear');
    }

    /**
     * Установка категории
     */
    setCategory(category: TicketCategoryInterface): void{
        this.category = category;

        pageMetaStore.commit('setTitle', `Заявки: ${category.name}`);
        pageMetaStore.commit('setPageTitle', `${category.name}`);

        // рендер списка тикетов
        this.$store.dispatch('clear').then(() => {
            this.$store.dispatch('fetchList', this.category.id);
        });
    }

    /**
     * Проверка прав и получение категории тикетной системы
     */
    beforeRouteEnter(to, from, next): void {
        // проверить возможность просмотра категории текущим пользователем
        // ticketCategoriesStore хранит в себе все доступные категории для пользователя
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
        // ticketCategoriesStore хранит в себе все доступные категории для пользователя
        this.category = null;

        ticketCategoriesStore.dispatch('checkCategory', to.params.category)
            .then((category: TicketCategoryInterface) => {
                this.setCategory(category);
                next();
            }, () => {
                next({name: '403'});
            });
    }
}
