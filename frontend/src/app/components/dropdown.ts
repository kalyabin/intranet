import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";

/**
 * Бутстраповский дропдаун
 */
@Component({
    template: require('./dropdown.html')
})
export class Dropdown extends Vue {
    /**
     * Свойство для отображения
     */
    @Prop() displayProp: string;

    /**
     * Текущий выбранный элемент
     */
    @Prop() item: any;

    @Model() currentItem: any = this.item;

    /**
     * Элементы для отображения
     */
    @Prop() items: any[];

    @Watch('item') changeItem(item: any): void {
        this.currentItem = item;
    }

    /**
     * Получить подпись селекта
     */
    get label(): string {
        let currentItem = this.currentItem || this.items[0];
        return this.getItemLabel(currentItem);
    }

    /**
     * Получить подпись элемента
     */
    getItemLabel(item: any): string {
        if (!item) {
            return '';
        }
        return item[this.displayProp] instanceof Function ?
            item[this.displayProp]() :
            item[this.displayProp];
    }

    /**
     * Выбор элемента
     */
    selectItem(item: any): void {
        this.$emit('select', item);
        this.currentItem = item;
    }
}
