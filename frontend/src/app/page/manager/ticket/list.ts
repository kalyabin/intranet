import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../../store/ticket-categories.store";
import {pageMetaStore} from "../../../router/page-meta-store";

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
    template: require('./list.html')
})
export class ManagerTicketList extends Vue {
    /**
     * Категория тикетной системы
     */
    category: TicketCategoryInterface;

    /**
     * Установка категории
     */
    setCategory(category: TicketCategoryInterface): void{
        this.category = category;

        pageMetaStore.commit('setTitle', `Заявки: ${category.name}`);
        pageMetaStore.commit('setPageTitle', `${category.name}`);
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
