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
     * Флаг необходимости ререндеринга таблицы
     */
    protected needRebuildTable: boolean = false;

    /**
     * Текущий редактируемый или создаваемый пользователь
     */
    @Model() currentUser: UserInterface = null;

    /**
     * Список пользователей
     */
    @Model() list = [];

    /**
     * При изменении списка перегенерировать dataTables
     */
    @Watch('list')
    onChangeList(): void {
        this.needRebuildTable = true;

    }

    /**
     * Рендер списка пользователя при изменинии массива
     */
    updated(): void {
        if (this.needRebuildTable) {
            if (this.dtHandler) {
                $(this.$refs['table']).dataTable().fnDestroy();
            }
            this.dtHandler = $(this.$refs['table']).dataTable(defaultDtOptions).api();
        }
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
                    } else {
                        this.needRebuildTable = true;
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

        let window: ModalWindowComponent = <ModalWindowComponent>this.$refs['modal-window'];
        window.show();
    }
}
