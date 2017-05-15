import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import TabsComponent from "./tabs.component";

Component.registerHooks([
    'mounted'
]);

/**
 * Единичный таб
 */
@Component({
    template: require('./tab-pane.component.html')
})
export default class TabPaneComponent extends Vue {
    /**
     * Заголовок таба
     */
    @Prop(String) label: string;

    /**
     * Состояние таба
     */
    @Model() opened: boolean = false;

    mounted(): void {
        let parent = <TabsComponent>this.$parent;
        parent.tabPanes.push(this);
    }

    open(): void {
        this.opened = true;
    }

    close(): void {
        this.opened = false;
    }
}