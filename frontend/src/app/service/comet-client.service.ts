import {environment} from "../../environment";
import * as io from "socket.io-client";
import {authUserStore} from "../store/auth-user.store";
import {authUserService} from "./auth-user.service";
import {notificationStore} from "../store/notification.store";

/**
 * Клиент для подключения к comet-серверу
 */
export class CometClientService {
    /**
     * Сокет
     */
    private socket: SocketIOClient.Socket;

    /**
     * Зарегистрированные обработчики
     */
    private registeredEvents: string[] = [];

    constructor(
        private url: string
    ) { }

    /**
     * Подключение. Если уже подключен - ничего не делает
     */
    connect(): void {
        if (!this.socket) {
            this.socket = io(this.url);
            if (authUserStore.state.isAuth) {
                this.joinUser(authUserStore.state.userData.id);
            }
        }
    }

    /**
     * Регистрирует комнату пользователя
     */
    joinUser(userId: number): void {
        this.socket.emit('join_user', userId);
    }

    /**
     * Зарегистрировать обработчик события
     */
    registerEvent(eventName: string, fn: Function): void {
        this.socket.on(eventName, fn);
        if (this.registeredEvents.indexOf(eventName) === -1) {
            this.registeredEvents.push(eventName);
        }
    }

    /**
     * При появлении новых персонализированных уведомлений - запрашивает их на бекенде
     */
    registerFetchNewNotifications(): void {
        this.registerEvent('fetchNewNotifications', (userId: number) => {
            if (authUserStore.state.isAuth && authUserStore.state.userData.id == userId) {
                notificationStore.dispatch('fetchNew');
            }
        });
    }

    /**
     * Отключение. Если уже отключен - ничего не делает
     */
    disconnect(): void {
        if (this.socket) {
            // отключить все обработчики
            for (let eventName of this.registeredEvents) {
                this.socket.off(eventName);
            }
            this.registeredEvents = [];
            this.socket.close();
            this.socket = null;
        }
    }

    /**
     * ПРоверка подключения
     */
    isConnected(): boolean {
        return !!(this.socket && this.socket.connected);
    }
}

export const cometClientService = new CometClientService(environment.comet.url);


