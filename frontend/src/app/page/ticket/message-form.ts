import * as Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import {TicketInterface} from "../../service/model/ticket.interface";
import {pageMetaStore} from "../../router/page-meta-store";
import {ticketService} from "../../service/ticket.service";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {TicketMessageResponseInterface} from "../../service/response/ticket-message-response.interface";
import {TicketResponseInterface} from "../../service/response/ticket-response.interface";
import {notificationStore} from "../../store/notification.store";

/**
 * Форма создания сообщения по тикету
 */
@Component({
    template: require('./message-form.html')
})
export class TicketMessageForm extends Vue {
    /**
     * Текст сообщения
     */
    @Model() text: string = '';

    /**
     * Флаг закрытия заявки
     */
    @Model() closeTicket: boolean = false;

    /**
     * Текст ошибки
     */
    @Model() errorMessage: string = '';

    /**
     * Защита от двойного субмита
     */
    awaitForSubmit: boolean = false;

    /**
     * Тикет, по которому необходимо создать сообщение
     */
    @Prop() ticket: TicketInterface;

    /**
     * Категория тикета
     */
    @Prop() category: TicketCategoryInterface;

    /**
     * Субмит сообщения
     */
    submit(): void {
        if (this.awaitForSubmit) {
            return;
        }

        this.errorMessage = '';

        this.$validator.validateAll().then(() => {
            pageMetaStore.commit('showPageLoader');
            this.awaitForSubmit = true;
            ticketService.createMessage(this.ticket.id, {
                text: this.text,
                closeTicket: this.closeTicket
            }).then((response: TicketMessageResponseInterface) => {
                pageMetaStore.commit('hidePageLoader');
                this.awaitForSubmit = false;
                if (response.success) {
                    // очистить текст сообщения
                    this.errorMessage = '';
                    this.text = '';
                    notificationStore.dispatch('flash', {
                        type: 'success',
                        text: 'Сообщение было отправлено'
                    });
                    this.$emit('ticket-changed', response);
                } else {
                    this.errorMessage = response.firstError;
                }
            }, () => {
                pageMetaStore.commit('hidePageLoader');
                this.awaitForSubmit = false;
            });
        }, () => {});
    }

    /**
     * Закрыть заявку
     */
    close(): void {
        if (this.awaitForSubmit) {
            return;
        }

        this.awaitForSubmit = true;
        this.errorMessage = '';

        pageMetaStore.commit('showPageLoader');

        ticketService.closeTicket(this.ticket.id)
            .then((response: TicketResponseInterface) => {
                pageMetaStore.commit('hidePageLoader');
                this.awaitForSubmit = false;
                if (!response.success) {
                    this.errorMessage = 'Не удалось закрыть заявку';
                } else {
                    this.text = '';
                }
                this.$emit('ticket-changed', response);
            }, () => {
                pageMetaStore.commit('hidePageLoader');
                this.awaitForSubmit = false;
            });
    }
}
