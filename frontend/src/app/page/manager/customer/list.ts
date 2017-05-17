import Vue from "vue";
import Component from "vue-class-component";
import {customerListStore} from "../../../store/customer-list.store";
import {CustomerInterface} from "../../../service/model/customer.interface";
import {defaultDtOptions} from "../../../components/default-dt-options";

/**
 * Список арендаторов
 */
@Component({
    template: require('./list.html'),
    store: customerListStore
})
export class ManagerCustomerList extends Vue {
    /**
     * API для управления datatables.net
     */
    protected dtHandler;

    /**
     * Ребилд таблицы datatables.net
     */
    beforeUpdate(): void {
        if (this.dtHandler) {
            $(this.$refs['table']).DataTable().destroy();
        }
    }

    /**
     * Рендер datatables.net
     */
    updated(): void {
        this.dtHandler = $(this.$refs['table']).DataTable(defaultDtOptions);
    }

    /**
     * Заполнить список пользователей при переходе на роут
     */
    mounted(): void {
        this.$store.dispatch('fetchList');
    }

    /**
     * Очистить сторейдж после выхода со страницы
     */
    beforeDestroy(): void {
        this.$store.commit('clear');
    }

    /**
     * Список арендаторов
     */
    get list(): CustomerInterface[] {
        return this.$store.state.list;
    }
}
