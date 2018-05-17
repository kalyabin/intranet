<?php

namespace AppBundle\Db\DataFixtures\Example;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use TicketBundle\Entity\TicketCategoryEntity;

/**
 * Категории тикетной системы
 *
 * @package AppBundle\Db\DataFixtures\Example
 */
class TicketCategoryFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $common = (new TicketCategoryEntity())
            ->setManagerRole('ROLE_ACCOUNT_MANAGEMENT')
            ->setName('Общие вопросы')
            ->setId('common');

        $finance = (new TicketCategoryEntity())
            ->setManagerRole('ROLE_FINANCE_MANAGEMENT')
            ->setCustomerRole('ROLE_FINANCE_CUSTOMER')
            ->setName('Финансовые вопросы')
            ->setId('finance-department');

        $maintaince = (new TicketCategoryEntity())
            ->setManagerRole('ROLE_MAINTAINCE_MANAGEMENT')
            ->setCustomerRole('ROLE_MAINTAINCE_CUSTOMER')
            ->setName('Служба эксплуатации')
            ->setId('maintaince-department');

        $it = (new TicketCategoryEntity())
            ->setManagerRole('ROLE_IT_MANAGEMENT')
            ->setCustomerRole('ROLE_IT_CUSTOMER')
            ->setName('IT-аутсорсинг')
            ->setId('it-department');

        $booker = (new TicketCategoryEntity())
            ->setManagerRole('ROLE_BOOKER_MANAGEMENT')
            ->setCustomerRole('ROLE_BOOKER_CUSTOMER')
            ->setName('SMART-бухгалтер')
            ->setId('booker-department');

        $manager->persist($common);
        $manager->persist($finance);
        $manager->persist($maintaince);
        $manager->persist($it);
        $manager->persist($booker);

        $manager->flush();
    }
}
