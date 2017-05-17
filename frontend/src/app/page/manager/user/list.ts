import Vue from "vue";
import Component from "vue-class-component";
import {Model, Watch} from "vue-property-decorator";
import {userManagerService} from "../../../service/user-manager.service";
import {UserListInterface} from "../../../service/response/user-list.interface";
import $ from 'jquery';
import {defaultDtOptions} from "../../../widgets/default-dt-options";
import ModalWindowComponent from "../../../widgets/modal-window.component";
import {UserDetailsInterface} from "../../../service/model/user-datails.interface";
import {UserInterface} from "../../../service/model/user.interface";
import UserManagerFormComponent from "./form";
import {userListStore} from "../../../store/user-list.store";

Component.registerHooks([
    'mounted',
    'beforeUpdate',
    'updated',
]);

/**
 * Список пользователей
 */
@Component({
    template: require('./list.html'),
    store: userListStore,
    components: {
        'user-form': UserManagerFormComponent
    }
})
export default class ManagerUserList extends Vue {
    /**
     * API для управления datatables.net
     */
    protected dtHandler;

    /**
     * Текущий редактируемый или создаваемый пользователь
     */
    @Model() currentUser: UserInterface = null;

    /**
     * Показать / скрыть форму
     */
    @Model() viewForm: boolean = false;

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
     * Открыть диалог создания нового пользователя
     */
    openCreateDialog(event): void {
        event.preventDefault();

        this.currentUser = null;
        this.viewForm = true;

        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.show();
    }

    /**
     * Открыть диалог редактирования пользователя
     */
    openEditDialog(user: UserInterface): void {
        this.currentUser = user;
        this.viewForm = true;

        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.show();
    }

    /**
     * Создан новый пользователь
     */
    newUser(user: UserInterface): void {
        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.hide();

        this.currentUser = null;

        this.$store.commit('addUser', user);
    }

    /**
     * Отредактирован пользователь
     */
    updatedUser(user: UserInterface): void {
        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.hide();

        this.currentUser = null;

        this.$store.commit('updateUser', user);
    }

    /**
     * Пользователь удален
     */
    removedUser(id: number): void {
        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.hide();

        this.currentUser = null;

        this.$store.commit('removeUser', id);
    }
}
