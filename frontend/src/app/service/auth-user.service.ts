import {BackendService, backendService} from "./backend.service";
import {AuthInterface} from "./response/auth.interface";
import {AxiosResponse} from "axios";
import {LoginInterface} from "./model/login.interface";
import {UserInterface} from "./model/user.interface";
import {environment} from "../../environment";
import {RememberPasswordInterface} from "./response/remember-password.interface";
import {RestorePasswordInterface} from "./response/restore-password.interface";
import {authUserStore} from "../store/auth-user.store";
import {UserNotificationInterface} from "./model/user-notification.interface";
import {cometClientService} from "./comet-client.service";
import {extendedServiceStore} from "../store/extended-service.store";

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
     * Подключение к comet-серверу
     */
    protected connectComet(): void {
        if (authUserStore.state.isAuth) {
            // активация comet-клиента
            cometClientService.disconnect();
            cometClientService.connect();
            cometClientService.registerFetchNewNotifications();
        }
    }

    /**
     * Отключение от comet-сервера
     */
    protected disconnectComet(): void {
        cometClientService.disconnect();
    }

    /**
     * Проверка авторизации
     */
    checkAuth(): Promise<AuthInterface> {
        const commitUserData = (data: AuthInterface) => {
            if (!authUserStore.state.userData && data.user) {
                // первичное заполнение данных о пользователе
                // получить список доступных услуг
                extendedServiceStore.dispatch('fetchActualList').then(() => {});
                if (data.user.userType == 'customer') {
                    // первичное заполнение данных арендатора
                    // заполнить список активированных услуг арендатора
                    extendedServiceStore.dispatch('fetchActivatedList').then(() => {});
                }
            }
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
                    if (!cometClientService.isConnected()) {
                        // если comet-сервер ещё не подключен - подключиться
                        this.connectComet();
                    }

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

                // очистить доп услуги
                extendedServiceStore.commit('clearActivatedList');
                extendedServiceStore.commit('clearActualList');

                // отключение comet-клиента
                this.disconnectComet();

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

    /**
     * Получить персонализированные уведомления для текущего авторизованного пользователя
     */
    notifications(): Promise<UserNotificationInterface[]> {
        return this.backendService
            .makeRequest('GET', 'notifications')
            .then((response: AxiosResponse) => {
                return response.data['list'] as UserNotificationInterface[];
            }, () => {
                return [];
            });
    }

    /**
     * Пометить все уведомления как прочитанные
     */
    readAllNotifications(): Promise<boolean> {
        return this.backendService
            .makeRequest('POST', 'notifications/read-all')
            .then(() => {
                return true;
            }, () => {
                return false;
            });
    }
}

export const authUserService = new AuthUserService(backendService, environment.reloadAuthStateInterval);
