import Vue from "vue";
import Component from "vue-class-component";
import {Model, Watch} from "vue-property-decorator";
import {userManagerService} from "../../service/user-manager.service";
import {UserListInterface} from "../../service/response/user-list.interface";
import $ from 'jquery';
import {defaultDtOptions} from "../../widgets/default-dt-options";

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

    updated(): void {
        if (this.needRebuildTable) {
            if (this.dtHandler) {
                $(this.$refs['table']).dataTable().fnDestroy();
            }
            this.dtHandler = $(this.$refs['table']).dataTable(defaultDtOptions).api();
        }
    }

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
}
