import Vue from 'vue';
import Component from "vue-class-component";
import $ from 'jquery';
import {LoginForm} from "./login-form";
import {RememberPasswordForm} from "./remember-password-form";

Component.registerHooks([
    'mounted',
    'beforeRouteLeave'
]);

/**
 * Страница авторизации
 */
@Component({
    template: require('./sign-in.html'),
    components: {
        'login-form': LoginForm,
        'remember-password-form': RememberPasswordForm
    }
})
export class SignIn extends Vue {
    mounted(): void {
        $('body').addClass('login');
    }

    beforeRouteLeave(to, from, next): void {
        $('body').removeClass('login');
        next();
    }
}
