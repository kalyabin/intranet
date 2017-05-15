import {DirectiveOptions} from "vue";
import Switchery from "switchery";

/**
 * Директива для стилизации свитчей
 */
export const SwitcheryDirective: DirectiveOptions = {
    unbind: () => {
        if (this.switchery) {
            delete this.switchery;
        }
    },
    componentUpdated: (el) => {
        if (!this.switchery) {
            this.switchery = new Switchery(el, {
                color: '#26B99A'
            });
        }
    }
};
