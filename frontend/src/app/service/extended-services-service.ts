import {BackendService, backendService} from "./backend.service";
import {ListInterface} from "./response/list.interface";
import {ServiceInterface} from "./model/service.interface";
import {AxiosResponse} from "axios";
import {ServiceResponseInterface} from "./response/service-response.interface";
import {ServiceActivatedInterface} from "./model/service-activated.interface";
import {ServiceActivationResponseInterface} from "./response/service-activation-response.interface";
import {ServiceTariffInterface} from "./model/service-tariff.interface";

/**
 * Сервис для работы с дополнительными услугами
 */
export class ExtendedServicesService {
    constructor(
        private backendService: BackendService
    ) { }

    /**
     * Получить список для арендатора.
     * Возвращается только список актуальных (доступных для активации) услуг.
     */
    public actualList(): Promise<ListInterface<ServiceInterface>> {
        return this.backendService.makeRequest('GET', 'customer/service').then((response: AxiosResponse) => {
            return response.data as ListInterface<ServiceInterface>;
        });
    }

    /**
     * Получить список активированных услуг для арендатора
     */
    public activatedList(): Promise<ListInterface<ServiceActivatedInterface>> {
        return this.backendService.makeRequest('GET', 'customer/service/activated').then((response: AxiosResponse) => {
            return response.data as ListInterface<ServiceActivatedInterface>;
        });
    }

    /**
     * Активация услуги у арендатора.
     *
     * Тариф необходимо передавать, если у услуги есть тарифы.
     */
    public activate(service: ServiceInterface, tariff?: ServiceTariffInterface): Promise<ServiceActivationResponseInterface> {
        return this.backendService.makeRequest('POST', `customer/service/${service.id}/activate`, tariff ? {
            tariff: tariff.id
        } : null).then((response: AxiosResponse) => {
            return response.data as ServiceActivationResponseInterface;
        });
    }

    /**
     * Деактивация услуги у арендатора.
     */
    public deactivate(service: ServiceInterface): Promise<ServiceActivationResponseInterface> {
        return this.backendService.makeRequest('POST', `customer/service/${service.id}/deactivate`).then((response: AxiosResponse) => {
            return response.data as ServiceActivationResponseInterface;
        });
    }

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
