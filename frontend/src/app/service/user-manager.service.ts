import {BackendService, backendService} from "./backend.service";
import {AxiosResponse} from "axios";
import {UserListInterface} from "./response/user-list.interface";
import {UserRequestInterface} from "./request/user-request.interface";
import {UserResponseInterface} from "./response/user-response.interface";
import {UserDetailsInterface} from "./model/user-datails.interface";
import {RolesResponseInterface} from "./response/roles-response.interface";

/**
 * Сервис для менеджера пользователей
 */
export class UserManagerService {
    constructor(
        protected backendService: BackendService
    ) { }

    /**
     * Список пользователей с постраничной навигацией
     */
    list(pageNum: number = 0, pageSize: number = 150): Promise<UserListInterface> {
        return this.backendService
            .makeRequest('GET', 'manager/user', {
                pageNum: pageNum,
                pageSize: pageSize
            })
            .then((response: AxiosResponse) => {
                let data: UserListInterface = <UserListInterface>response.data;
                return data;
            });
    }

    /**
     * Создание пользователя
     */
    create(user: UserRequestInterface): Promise<UserResponseInterface> {
        return this.backendService
            .makeRequest('POST', 'manager/user', {
                user: user
            })
            .then((response: AxiosResponse) => {
                let data: UserResponseInterface = <UserResponseInterface>response.data;
                return data;
            });
    }

    /**
     * Обновление пользователя
     */
    update(id: number, user: UserRequestInterface): Promise<UserResponseInterface> {
        return this.backendService
            .makeRequest('POST', `manager/user/${id}`, {
                user: user
            })
            .then((response: AxiosResponse) => {
                let data: UserResponseInterface = <UserResponseInterface>response.data;
                return data;
            });
    }

    /**
     * Удаление пользователя
     */
    remove(id: number): Promise<UserResponseInterface> {
        return this.backendService
            .makeRequest('DELETE', `manager/user/${id}`)
            .then((response: AxiosResponse) => {
                let data: UserResponseInterface = <UserResponseInterface>response.data;
                return data;
            });
    }

    /**
     * Детальные данные о пользователе
     */
    details(id: number): Promise<UserDetailsInterface> {
        return this.backendService
            .makeRequest('GET', `manager/user/${id}`)
            .then((response: AxiosResponse) => {
                let data: UserDetailsInterface = <UserDetailsInterface>response.data;
                return data;
            });
    }

    /**
     * Информация о ролях
     */
    roles(): Promise<RolesResponseInterface> {
        return this.backendService
            .makeRequest('GET', 'manager/user/roles')
            .then((response: AxiosResponse) => {
                let data: RolesResponseInterface = <RolesResponseInterface>response.data;
                return data;
            });
    }
}

export const userManagerService = new UserManagerService(backendService);
