<?php

namespace TicketBundle\Utils;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketClosedEvent;
use TicketBundle\Event\TicketManagerSetEvent;
use TicketBundle\Event\TicketNewEvent;
use TicketBundle\Event\TicketNewMessageEvent;
use TicketBundle\Event\TicketUserNotificationEvent;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Уведомления для пользователей и менеджеров тикетной системы
 *
 * @package TicketBundle\Utils
 */
class TicketNotificationManager implements EventSubscriberInterface
{
    /**
     * @var UserRepository Репозиторий для поиска пользователей по группам
     */
    protected $userRepository;

    /**
     * @var RolesManager Менеджер для работы с ролями
     */
    protected $rolesManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * TicketMailManager constructor.
     *
     * @param ObjectManager $entityManager Менеджер для работы с БД
     * @param RolesManager $rolesManager Менеджер для работы с ролями
     * @param EventDispatcherInterface $eventDispatcher Диспатчер дочерних событий
     */
    public function __construct(ObjectManager $entityManager, RolesManager $rolesManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->rolesManager = $rolesManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            TicketNewEvent::NAME => 'onNewTicket',
            TicketNewMessageEvent::NEW_ANSWER => 'onNewAnswer',
            TicketNewMessageEvent::NEW_QUESTION => 'onNewQuestion',
            TicketManagerSetEvent::NAME => 'onManagerSet',
            TicketClosedEvent::NAME => 'onClosedTicket',
        ];
    }

    /**
     * Получить всех пользователей контрагента, участвующих в тикете (задающих вопросы по тикету)
     *
     * @param TicketEntity $ticket
     *
     * @return UserEntity[]
     */
    protected function getAllCustomerUsersByTicket(TicketEntity $ticket): array
    {
        /** @var UserEntity[] $result */
        $result = [];
        $ids = [];

        foreach ($ticket->getMessage() as $item) {
            /** @var TicketMessageEntity $item */
            if ($item->getType() == TicketMessageEntity::TYPE_QUESTION) {
                $user = $item->getCreatedBy();
                if ($user && !in_array($user->getId(), $ids)) {
                    $ids[] = $user->getId();
                    $result[] = $user;
                }
            }
        }

        return $result;
    }

    /**
     * Событие на создание нового тикета
     *
     * @param TicketNewEvent $event
     *
     * @return int
     */
    public function onNewTicket(TicketNewEvent $event): int
    {
        $result = 0;

        // отправка уведомлений сотрудникам
        // получить все роли, в том числе и родительские для указанной
        $managerRole = $this->rolesManager->getParentRoles($event->getTicket()->getCategory()->getManagerRole());
        // пользователи по ролям
        $batchList = $this->userRepository->findByRole($managerRole);
        foreach ($batchList as $users) {
            foreach ($users as $user) {
                $childEvent = new TicketUserNotificationEvent(TicketNewEvent::NAME, $event, $user);
                $this->eventDispatcher->dispatch('user_notification', $childEvent);
                $result++;
            }
        }

        // отправка уведомлений пользователю создавшему тикет
        if ($event->getTicket()->getCreatedBy()) {
            $childEvent = new TicketUserNotificationEvent(TicketNewEvent::NAME, $event, $event->getTicket()->getCreatedBy());
            $this->eventDispatcher->dispatch('user_notification', $childEvent);
            $result++;
        }

        return $result;
    }

    /**
     * Событие на создание нового ответа по тикету
     *
     * @param TicketNewMessageEvent $event
     *
     * @return int
     */
    public function onNewAnswer(TicketNewMessageEvent $event): int
    {
        $result = 0;

        // отправка всем пользователям арендатора
        foreach ($this->getAllCustomerUsersByTicket($event->getTicket()) as $user) {
            $childEvent = new TicketUserNotificationEvent(TicketNewMessageEvent::NEW_ANSWER, $event, $user);
            $this->eventDispatcher->dispatch('user_notification', $childEvent);
            $result++;
        }

        return $result;
    }

    /**
     * Событие на создание нового вопроса по тикету
     *
     * @param TicketNewMessageEvent $event
     *
     * @return int
     */
    public function onNewQuestion(TicketNewMessageEvent $event): int
    {
        $result = 0;

        $ticket = $event->getTicket();

        if ($ticket->getManagedBy()) {
            // если по заявке уже работет менеджер, то она отправляется только ему
            $childEvent = new TicketUserNotificationEvent(TicketNewMessageEvent::NEW_QUESTION, $event, $ticket->getManagedBy());
            $this->eventDispatcher->dispatch('user_notification', $childEvent);
            $result++;
        } else {
            // иначе заявка отправляется всем ответственным
            $managerRole = $this->rolesManager->getParentRoles($ticket->getCategory()->getManagerRole());
            $batchList = $this->userRepository->findByRole($managerRole);

            foreach ($batchList as $users) {
                foreach ($users as $user) {
                    /** @var UserEntity $user */
                    $childEvent = new TicketUserNotificationEvent(TicketNewMessageEvent::NEW_QUESTION, $event, $user);
                    $this->eventDispatcher->dispatch('user_notification', $childEvent);
                    $result++;
                }
            }
        }

        return $result;
    }

    /**
     * Событие на установку менеджера по тикету
     *
     * @param TicketManagerSetEvent $event
     *
     * @return int
     */
    public function onManagerSet(TicketManagerSetEvent $event): int
    {
        $result = 0;

        foreach ($this->getAllCustomerUsersByTicket($event->getTicket()) as $user) {
            $childEvent = new TicketUserNotificationEvent(TicketManagerSetEvent::NAME, $event, $user);
            $this->eventDispatcher->dispatch('user_notification', $childEvent);
            $result++;
        }

        return $result;
    }

    /**
     * Событие на закрытие тикета
     *
     * @param TicketClosedEvent $event
     *
     * @return int
     */
    public function onClosedTicket(TicketClosedEvent $event): int
    {
        $result = 0;

        foreach ($this->getAllCustomerUsersByTicket($event->getTicket()) as $user) {
            $childEvent = new TicketUserNotificationEvent(TicketClosedEvent::NAME, $event, $user);
            $this->eventDispatcher->dispatch('user_notification', $childEvent);
            $result++;
        }

        return $result;
    }
}
