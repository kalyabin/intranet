import LoginComponent from "../login.component";
import DashboardComponent from "../dashboard.component";
import {RouteConfig} from "vue-router";

/**
 * Правила роутинга
 */
export const routes: Array<RouteConfig> = [
    { path: '/login', name: 'login', component: LoginComponent },
    { path: '/', name: 'dashboard', component: DashboardComponent }
];
