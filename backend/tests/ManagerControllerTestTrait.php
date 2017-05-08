<?php

namespace Tests;

/**
 * Хелперы для тестов администраторских контроллеров
 *
 * @package Tests
 */
trait ManagerControllerTestTrait
{
    /**
     * Тестирование контроллера неавторизованным пользователем
     *
     * @param string $method Метод доступа к контроллеру (GET, POST, PUT, etc)
     * @param string $url URL запрашиваемой страницы
     * @param array $postData POST-данные (по умолчанию - пусто)
     */
    protected function assertNonAuthenticatedUsers($method, $url, array $postData = [])
    {
        $nonAdminUser = $this->fixtures->getReference('active-user');

        $client = $this->createClient();

        // неавторизованный пользователь должен видеть 401 ошибку
        $client->request($method, $url, $postData);
        $this->assertStatusCode(401, $client);

        // авторизованный пользователь но не админ должен получить 403-ю ошибку
        $this->loginAs($nonAdminUser, 'main');

        $client = static::makeClient();
        $client->request($method, $url, $postData);
        $this->assertStatusCode(403, $client);
    }
}
