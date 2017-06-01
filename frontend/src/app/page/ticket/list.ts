import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../store/ticket-categories.store";
import {pageMetaStore} from "../../router/page-meta-store";
import {Model} from "vue-property-decorator";
import {ticketListStore} from "../../store/ticket-list.store";
import {TicketInterface} from "../../service/model/ticket.interface";
import {authUserStore} from "../../store/auth-user.store";
import {UserType} from "../../service/model/user.interface";
import {router} from "../../router/router";

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
    store: ticketListStore
})
export class TicketList extends Vue {
    /**
     * Категория тикетной системы
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * Список тикетов
     */
    get list(): TicketInterface[] {
        return this.$store.state.list as TicketInterface[];
    }

    /**
     * Получить ссылку на форму создания нового тикета
     */
    get createLinkRoute() {
        return {
            name: 'ticket_customer_list',
            params: {
                category: this.category ? this.category.id : ''
            }
        };
    }

    /**
     * Получить тип пользователя
     */
    get userType(): UserType {
        return authUserStore.state.userData.userType;
    }

    /**
     * При выходе из компонента очистить список тикетов
     */
    beforeDestroy(): void {
        this.$store.commit('clear');
    }

    /**
     * Открыть страницу тикета
     */
    openTicket(ticket: TicketInterface): void {
        router.push({
            name: this.userType == 'customer' ? 'cabinet_ticket_details' : 'manager_ticket_details',
            params: <any>{
                category: this.category.id,
                ticket: ticket.id
            }
        });
    }

    /**
     * Установка категории
     */
    setCategory(category: TicketCategoryInterface): void{
        pageMetaStore.commit('setTitle', `Заявки: ${category.name}`);
        pageMetaStore.commit('setPageTitle', `${category.name}`);

        // рендер списка тикетов
        if (!this.category || this.category.id != category.id) {
            this.$store.dispatch('clear').then(() => {
                this.$store.dispatch('fetchList', this.category.id);
            });
        }

        this.category = category;
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
