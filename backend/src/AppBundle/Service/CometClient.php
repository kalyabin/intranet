<?php

namespace AppBundle\Service;
use UserBundle\Entity\UserEntity;


/**
 * Сервис для отправки уведомлений в comet-сервер
 *
 * @package AppBundle\Service
 */
class CometClient
{
    /**
     * @var string URL, на который необходимо пробрасывать POST-запросы
     */
    protected $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Выполнить запрос
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function makeRequest(array $data)
    {
        $ch = curl_init($this->url);

        curl_setopt_array($ch, [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Отправить запрос типа "задача" для пользователя
     *
     * @param string $task
     * @param array $data
     *
     * @return mixed
     */
    public function sendTask(string $task, array $data)
    {
        $data['task'] = $task;

        return $this->makeRequest($data);
    }

    /**
     * Отправка сообщения пользователю о необходимости получить новые уведомления
     *
     * @param UserEntity $user
     *
     * @return mixed
     */
    public function fetchNewNotification(UserEntity $user)
    {
        return $this->sendTask('fetchNewNotifications', ['userId' => $user->getId()]);
    }
}
