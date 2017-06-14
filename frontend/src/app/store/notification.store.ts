/// <reference path="../../../node_modules/@types/jquery.pnotify/index.d.ts" />

import Vue from "vue";
import Vuex from "vuex";
import {UserNotificationInterface} from "../service/model/user-notification.interface";
import {authUserService} from "../service/auth-user.service";

Vue.use(Vuex);

/**
 * Хранилище для системных уведомлений
 */
export interface NotificationStateInterface {
    /**
     * Персонализированные уведомления для пользователя из БД
     */
    userNotifications: UserNotificationInterface[];

    /**
     * Количество непрочитанных уведомлений
     */
    unread: number;
}

export let notificationStore = new Vuex.Store<NotificationStateInterface>({
    state: <NotificationStateInterface>{
        userNotifications: [],
        unread: 0,
    },
    mutations: {
        /**
         * Очистить все пользовательские уведомления
         */
        clearUserNotifications: (state: NotificationStateInterface) => {
            state.userNotifications = [];
            state.unread = 0;
        },
        /**
         * Добавить пользовательское уведомление
         */
        addNotification: (state: NotificationStateInterface, notification: UserNotificationInterface) => {
            state.userNotifications.push(notification);
            if (!notification.isRead) {
                state.unread++;
            }
        },
        /**
         * Добавить пачку пользовательских уведомлений
         */
        addNotifications: (state: NotificationStateInterface, notifications: UserNotificationInterface[]) => {
            state.userNotifications = state.userNotifications.concat(notifications);
            for (let item of notifications) {
                if (!item.isRead) {
                    state.unread++;
                }
            }
        },
        /**
         * Пометить все уведомления как прочитанные
         */
        markAllAsRead: (state: NotificationStateInterface) => {
            for (let item of state.userNotifications) {
                item.isRead = true;
            }
            state.unread = 0;
        },
    },
    actions: {
        /**
         * Создать мерцающее системное уведомление
         */
        systemMessage: (action, notifyOptions: PNotifyOptions) => {
            if (Object.keys(notifyOptions).indexOf('hide') == -1) {
                notifyOptions.hide = true;
            }
            notifyOptions.styling = 'bootstrap3';
            notifyOptions.buttons = {
                closer: true,
                closer_hover: true,
                sticker: false,
                sticker_hover: false
            };
            new PNotify(notifyOptions);
        },
        /**
         * Получить все персонализированные уведомления с бекенда
         */
        fetchAll: (action) => {
            return new Promise<UserNotificationInterface[]>((resolve) => {
                action.commit('clearUserNotifications');

                authUserService.notifications().then((list: UserNotificationInterface[]) => {
                    action.commit('addNotifications', list);
                    resolve(list);
                }, () => {
                    resolve([]);
                });
            });
        },
        /**
         * Получить только новые персонализированные уведомления с бекенда
         */
        fetchNew: (action) => {
            return new Promise<UserNotificationInterface[]>((resolve) => {
                let newList: UserNotificationInterface[] = [];
                let existsIds: number[] = [];

                for (let item of action.state.userNotifications) {
                    existsIds.push(item.id);
                }

                authUserService.notifications().then((list: UserNotificationInterface[]) => {
                    for (let item of list) {
                        if (existsIds.indexOf(item.id) == -1) {
                            newList.push(item);
                            action.commit('addNotification', item);
                        }
                    }
                    resolve(newList);
                }, () => {
                    resolve(newList);
                });
            });
        },
        /**
         * Пометить все уведомления как прочитанные
         */
        readAll: (action) => {
            return new Promise<boolean>((resolve) => {
                if (action.state.unread > 0) {
                    action.commit('markAllAsRead');
                    authUserService.readAllNotifications().then(() => {
                        resolve(true);
                    }, () => {
                        resolve(false);
                    });
                } else {
                    resolve(true);
                }
            });
        }
    }
});
