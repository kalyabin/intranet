import LoginComponent from "../user/sign-in.component";
import DashboardComponent from "../dashboard.component";
import {RouteConfig} from "vue-router";
import RestorePasswordComponent from "../user/restore-password.component";
import PageNotFoundComponent from "../page-not-found.component";
import IndexComponent from "../index.component";

/**
 * Правила роутинга
 */
export const routes: Array<RouteConfig> = [
    { path: '/', name: 'login', component: LoginComponent },
    { path: '/change-password/:checkerId/:checkerCode', name: 'restore-password', component: RestorePasswordComponent },
    { path: '/404', name: '404', component: PageNotFoundComponent },
    { path: '/auth', name: 'dashboard', component: DashboardComponent, children: [
        { path: '/auth/index', name: 'auth_index', component: IndexComponent }
    ] },
];
