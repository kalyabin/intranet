<?php

namespace AppBundle\Db\DataFixtures\Example;

use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ServiceTariffFixture
 * @package AppBundle\Db\DataFixtures\Example
 */
class ServiceTariffFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $itTariff1 = (new ServiceTariffEntity())
            ->setTitle('Парк до 10 машин')
            ->setIsActive(true)
            ->setMonthlyCost(10000);

        $itTariff2 = (new ServiceTariffEntity())
            ->setTitle('Парк до 20 машин')
            ->setIsActive(true)
            ->setMonthlyCost(15000);

        $itTariff3 = (new ServiceTariffEntity())
            ->setTitle('Парк до 20 машин + 1С')
            ->setIsActive(true)
            ->setMonthlyCost(20000);

        $manager->persist($itTariff1);
        $manager->persist($itTariff2);
        $manager->persist($itTariff3);

        $manager->flush();

        $this->addReference('it_tariff_1', $itTariff1);
        $this->addReference('it_tariff_2', $itTariff2);
        $this->addReference('it_tariff_3', $itTariff3);
    }

    public function getDependencies()
    {
        return [
            ServiceFixture::class,
        ];
    }
}
