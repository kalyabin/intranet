import {VNodeDirective} from "vue/types/vnode";
import {UserType} from "../service/model/user.interface";
import $ from 'jquery';
import {authUserStore} from "../store/auth-user.store";
import {DirectiveOptions} from "vue/types/options";

let checkUserType = (el, binding: VNodeDirective) => {
    let type: UserType = <UserType>binding.value;

    if (type && type != authUserStore.state.userData.userType) {
        $(el).hide();
    } else {
        $(el).show();
    }
};

/**
 * Директива скрывающая элемент, если тип пользователя не совпадает с указанным
 */
export const NeedUserTypeDirective: DirectiveOptions = {
    inserted: checkUserType,
    update: checkUserType,
    componentUpdated: checkUserType
};
