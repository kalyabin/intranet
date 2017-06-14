/// <reference path="../../../node_modules/@types/mcustomscrollbar/index.d.ts" />

import {DirectiveOptions, VNodeDirective} from "vue";
import $ from "jquery";

/**
 * Кастомный скроллбар
 */
export const CustomScrollbarDirective: DirectiveOptions = {
    inserted: (el, binding: VNodeDirective): void => {
        let fullContentScroll = <Function>binding.value;
        let options = {
            autoHideScrollbar: true,
            theme: 'minimal',
        };
        if (fullContentScroll) {
            options['callbacks'] = {
                onTotalScroll: fullContentScroll
            };
        }
        $(el).mCustomScrollbar(options);
    },
    unbind: (el): void => {
        $(el).mCustomScrollbar('destroy');
    }
};
