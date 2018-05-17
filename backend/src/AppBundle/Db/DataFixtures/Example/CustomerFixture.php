<?php

namespace AppBundle\Db\DataFixtures\Example;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Фикстуры арендаторов для примера
 *
 * @package AppBundle\Db\DataFixtures\Example
 */
class CustomerFixture extends Fixture
{
    protected $data = [
        'name' => 'ООО "Рога и копыта"',
        'current_agreement' => ''
    ];

    public function load(ObjectManager $manager)
    {
        $customer1 = (new CustomerEntity())
            ->setName('ООО "Рога и копыта"')
            ->setCurrentAgreement('БЦ-ДОГ-1');

        $customer2 = (new CustomerEntity())
            ->setName('ЗАО "Ромашка"')
            ->setCurrentAgreement('БЦ-ДОГ-2');

        $customer3 = (new CustomerEntity())
            ->setName('ИП Иванов ИИ')
            ->setCurrentAgreement('БЦ-ДОГ-3');

        $manager->persist($customer1);
        $manager->persist($customer2);
        $manager->persist($customer3);

        $manager->flush();

        $this->addReference('customer1', $customer1);
        $this->addReference('customer2', $customer2);
        $this->addReference('customer3', $customer3);
    }
}
