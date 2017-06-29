import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../store/ticket-categories.store";
import {pageMetaStore} from "../../router/page-meta-store";
import {Model} from "vue-property-decorator";
import {authUserStore} from "../../store/auth-user.store";
import {UserType} from "../../service/model/user.interface";
import {router} from "../../router/router";
import {Location} from "vue-router";
import {TicketTable} from "../../components/ticket/table";
import {createTicketRouteHelper} from "../../helpers/create-ticket-route";

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteUpdate',
]);

/**
 * Список тикетов.
 *
 * Компонент единый для всех: для арендаторов и менеджеров.
 * Для менеджеров скрывается кнопка добавления тикета,
 * для арендаторов скрывается статистика и возможность смены менеджера.
 *
 * Внутрь компонента необходимо передавать категорию тикета.
 */
@Component({
    template: require('./list.html'),
    components: {
        'ticket-table': TicketTable
    }
})
export class TicketList extends Vue {
    /**
     * Выбранная категория
     */
    @Model() category: TicketCategoryInterface = null;

    get allCategories(): TicketCategoryInterface {
        return {
            id: null,
            name: 'Все',
            managerRole: null,
            customerRole: null
        };
    }

    /**
     * Список категорий
     */
    get categories(): TicketCategoryInterface[] {
        let list: TicketCategoryInterface[] = [this.allCategories];
        return list.concat(ticketCategoriesStore.state.list);
    }

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
        return createTicketRouteHelper(this.category);
    }

    /**
     * Переход к другой категории
     */
    selectCategory(category: TicketCategoryInterface): void {
        let route = {};
        if (category && category.id) {
            route = {
                name: this.userType == 'customer' ? 'cabinet_ticket_list' : 'manager_ticket_list',
                params: <any>{
                    category: category.id
                }
            };
        } else {
            route = {
                name: this.userType == 'customer' ? 'cabinet_ticket_root' : 'manager_ticket_root'
            };
        }
        router.push(route);
    }

    /**
     * Установка категории
     */
    setCategory(category: TicketCategoryInterface): void {
        if (category) {
            pageMetaStore.commit('setTitle', `Заявки: ${category.name}`);
        } else {
            pageMetaStore.commit('setTitle', `Заявки`);
        }

        this.category = category ? category : this.allCategories;
    }

    /**
     * Проверка прав и получение категории тикетной системы
     */
    beforeRouteEnter(to, from, next): void {
        if (to.params.category) {
            ticketCategoriesStore.dispatch('checkCategory', to.params.category)
                .then((category: TicketCategoryInterface) => {
                    next(vm => vm.setCategory(category));
                }, () => {
                    next(vm => vm.setCategory(null));
                });
        } else {
            next(vm => vm.setCategory(null));
        }
    }

    /**
     * Проверка прав и получение категории тикетной системы
     */
    beforeRouteUpdate(to, from, next): void {
        if (to.params.category) {
            ticketCategoriesStore.dispatch('checkCategory', to.params.category)
                .then((category: TicketCategoryInterface) => {
                    this.setCategory(category);
                    next();
                }, () => {
                    this.setCategory(null);
                    next();
                });
        } else {
            this.setCategory(null);
        }
    }
}
