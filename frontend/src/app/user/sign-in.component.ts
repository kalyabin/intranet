import Vue from 'vue';
import Component from "vue-class-component";
import $ from 'jquery';

Component.registerHooks([
    'mounted',
    'beforeRouteLeave'
]);

/**
 * Страница авторизации
 */
@Component({
    template: require('./sign-in.component.html')
})
export default class SignInComponent extends Vue {
    mounted(): void {
        $('body').addClass('login');
    }

    beforeRouteLeave(to, from, next): void {
        $('body').removeClass('login');
        next();
    }
}
