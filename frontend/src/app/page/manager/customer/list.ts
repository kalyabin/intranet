import Vue from "vue";
import Component from "vue-class-component";
import {customerListStore} from "../../../store/customer-list.store";
import {CustomerInterface} from "../../../service/model/customer.interface";
import {defaultDtOptions} from "../../../components/default-dt-options";
import {ManagerCustomerForm} from "./form";
import {Model} from "vue-property-decorator";
import {ModalWindow} from "../../../components/modal-window";

/**
 * Список арендаторов
 */
@Component({
    template: require('./list.html'),
    store: customerListStore,
    components: {
        'customer-form': ManagerCustomerForm
    }
})
export class ManagerCustomerList extends Vue {
    /**
     * API для управления datatables.net
     */
    protected dtHandler;

    /**
     * Скрыть показать форму
     */
    @Model() viewForm: boolean = false;

    /**
     * Текущий редактируемый контрганте
     */
    @Model() currentCustomer: CustomerInterface = null;

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

    /**
     * Открыть диалог редактирования арендатора
     */
    openDialog(customer?: CustomerInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.show();

        this.currentCustomer = customer;
        this.viewForm = true;
    }

    /**
     * Создан новый контрагент
     */
    newCustomer(customer: CustomerInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        this.$store.dispatch('addCustomer', customer);
    }

    /**
     * Отредактирован контрагент
     */
    updatedCustomer(customer: CustomerInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        this.$store.dispatch('updateCustomer', customer);
    }

    /**
     * Контрагент удален
     */
    removedCustomer(id: number): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        this.$store.dispatch('removeCustomer', id);
    }
}
