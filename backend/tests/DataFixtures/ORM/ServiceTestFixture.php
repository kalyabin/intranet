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
            ->setCustomerRole('ROLE_IT_CUSTOMER');
        $itTariff = new ServiceTariffEntity();
        $itTariff
            ->setIsActive(true)
            ->setTitle('testing rate')
            ->setService($it)
            ->setMonthlyCost(200);
        $it->addTariff($itTariff);

        // SMART-бухгалтер
        $booker = new ServiceEntity();
        $booker
            ->setId('booker-department')
            ->setIsActive(true)
            ->setDescription('testing description')
            ->setTitle('SMART-бухгалтер')
            ->setCustomerRole('ROLE_BOOKER_CUSTOMER');
        $bookerTariff = new ServiceTariffEntity();
        $bookerTariff
            ->setIsActive(true)
            ->setTitle('testing rate')
            ->setService($booker)
            ->setMonthlyCost(100);
        $booker->addTariff($bookerTariff);

        // неактивная услуга
        $inactive = new ServiceEntity();
        $inactive
            ->setId('inactive-department')
            ->setIsActive(false)
            ->setDescription('inactive department')
            ->setTitle('Inactive department')
            ->setCustomerRole('ROLE_INACTIVE_CUSTOMER');
        $inactiveTariff = new ServiceTariffEntity();
        $inactiveTariff
            ->setIsActive(false)
            ->setTitle('testing rate')
            ->setService($inactive)
            ->setMonthlyCost(300);
        $inactive->addTariff($inactiveTariff);


        $manager->persist($it);
        $manager->persist($itTariff);
        $manager->persist($booker);
        $manager->persist($bookerTariff);
        $manager->persist($inactive);
        $manager->persist($inactiveTariff);
        $manager->flush();

        $this->addReference('service-it', $it);
        $this->addReference('service-it-tariff', $itTariff);
        $this->addReference('service-booker', $booker);
        $this->addReference('service-booker-tariff', $bookerTariff);
        $this->addReference('service-inactive', $inactive);
        $this->addReference('service-inactive-tariff', $inactiveTariff);
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
