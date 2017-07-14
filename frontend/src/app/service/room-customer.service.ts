import {BackendService, backendService} from "./backend.service";
import {ListInterface} from "./response/list.interface";
import {RoomInterface} from "./model/room.interface";
import {AxiosResponse} from "axios";
import {RoomRequestInterface} from "./model/room-request.interface";
import {CustomerRoomDetailsInterface} from "./model/customer-room-details.interface";
import {UpdateRoomRequestInterface} from "./request/create-room-request.interface";
import {RoomRequestResponseInterface} from "./response/room-request-response.interface";

/**
 * Менеджер переговорных комнат для арендатора
 */
export class RoomCustomerService {
    constructor(
        protected backendService: BackendService
    ) {}

    /**
     * Получить список переговорок
     */
    public list(): Promise<RoomInterface[]> {
        return this.backendService.makeRequest('GET', 'customer/room')
            .then((response: AxiosResponse) => {
                return response.data['list'] as RoomInterface[];
            });
    }

    /**
     * Детальная информация о переговорке
     */
    public details(id: number): Promise<CustomerRoomDetailsInterface> {
        return this.backendService.makeRequest('GET', `customer/room/${id}`)
            .then((response: AxiosResponse) => {
                return response.data as CustomerRoomDetailsInterface;
            });
    }

    /**
     * Актуальные заявки арендатора для всех комнат
     */
    public actualRequests(): Promise<RoomRequestInterface[]> {
        return this.backendService.makeRequest('GET', 'customer/room/request')
            .then((response: AxiosResponse) => {
                return response.data['list'] as RoomRequestInterface[];
            });
    }

    /**
     * Создание новой заявки
     */
    public createRequest(request: UpdateRoomRequestInterface): Promise<RoomRequestResponseInterface> {
        return this.backendService.makeRequest('POST', 'customer/room/request', {
            room_request: request
        }).then((response: AxiosResponse) => {
            return response.data as RoomRequestResponseInterface;
        });
    }

    /**
     * Отмена заявки
     */
    public cancelRequest(id: number): Promise<RoomRequestResponseInterface> {
        return this.backendService.makeRequest('DELETE', `customer/room/request/${id}`)
            .then((response: AxiosResponse) => {
                return response.data as RoomRequestResponseInterface;
            });
    }
}

export const roomCustomerService = new RoomCustomerService(backendService);
