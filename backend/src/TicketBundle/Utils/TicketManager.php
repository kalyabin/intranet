<?php

namespace TicketBundle\Utils;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketHistoryEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketClosedEvent;
use TicketBundle\Event\TicketManagerSetEvent;
use TicketBundle\Event\TicketNewEvent;
use TicketBundle\Event\TicketNewMessageEvent;
use TicketBundle\Form\Type\TicketMessageType;
use TicketBundle\Form\Type\TicketType;
use UserBundle\Entity\UserEntity;

/**
 * API для работы с заявками:
 *
 * - ответ на сообщения;
 * - закрытие или открытие тикетов;
 * - и т.п.
 *
 * @package TicketBundle\Utils
 */
class TicketManager
{
    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var TicketRepository
     */
    protected $ticketRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var integer Время жизни в миунатах отвеченного тикета
     */
    protected $answeredTicketLifetime;

    /**
     * TicketManager constructor.
     *
     * @param ObjectManager $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param integer $answeredTicketLifetime Максимальное время жизни
     */
    public function __construct(ObjectManager $objectManager, EventDispatcherInterface $eventDispatcher, int $answeredTicketLifetime)
    {
        $this->entityManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->ticketRepository = $objectManager->getRepository(TicketEntity::class);
        $this->answeredTicketLifetime = $answeredTicketLifetime;
    }

    /**
     * Генерация номера заявки
     *
     * @param UserEntity $author Автор заявки
     *
     * @return string
     */
    protected function generateTicketNumber(UserEntity $author): string
    {
        $customer = $author->getCustomer();

        if ($customer) {
            $cnt = $this->ticketRepository->getTotalCountByCustomer($customer);
            return implode('-', [
                'C',
                $customer->getId(),
                $customer->getCurrentAgreement(),
                $cnt + 1
            ]);
        } else {
            $cnt = $this->ticketRepository->getTotalCountByUser($author);
            return implode('-', [
                'U',
                $author->getId(),
                $cnt + 1
            ]);
        }
    }

    /**
     * Закрытие тикета.
     *
     * Меняет статус тикета и отправляет событие на закрытие.
     *
     * Возвращает false, если тикет уже закрыт.
     *
     * @param TicketEntity $ticket Тикет
     * @param null|UserEntity $author Автор события (по умолчанию - пользователь, создавший тикет)
     *
     * @return bool
     */
    public function closeTicket(TicketEntity $ticket, ?UserEntity $author = null): bool
    {
        // повторно заявку закрыть нельзя
        if ($ticket->getCurrentStatus() == TicketEntity::STATUS_CLOSED) {
            return false;
        }

        $author = $author ? $author : $ticket->getCreatedBy();

        // возможно пользователь контрагента был удален
        // в таком случае работаем без события и изменений в истории
        if (!$author) {
            $ticket->setCurrentStatus(TicketEntity::STATUS_CLOSED);
            $this->entityManager->persist($ticket);
            $this->entityManager->flush();
            return true;
        }

        $this->setTicketStatus($ticket, $author, TicketEntity::STATUS_CLOSED);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // инициация события
        $event = new TicketClosedEvent($ticket, $author);
        $this->eventDispatcher->dispatch(TicketClosedEvent::NAME, $event);

        return true;
    }

    /**
     * Установка менеджера по тикету.
     *
     * Если менеджер уже установлен на текущего возвращает true, иначе возвращает false.
     *
     * Нужно отдельным методом только для того, чтобы пользователь получил письмо по данному событию.
     *
     * @param TicketEntity $ticket Тикет
     * @param UserEntity $manager Менеджер для установки
     *
     * @return boolean
     */
    public function appointTicketToManager(TicketEntity $ticket, UserEntity $manager): bool
    {
        if ($ticket->getManagedBy() && $ticket->getManagedBy()->getId() == $manager->getId()) {
            return false;
        }

        $ticket->setManagedBy($manager);
        $this->setTicketStatus($ticket, $manager, TicketEntity::STATUS_IN_PROCESS);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // отправка события
        $event = new TicketManagerSetEvent($ticket, $manager);
        $this->eventDispatcher->dispatch(TicketManagerSetEvent::NAME, $event);

        return true;
    }

