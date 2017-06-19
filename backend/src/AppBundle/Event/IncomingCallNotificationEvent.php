<?php

namespace AppBundle\Event;


use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Utils\MailManager;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Интерфейс ивента входящего звонка.
 *
 * Если нет флага toCustomer (аргумент toCustomer), значит уведомление приходит секретарю.
 * Иначе - секретарь пересылает звонок арендатору.
 *
 * Если секретарь пересылает звонок арендатору, то возможна установка также комментария (аргумент comment).
 *
 * @package AppBundle\Event
 */
class IncomingCallNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    /**
     * @inheritdoc
     */
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        if (!$this->toCustomer()) {
            // уведомление для секретаря
            // на e-mail отправлять не имеет смысла
            return null;
        } else {
            // уведомление для арендатора
            return $mailManager->buildMessageToUser($this->getReceiver(), 'Входящий звонок с проходной', '@app_emails/incoming_call_to_customer.html.twig', [
                'callerId' => $this->getCallerId(),
                'comment' => $this->getComment(),
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        $notification
            ->setType(UserNotificationEntity::TYPE_INCOMING_CALL)
            ->setCallerId($this->getCallerId())
            ->setComment($this->getComment());

        return $notification;
    }

    /**
     * Получить телефонный номер звонящего
     *
     * @return string
     */
    public function getCallerId(): string
    {
        return $this->getArgument('callerId');
    }

    /**
     * @inheritdoc
     */
    public function getReceiver(): UserEntity
    {
        return $this->getArgument('receiver');
    }

    /**
     * True, если уведомление для арендатора
     *
     * @return bool
     */
    public function toCustomer(): bool
    {
        return $this->hasArgument('toCustomer') && $this->hasArgument('toCustomer') == true;
    }

    /**
     * Получить комментарий для арендатора
     *
     * @return null|string
     */
    public function getComment(): ?string
    {
        return $this->hasArgument('comment') ? $this->getArgument('comment') : null;
    }
}
