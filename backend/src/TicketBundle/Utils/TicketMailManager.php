<?php

namespace TicketBundle\Utils;


use AppBundle\Utils\MailManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketClosedEvent;
use TicketBundle\Event\TicketManagerSetEvent;
use TicketBundle\Event\TicketNewEvent;
use TicketBundle\Event\TicketNewMessageEvent;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Мейлер для событий тикетной системы
 *
 * @package TicketBundle\Utils
 */
class TicketMailManager implements EventSubscriberInterface
{
    /**
     * @var MailManager Системный мейлер
     */
    protected $mailManager;

    /**
     * @var UserRepository Репозиторий для поиска пользователей по группам
     */
    protected $userRepository;

    /**
     * @var RolesManager Менеджер для работы с ролями
     */
    protected $rolesManager;

    /**
     * TicketMailManager constructor.
     *
     * @param MailManager $mailManager Мейлер по умолчанию
     * @param ObjectManager $entityManager Менеджер для работы с БД
     * @param RolesManager $rolesManager Менеджер для работы с ролями
     */
    public function __construct(MailManager $mailManager, ObjectManager $entityManager, RolesManager $rolesManager)
    {
        $this->mailManager = $mailManager;
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->rolesManager = $rolesManager;
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
     * Отправка уведомления всем менеджерам входящие в тикетную группу и имеющие право отвечать на эту очередь.
     *
     * @param TicketEntity $ticket Созданный тикет
     * @param TicketMessageEntity $ticketMessage Сообщение в созданном тикете
     *
     * @return string[] E-mailы на которые были отправлены сообщения
     */
    public function sendNewTicketToManager(TicketEntity $ticket, TicketMessageEntity $ticketMessage): array
    {
        $category = $ticket->getCategory();

        $subject = $category->getName() . ': Получена новая заявка №' . $ticket->getNumber();

        // получить все роли, в том числе и родительские для указанной
        $managerRole = $this->rolesManager->getParentRoles($category->getManagerRole());

        // пользователи по ролям
        $batchList = $this->userRepository->findByRole($managerRole);

        $result = [];

        foreach ($batchList as $users) {
            foreach ($users as $user) {
                /** @var UserEntity $user */
                $message = $this->mailManager->buildMessageToUser($user, $subject, '@ticket_emails/new_ticket_to_manager.html.twig', [
                    'ticket' => $ticket,
                    'category' => $category,
                    'message' => $ticketMessage,
                ]);

                $this->mailManager->sendMessage($message);
                $result[] = $user->getEmail();
            }
        }

        return $result;
    }

    /**
     * Отправка уведомления пользователю о создании новой заявки.
     *
     * @param TicketEntity $ticket Созданный тикет
     * @param TicketMessageEntity $ticketMessage Сообщение в созданном тикете
     *
     * @return int
     */
    public function sendNewTicketToUser(TicketEntity $ticket, TicketMessageEntity $ticketMessage): ?int
    {
        $user = $ticket->getCreatedBy();

        if (!$user) {
            return 0;
        }

        $subject = 'Заявка №' . $ticket->getNumber() . ' зарегистрирована в системе';

        $message = $this->mailManager->buildMessageToUser($user, $subject, '@ticket_emails/new_ticket_to_user.html.twig', [
            'ticket' => $ticket,
            'category' => $ticket->getCategory(),
            'message' => $ticketMessage
        ]);

        return $this->mailManager->sendMessage($message);
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
     * Отправить ответ по заявке всем пользователям, которые задавали по ней вопросы
     *
     * @param TicketEntity $ticket
     * @param TicketMessageEntity $ticketMessage
     *
     * @return array
     */
    public function sendNewAnswerToUser(TicketEntity $ticket, TicketMessageEntity $ticketMessage): array
    {
        $subject = 'Заявка №' . $ticket->getNumber() . ': поступил новый ответ';

        $emails = [];

        // ответ поступает всем пользователям, задававшим вопросы по заявке
        foreach ($this->getAllCustomerUsersByTicket($ticket) as $user) {
            $message = $this->mailManager->buildMessageToUser($user, $subject, '@ticket_emails/new_answer_to_user.html.twig', [
                'ticket' => $ticket,
                'category' => $ticket->getCategory(),
                'message' => $ticketMessage
            ]);
            $this->mailManager->sendMessage($message);
            $emails[] = $user->getEmail();
        }

        return $emails;
    }


    /**
     * Отправить всем пользователям контрагента письмо с информацией об установке нового менеджера по тикету
     *
     * @param TicketEntity $ticket Тикет
     * @param UserEntity $manager Новый менеджер
     *
     * @return string[] E-mailы на которые отправлено письмо
     */
    public function sendSetManagerToUser(TicketEntity $ticket, UserEntity $manager): array
    {
        $subject = 'Заявка №' . $ticket->getNumber() . ': назначен менеджер';

        // ответ поступает всем пользователям, задававшим вопросы по заявке
        $emails = [];
        foreach ($this->getAllCustomerUsersByTicket($ticket) as $user) {
            $message = $this->mailManager->buildMessageToUser($user, $subject, '@ticket_emails/set_manager_to_user.html.twig', [
                'ticket' => $ticket,
                'category' => $ticket->getCategory(),
                'manager' => $manager
            ]);
            $this->mailManager->sendMessage($message);
            $emails[] = $user->getEmail();
        }

        return $emails;
    }

    /**
     * Отправить пользователям письмо о закрытии заявки
     *
     * @param TicketEntity $ticket
     *
     * @return string[] E-mailы на которые отправлено письмо
     */
    public function sendClosedToUser(TicketEntity $ticket)
    {
        $subject = 'Заявка №' . $ticket->getNumber() . ': заявка закрыта';

        // ответ поступает всем пользователям, задававшим вопросы по заявке
        $emails = [];
        foreach ($this->getAllCustomerUsersByTicket($ticket) as $user) {
            $message = $this->mailManager->buildMessageToUser($user, $subject, '@ticket_emails/closed_to_user.html.twig', [
                'ticket' => $ticket,
                'category' => $ticket->getCategory()
            ]);
            $this->mailManager->sendMessage($message);
            $emails[] = $user->getEmail();
        }

        return $emails;
    }

    /**
     * Отправить новый вопрос по заявке менеджерам.
     *
     * Если по заявке есть уже отстветвенный менеджер - вопрос приходит только ему.
     * Иначе вопрос рассылается всем менеджерам в очереди тикетной системы.
     *
     * @param TicketEntity $ticket
     * @param TicketMessageEntity $ticketMessage
     *
     * @return array
     */
    public function sendNewQuestionToManager(TicketEntity $ticket, TicketMessageEntity $ticketMessage): array
    {
        $category = $ticket->getCategory();

        $subject = $category->getName() . ': поступил новый вопрос по заявке №' . $ticket->getNumber();

        /** @var UserEntity[] $to */
        $to = [];

        if ($ticket->getManagedBy()) {
            // если по заявке уже работет менеджер, то она отправляется только ему
            $to[] = $ticket->getManagedBy();
        } else {
            // иначе заявка отправляется всем ответственным
            $managerRole = $this->rolesManager->getParentRoles($category->getManagerRole());
            $batchList = $this->userRepository->findByRole($managerRole);

            foreach ($batchList as $users) {
                foreach ($users as $user) {
                    /** @var UserEntity $user */
                    $to[] = $user;
                }
            }
        }

        $emails = [];

        foreach ($to as $user) {
            $message = $this->mailManager->buildMessageToUser($user, $subject, '@ticket_emails/new_question_to_manager.html.twig', [
                'ticket' => $ticket,
                'category' => $ticket->getCategory(),
                'message' => $ticketMessage
            ]);
            $this->mailManager->sendMessage($message);

            $emails[] = $user->getEmail();
        }

        return $emails;
    }

    /**
     * Событие на создание нового тикета
     *
     * @param TicketNewEvent $event
     */
    public function onNewTicket(TicketNewEvent $event)
    {
        $this->sendNewTicketToManager($event->getTicket(), $event->getMessage());
        $this->sendNewTicketToUser($event->getTicket(), $event->getMessage());
    }

    /**
     * Событие на создание нового ответа по тикету
     *
     * @param TicketNewMessageEvent $event
     */
    public function onNewAnswer(TicketNewMessageEvent $event)
    {
        $this->sendNewAnswerToUser($event->getTicket(), $event->getMessage());
    }

    /**
     * Событие на создание нового вопроса по тикету
     *
     * @param TicketNewMessageEvent $event
     */
    public function onNewQuestion(TicketNewMessageEvent $event)
    {
        $this->sendNewQuestionToManager($event->getTicket(), $event->getMessage());
    }

    /**
     * Событие на установку менеджера по тикету
     *
     * @param TicketManagerSetEvent $event
     */
    public function onManagerSet(TicketManagerSetEvent $event)
    {
        $this->sendSetManagerToUser($event->getTicket(), $event->getManager());
    }

    /**
     * Событие на закрытие тикета
     *
     * @param TicketClosedEvent $event
     */
    public function onClosedTicket(TicketClosedEvent $event)
    {
        $this->sendClosedToUser($event->getTicket());
    }
}
