import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../../store/ticket-categories.store";
import {pageMetaStore} from "../../../router/page-meta-store";
import {Model} from "vue-property-decorator";
import {authUserStore} from "../../../store/auth-user.store";
import {UserType} from "../../../service/model/user.interface";
import {Location} from "vue-router";
import {TicketTable} from "../../../components/ticket/table";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Список тикетов для арендатора.
 *
 * Внутрь компонента необходимо передавать категорию тикета.
 */
@Component({
    template: require('./list.html'),
    components: {
        'ticket-table': TicketTable
    }
})
export class CustomerTicketList extends Vue {
    /**
     * Выбранная категория
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * Получить тип пользователя
     */
    get userType(): UserType {
        return authUserStore.state.userData.userType;
    }

    /**
     * Получить ссылку на создание нового тикета
     */
    get createRoute(): Location {
        return {
            name: 'cabinet_ticket_create',
            params: <any>{
                category: this.category ? this.category.id : null
            }
        };
    }

    /**
     * Установка категории
     */
    setCategory(category: TicketCategoryInterface): void {
        pageMetaStore.commit('setTitle', `Заявки: ${category.name}`);
        pageMetaStore.commit('setPageTitle', `Заявки: ${category.name}`);
        this.category = category;
    }

    /**
     * Проверка прав и получение категории тикетной системы
     */
    beforeRouteEnter(to, from, next): void {
        ticketCategoriesStore.dispatch('checkCategory', to.params.category)
            .then((category: TicketCategoryInterface) => {
                next(vm => vm.setCategory(category));
            }, () => next({name: '403'}));
    }

    /**
     * Проверка прав и получение категории тикетной системы
     */
    beforeRouteUpdate(to, from, next): void {
        ticketCategoriesStore.dispatch('checkCategory', to.params.category)
            .then((category: TicketCategoryInterface) => {
                this.setCategory(category);
                next();
            }, () => next({name: '403'}));
    }
}
