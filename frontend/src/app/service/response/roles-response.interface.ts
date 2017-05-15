/**
 * Ответ на запрос ролей
 */
export interface RolesResponseInterface {
    /**
     * Подписи ролей (проиндексированные по коду ролей)
     */
    labels: {[key: string]: string}

    /**
     * Иерархия ролей: код родительской роли - массив кодов дочерних ролей
     */
    hierarchy: {[key: string]: string[]};

    /**
     * Роли, проиндексированные по типу пользователя
     */
    roles: {[key: string]: string[]};
}
