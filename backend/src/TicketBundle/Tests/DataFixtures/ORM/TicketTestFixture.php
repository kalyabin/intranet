<?php

namespace TicketBundle\Tests\DataFixtures\ORM;


use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
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
class TicketTestFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        // категория тикетов
        $category = new TicketCategoryEntity();

        $category
            ->setName('testing')
            ->setManagerRole('ROLE_IT_MANAGEMENT')
            ->setCustomerRole('ROLE_IT_CUSTOMER')
            ->setId('testing');

        $manager->persist($category);

        // пользователи
        $role = new UserRoleEntity();
        $role->setCode('ROLE_SUPERADMIN');

        $userManager = new UserEntity();
        $userManager
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket manager')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->addRole($role)
            ->setEmail('ticket-manager@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($userManager);

        $role = new UserRoleEntity();
        $role->setCode('ROLE_IT_MANAGEMENT');

        $userManagerOther = new UserEntity();
        $userManagerOther
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket manager second')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->addRole($role)
            ->setEmail('ticket-manager-other@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($userManagerOther);

        $role = new UserRoleEntity();
        $role->setCode('ROLE_DOCUMENT_MANAGER');

        $userDeniedManager = new UserEntity();
        $userDeniedManager
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket manager second')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->addRole($role)
            ->setEmail('ticket-manager-denied@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($userDeniedManager);

        $customer = new CustomerEntity();

        $customer
            ->setName('testing ticket customer')
            ->setAllowItDepartment(true)
            ->setAllowBookerDepartment(true)
            ->setCurrentAgreement('testing');

        $manager->persist($customer);

        $manager->flush();

        $userCustomer = new UserEntity();

        $role = new UserRoleEntity();
        $role->setCode('ROLE_CUSTOMER_ADMIN');

        $userCustomer
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket customer')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->addRole($role)
            ->setCustomer($customer)
            ->setEmail('ticket-customer@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($userCustomer);

        $manager->flush();

        // тестовый тикет
        $entity = new TicketEntity();

        $entity
            ->setNumber('testing number')
            ->setCustomer($customer)
            ->setCategory($category)
            ->setCreatedAt(new \DateTime())
            ->setCustomer($customer)
            ->setCreatedBy($userCustomer)
            ->setCurrentStatus(TicketEntity::STATUS_NEW)
            ->setLastAnswerAt(new \DateTime())
            ->setLastQuestionAt(new \DateTime())
            ->setVoidedAt(new \DateTime())
            ->setManagedBy($userManager)
            ->setTitle('testing ticket');

        $manager->persist($entity);

        $message = new TicketMessageEntity();

        $message
            ->setCreatedBy($userCustomer)
            ->setCreatedAt(new \DateTime())
            ->setTicket($entity)
            ->setText('testing text')
            ->setType(TicketMessageEntity::TYPE_QUESTION);

        $manager->persist($message);

        $answer = new TicketMessageEntity();

        $answer
            ->setCreatedBy($userManager)
            ->setCreatedAt(new \DateTime())
            ->setTicket($entity)
            ->setText('testing answer')
            ->setType(TicketMessageEntity::TYPE_ANSWER);

        $manager->persist($answer);

        $historyItem = new TicketHistoryEntity();

        $historyItem
            ->setCreatedBy($userCustomer)
            ->setCreatedAt(new \DateTime())
            ->setTicket($entity)
            ->setStatus(TicketEntity::STATUS_CLOSED);

        $manager->persist($historyItem);

        $otherCustomer = new CustomerEntity();

        $otherCustomer
            ->setName('testing ticket customer')
            ->setAllowItDepartment(false)
            ->setAllowBookerDepartment(false)
            ->setCurrentAgreement('testing');

        $manager->persist($otherCustomer);

        $otherCustomerUser = new UserEntity();

        $role = new UserRoleEntity();
        $role->setCode('ROLE_CUSTOMER_ADMIN');

        $otherCustomerUser
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket customer')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->addRole($role)
            ->setCustomer($otherCustomer)
            ->setEmail('ticket-customer+other@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($otherCustomerUser);

        $manager->flush();

        $this->addReference('ticket-category', $category);
        $this->addReference('ticket-customer', $customer);
        $this->addReference('ticket-other-customer', $otherCustomer);
        $this->addReference('ticket-manager', $userManager);
        $this->addReference('ticket-manager-other', $userManagerOther);
        $this->addReference('ticket-manager-denied', $userDeniedManager);
        $this->addReference('ticket-customer-user', $userCustomer);
        $this->addReference('ticket-other-customer-user', $otherCustomerUser);
        $this->addReference('ticket-message', $message);
        $this->addReference('ticket-answer', $answer);
        $this->addReference('ticket-history-item', $historyItem);
        $this->addReference('ticket', $entity);
    }
}
