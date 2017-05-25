<?php

namespace TicketBundle\Tests\DataFixtures\ORM;


use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
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
            ->setManagerRole('SUPERADMIN')
            ->setCustomerRole('CUSTOMER_ADMIN')
            ->setId('testing');

        $manager->persist($category);

        // пользователи
        $role = new UserRoleEntity();
        $role->setCode('SUPERADMIN');

        $userManager = new UserEntity();
        $userManager
            ->setName('testing ticket manager')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->addRole($role)
            ->setEmail('ticket-manager@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($userManager);

        $role = new UserRoleEntity();
        $role->setCode('SUPERADMIN');

        $userManagerOther = new UserEntity();
        $userManagerOther
            ->setName('testing ticket manager second')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->addRole($role)
            ->setEmail('ticket-manager-other@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($userManagerOther);

        $customer = new CustomerEntity();

        $customer
            ->setName('testing ticket customer')
            ->setAllowBookerDepartment(true)
            ->setAllowBookerDepartment(true)
            ->setCurrentAgreement('testing');

        $manager->persist($customer);

        $manager->flush();

        $userCustomer = new UserEntity();

        $role = new UserRoleEntity();
        $role->setCode('CUSTOMER_ADMIN');

        $userCustomer
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

        $manager->flush();

        $this->addReference('ticket-category', $category);
        $this->addReference('ticket-customer', $customer);
        $this->addReference('ticket-manager', $userManager);
        $this->addReference('ticket-manager-other', $userManagerOther);
        $this->addReference('ticket-customer-user', $userCustomer);
        $this->addReference('ticket-message', $message);
        $this->addReference('ticket-answer', $answer);
        $this->addReference('ticket', $entity);
    }
}
