import AppComponent from "./app/app.component";
import {routes} from "./app/routes";

/**
 * Точка входа приложения
 */

require('./theme/nprogress.css');
require('./theme/animate.min.css');
require('./theme/custom.min.css');

const app = new AppComponent({
    el: '#app',
    data: {
        currentState: window.location.pathname,
        routes: routes
    }
});

window.addEventListener('pushstate', () => {
    app.currentState = window.location.pathname;
});
