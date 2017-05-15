import Vue from "vue";
import Component from "vue-class-component";
import {Model, Watch} from "vue-property-decorator";
import {userManagerService} from "../../service/user-manager.service";
import {UserListInterface} from "../../service/response/user-list.interface";
import $ from 'jquery';
import {defaultDtOptions} from "../../widgets/default-dt-options";
import ModalWindowComponent from "../../widgets/modal-window.component";
import {UserDetailsInterface} from "../../service/model/user-datails.interface";
import {UserInterface} from "../../service/model/user.interface";

Component.registerHooks([
    'mounted',
    'beforeUpdate',
    'updated',
]);

/**
 * Список пользователей
 */
@Component({
    template: require('./list.component.html')
})
export default class UserManagerListComponent extends Vue {
    /**
     * API для управления datatables.net
     */
    protected dtHandler;

    /**
     * Текущий редактируемый или создаваемый пользователь
     */
    @Model() currentUser: UserInterface = null;

    /**
     * Список пользователей
     */
    @Model() list = [];

    /**
     * Показать / скрыть форму
     */
    @Model() viewForm: boolean = false;

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
        let currentPage = 0;

        // заполнить список пользователей
        let fetchItems = () => {
            userManagerService
                .list(currentPage, 1)
                .then((response: UserListInterface) => {
                    this.list = this.list.concat(response.list);
                    currentPage = response.pageNum + 1;
                    if (response.totalCount > this.list.length) {
                        // запросить еще порцию пользователей
                        fetchItems();
                    }
                });
        };

        fetchItems();
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

        this.currentUser = user;
        this.viewForm = false;

        this.list.push(user);
    }

    /**
     * Отредактирован пользователь
     */
    updatedUser(user: UserInterface): void {
        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.hide();

        this.currentUser = user;
        this.viewForm = false;

        for (let i in this.list) {
            if (this.list[i].id == user.id) {
                this.list[i] = user;
                break;
            }
        }
    }

    /**
     * Пользователь удален
     */
    removedUser(id: number): void {
        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.hide();

        this.currentUser = null;
        this.viewForm = false;

        for (let i in this.list) {
            if (this.list[i].id == id) {
                this.list.splice(parseInt(i), 1);
                break;
            }
        }
    }
}
