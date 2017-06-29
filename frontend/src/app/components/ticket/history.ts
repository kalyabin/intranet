import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import {TicketInterface} from "../../service/model/ticket.interface";
import {TicketHistoryInterface} from "../../service/model/ticket-history.interface";
import {TicketMessageInterface} from "../../service/model/ticket-message.interface";
import {TicketDetailsResponseInterface} from "../../service/response/ticket-details-response.interface";
import * as moment from "moment";

/**
 * Вывод истории (лога) по заявке
 */
@Component({
    template: require('./history.html')
})
export class TicketHistory extends Vue {
    /**
     * Тикет, по которому вывести истори
     */
    @Prop(Object) ticket: TicketInterface;

    /**
     * Входящий ответ от сервера
     */
    @Prop(Object) details: TicketDetailsResponseInterface;

    /**
     * Отсортированная история для вывода
     */
    @Model() history: Array<{
        createdAt: string,
        type: 'message' | 'status',
        item: TicketHistoryInterface | TicketMessageInterface
    }> = [];

    /**
     * При изменении детальной информации формировать массив истории
     */
    @Watch('details', {
        immediate: true
    })
    onChangeHistory(details: TicketDetailsResponseInterface): void {
        this.fetchHistory(details);
    }

    /**
     * Обработка детальной информации по тикету от сервера и сортировка результатов
     */
    fetchHistory(details: TicketDetailsResponseInterface): void {
        this.history = [];

        // формирование истории
        for (let item of details.messages) {
            this.history.push({
                createdAt: item.createdAt,
                type: 'message',
                item: item
            });
        }
        for (let item of details.history) {
            this.history.push({
                createdAt: item.createdAt,
                type: 'status',
                item: item
            });
        }

        // сортировка истории по дате и типу
        this.history = this.history.sort((itemA: {
            createdAt: string,
            type: 'message' | 'status',
            item: TicketHistoryInterface | TicketMessageInterface
        }, itemB: {
            createdAt: string,
            type: 'message' | 'status',
            item: TicketHistoryInterface | TicketMessageInterface
        }) => {
            let dateA = moment(itemA.createdAt);
            let dateB = moment(itemB.createdAt);

            // статус заявки "Новая" всегда должна идти первой
            if (itemB.type == 'status' && itemB.item['status'] == 'new') {
                return 1;
            } else if (itemA.type == 'status' && itemA.item['status'] == 'new') {
                return -1;
            }

            if (dateB.isBefore(dateA)) {
                // А младше B
                return 1;
            } else if (dateB.isAfter(dateA)) {
                // B младше A
                return -1;
            } else if (dateB.isSame(dateA) && itemA.type == 'message') {
                // сообщения идут после статуса
                return 1;
            } else if (dateB.isSame(dateA) && itemA.type == 'status') {
                // статусы идут перед сообщений
                return -1;
            } else {
                return 0;
            }
        });
    }
}
