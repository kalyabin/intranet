import Vue from "vue";
import Component from "vue-class-component";
import {FlashNotificationInterface, notificationStore} from "../../store/notification.store";

/**
 * Вывод flash-уведомлений
 */
@Component({
    template: require('./flash-notifications.html')
})
export class FlashNotifications extends Vue {
    /**
     * Список всплывающих уведомлений для всех пользователей
     */
    get list(): FlashNotificationInterface[] {
        return notificationStore.state.flashNotifications;
    }

    /**
     * Удалить всплывающее уведомление
     */
    removeNotification(flash: FlashNotificationInterface): void {
        notificationStore.commit('removeFlash', flash);
    }
}
