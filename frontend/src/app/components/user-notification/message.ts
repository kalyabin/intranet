import Vue from "vue";
import Component from "vue-class-component";
import {Prop} from "vue-property-decorator";
import {UserNotificationInterface} from "../../service/model/user-notification.interface";
import {TicketInterface} from "../../service/model/ticket.interface";
import {authUserStore} from "../../store/auth-user.store";
import {UserType} from "../../service/model/user.interface";
import * as moment from "moment";

/**
 * Форматирование сообщения польовательского уведомления
 */
@Component({
    template: require('./message.html')
})
export class UserNotificationMessage extends Vue {
    /**
     * Модель уведомления, которое необходимо отобразить
     */
    @Prop() notification: UserNotificationInterface;

    /**
     * Получить тип пользователя
     */
    get userType(): UserType {
        return authUserStore.state.userData.userType;
    }

    /**
     * Получить отформатированную дату
     */
    formatDate(date: string, format: string = 'DD.MM.YYYY HH:mm'): string {
        return moment(date).format(format);
    }

    /**
     * Получить ссылку на тикет
     */
    getTicketRoute(ticket: TicketInterface): {[key: string]: any} {
        if (authUserStore.state.userData.userType == 'customer') {
            return {
                name: 'cabinet_ticket_details',
                params: {
                    category: ticket.category,
                    ticket: ticket.id
                }
            };
        } else {
            return {
                name: 'manager_ticket_details',
                params: {
                    category: ticket.category,
                    ticket: ticket.id
                }
            };
        }
    }
}
