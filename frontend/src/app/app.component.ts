import Vue from "vue";
import Component from "vue-class-component";
import PageNotFoundComponent from "./page-not-found.component";

/**
 * Корневой компонент: редиректы состояний, другие корневые события
 */
@Component({
    template: '<div>testing</div>',
    props: {
        currentState: String,
        routes: {}
    }
})
export default class AppComponent extends Vue {
    /**
     * Текущий роут
     */
    currentState: string;

    /**
     * Правила роутинга
     */
    routes: {[key: string]: Vue};

    render(h) {
        return h(this.routes[this.currentState] || PageNotFoundComponent);
    }
}
