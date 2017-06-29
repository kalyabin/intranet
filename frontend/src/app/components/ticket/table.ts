import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {Prop, Watch} from "vue-property-decorator";
import {ticketCategoriesStore} from "../../store/ticket-categories.store";
import {TicketInterface} from "../../service/model/ticket.interface";
import {ticketListStore} from "../../store/ticket-list.store";
import {UserType} from "../../service/model/user.interface";
import {authUserStore} from "../../store/auth-user.store";
import {createTicketRouteHelper} from "../../helpers/create-ticket-route";
import {router} from "../../router/router";
import {ticketDetailsRouteHelper} from "../../helpers/ticket-details-route";
import {ServiceInterface} from "../../service/model/service.interface";

/**
 * Список тикетов, доступных пользователю (арендатору или менеджеру) по определенной категории
 */
@Component({
    template: require('./table.html'),
    store: ticketListStore
})
export class TicketTable extends Vue {
    /**
     * Выбранная категория
     */
    @Prop(Object) category: TicketCategoryInterface;

    /**
     * Услуга, если список тикетов отображается в услугах
     */
    @Prop(Object) service: ServiceInterface;

    /**
     * Получить список тикетов
     */
    fetchTicketList(categoryId: string): void {
        let fetchList = (categoryId: string) => {
            this.$store.dispatch('clear').then(() => {
                this.$store.dispatch('fetchList', categoryId).then(() => {}, () => {});
            }, () => {});
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

    @Watch('category', {
        immediate: true
    })
    onChangeCategory(category: TicketCategoryInterface): void {
        if (category && category.id) {
            this.fetchTicketList(category.id);
        } else {
            this.fetchTicketList(null);
        }
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
     * Получить ссылку на создание нового тикета
     */
    get createRoute() {
        return createTicketRouteHelper(this.category);
    }

    /**
     * Открыть страницу тикета
     */
    openTicket(ticket: TicketInterface): void {
        router.push(ticketDetailsRouteHelper(ticket, this.service));
    }
}
