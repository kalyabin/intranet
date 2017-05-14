import Vue from "vue";
import Component from "vue-class-component";
import {Prop} from "vue-property-decorator";
import $ from "jquery";

/**
 * Модальное окно
 */
@Component({
    template: require('./modal-window.component.html')
})
export default class ModalWindowComponent extends Vue {
    /**
     * Заголовок модального окна
     */
    @Prop(String) title: string;

    /**
     * Показать модальное окно
     */
    show(): void {
        $(this.$refs['modal']).modal('show');
    }

    /**
     * Скрыть модальное окно
     */
    hide(): void {
        $(this.$refs['modal']).modal('hide');
    }
}
