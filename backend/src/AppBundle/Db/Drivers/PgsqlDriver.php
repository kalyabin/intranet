<?php

namespace AppBundle\Db\Drivers;

use Doctrine\DBAL\Driver\PDOPgSql\Driver;

/**
 * @todo: собственный драйвер для поддержки постоянных соединений с БД для того, чтобы избежать проблему too many connections
 * @todo: иного пути пока не было найдено
 * @inheritdoc
 * @package AppBundle\Db\Drivers
 */
class PgsqlDriver extends Driver
{
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        static $connection;
        if (null === $connection) {
            $connection = parent::connect($params, $username, $password, $driverOptions);
        }
        return $connection;
    }
}
