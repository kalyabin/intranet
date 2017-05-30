import {BackendService, backendService} from "./backend.service";
import {TicketInterface} from "./model/ticket.interface";
import {AxiosResponse} from "axios";
import {TicketRequestInterface} from "./request/ticket-request.interface";
import {TicketResponseInterface} from "./response/ticket-response.interface";
import {TicketDetailsResponseInterface} from "./response/ticket-details-response.interface";
import {TicketMessageRequestInterface} from "./request/ticket-message-request.interface";
import {TicketMessageResponseInterface} from "./response/ticket-message-response.interface";
import {UserInterface} from "./model/user.interface";
import {ListInterface} from "./response/list.interface";
import {TicketCategoryInterface} from "./model/ticket-category.interface";

/**
 * Сервис для работы с тикетной системой
 */
export class TicketService {
    constructor(
        protected backendService: BackendService
    ) { }

    /**
     * Список тикетов. Единый метод для менеджеров и арендаторов
     */
    list(category: string, pageNum: number = 0, pageSize: number = 150): Promise<ListInterface<TicketInterface>> {
        return this.backendService
            .makeRequest('GET', `ticket/${category}`, {
                pageNum: pageNum,
                pageSize: pageSize
            })
            .then((response: AxiosResponse) => {
                return response.data as ListInterface<TicketInterface>;
            });
    }

    /**
     * Создание тикета. Метод может выполнять только арендатор
     */
    createTicket(category: string, ticket: TicketRequestInterface): Promise<TicketResponseInterface> {
        return this.backendService
            .makeRequest('POST', `ticket/${category}`, {
                ticket: ticket
            })
            .then((response: AxiosResponse) => {
                return response.data as TicketResponseInterface;
            });
    }

    /**
     * Получить детальную информацию о тикете. Единый метод для менеджеров и арендаторов
     */
    ticketDetails(category: string, ticketId: number): Promise<TicketDetailsResponseInterface> {
        return this.backendService
            .makeRequest('GET', `ticket/${category}/${ticketId}`)
            .then((response: AxiosResponse) => {
                return response.data as TicketDetailsResponseInterface;
            });
    }

    /**
     * Создание сообщения в тикете. Единый метод для менеджеров и арендаторов.
     */
    createMessage(category: string, ticketId: number, message: TicketMessageRequestInterface): Promise<TicketMessageResponseInterface> {
        return this.backendService
            .makeRequest('POST', `ticket/${category}/${ticketId}/message`, {
                'ticket-message': message
            })
            .then((response: AxiosResponse) => {
                return response.data as TicketMessageResponseInterface;
            });
    }

    /**
     * Закрытие тикета. Метод единый для менеджеров и арендаторов.
     */
    closeTicket(category: string, ticketId: number): Promise<TicketResponseInterface> {
        return this.backendService
            .makeRequest('POST', `ticket/${category}/${ticketId}/close`)
            .then((response: AxiosResponse) => {
                return response.data as TicketResponseInterface;
            });
    }

    /**
     * Запрос на список менеджеров работающих с очередью. Доступен только для администраторов тикетной системы.
     */
    managers(category: string): Promise<{list: UserInterface[]}> {
        return this.backendService
            .makeRequest('GET', `ticket/${category}/managers`)
            .then((response: AxiosResponse) => {
                return response.data as {list: UserInterface[]};
            });
    }

    /**
     * Назначить ответственного менеджера.
     *
     * По умолчанию назначается текущий пользователь.
     * Если передан managerId, то назначается менеджер с указанным идентификатором.
     * Опцию managerId может передавать только администратор тикетной системы.
     */
    assign(category: string, ticketId: number, managerId?: number): Promise<TicketResponseInterface> {
        let request = {};
        if (managerId) {
            request['managerId'] = managerId;
        }
        return this.backendService
            .makeRequest('POST', `ticket/${category}/${ticketId}/assign`, request)
            .then((response: AxiosResponse) => {
                return response.data as TicketResponseInterface;
            });
    }

    /**
     * Получить категории доступные пользователю
     */
    categories(): Promise<TicketCategoryInterface[]> {
        return this.backendService
            .makeRequest('GET', 'ticket')
            .then((response: AxiosResponse) => {
                return response.data['list'] as TicketCategoryInterface[];
            });
    }
}

export const ticketService = new TicketService(backendService);
