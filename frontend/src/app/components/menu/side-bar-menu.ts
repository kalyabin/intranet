import Vue from "vue";
import Component from "vue-class-component";
import {Prop} from "vue-property-decorator";
import {SidebarMenuItem} from "../../menu/sidebar-menu-item.interface";
import {SideBarMenuChildren} from "./side-bar-menu-children";
import $ from "jquery";

/**
 * Вывод левого меню
 */
@Component({
    template: require('./side-bar-menu.html'),
    components: {
        children: SideBarMenuChildren
    }
})
export class SideBarMenu extends Vue {
    @Prop(Array) items: SidebarMenuItem[];

    toggleActivate(event: MouseEvent) {
        let $li = $(event.target).parents('li:first');
        if ($li.hasClass('active')) {
            $li.find('ul.child_menu').slideUp('fast');
        } else {
            $li.find('ul.child_menu').slideDown('fast');
        }
        $li.toggleClass('active');
    }
}
