<?php

namespace TicketBundle\Utils;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketNewEvent;
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

        if ($type == TicketMessageEntity::TYPE_ANSWER) {
            // время автоматической очистки сообщения
            $date = new \DateTime();
            $date->add(new \DateInterval('PT' . $this->answeredTicketLifetime . 'M'));

            $ticket
                ->setLastAnswerAt(new \DateTime())
                ->setCurrentStatus(TicketEntity::STATUS_ANSWERED)
                ->setVoidedAt($date);
        } else {
            // очистить время автоматической очистки сообщения
            $ticket
                ->setLastQuestionAt(new \DateTime())
                ->setCurrentStatus(TicketEntity::STATUS_WAIT)
                ->setVoidedAt(null);
        }

        $this->entityManager->persist($ticket);

        $this->entityManager->persist($entity);

        $this->entityManager->flush();

        // событие в зависимости от типа сообщения
        $event = new TicketNewEvent($ticket, $entity);
        $eventType = $type == TicketMessageEntity::TYPE_ANSWER ?
            TicketNewEvent::NEW_ANSWER :
            TicketNewEvent::NEW_QUESTION;

        $this->eventDispatcher->dispatch($eventType, $event);

        return $entity;
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
            ->setCurrentStatus(TicketEntity::STATUS_NEW)
            ->setCategory($category)
            ->setTitle($ticket->getTitle());

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
        $this->eventDispatcher->dispatch(TicketNewEvent::NEW_TICKET, $event);

        return $entity;
    }
}
