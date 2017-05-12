import {BackendService, backendService} from "./backend.service";
import {AuthInterface} from "../interface/auth.interface";
import {AxiosResponse} from "axios";
import {LoginInterface} from "../interface/login.interface";
import {UserInterface} from "../interface/user.interface";
import {environment} from "../../environment";

/**
 * Сервис для работы с текущим авторизованным пользователем
 */
export class AuthUserService {
    /**
     * Авторизован ли пользователь или нет
     */
    protected isAuth: boolean;

    /**
     * Данные пользователя
     */
    protected userData: UserInterface;

    /**
     * Временный пароль
     */
    protected isTemporaryPassword: boolean;

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
        return this.backendService
            .makeRequest('POST', 'check_auth')
            .then((response: AxiosResponse) => {
                let data = <AuthInterface>response.data;
                this.isAuth = data.auth;
                this.userData = data.user;
                this.isTemporaryPassword = data.isTemporaryPassword;

                if (this.checkAuthTimeout) {
                    clearTimeout(this.checkAuthTimeout);
                }

                if (this.isAuth) {
                    // запустить регулярную проверку авторизации, если пользователь авторизован
                    this.checkAuthTimeout = setTimeout(() => {
                        this.checkAuth();
                    }, this.reloadAuthStateInterval);
                }

                return data;
            })
            .catch(() => {
                let data: AuthInterface = {
                    auth: false
                };
                return data;
            });
    }

    /**
     * Авторизация
     */
    login(username: string, password: string): Promise<LoginInterface> {
        return this.backendService
            .makeRequest('POST', 'login/check', {
                username: username,
                password: password
            })
            .then((response: AxiosResponse) => {
                let data = <LoginInterface>response.data;
                return data;
            });
    }

    /**
     * Возвращает true, если требуется проверить авторизовацию пользователя
     */
    needCheckAuth(): boolean {
        return typeof this.isAuth == 'undefined';
    }

    /**
     * Возвращает true если пользователь авторизован
     */
    getIsAuth(): boolean {
        return this.isAuth == true;
    }
}

export const authUserService = new AuthUserService(backendService, environment.reloadAuthStateInterval);
