import Vue from "vue";
import Component from "vue-class-component";
import {UserNotificationInterface} from "../../service/model/user-notification.interface";
import {managerIncomingCallStore} from "../../store/manager-incoming-call.store";
import {Model, Watch} from "vue-property-decorator";
import {ModalWindow} from "../modal-window";
import {CustomerInterface} from "../../service/model/customer.interface";
import {customerListStore} from "../../store/customer-list.store";
import {incomingCallService} from "../../service/incoming-call.service";
import {IncomingCallResendResponseInterface} from "../../service/response/incoming-call-resend-response.interface";
import {notificationStore} from "../../store/notification.store";

/**
 * Модальное окно с информацией о входящем звонке для менеджера
 */
@Component({
    template: require('./manager-incoming-call-window.html')
})
export class ManagerIncomingCallWindow extends Vue {
    @Model() viewWindow: boolean = false;

    /**
     * Ожидание субмита
     */
    @Model() awaitOfSubmit: boolean = false;

    /**
     * Текст ошибки
     */
    @Model() errorMessage: string = '';

    /**
     * Идентификатор выбранного контрагента
     */
    @Model() customer: number = null;

    /**
     * Комментарий
     */
    @Model() comment: string = null;

    mounted(): void {
        customerListStore.dispatch('fetchList');
    }

    updated(): void {
        customerListStore.dispatch('fetchList');
    }

    /**
     * Уведомление о входящем звонке для менеджера
     */
    get incomingCall(): UserNotificationInterface {
        return managerIncomingCallStore.state.notification;
    }

    get customers(): CustomerInterface[] {
        return customerListStore.state.list;
    }

    /**
     * Показать модальное окно
     */
    @Watch('incomingCall')
    onShowIncomingCall(): void {
        if (this.incomingCall && !this.viewWindow) {
            let window: ModalWindow = <ModalWindow>this.$refs['window'];
            window.show();
            this.viewWindow = true;
        }
    }

    /**
     * Скрыть модальное окно и удалить звонок из стека
     */
    onHideModal(): void {
        this.viewWindow = false;
        // удалить уведомление из стека
        managerIncomingCallStore.commit('removeIncomingCall');
    }

    /**
     * Скрыть модальное окно
     */
    hideModal(): void {
        let window: ModalWindow = <ModalWindow>this.$refs['window'];
        window.hide();
    }

    /**
     * Субмит формы
     */
    submit(): void {
        this.$validator.validateAll().then(() => {
            this.awaitOfSubmit = true;
            this.errorMessage = '';

            incomingCallService.resend(this.incomingCall.callerId, this.customer, this.comment).then((response: IncomingCallResendResponseInterface) => {
                if (response.success) {
                    notificationStore.dispatch('flash', {
                        type: 'success',
                        text: 'Звонок успешно отправлен арендатору'
                    });
                    this.awaitOfSubmit = false;
                    this.comment = null;
                    this.customer = null;
                    this.hideModal();
                } else {
                    this.awaitOfSubmit = false;
                    this.errorMessage = response.firstError;
                }
            });
        }).catch(() => {});
    }
}
