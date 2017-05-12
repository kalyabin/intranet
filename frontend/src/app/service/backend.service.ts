import Axios, {AxiosError, AxiosInstance, AxiosPromise, AxiosRequestConfig, AxiosResponse} from "axios";
import {environment} from "../../environment";

/**
 * Сервис для выполнения API-запросов на бекенд
 */
export class BackendService {
    /**
     * HTTP-сервис
     */
    protected http: AxiosInstance;

    /**
     * Токен пользователя
     */
    protected csrfToken: string;

    constructor(
        baseApiUrl: string
    ) {
        this.http = Axios.create({
            baseURL: baseApiUrl,
            headers: {
                'Content-Type': 'application/json'
            }
        });
    }

    /**
     * Выполнить запрос
     */
    makeRequest(method: string, uri: string, data?: any): AxiosPromise {
        let headers = {};

        if (this.csrfToken) {
            headers['X-CSRF-Token'] = this.csrfToken;
        }

        return this.http.request({
            method: method,
            url: uri,
            data: data,
            headers: headers
        })
        .then((response: AxiosResponse) => {
            // если пришел новый токен - запомнить его в переменной
            for (let key in response.headers) {
                if (key.toLowerCase() == 'x-csrf-token') {
                    this.csrfToken = response.headers[key];
                }
            }
            return response;
        })
        .catch((error: AxiosError) => {
            // TODO: сделать обработку ошибок
            console.log(error.response);

            return error.response;
        });
    }
}

export const backendService = new BackendService(environment.backendApiUrl);
