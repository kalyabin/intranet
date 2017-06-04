import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {ticketCategoriesStore} from "../../store/ticket-categories.store";
import {pageMetaStore} from "../../router/page-meta-store";
import {Model, Watch} from "vue-property-decorator";
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
     * Выбранная категория
     */
    @Model() category: TicketCategoryInterface = null;

    /**
     * При изменении категории - получать новый список
     */
    @Watch('category') onChangeCategory(category: TicketCategoryInterface) {
        let categoryId = category ? category.id : null;

        let fetchList = (categoryId: string) => {
            pageMetaStore.commit('showPageLoader');
            this.$store.dispatch('clear').then(() => {
                this.$store.dispatch('fetchList', categoryId).then(() => {
                    pageMetaStore.commit('hidePageLoader');
                });
            });
        };

        // проверить права на просмотр категории
        if (categoryId) {
            ticketCategoriesStore.dispatch('checkCategory', categoryId).then(() => {
                fetchList(categoryId);
            }, () => {
                // если нет доступа к категории - показать все доступные тикеты
                fetchList(null);
            });
        } else {
            fetchList(null);
        }
    }

    /**
     * Список категорий
     */
    get categories(): TicketCategoryInterface[] {
        return ticketCategoriesStore.state.list as TicketCategoryInterface[];
    }

    /**
     * Список тикетов
     */
    get list(): TicketInterface[] {
        return this.$store.state.list as TicketInterface[];
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
     * Переход к другой категории
     */
    selectCategory(category: TicketCategoryInterface): void {
        router.push({
            name: this.userType == 'customer' ? 'cabinet_ticket_list' : 'manager_ticket_list',
            params: <any>{
                category: category.id
            }
        });
    }

    /**
     * Открыть страницу тикета
     */
    openTicket(ticket: TicketInterface): void {
        router.push({
            name: this.userType == 'customer' ? 'cabinet_ticket_details' : 'manager_ticket_details',
            params: <any>{
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

        this.category = category;
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
                    next(vm => vm.setCategory(null))
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
