import Vue from "vue";
import Component from "vue-class-component";
import {Model} from "vue-property-decorator";
import $ from 'jquery';
import {UserInterface} from "../../../service/model/user.interface";
import {userListStore} from "../../../store/user-list.store";
import {defaultDtOptions} from "../../../components/default-dt-options";
import {ManagerUserForm} from "./form";
import {ModalWindow} from "../../../components/modal-window";
import {customerListStore} from "../../../store/customer-list.store";

/**
 * Список пользователей
 */
@Component({
    template: require('./list.html'),
    store: userListStore,
    components: {
        'user-form': ManagerUserForm
    }
})
export class ManagerUserList extends Vue {
    /**
     * Текущий редактируемый пользователь
     */
    @Model() currentUser: UserInterface = null;

    /**
     * Показать / скрыть форму
     */
    @Model() viewForm: boolean = false;

    /**
     * API для управления datatables.net
     */
    protected dtHandler;

    /**
     * Список пользователей
     */
    get list(): UserInterface[] {
        return this.$store.state.list;
    }

    /**
     * Ребилд таблицы
     */
    beforeUpdate(): void {
        if (this.dtHandler) {
            $(this.$refs['table']).DataTable().destroy();
        }
    }

    /**
     * Рендер списка пользователя при изменинии массива
     */
    updated(): void {
        this.dtHandler = $(this.$refs['table']).DataTable(defaultDtOptions);
    }

    /**
     * Получение массива пользователей при рендере компонента
     */
    mounted(): void {
        this.$store.dispatch('fetchList');
    }

    /**
     * Очищать сторейдж после выхода со страницы
     */
    beforeDestroy(): void {
        this.$store.commit('clear');
        customerListStore.commit('clear');
    }

    /**
     * Открыть диалог редактирования пользователя
     */
    openDialog(user?: UserInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.show();

        this.currentUser = user;
        this.viewForm = true;
    }

    /**
     * Создан новый пользователь
     */
    newUser(user: UserInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        this.$store.dispatch('addUser', user);
    }

    /**
     * Отредактирован пользователь
     */
    updatedUser(user: UserInterface): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        this.$store.dispatch('updateUser', user);
    }

    /**
     * Пользователь удален
     */
    removedUser(id: number): void {
        let window: ModalWindow = <ModalWindow>this.$refs['modal-window'];
        window.hide();

        this.$store.dispatch('removeUser', id);
    }
}
