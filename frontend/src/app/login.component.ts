import Vue from 'vue';
import Component from "vue-class-component";

declare const $: any;

/**
 * Страница авторизации
 */
@Component({
    template: require('./login.component.html')
})
export default class LoginComponent extends Vue {
    mounted(): void {
        $('body').addClass('login');
    }

    detached(): void {
        $('body').removeClass('login');
    }
}
