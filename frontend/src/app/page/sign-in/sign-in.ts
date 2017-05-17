import Vue from 'vue';
import Component from "vue-class-component";
import LoginFormComponent from "./login-form";
import RememberPasswordFormComponent from "./remember-password-form";
import $ from 'jquery';

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
        'login-form': LoginFormComponent,
        'remember-password-form': RememberPasswordFormComponent
    }
})
export default class SignIn extends Vue {
    mounted(): void {
        $('body').addClass('login');
    }

    beforeRouteLeave(to, from, next): void {
        $('body').removeClass('login');
        next();
    }
}
