<?php

namespace Tests\DataFixtures\ORM;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\ServiceActivatedEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceHistoryEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Фикстуры контрагентов для тестирования
 *
 * @package CustomerBundle\Tests\DataFixtures\ORM
 */
class CustomerTestFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        /** @var ServiceEntity $itService */
        $itService = $this->getReference('service-it');
        /** @var ServiceTariffEntity $itTariff */
        $itTariff = $this->getReference('service-it-tariff');
        /** @var ServiceEntity $bookerService */
        $bookerService = $this->getReference('service-booker');
        /** @var ServiceTariffEntity $bookerTariff */
        $bookerTariff = $this->getReference('service-booker-tariff');

        // контрагент с доступом ко всем департаментам
        $allowAllCustomer = new CustomerEntity();
        $allowAllCustomer
            ->setName('testing customer')
            ->setCurrentAgreement('testing agreement');
        $service = new ServiceActivatedEntity();
        $service
            ->setService($itService)
            ->setCreatedAt(new \DateTime())
            ->setCustomer($allowAllCustomer);
        $allowAllCustomer->addService($service);
        $service = new ServiceActivatedEntity();
        $service
            ->setService($bookerService)
            ->setCreatedAt(new \DateTime())
            ->setCustomer($allowAllCustomer);
        $allowAllCustomer->addService($service);

        // контрагент с доступом к IT-департаменту
        $allowItCustomer = new CustomerEntity();
        $allowItCustomer
            ->setName('testing customer')
            ->setCurrentAgreement('testing agreement');
        $service = new ServiceActivatedEntity();
        $service
            ->setService($itService)
            ->setCreatedAt(new \DateTime())
            ->setCustomer($allowItCustomer);
        $allowItCustomer->addService($service);

        // контрагент с доступом к SMART-бухгалтеру
        $allowBookerCustomer = new CustomerEntity();
        $allowBookerCustomer
            ->setName('testing customer')
            ->setCurrentAgreement('testing agreement');
        $service = new ServiceActivatedEntity();
        $service
            ->setService($bookerService)
            ->setCreatedAt(new \DateTime())
            ->setCustomer($allowBookerCustomer);
        $allowBookerCustomer->addService($service);

        // контрагент без доступа к дополнительным услугам
        $allowNoneCustomer = new CustomerEntity();
        $allowNoneCustomer
            ->setName('testing customer')
            ->setCurrentAgreement('testing agreement');

        $manager->persist($allowAllCustomer);
        $manager->persist($allowItCustomer);
        $manager->persist($allowBookerCustomer);
        $manager->persist($allowNoneCustomer);

        $manager->flush();

        $this->addReference('all-customer', $allowAllCustomer);
        $this->addReference('it-customer', $allowItCustomer);
        $this->addReference('booker-customer', $allowBookerCustomer);
        $this->addReference('none-customer', $allowNoneCustomer);
    }

    /**
     * От контрагентов зависят пользователи
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
