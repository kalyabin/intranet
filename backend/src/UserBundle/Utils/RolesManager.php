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
     * @var array Вложенность ролей из модуля security
     */
    protected $rolesHierarchy;

    /**
     * RolesManager constructor.
     *
     * @param array $rolesHierarchy Иерархия ролей из модуля security
     */
    public function __construct(array $rolesHierarchy = [])
    {
        $this->rolesHierarchy = $rolesHierarchy;
    }

    /**
     * Получить взаимосвязанные (наследованные) роли.
     *
     * Если в массиве указана дочерняя роль, то в массиве возвращается она и ее родитель, если есть.
     *
     * @param string|string[] $roles
     *
     * @return array
     */
    public function getParentRoles($roles): array
    {
        $roles = is_array($roles) ? $roles : [$roles];

        $ret = [];

        $checkChildren = function(string $role, array $children, callable $rec) use (&$ret) {
            foreach ($children as $parent => $items) {
                if (!is_array($items) || in_array($parent, $ret)) {
                    continue;
                }

                if (in_array($role, $items)) {
                    $ret[] = $parent;
                }

                $rec($role, $items, $rec);
            }
        };

        foreach ($roles as $role) {
            $ret[] = $role;

            $checkChildren($role, $this->rolesHierarchy, $checkChildren);
        }

        return array_unique($ret);
    }

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
                'IT_CUSTOMER',
                'BOOKER_CUSTOMER',
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
                'TICKET_ADMIN_MANAGEMENT',
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
            'TICKET_ADMIN_MANAGEMENT' => 'Администратор тикетной системы (просмотр и назначение ответственных по заявкам)',

            'USER_CUSTOMER' => 'Создание пользователей арендатора',
            'FINANCE_CUSTOMER' => 'Отправка заявок "Финансовые вопросы"',
            'MAINTAINCE_CUSTOMER' => 'Отправка заявок "Служба эксплуатации"',
            'DOCUMENT_CUSTOMER' => 'Просмотр документов арендатора',
            'RENT_CUSTOMER' => 'Отправка заявок в службу аренды',
            'STORAGE_CUSTOMER' => 'Заказ товаров на складе',
            'IT_CUSTOMER' => 'Отправка заявок "IT аутсорсинг"',
            'BOOKER_CUSTOMER' => 'Пользование услугами SMART-бухгалтера',

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
