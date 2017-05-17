import * as Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import {TabPane} from "./tab-pane";

/**
 * Виджет табов (работает вместе с tab-pane)
 */
@Component({
    template: require('./tabs.html')
})
export class Tabs extends Vue {
    /**
     * По умолчанию открытая панель
     */
    @Prop({type: Number, default: 0}) defaultTabPane: number;

    /**
     * Табы отображаются внутри модального окна
     */
    @Prop({type: Boolean, default: false}) inModalWindow: boolean;

    /**
     * Панели табов
     */
    @Model() tabPanes: Array<TabPane> = [];

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
