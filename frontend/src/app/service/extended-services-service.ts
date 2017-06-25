import {BackendService, backendService} from "./backend.service";
import {ListInterface} from "./response/list.interface";
import {ServiceInterface} from "./model/service.interface";
import {AxiosResponse} from "axios";
import {ServiceResponseInterface} from "./response/service-response.interface";
/**
 * Сервис для работы с дополнительными услугами
 */
export class ExtendedServicesService {
    constructor(
        private backendService: BackendService
    ) { }

    /**
     * Получить список услуг для менеджера, управляющего этими услугами.
     * Разница в том, что менеджер видит в том числе и деактивированные услуги.
     */
    public managerList(): Promise<ListInterface<ServiceInterface>> {
        return this.backendService.makeRequest('GET', 'manager/service').then((response: AxiosResponse) => {
            return response.data as ListInterface<ServiceInterface>;
        });
    }

    /**
     * Получить детальную ифнормацию о услуге.
     * Сервис доступен только для менеджера
     */
    public managerDetails(id: string): Promise<ServiceInterface> {
        return this.backendService.makeRequest('GET', `manager/service/${id}`).then((response: AxiosResponse) => {
            let data = response.data;
            return data.service as ServiceInterface
        });
    }

    /**
     * Создать новую услугу с ее тарифами
     */
    public create(service: ServiceInterface): Promise<ServiceResponseInterface> {
        return this.backendService.makeRequest('POST', 'manager/service', {
            service: service
        }).then((response: AxiosResponse) => {
            return response.data as ServiceResponseInterface;
        });
    }

    /**
     * Обновить услугу со всеми ее тарифами
     */
    public update(id: string, service: ServiceInterface): Promise<ServiceResponseInterface> {
        return this.backendService.makeRequest('POST', `manager/service/${id}`, {
            service: service
        }).then((response: AxiosResponse) => {
            return response.data as ServiceResponseInterface;
        });
    }

    /**
     * Удаление услуги
     */
    public remove(id: string): Promise<ServiceResponseInterface> {
        return this.backendService.makeRequest('DELETE', `manager/service/${id}`).then((response: AxiosResponse) => {
            return response.data as ServiceResponseInterface;
        })
    }
}

export const extendedServicesService = new ExtendedServicesService(backendService);
