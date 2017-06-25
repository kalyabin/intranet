<?php

namespace Tests;


/**
 * Тестирование доступа к контроллеру
 *
 * @package Tests
 */
trait AccessToActionTrait
{
    use JsonResponseTestTrait;

    /**
     * Проверка контроллера различными пользователями:
     * - неавторизованным пользователем;
     * - авторизованным, но недоступным для правки пользователем;
     * - авторизованным и доступным для правки пользователем
     *
     * @param string $url
     * @param string $method
     * @param string|string[] $allowedUsers Ключ или ключи в фикстурах для разрешенного пользователя
     * @param string|string[] $deniedUsers КЛюч или ключи для запрещенного пользователя в фикстурах
     * @param array|null $invalidData
     * @param array|null $validData
     *
     * @return array
     */
    public function assertAccessToAction(string $url, string $method, $allowedUsers, $deniedUsers, ?array $invalidData = null, ?array $validData = null)
    {
        $validData = is_null($validData) ? [] : $validData;

        // пробуем различные варианты неавторизованным пользователем
        $client = $this->createClient();

        if (!is_null($invalidData)) {
            $client->request($method, $url, $invalidData);
            $this->assertStatusCode(401, $client);
        }

        $client->request($method, $url, $validData);
        $this->assertStatusCode(401, $client);

        // пробуем запрещенным пользователем различные варианты
        $deniedUsers = is_array($deniedUsers) ? $deniedUsers : [$deniedUsers];

        foreach ($deniedUsers as $deniedUser) {
            /** @var UserEntity $deniedUser */
            $deniedUser = $this->fixtures->getReference($deniedUser);
            $this->loginAs($deniedUser, 'main');
            $client = static::makeClient();

            if (!is_null($invalidData)) {
                $client->request($method, $url, $invalidData);
                $this->assertStatusCode(403, $client);
            }

            $client->request($method, $url, $validData);

            $this->assertStatusCode(403, $client);
        }

        // пробуем разрешенным пользователем различные варианты
        $allowedUsers = is_array($allowedUsers) ? $allowedUsers : [$allowedUsers];

        $result = [];
        foreach ($allowedUsers as $k => $allowedUser) {
            /** @var UserEntity $allowedUser */
            $allowedUser = $this->fixtures->getReference($allowedUser);

            $this->loginAs($allowedUser, 'main');
            $client = static::makeClient();

            if (!is_null($invalidData)) {
                $client->request($method, $url, $invalidData);

                $this->assertStatusCode(400, $client);
            }

            $client->request($method, $url, $validData);

            $this->assertStatusCode(200, $client);
            $result = $this->assertIsValidJsonResponse($client->getResponse());
        }

        return $result;
    }
}
