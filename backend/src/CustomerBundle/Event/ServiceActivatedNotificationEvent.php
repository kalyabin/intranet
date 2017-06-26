<?php

namespace CustomerBundle\Event;

use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление об активации услуги для пользователя арендатора
 *
 * @package CustomerBundle\Event
 */
class ServiceActivatedNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    /**
     * @inheritdoc
     */
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        $service = $this->getService();
        $tariff = $this->getTariff();
        $receiver = $this->getReceiver();

        $subject = 'Активирована услуга ' . $service->getTitle();
        $template = '@customer_emails/service_activated.html.twig';

        return $mailManager->buildMessageToUser($receiver, $subject, $template, [
            'service' => $service,
            'tariff' => $tariff,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        $notification
            ->setType(UserNotificationEntity::TYPE_SERVICE_ACTIVATED)
            ->setService($this->getService());

        if ($this->getTariff()) {
            $notification->setTariff($this->getTariff());
        }

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

    public function getTariff(): ?ServiceTariffEntity
    {
        return $this->hasArgument('tariff') ? $this->getArgument('tariff') : null;
    }
}
