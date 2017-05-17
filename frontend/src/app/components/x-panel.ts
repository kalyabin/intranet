import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import $ from "jquery";

@Component({
    template: require('./x-panel.html')
})
export class XPanel extends Vue {
    /**
     * Заголовок панели
     */
    @Prop() title: string;

    /**
     * Состояние блока
     */
    @Prop({type: Boolean, default: true}) toggled: boolean;

    @Model() visible: boolean = this.toggled;

    /**
     * Развернуть / свернуть
     */
    toggle(event = null): void {
        if (event) {
            event.preventDefault();
        }

        if (this.visible) {
            $(this.$refs['content']).slideUp(200, () => {
                this.visible = false;
            });
        } else {
            $(this.$refs['content']).slideDown(200, () => {
                this.visible = true;
            });
        }
    }
}
