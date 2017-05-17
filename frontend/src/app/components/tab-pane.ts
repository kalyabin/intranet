import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import {Tabs} from "./tabs";

/**
 * Единичный таб
 */
@Component({
    template: require('./tab-pane.html')
})
export class TabPane extends Vue {
    /**
     * Заголовок таба
     */
    @Prop(String) label: string;

    /**
     * Состояние таба
     */
    @Model() opened: boolean = false;

    /**
     * Статус отображения
     */
    @Prop({type: Boolean, default: true}) visible: boolean;

    /**
     * Подсвечивать как ошибку
     */
    @Prop({type: Boolean, default: false}) hasError: boolean;

    /**
     * Добавить к родителю
     */
    mounted(): void {
        let parent = <Tabs>this.$parent;
        parent.tabPanes.push(this);
    }

    open(): void {
        this.opened = true;
    }

    close(): void {
        this.opened = false;
    }
}
