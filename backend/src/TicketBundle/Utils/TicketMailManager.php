<?php

namespace TicketBundle\Utils;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketNewEvent;
use TicketBundle\Event\TicketNewMessageEvent;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;

/**
 * Мейлер для событий тикетной системы
 *
 * @package TicketBundle\Utils
 */
class TicketMailManager implements EventSubscriberInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var \Swift_Message Последнее отправленное сообщение
     */
    protected $lastMessage;

    /**
     * @var UserRepository Репозиторий для поиска пользователей по группам
     */
    protected $userRepository;

    /**
     * TicketMailManager constructor.
     *
     * @param \Swift_Mailer $mailer Мейлер для отправки почты
     * @param EngineInterface $templating Движок для шаблонизации twig
     * @param ObjectManager $entityManager Менеджер для работы с БД
     * @param null|string $from E-mail отправителя (по умолчанию - без отправителя)
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, ObjectManager $entityManager, ?string $from = null)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->from = $from;
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
    }

    /**
     * Установка отправителя
     *
     * @param string $from
     *
     * @return TicketMailManager
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public static function getSubscribedEvents()
    {
        return [
            TicketNewEvent::NAME         => 'onNewTicket',
            TicketNewMessageEvent::NEW_ANSWER   => 'onNewAnswer',
            TicketNewMessageEvent::NEW_QUESTION => 'onNewQuestion',
        ];
    }

    /**
     * Отправка уже сформированного письма
     *
     * @param \Swift_Message $message Сообщение с сабжектом и телом
     * @param string $email E-mail, на который надо отправить письмо
     *
     * @return int
     */
    protected function sendMessage(\Swift_Message $message, $email)
    {
        $message
            ->setFrom($this->from)
            ->setTo($email);

        $this->lastMessage = $message;

        return $this->mailer->send($message);
    }

    /**
     * Получить последнее сообщение
     *
     * @return null|\Swift_Message
     */
    public function getLastMessage(): ?\Swift_Message
    {
        return $this->lastMessage;
    }

    /**
     * Стереть последнее сообщение
     */
    public function clearLastMessage()
    {
        $this->lastMessage = null;
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
        $managerRole = $category->getManagerRole();

        $subject = $category->getName() . ': Получена новая заявка №' . $ticket->getNumber();
        // сформировать письмо
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setBody(
                $this->templating->render('@ticket_emails/new_ticket_to_manager.html.twig', [
                    'ticket' => $ticket,
                    'category' => $category,
                    'message' => $ticketMessage,
                ]),
                'text/html'
            );

        $batchList = $this->userRepository->findByRole($managerRole);

        $result = [];

        foreach ($batchList as $users) {
            foreach ($users as $user) {
                /** @var UserEntity $user */
                $this->sendMessage($message, $user->getEmail());
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
        $subject = 'Заявка №' . $ticket->getNumber() . ' зарегистрирована в системе';

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setBody(
                $this->templating->render('@ticket_emails/new_ticket_to_user.html.twig', [
                    'ticket' => $ticket,
                    'category' => $ticket->getCategory(),
                    'message' => $ticketMessage
                ]),
                'text/html'
            );

        $user = $ticket->getCreatedBy();

        return $user ? $this->sendMessage($message, $user->getEmail()) : 0;
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

        // ответ поступает всем пользователям, задававшим вопросы по заявке
        $emails = [];
        foreach ($ticket->getMessage() as $item) {
            /** @var TicketMessageEntity $item */
            if ($item->getType() == TicketMessageEntity::TYPE_QUESTION) {
                $user = $item->getCreatedBy();
                if ($user && !in_array($user->getEmail(), $emails)) {
                    $emails[] = $user->getEmail();
                }
            }
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setBody(
                $this->templating->render('@ticket_emails/new_answer_to_user.html.twig', [
                    'ticket' => $ticket,
                    'category' => $ticket->getCategory(),
                    'message' => $ticketMessage
                ]),
                'text/html'
            );

        foreach ($emails as $email) {
            $this->sendMessage($message, $email);
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

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setBody(
                $this->templating->render('@ticket_emails/new_question_to_manager.html.twig', [
                    'ticket' => $ticket,
                    'category' => $ticket->getCategory(),
                    'message' => $ticketMessage
                ]),
                'text/html'
            );

        $emails = [];

        if ($ticket->getManagedBy()) {
            // если по заявке уже работет менеджер, то она отправляется только ему
            $emails[] = $ticket->getManagedBy()->getEmail();
        } else {
            // иначе заявка отправляется всем ответственным
            $batchList = $this->userRepository->findByRole($category->getManagerRole());

            foreach ($batchList as $users) {
                foreach ($users as $user) {
                    /** @var UserEntity $user */
                    $emails[] = $user->getEmail();
                }
            }
        }

        foreach ($emails as $email) {
            $this->sendMessage($message, $email);
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
        // TODO: реализовать логику
    }
}
