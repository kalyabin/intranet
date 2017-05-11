import LoginComponent from "./login.component";
import DashboardComponent from "./dashboard.component";

/**
 * Правила роутинга
 */
export const routes = {
    '/login': LoginComponent,
    '/': DashboardComponent
};
