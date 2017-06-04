import Vue from 'vue';
import Component from "vue-class-component";
import $ from "jquery";
import {UserInterface} from "../service/model/user.interface";
import {authUserService} from "../service/auth-user.service";
import {router} from "../router/router";
import {authUserStore} from "../store/auth-user.store";
import {Model} from "vue-property-decorator";
import {SideBarMenuItem, managerSideBarMenu, customerSideBarMenu} from "../sidebar-menu";
import {pageMetaStore} from "../router/page-meta-store";
import {ticketCategoriesStore} from "../store/ticket-categories.store";
import {TicketCategoryInterface} from "../service/model/ticket-category.interface";

Component.registerHooks([
    'beforeRouteLeave'
]);

/**
 * Главная страница личного кабинета
 */
@Component({
    template: require('./dashboard.html')
})
export class Dashboard extends Vue {
    /**
     * Состояние меню
     */
    @Model() menuToggled: boolean = true;

    /**
     * Все возможные пункты меню с разбивкой по ролям
     */
    @Model() sideBarMenu: Array<SideBarMenuItem> = [];

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

            this.sideBarMenu = userType == 'manager' ? managerSideBarMenu : customerSideBarMenu;

            // получить подразделы для тикетной системы
            ticketCategoriesStore
                .dispatch('fetchList')
                .then((categories: TicketCategoryInterface[]) => {
                    // поместить категории в главное меню с заявками
                    let routeName = userType == 'manager' ? 'manager_ticket_list' : 'customer_ticket_list';
                    for (let item of this.sideBarMenu) {
                        if (item.route.name == routeName) {
                            let key = this.sideBarMenu.indexOf(item);
                            this.sideBarMenu[key].children = [];
                            for (let category of categories) {
                                this.sideBarMenu[key].children.push({
                                    route: {
                                        name: routeName,
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
    }

    beforeRouteLeave(to, from, next): void {
        $(window).off('resize');
        $('body').removeClass('nav-md footer_fixed');
        next();
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
