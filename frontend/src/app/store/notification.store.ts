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

    /**
     * Всплывающие уведомления
     */
    flashNotifications: FlashNotificationInterface[];
}

/**
 * Тип всплывающего уведомления
 */
export type FlashNotificationType = 'default' | 'danger' | 'success' | 'info' | 'warning';

/**
 * Модель всплывающего уведомления
 */
export interface FlashNotificationInterface {
    /**
     * Автоматически скрывать уведомление. По умолучанию - всегда скрывать.
     */
    autoHide?: boolean;

    /**
     * Привязка к персонализированному ведомлению
     */
    userNotify?: UserNotificationInterface;

    /**
     * Заголовок уведомления
     */
    title?: string;

    /**
     * Текст уведомления, если нет привязки к персонализированному уведомлению
     */
    text?: string;

    /**
     * Тип всплывающего уведомления
     */
    type?: FlashNotificationType;
}

export let notificationStore = new Vuex.Store<NotificationStateInterface>({
    state: <NotificationStateInterface>{
        userNotifications: [],
        unread: 0,
        flashNotifications: []
    },
    mutations: {
        /**
         * Добавить всплывающее уведомление в стек
         */
        pushFlash: (state: NotificationStateInterface, flash: FlashNotificationInterface) => {
            state.flashNotifications.push(flash);
        },
        /**
         * Удалить всплывающее уведомление из стека
         */
        removeFlash: (state: NotificationStateInterface, flash: FlashNotificationInterface) => {
            let index = state.flashNotifications.indexOf(flash);
            if (index !== -1) {
                state.flashNotifications.splice(index, 1);
            }
        },
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
                            action.dispatch('flash', {
                                userNotify: item,
                                type: 'info'
                            });
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
        },
        /**
         * Поместить сплывающее уведомление
         */
        flash: (action, flash: FlashNotificationInterface) => {
            // если не чего показывать
            if (!flash.userNotify && !flash.title && !flash.text) {
                return;
            }

            if (!flash.type) {
                flash.type = 'default';
            }

            action.commit('pushFlash', flash);

            // если не установлен флаг автоматического удаления уведомления - всегда скрывать
            if (typeof flash.autoHide == 'undefined' || flash.autoHide) {
                setTimeout(() => {
                    action.commit('removeFlash', flash);
                }, 5000);
            }
        }
    }
});
