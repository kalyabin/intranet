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
    list(category: string = null, opened: boolean = true, pageNum: number = 0, pageSize: number = 150): Promise<ListInterface<TicketInterface>> {
        return this.backendService
            .makeRequest('GET', `ticket`, {
                category: category,
                opened: opened ? '1' : '0',
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
    createTicket(ticket: TicketRequestInterface): Promise<TicketResponseInterface> {
        return this.backendService
            .makeRequest('POST', `ticket`, {
                ticket: ticket
            })
            .then((response: AxiosResponse) => {
                return response.data as TicketResponseInterface;
            });
    }

    /**
     * Получить детальную информацию о тикете. Единый метод для менеджеров и арендаторов
     */
    ticketDetails(ticketId: number): Promise<TicketDetailsResponseInterface> {
        return this.backendService
            .makeRequest('GET', `ticket/${ticketId}`)
            .then((response: AxiosResponse) => {
                return response.data as TicketDetailsResponseInterface;
            });
    }

    /**
     * Создание сообщения в тикете. Единый метод для менеджеров и арендаторов.
     */
    createMessage(ticketId: number, message: TicketMessageRequestInterface): Promise<TicketMessageResponseInterface> {
        return this.backendService
            .makeRequest('POST', `ticket/${ticketId}/message`, {
                'ticket_message': message
            })
            .then((response: AxiosResponse) => {
                return response.data as TicketMessageResponseInterface;
            });
    }

    /**
     * Закрытие тикета. Метод единый для менеджеров и арендаторов.
     */
    closeTicket(ticketId: number): Promise<TicketResponseInterface> {
        return this.backendService
            .makeRequest('POST', `ticket/${ticketId}/close`)
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
    assign(ticketId: number, managerId?: number): Promise<TicketResponseInterface> {
        let request = {};
        if (managerId) {
            request['managerId'] = managerId;
        }
        return this.backendService
            .makeRequest('POST', `ticket/${ticketId}/assign`, request)
            .then((response: AxiosResponse) => {
                return response.data as TicketResponseInterface;
            });
    }

    /**
     * Получить категории доступные пользователю
     */
    categories(): Promise<TicketCategoryInterface[]> {
        return this.backendService
            .makeRequest('GET', 'ticket/categories')
            .then((response: AxiosResponse) => {
                return response.data['list'] as TicketCategoryInterface[];
            });
    }
}

export const ticketService = new TicketService(backendService);
