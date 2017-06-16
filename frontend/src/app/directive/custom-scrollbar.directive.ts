/// <reference path="../../../node_modules/@types/mcustomscrollbar/index.d.ts" />

import {DirectiveOptions, VNodeDirective} from "vue";
import $ from "jquery";

const connectScrollbar = (el, binding: VNodeDirective): void => {
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
};

const disconnectScrollbar = (el): void => {
    $(el).mCustomScrollbar('destroy');
};

/**
 * Кастомный скроллбар
 */
export const CustomScrollbarDirective: DirectiveOptions = {
    inserted: (el, binding: VNodeDirective): void => {
        connectScrollbar(el, binding);
    },
    componentUpdated: (el, binding: VNodeDirective): void => {
        disconnectScrollbar(el);
        connectScrollbar(el, binding);
    },
    unbind: (el): void => {
        disconnectScrollbar(el);
    }
};
