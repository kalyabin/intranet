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
    makeRequest(method: string, uri: string, data?: any, asPayload: boolean = true): AxiosPromise {
        let headers = {};

        if (this.csrfToken) {
            headers['X-CSRF-Token'] = this.csrfToken;
        }

        if (!asPayload) {
            headers['Content-Type'] = 'multipart/form-data';

            let newData = new FormData();

            for (let key of Object.keys(data)) {
                newData.append(key, data[key]);
            }

            data = newData;
        }

        let retrieveCsrfToken = (response: AxiosResponse) => {
            if (response && response.headers) {
                for (let key in response.headers) {
                    if (key.toLowerCase() == 'x-csrf-token') {
                        this.csrfToken = response.headers[key];
                    }
                }
            }
        };

        let request: AxiosRequestConfig = {
            method: method,
            url: uri,
            headers: headers
        };

        if (method == 'GET') {
            request.params = data;
        } else {
            request.data = data;
        }

        return this.http.request(request)
        .then((response: AxiosResponse) => {
            // если пришел новый токен - запомнить его в переменной
            retrieveCsrfToken(response);
            return response;
        })
        .catch((error: AxiosError) => {
            retrieveCsrfToken(error.response);

            // TODO: сделать обработку ошибок
            console.log(error.response);

            return error.response;
        });
    }
}

export const backendService = new BackendService(environment.backendApiUrl);
