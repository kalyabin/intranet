import LoginComponent from "../user/sign-in.component";
import DashboardComponent from "../dashboard.component";
import {RouteConfig} from "vue-router";
import RestorePasswordComponent from "../user/restore-password.component";
import PageNotFoundComponent from "../page-not-found.component";

/**
 * Правила роутинга
 */
export const routes: Array<RouteConfig> = [
    { path: '/login', name: 'login', component: LoginComponent },
    { path: '/', name: 'dashboard', component: DashboardComponent },
    { path: '/change-password/:checkerId/:checkerCode', name: 'restore-password', component: RestorePasswordComponent },
    { path: '/404', name: '404', component: PageNotFoundComponent }
];
