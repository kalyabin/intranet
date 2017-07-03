import {BackendService, backendService} from "./backend.service";
import {RoomInterface} from "./model/room.interface";
import {AxiosResponse} from "axios";
import {RoomResponseInterface} from "./response/room-response.interface";
import {RoomRequestInterface} from "./model/room-request.interface";

/**
 * Сервис для управления помещениями для аренды
 */
export class RoomManagerService {
    constructor(
        protected backendService: BackendService
    ) { }

    /**
     * Получение списка помещений
     */
    list(): Promise<RoomInterface[]> {
        return this.backendService.makeRequest('GET', 'manager/room')
            .then((response: AxiosResponse) => {
                return response.data.list as RoomInterface[];
            });
    }

    /**
     * Создание помещения
     */
    create(room: RoomInterface): Promise<RoomResponseInterface> {
        return this.backendService.makeRequest('POST', 'manager/room', {
            'room': room
        }).then((response: AxiosResponse) => {
            return response.data as RoomResponseInterface;
        });
    }

    /**
     * Детальная информация о помещении с заявками
     */
    details(id: number): Promise<{room: RoomInterface, requests: RoomRequestInterface[]}> {
        return this.backendService.makeRequest('GET', `manager/room/${id}`).then((response: AxiosResponse) => {
            return response.data as {room: RoomInterface, requests: RoomRequestInterface[]};
        });
    }

    /**
     * Редактирование помещения
     */
    update(id: number, room: RoomInterface): Promise<RoomResponseInterface> {
        return this.backendService.makeRequest('POST', `manager/room/${id}`, {
            'room': room
        }).then((response: AxiosResponse) => {
            return response.data as RoomResponseInterface;
        });
    }

    /**
     * Удаление помещения
     */
    remove(id: number): Promise<RoomResponseInterface> {
        return this.backendService.makeRequest('DELETE', `manager/room/${id}`)
            .then((response: AxiosResponse) => {
                return response.data as RoomResponseInterface;
            });
    }
}

export const roomManagerService = new RoomManagerService(backendService);
