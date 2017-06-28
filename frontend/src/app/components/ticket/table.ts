import Vue from "vue";
import Component from "vue-class-component";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {Prop} from "vue-property-decorator";
import {pageMetaStore} from "../../router/page-meta-store";
import {ticketCategoriesStore} from "../../store/ticket-categories.store";
import {TicketInterface} from "../../service/model/ticket.interface";
import {ticketListStore} from "../../store/ticket-list.store";

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
    @Prop(Object) category: TicketCategoryInterface = null;

    /**
     * Получить список тикетов
     */
    fetchTicketList(categoryId: string): void {
        let fetchList = (categoryId: string) => {
            pageMetaStore.commit('showPageLoader');
            this.$store.dispatch('clear').then(() => {
                this.$store.dispatch('fetchList', categoryId).then(() => {
                    pageMetaStore.commit('hidePageLoader');
                }, () => pageMetaStore.commit('hidePageLoader'));
            }, () => pageMetaStore.commit('hidePageLoader'));
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
}
