import {BackendService, backendService} from "./backend.service";
import {AuthInterface} from "./response/auth.interface";
import {AxiosResponse} from "axios";
import {LoginInterface} from "./model/login.interface";
import {UserInterface} from "./model/user.interface";
import {environment} from "../../environment";
import {RememberPasswordInterface} from "./response/remember-password.interface";
import {RestorePasswordInterface} from "./response/restore-password.interface";
import {authUserStore} from "../store/auth-user.store";

/**
 * Сервис для работы с текущим авторизованным пользователем
 */
export class AuthUserService {
    /**
     * Интервал для перезапуска статуса авторизации
     */
    protected checkAuthTimeout;

    constructor(
        protected backendService: BackendService,
        protected reloadAuthStateInterval: number
    ) { }

    /**
     * Проверка авторизации
     */
    checkAuth(): Promise<AuthInterface> {
        const commitUserData = (data: AuthInterface) => {
            authUserStore.commit('isAuth', data.auth);
            authUserStore.commit('userData', data.user);
            authUserStore.commit('isTemporaryPassword', data.isTemporaryPassword);
            authUserStore.commit('roles', data.roles);
        };

        return this.backendService
            .makeRequest('POST', 'check_auth')
            .then((response: AxiosResponse) => {
                let data = <AuthInterface>response.data;

                commitUserData(data);

                if (this.checkAuthTimeout) {
                    clearTimeout(this.checkAuthTimeout);
                }

                if (data.auth) {
                    // запустить регулярную проверку авторизации, если пользователь авторизован
                    this.checkAuthTimeout = setTimeout(() => {
                        this.checkAuth();
                    }, this.reloadAuthStateInterval);
                }

                return data;
            })
            .catch(() => {
                let data: AuthInterface = {
                    auth: false,
                    user: null,
                    isTemporaryPassword: false,
                    roles: []
                };
                commitUserData(data);
                return data;
            });
    }

    /**
     * Авторизация
     */
    login(username: string, password: string): Promise<LoginInterface> {
        return this.backendService
            .makeRequest('POST', 'login/check', {
                _username: username,
                _password: password
            }, false)
            .then((response: AxiosResponse) => {
                let data = <LoginInterface>response.data;
                return data;
            });
    }

    /**
     * Логаут пользователя
     */
    logout(): Promise<boolean> {
        return this.backendService
            .makeRequest('POST', 'logout')
            .then(() => {
                authUserStore.commit('isAuth', false);
                authUserStore.commit('userData', null);
                authUserStore.commit('isTemporaryPassword', false);
                authUserStore.commit('roles', []);

                if (this.checkAuthTimeout) {
                    clearTimeout(this.checkAuthTimeout);
                }

                return true;
            }).catch(() => false);
    }

    /**
     * Напоминание пароля
     */
    rememberPassword(email: string): Promise<RememberPasswordInterface> {
        return this.backendService
            .makeRequest('POST', 'remember_password/remember', {
                remember_password: {
                    email: email
                }
            })
            .then((response: AxiosResponse) => {
                let data = <RememberPasswordInterface>response.data;
                return data;
            });
    }

    /**
     * Восстановление пароля
     */
    restorePassword(checkerId: number, checkerCode: string, newPassword: string) {
        return this.backendService
            .makeRequest('POST', 'change_password/' + checkerId + '/' + checkerCode, {
                'change_password': {
                    'password': {
                        'first': newPassword,
                        'second': newPassword,
                    }
                }
            })
            .then((response: AxiosResponse) => {
                if (response.status == 404) {
                    let data: RestorePasswordInterface = {
                        success: false,
                        valid: false,
                        validationErrors: {
                            notFound: 'Неверный код подтверждения'
                        },
                        firstError: 'Неверный код подтверждения',
                        submitted: true
                    };
                    return data;
                }

                let data = <RestorePasswordInterface>response.data;
                return data;
            });
    }

    /**
     * Проверить роль пользователя
     */
    hasRole(role: string): boolean {
        let roles = authUserStore.state.roles;
        return !!(roles && roles.indexOf(role) != -1);
    }
}

export const authUserService = new AuthUserService(backendService, environment.reloadAuthStateInterval);
