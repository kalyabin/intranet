import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import {TicketCategoryInterface} from "../../service/model/ticket-category.interface";
import {TicketRequestInterface} from "../../service/request/ticket-request.interface";
import {pageMetaStore} from "../../router/page-meta-store";
import {ticketService} from "../../service/ticket.service";
import {TicketResponseInterface} from "../../service/response/ticket-response.interface";
import {notificationStore} from "../../store/notification.store";

/**
 * Форма создания тикета
 */
@Component({
    template: require('./form.html')
})
export class TicketForm extends Vue {
    /**
     * Категория, в которой создать тикет
     */
    @Prop(Object) category: TicketCategoryInterface;

    /**
     * Модель заполнеяемого тикета
     */
    @Model() ticket: TicketRequestInterface = {
        category: this.category ? this.category.id : '',
        title: '',
        text: ''
    };

    /**
     * Текст сообщения из бекенда
     */
    @Model() errorMessage: string = '';

    /**
     * Ожидание окончания субмита
     */
    @Model() awaitOfSubmit: boolean = false;

    @Watch('category', {
        immediate: true
    })
    onChangeCategory(category: TicketCategoryInterface): void {
        this.ticket.category = category ? category.id : '';
    }

    /**
     * Нажатие на кнопку отмены
     */
    cancel(): void {
        this.$emit('cancel');
    }

    /**
     * Субмит формы
     */
    submit(): void {
        this.$validator.validateAll().then(() => {
            // защита от двойного вскликивания
            if (this.awaitOfSubmit) {
                return;
            }
            this.awaitOfSubmit = true;

            pageMetaStore.commit('showPageLoader');
            ticketService.createTicket(this.ticket)
                .then((response: TicketResponseInterface) => {
                    if (response.success) {
                        notificationStore.dispatch('flash', {
                            type: 'success',
                            text: `Заявка №${response.ticket.number} зарегистрирована в системе`
                        });
                        this.$emit('saved', response.ticket);
                    } else {
                        this.errorMessage = response.firstError;
                    }
                    this.awaitOfSubmit = false;
                    pageMetaStore.commit('hidePageLoader');
                }, () => {
                    this.awaitOfSubmit = false;
                    pageMetaStore.commit('hidePageLoader');
                });
        }, () => {});
    }
}
