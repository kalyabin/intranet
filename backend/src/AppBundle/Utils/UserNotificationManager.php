<?php

namespace AppBundle\Utils;
use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use Doctrine\ORM\EntityManagerInterface;


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
     * UserNotificationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MailManager $mailManager
     */
    public function __construct(EntityManagerInterface $entityManager, MailManager $mailManager)
    {
        $this->entityManager = $entityManager;
        $this->mailManager = $mailManager;
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
    }
}
