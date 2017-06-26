<?php

namespace CustomerBundle\Event;


use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use CustomerBundle\Entity\ServiceEntity;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление о деактивации услуги
 *
 * @package CustomerBundle\Event
 */
class ServiceDeactivatedNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    /**
     * @inheritdoc
     */
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        $service = $this->getService();
        $receiver = $this->getReceiver();

        $subject = 'Активирована услуга ' . $service->getTitle();
        $template = '@customer_emails/service_deactivated.html.twig';

        return $mailManager->buildMessageToUser($receiver, $subject, $template, [
            'service' => $service,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        $notification
            ->setType(UserNotificationEntity::TYPE_SERVICE_DEACTIVATED)
            ->setService($this->getService());

        return $notification;
    }

    /**
     * @inheritdoc
     */
    public function getReceiver(): UserEntity
    {
        return $this->getArgument('receiver');
    }

    public function getService(): ServiceEntity
    {
        return $this->getArgument('service');
    }
}
