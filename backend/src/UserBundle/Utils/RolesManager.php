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
                'ROLE_CUSTOMER_ADMIN',
                'ROLE_USER_CUSTOMER',
                'ROLE_FINANCE_CUSTOMER',
                'ROLE_MAINTAINCE_CUSTOMER',
                'ROLE_DOCUMENT_CUSTOMER',
                'ROLE_RENT_CUSTOMER',
                'ROLE_STORAGE_CUSTOMER',
                'ROLE_IT_CUSTOMER',
                'ROLE_BOOKER_CUSTOMER',
                'ROLE_INCOMING_CALLS_CUSTOMER',
                'ROLE_SERVICE_MANAGEMENT',
            ],
            UserEntity::TYPE_MANAGER => [
                'ROLE_SUPERADMIN',
                'ROLE_USER_MANAGEMENT',
                'ROLE_RENT_MANAGEMENT',
                'ROLE_STORAGE_MANAGEMENT',
                'ROLE_DOCUMENT_MANAGEMENT',
                'ROLE_IT_MANAGEMENT',
                'ROLE_FINANCE_MANAGEMENT',
                'ROLE_MAINTAINCE_MANAGEMENT',
                'ROLE_BOOKER_MANAGEMENT',
                'ROLE_TICKET_ADMIN_MANAGEMENT',
                'ROLE_ACCOUNT_MANAGEMENT',
                'ROLE_INCOMING_CALLS_MANAGEMENT',
                'ROLE_SERVICE_CUSTOMER',
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
            'ROLE_SUPERADMIN' => 'Супер администратор (доступны все права сотрудников)',
            'ROLE_CUSTOMER_ADMIN' => 'Администратор арендатора (доступны все права арендатора)',

            'ROLE_USER_CUSTOMER' => 'Создание пользователей арендатора',
            'ROLE_FINANCE_CUSTOMER' => 'Отправка заявок "Финансовые вопросы"',
            'ROLE_MAINTAINCE_CUSTOMER' => 'Отправка заявок "Служба эксплуатации"',
            'ROLE_DOCUMENT_CUSTOMER' => 'Просмотр документов арендатора',
            'ROLE_RENT_CUSTOMER' => 'Отправка заявок в службу аренды',
            'ROLE_STORAGE_CUSTOMER' => 'Заказ товаров на складе',
            'ROLE_IT_CUSTOMER' => 'Отправка заявок "IT аутсорсинг"',
            'ROLE_BOOKER_CUSTOMER' => 'Пользование услугами SMART-бухгалтера',
            'ROLE_INCOMING_CALLS_CUSTOMER' => 'Получение входящих звонков с проходной',
            'ROLE_SERVICE_CUSTOMER' => 'Подключение и управление дополнительными услугами',

            'ROLE_TICKET_ADMIN_MANAGEMENT' => 'Администратор тикетной системы (просмотр и назначение ответственных по заявкам)',
            'ROLE_ACCOUNT_MANAGEMENT' => 'Управляющий менеджер',
            'ROLE_USER_MANAGEMENT' => 'Управление всеми пользователями',
            'ROLE_RENT_MANAGEMENT' => 'Сотрудник службы аренды',
            'ROLE_STORAGE_MANAGEMENT' => 'Сотрудник склада',
            'ROLE_DOCUMENT_MANAGEMENT' => 'Менеджер документов',
            'ROLE_IT_MANAGEMENT' => 'Сотрудник IT-отдела',
            'ROLE_FINANCE_MANAGEMENT' => 'Сотрудник финансовой службы',
            'ROLE_MAINTAINCE_MANAGEMENT' => 'Сотрудник службы эксплуатации',
            'ROLE_BOOKER_MANAGEMENT' => 'Сотрудник SMART-бухгалтер',
            'ROLE_INCOMING_CALLS_MANAGEMENT' => 'Получение входящих звонков и переотправка их арендаторам',
            'ROLE_SERVICE_MANAGEMENT' => 'Управление дополнительными услугами',
        ];
    }
}
