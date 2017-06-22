import {BackendService, backendService} from "./backend.service";
import {CustomerInterface} from "./model/customer.interface";
import {AxiosResponse} from "axios";
import {CustomerResponseInterface} from "./response/customer-response.interface";
import {ListInterface} from "./response/list.interface";

/**
 * Сервис для менеджера арендаторов
 */
export class CustomerManager {
    constructor(
        protected backendService: BackendService
    ) { }

    /**
     * Список контрагентов с постраничной навигацией
     */
    list(pageNum: number = 0, pageSize: number = 150): Promise<ListInterface<CustomerInterface>> {
        return this.backendService
            .makeRequest('GET', 'manager/customer', {
                pageNum: pageNum,
                pageSize: pageSize
            })
            .then((response: AxiosResponse) => {
                return response.data as ListInterface<CustomerInterface>;
            });
    }

    /**
     * Редактирование контрагента
     */
    update(id: number, customer: CustomerInterface): Promise<CustomerResponseInterface> {
        return this.backendService
            .makeRequest('POST', `manager/customer/${id}`, {
                customer: {
                    name: customer.name,
                    currentAgreement: customer.currentAgreement
                }
            })
            .then((response: AxiosResponse) => {
                let data: CustomerResponseInterface = <CustomerResponseInterface>response.data;
                return data;
            });
    }

    /**
     * Создание контрагента
     */
    create(customer: CustomerInterface): Promise<CustomerResponseInterface> {
        return this.backendService
            .makeRequest('POST', 'manager/customer', {
                customer: customer
            })
            .then((response: AxiosResponse) => {
                let data: CustomerResponseInterface = <CustomerResponseInterface>response.data;
                return data;
            });
    }

    /**
     * Удаление контрагента
     */
    remove(id: number): Promise<CustomerResponseInterface> {
        return this.backendService
            .makeRequest('DELETE', `manager/customer/${id}`)
            .then((response: AxiosResponse) => {
                let data: CustomerResponseInterface = <CustomerResponseInterface>response.data;
                return data;
            });
    }
}

export const customerManagerService = new CustomerManager(backendService);
