<?php

namespace Tests\DataFixtures\ORM;

use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Фикстуры дополнительных услуг
 *
 * @package Tests\DataFixtures\ORM
 */
class ServiceTestFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // IT-аутсорсинг
        $it = new ServiceEntity();

        $it
            ->setId('it-department')
            ->setIsActive(true)
            ->setDescription('testing description')
            ->setTitle('IT-аутсорсинг')
            ->setEnableCustomerRole('ROLE_IT_CUSTOMER');

        $itTariff = new ServiceTariffEntity();
        
        $itTariff
            ->setIsActive(true)
            ->setTitle('testing rate')
            ->setService($it)
            ->setMonthlyCost(200);
        
        $manager->persist($it);
        $manager->persist($itTariff);
        $manager->flush();

        $this->addReference('service-it', $it);
        $this->addReference('service-it-tariff', $itTariff);
    }

    /**
     * Дополнительные услуги ни от чего не зависят, идут первыми в списке
     *
     * @return int
     */
    public function getOrder()
    {
        return 0;
    }
}
