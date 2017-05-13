import {DirectiveOptions, VNodeDirective} from "vue";
import $ from 'jquery';
import {userStore} from "../user/user-store";

let checkRole = (el, binding: VNodeDirective) => {
    let role: string = <string>binding.value;

    if (role) {
        // проверить роль, есть ли она у пользователя
        let roles: string[] = userStore.state.roles;
        if (roles.length && roles.indexOf(role) == -1) {
            $(el).hide();
        } else {
            $(el).show();
        }
    } else {
        // если роль не передана - показывать элемент всегда
        $(el).show();
    }
};

/**
 * Директива скрывающая элемент, если у пользователя недостаточно прав для ее просмотра.
 */
export const NeedRoleDirective: DirectiveOptions = {
    inserted: checkRole,
    update: checkRole,
    componentUpdated: checkRole
};
