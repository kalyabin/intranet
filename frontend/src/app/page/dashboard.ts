import Vue from 'vue';
import Component from "vue-class-component";
import $ from "jquery";
import {UserInterface} from "../service/model/user.interface";
import {authUserService} from "../service/auth-user.service";
import {router} from "../router/router";
import {authUserStore} from "../store/auth-user.store";
import {Model} from "vue-property-decorator";
import {customerSidebarMenuItems} from "../menu/customer-sidebar-menu-items";
import {managerSidebarMenuItems} from "../menu/manager-sidebar-menu-items";
import {pageMetaStore} from "../router/page-meta-store";
import {ticketCategoriesStore} from "../store/ticket-categories.store";
import {TicketCategoryInterface} from "../service/model/ticket-category.interface";
import {notificationStore} from "../store/notification.store";
import {UserNotificationInterface} from "../service/model/user-notification.interface";
import {SidebarMenuItem} from "../menu/sidebar-menu-item.interface";
import {SideBarMenu} from "../components/menu/side-bar-menu";

Component.registerHooks([
    'beforeRouteLeave',
    'beforeRouteUpdate',
    'beforeRouteEnter',
]);

/**
 * Главная страница личного кабинета
 */
@Component({
    template: require('./dashboard.html'),
    components: {
        'side-bar-menu': SideBarMenu
    }
})
export class Dashboard extends Vue {
    /**
     * Состояние меню
     */
    @Model() menuToggled: boolean = true;

    /**
     * Все возможные пункты меню с разбивкой по ролям
     */
    @Model() sideBarMenu: Array<SidebarMenuItem> = [];

    protected fixContentHeight(): void {
        $(this.$refs['right-col']).css('min-height', Math.max($(this.$refs['left-col']).outerHeight(), $(window).height()));
    }

    /**
     * Заголовок текущей страницы
     */
    get pageTitle(): string {
        return pageMetaStore.state.pageTitle;
    }

    /**
     * Флаг авторизованности
     */
    get isAuth(): boolean {
        if (!authUserStore.state.isAuth) {
            router.push({name: 'login'});
        }
        return authUserStore.state.isAuth;
    }

    /**
     * Данные пользователя
     */
    get userData(): UserInterface {
        return authUserStore.state.userData;
    }

    mounted(): void {
        authUserStore.dispatch('fetchData').then(() => {
            let userType = authUserStore.state.userData.userType;

            this.sideBarMenu = userType == 'manager' ? managerSidebarMenuItems : customerSidebarMenuItems;

            // получить подразделы для тикетной системы
            ticketCategoriesStore
                .dispatch('fetchList')
                .then((categories: TicketCategoryInterface[]) => {
                    // поместить категории в главное меню с заявками
                    let routeName = userType == 'manager' ? 'manager_ticket_root' : 'cabinet_ticket_root';
                    for (let item of this.sideBarMenu) {
                        if (item.route.name == routeName) {
                            let key = this.sideBarMenu.indexOf(item);
                            this.sideBarMenu[key].children = [];
                            for (let category of categories) {
                                this.sideBarMenu[key].children.push({
                                    route: {
                                        name: userType == 'manager' ? 'manager_ticket_list' : 'cabinet_ticket_list',
                                        params: {
                                            category: category.id
                                        }
                                    },
                                    menuName: category.name
                                });
                            }
                            break;
                        }
                    }
                });
        });

        if (this.menuToggled) {
            $('body').addClass('nav-md');
        }
        $('body').addClass('footer_fixed');
        this.fixContentHeight();
        $(window).on('resize', () => this.fixContentHeight());

        notificationStore.dispatch('fetchAll');
    }

    beforeRouteLeave(to, from, next): void {
        $(window).off('resize');
        $('body').removeClass('nav-md footer_fixed');
        next();
    }

    /**
     * Получить количество непрочитанных уведомлений
     */
    get unreadNotifications(): number {
        return notificationStore.state.unread;
    }

    /**
     * Получить список уведомлений для отображения в шапке
     */
    get notifications(): UserNotificationInterface[] {
        return notificationStore.state.userNotifications;
    }

    /**
     * Пометить все уведомления как прочитанные
     */
    readAllNotifications(): void {
        notificationStore.dispatch('readAll');
    }

    /**
     * Скрыть / раскрыть левое меню
     */
    toggleMenu(event): void {
        event.preventDefault();

        this.menuToggled = !this.menuToggled;

        $('body').toggleClass('nav-md nav-sm');

        this.fixContentHeight();
    }

    /**
     * Логаут
     */
    logout(event): void {
        event.preventDefault();
        authUserService.logout().then(() => router.push({name: 'login'}));
    }
}
