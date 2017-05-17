import Vue from "vue";
import Component from "vue-class-component";
import {Prop} from "vue-property-decorator";

/**
 * Лоадер
 */
@Component({
    template: `<div class="loading" :class="{'visible': visible}"></div>`
})
export class Loader extends Vue {
    @Prop(Boolean) visible: boolean;
}
