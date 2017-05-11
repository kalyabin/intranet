import AppComponent from "./app/app.component";
import {routes} from "./app/routes";

/**
 * Точка входа приложения
 */

require('./theme/css/nprogress.css');
require('./theme/css/animate.min.css');
require('./theme/css/custom.min.css');

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