    /**
     * Создание сообщения по заявке.
     *
     * Если тип сообщения - ответ, то заполняет поле voided_at и last_answer_at, меняет на соответствующий статус.
     * Если тип сообщения - вопрос, то очищает поле voided_at и заполняет поле last_question_at, меняет на соответствующий статус.
     *
     * @param TicketEntity $ticket Тикет по которому создать сообщение
     * @param TicketMessageType $message Текст сообщения
     * @param string $type Тип сообщения
     * @param UserEntity $author Автор
     *
     * @return TicketMessageEntity
     */
    public function createTicketMessage(TicketEntity $ticket, TicketMessageType $message, string $type, UserEntity $author): TicketMessageEntity
    {
        $entity = new TicketMessageEntity();

        $entity
            ->setCreatedAt(new \DateTime())
            ->setType($type)
            ->setTicket($ticket)
            ->setText($message->getText())
            ->setCreatedBy($author);

        $status = $ticket->getCurrentStatus();

        if ($type == TicketMessageEntity::TYPE_ANSWER) {
            // время автоматической очистки сообщения
            $date = new \DateTime();
            $date->add(new \DateInterval('PT' . $this->answeredTicketLifetime . 'M'));

            $ticket
                ->setLastAnswerAt(new \DateTime())
                // тихая установка менеджера
                ->setManagedBy($author)
                ->setVoidedAt($date);

            $status = TicketEntity::STATUS_ANSWERED;
        } else {
            // очистить время автоматической очистки сообщения
            $ticket
                ->setLastQuestionAt(new \DateTime())
                ->setVoidedAt(null);

            $status = TicketEntity::STATUS_WAIT;

            if ($ticket->getCurrentStatus() == TicketEntity::STATUS_NEW) {
                // заявка, на которую еще не поступали ответы должна остаться новой
                $status = TicketEntity::STATUS_NEW;
            } else if ($ticket->getCurrentStatus() == TicketEntity::STATUS_CLOSED) {
                // закрытая заявка должна стать переоткрытой - самый жесткий случай
                $status = TicketEntity::STATUS_REOPENED;
            }
        }

        $this->setTicketStatus($ticket, $author, $status);

        $this->entityManager->persist($ticket);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // событие в зависимости от типа сообщения
        $event = new TicketNewMessageEvent($ticket, $entity);
        $eventType = $type == TicketMessageEntity::TYPE_ANSWER ?
            TicketNewMessageEvent::NEW_ANSWER :
            TicketNewMessageEvent::NEW_QUESTION;

        $this->eventDispatcher->dispatch($eventType, $event);

        return $entity;
    }

    /**
     * Установка текущего статуса тикета и добавление статуса в историю
     *
     * @param TicketEntity $ticket Тикет
     * @param UserEntity $author Инициатор смены статуса
     * @param string $status Код статуса
     *
     * @return bool
     */
    protected function setTicketStatus(TicketEntity $ticket, UserEntity $author, string $status): bool
    {
        if ($ticket->getCurrentStatus() === $status) {
            // статус не был изменен
            return false;
        }

        $ticket->setCurrentStatus($status);

        $historyItem = new TicketHistoryEntity();

        $historyItem
            ->setTicket($ticket)
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($author)
            ->setStatus($status);

        $ticket->addHistory($historyItem);

        return true;
    }

    /**
     * Создание заявки
     *
     * @param TicketType $ticket Заполненная форма заявки с заголовком и текстом первого сообщения
     * @param TicketCategoryEntity $category Категория для заявки (очередь)
     * @param UserEntity $author Автор заявки
     *
     * @return TicketEntity
     */
    public function createTicket(TicketType $ticket, TicketCategoryEntity $category, UserEntity $author): TicketEntity
    {
        $number = $this->generateTicketNumber($author);
        $customer = $author->getCustomer();

        $entity = new TicketEntity();

        $entity
            ->setNumber($number)
            ->setCreatedAt(new \DateTime())
            ->setLastQuestionAt(new \DateTime())
            ->setCreatedBy($author)
            ->setCategory($category)
            ->setTitle($ticket->getTitle());

        $this->setTicketStatus($entity, $author, TicketEntity::STATUS_NEW);

        $message = new TicketMessageEntity();

        $message
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($author)
            ->setType(TicketMessageEntity::TYPE_QUESTION)
            ->setTicket($entity)
            ->setText($ticket->getText());

        $entity->addMessage($message);

        if ($customer) {
            $entity->setCustomer($customer);
        }

        $this->entityManager->persist($entity);

        $this->entityManager->persist($message);

        $this->entityManager->flush();

        // генерация системного события
        $event = new TicketNewEvent($entity, $message);
        $this->eventDispatcher->dispatch(TicketNewEvent::NAME, $event);

        return $entity;
    }

    public function getUserAvailableCategories(UserEntity $user): array
    {
        $result = [];

        return $result;
    }
}
