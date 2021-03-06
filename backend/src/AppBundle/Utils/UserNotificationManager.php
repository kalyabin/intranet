<?php

namespace AppBundle\Utils;

use AppBundle\Entity\Repository\UserNotificationRepository;
use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Service\CometClient;
use Doctrine\ORM\EntityManagerInterface;
use UserBundle\Entity\UserEntity;


/**
 * Менеджер системных уведомлений для пользователя
 *
 * @package AppBundle\Utils
 */
class UserNotificationManager
{
    /**
     * @var MailManager
     */
    protected $mailManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserNotificationRepository
     */
    protected $repository;

    /**
     * @var CometClient
     */
    protected $cometClient;

    /**
     * UserNotificationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(UserNotificationEntity::class);
    }

    /**
     * Установка мейлера
     *
     * @param MailManager $mailManager
     */
    public function setMailManager(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * Установка клиента для comet-сервера
     *
     * @param CometClient $cometClient
     */
    public function setCometClient(CometClient $cometClient)
    {
        $this->cometClient = $cometClient;
    }

    /**
     * Если установлен мейлер - отправить системное уведомление по почте
     *
     * @param UserNotificationInterface $event
     *
     * @return bool
     */
    protected function sendMailMessage(UserNotificationInterface $event): bool
    {
        $receiver = $event->getReceiver();

        if ($this->mailManager) {
            $message = $event->buildMailMessage($this->mailManager);

            if ($message) {
                $message->setTo([
                    $receiver->getEmail() => $receiver->getName()
                ]);

                return $this->mailManager->sendMessage($message) == 1;
            }
        }

        return false;
    }

    /**
     * Отправка уведомления пользователю во фронтенд с новыми сообщениями
     *
     * @param UserNotificationInterface $event
     *
     * @return bool
     */
    protected function sendCometMessage(UserNotificationInterface $event): bool
    {
        $receiver = $event->getReceiver();

        if ($this->cometClient) {
            $this->cometClient->fetchNewNotification($receiver);

            return true;
        }

        return false;
    }

    /**
     * Сохранение уведомления в базе данных
     *
     * @param UserNotificationInterface $event
     *
     * @return bool
     */
    protected function saveNotification(UserNotificationInterface $event): bool
    {
        $entity = new UserNotificationEntity();

        $entity
            ->setIsRead(false)
            ->setReceiver($event->getReceiver())
            ->setCreatedAt(new \DateTime());

        $entity = $event->configureNotification($entity);

        if ($entity) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    /**
     * Пометить все уведомления как прочтенные
     *
     * @param UserEntity $user Пользователь для которого выполнить операцию
     *
     * @return int Количество помеченных уведомлений
     */
    public function setAllNotificationIsRead(UserEntity $user): int
    {
        $result = 0;

        foreach ($this->repository->findAllUnreadUserNotification($user) as $notification) {
            $notification->setIsRead(true);
            $this->entityManager->persist($notification);
            $result++;
        }

        if ($result > 0) {
            $this->entityManager->flush();
        }

        return $result;
    }

    /**
     * Обработка уведомления пользователю
     *
     * @param UserNotificationInterface $event
     */
    public function onUserNotification(UserNotificationInterface $event)
    {
        // сохранение уведомления в базе данных
        $this->saveNotification($event);
        // отправка уведомления по e-mail
        $this->sendMailMessage($event);
        // отправка уведомления прямо в браузер
        $this->sendCometMessage($event);
    }
}
