<?php

namespace Tests\DataFixtures\ORM;


use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketHistoryEntity;
use TicketBundle\Entity\TicketMessageEntity;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\UserRoleEntity;

/**
 * Заявки для тестов
 *
 * @package TicketBundle\Tests\DataFixtures\ORM
 */
class TicketTestFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->getReference('it-department');
        /** @var CustomerEntity $customer */
        $customer = $this->getReference('all-customer');
        /** @var UserEntity $userManager */
        $userManager = $this->getReference('it-manager-user');
        /** @var UserEntity $userManagerDenied */
        $userManagerDenied = $this->getReference('document-manager-user');
        /** @var UserEntity $superadmin */
        $superadmin = $this->getReference('superadmin-user');
        /** @var UserEntity $userCustomer */
        $userCustomer = $this->getReference('active-user');
        /** @var CustomerEntity $otherCustomer */
        $otherCustomer = $this->getReference('none-customer');
        /** @var UserEntity $otherCustomerUser */
        $otherCustomerUser = $this->getReference('none-customer-user');

        // тестовый тикет
        $entity = new TicketEntity();
        $entity
            ->setNumber('testing number')
            ->setCategory($category)
            ->setCreatedAt(new \DateTime())
            ->setCustomer($customer)
            ->setCreatedBy($userCustomer)
            ->setCurrentStatus(TicketEntity::STATUS_NEW)
            ->setLastAnswerAt(new \DateTime())
            ->setLastQuestionAt(new \DateTime())
            ->setVoidedAt(new \DateTime())
            ->setManagedBy($superadmin)
            ->setTitle('testing ticket');

        // сообщение в тикете
        $message = new TicketMessageEntity();
        $message
            ->setCreatedBy($userCustomer)
            ->setCreatedAt(new \DateTime())
            ->setTicket($entity)
            ->setText('testing text')
            ->setType(TicketMessageEntity::TYPE_QUESTION);

        // ответ в тикете
        $answer = new TicketMessageEntity();
        $answer
            ->setCreatedBy($superadmin)
            ->setCreatedAt(new \DateTime())
            ->setTicket($entity)
            ->setText('testing answer')
            ->setType(TicketMessageEntity::TYPE_ANSWER);

        // элемент истории
        $historyItem = new TicketHistoryEntity();
        $historyItem
            ->setCreatedBy($userCustomer)
            ->setCreatedAt(new \DateTime())
            ->setTicket($entity)
            ->setStatus(TicketEntity::STATUS_CLOSED);

        $manager->persist($entity);
        $manager->persist($message);
        $manager->persist($answer);
        $manager->persist($historyItem);
        $manager->flush();

        $this->addReference('ticket-category', $category);
        $this->addReference('ticket-customer', $customer);
        $this->addReference('ticket-other-customer', $otherCustomer);
        $this->addReference('ticket-manager', $superadmin);
        $this->addReference('ticket-manager-other', $userManager);
        $this->addReference('ticket-manager-denied', $userManagerDenied);
        $this->addReference('ticket-customer-user', $userCustomer);
        $this->addReference('ticket-other-customer-user', $otherCustomerUser);
        $this->addReference('ticket-message', $message);
        $this->addReference('ticket-answer', $answer);
        $this->addReference('ticket-history-item', $historyItem);
        $this->addReference('ticket', $entity);
    }

    /**
     * Тикетная система зависит от контрагентов и пользователей
     *
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }
}
