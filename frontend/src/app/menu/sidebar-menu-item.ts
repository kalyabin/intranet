/**
 * Элемент меню для левой колонки
 */
export interface SidebarMenuItem {
    route: any;
    faIcon?: string;
    menuName: string;
    role?: string;
    children?: SidebarMenuItem[];
}
