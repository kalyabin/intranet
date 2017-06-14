import Vue from "vue";
import Component from "vue-class-component";
import {Prop} from "vue-property-decorator";
import {UserNotificationInterface} from "../../service/model/user-notification.interface";
import {TicketInterface} from "../../service/model/ticket.interface";
import {Location} from "vue-router";
import {authUserStore} from "../../store/auth-user.store";

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
