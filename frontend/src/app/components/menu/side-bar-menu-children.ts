import Vue from "vue";
import Component from "vue-class-component";
import {Prop} from "vue-property-decorator";
import {SidebarMenuItem} from "../../menu/sidebar-menu-item.interface";

/**
 * Вывод дочерних пунктов меню, если они есть
 */
@Component({
    template: require('./side-bar-menu-children.html')
})
export class SideBarMenuChildren extends Vue {
    @Prop(Object) parent: SidebarMenuItem;
    @Prop(Number) index: number;
}
