import * as Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import TabPaneComponent from "./tab-pane.component";

Component.registerHooks([
    'mounted'
]);

/**
 * Виджет табов (работает вместе с tab-pane)
 */
@Component({
    template: require('./tabs.component.html')
})
export default class TabsComponent extends Vue {
    /**
     * По умолчанию открытая панель
     */
    @Prop({type: Number, default: 0}) defaultTabPane: number;

    /**
     * Панели табов
     */
    @Model() tabPanes: Array<TabPaneComponent> = [];

    /**
     * Текущая открытая панель
     */
    @Model() currentTab: number = this.defaultTabPane;

    @Watch('currentTab')
    onChangeCurrentTab(): void {
        for (let i in this.tabPanes) {
            let index = parseInt(i);
            let tabPane = this.tabPanes[index];
            tabPane.opened = index == this.currentTab;
        }
    }

    mounted(): void {
        this.onChangeCurrentTab();
    }

    selectTab(event, index): void {
        event.preventDefault();
        this.currentTab = index;
    }
}
