<?php

namespace Tests;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Тестирование результатов JSON-запросов
 *
 * @package Tests
 */
trait JsonResponseTestTrait
{
    /**
     * Проверка JSON-ответа
     *
     * @param Response $response
     */
    protected function assertIsJsonContentType(Response $response)
    {
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json')
        );
    }

    /**
     * Декодирование JSON в массив
     *
     * @param string $jsonData
     *
     * @return array
     */
    protected function decodeJson($jsonData)
    {
        $this->assertJson($jsonData);

        $decoder = new JsonDecode(true);
        $result = $decoder->decode($jsonData, JsonEncoder::FORMAT);

        $this->assertInternalType('array', $result);

        return $result;
    }

    /**
     * Проверяет, что был получен правильный JSON-ответ и возвращает массив JSON
     *
     * @param Response $response
     *
     * @return array
     */
    protected function assertIsValidJsonResponse(Response $response)
    {
        $this->assertIsJsonContentType($response);
        $jsonData = $this->decodeJson($response->getContent());
        return $jsonData;
    }
}
