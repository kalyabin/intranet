import Vue from 'vue';
import Component from "vue-class-component";
import $ from 'jquery';

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
