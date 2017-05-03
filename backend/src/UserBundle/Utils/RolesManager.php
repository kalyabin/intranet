<?php

namespace UserBundle\Utils;


use UserBundle\Entity\UserEntity;

/**
 * Управление ролями
 *
 * @package UserBundle\Utils
 */
class RolesManager
{
    /**
     * Получить доступные роли для каждого типа пользователя
     *
     * @return array
     */
    public function getRolesByUserType(): array
    {
        return [
            UserEntity::TYPE_CUSTOMER => [
                'CUSTOMER_ADMIN',
                'USER_CUSTOMER',
                'FINANCE_CUSTOMER',
                'MAINTAINCE_CUSTOMER',
                'DOCUMENT_CUSTOMER',
                'RENT_CUSTOMER',
                'STORAGE_CUSTOMER',
            ],
            UserEntity::TYPE_MANAGER => [
                'SUPERADMIN',
                'USER_MANAGEMENT',
                'RENT_MANAGEMENT',
                'STORAGE_MANAGEMENT',
                'DOCUMENT_MANAGEMENT',
                'IT_MANAGEMENT',
                'FINANCE_MANAGEMENT',
                'MAINTAINCE_MANAGEMENT',
                'BOOKER_MANAGEMENT',
            ],
        ];
    }

    /**
     * Получить словесное описание каждой роли
     *
     * @return array
     */
    public function getRolesLables(): array
    {
        return [
            'SUPERADMIN' => 'Супер администратор (доступны все права сотрудников)',
            'CUSTOMER_ADMIN' => 'Администратор арендатора (доступны все права арендатора)',

            'USER_CUSTOMER' => 'Создание пользователей арендатора',
            'FINANCE_CUSTOMER' => 'Отправка заявок "Финансовые вопросы"',
            'MAINTAINCE_CUSTOMER' => 'Отправка заявок "Служба эксплуатации"',
            'DOCUMENT_CUSTOMER' => 'Просмотр документов арендатора',
            'RENT_CUSTOMER' => 'Отправка заявок в службу аренды',
            'STORAGE_CUSTOMER' => 'Заказ товаров на складе',

            'USER_MANAGEMENT' => 'Управление всеми пользователями',
            'RENT_MANAGEMENT' => 'Сотрудник службы аренды',
            'STORAGE_MANAGEMENT' => 'Сотрудник склада',
            'DOCUMENT_MANAGEMENT' => 'Менеджер документов',
            'IT_MANAGEMENT' => 'Сотрудник IT-отдела',
            'FINANCE_MANAGEMENT' => 'Сотрудник финансовой службы',
            'MAINTAINCE_MANAGEMENT' => 'Сотрудник службы эксплуатации',
            'BOOKER_MANAGEMENT' => 'Сотрудник SMART-бухгалтер',
        ];
    }
}
