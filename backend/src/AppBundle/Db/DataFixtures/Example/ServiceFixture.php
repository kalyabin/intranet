<?php

namespace AppBundle\Db\DataFixtures\Example;

use CustomerBundle\Entity\ServiceEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Услуги
 *
 * @package AppBundle\Db\DataFixtures\Example
 */
class ServiceFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $service1 = (new ServiceEntity())
            ->setTitle('IT-атсорсинг')
            ->setDescription('Удаленный системный администратор')
            ->setIsActive(true)
            ->setId('it-department')
            ->setCustomerRole('ROLE_IT_CUSTOMER');

        $service2 = (new ServiceEntity())
            ->setTitle('SMART-бухгалтер')
            ->setDescription('Удаленный бухгалтер')
            ->setIsActive(true)
            ->setId('booker-department')
            ->setCustomerRole('ROLE_BOOKER_CUSTOMER');

        $service3 = (new ServiceEntity())
            ->setTitle('Хаус-мастер')
            ->setDescription('Электрики и плотники по вызову')
            ->setIsActive(true)
            ->setId('maintaince-department')
            ->setCustomerRole('ROLE_MAINTAINCE_CUSTOMER');

        $manager->persist($service1);
        $manager->persist($service2);
        $manager->persist($service3);

        $manager->flush();

        $this->addReference('it-department', $service1);
        $this->addReference('booker-department', $service2);
        $this->addReference('maintaince-department', $service3);
    }
}
