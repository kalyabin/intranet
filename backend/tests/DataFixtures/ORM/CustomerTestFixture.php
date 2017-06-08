<?php

namespace Tests\DataFixtures\ORM;


use CustomerBundle\Entity\CustomerEntity;
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
        // контрагент с доступом ко всем департаментам
        $allowAllCustomer = new CustomerEntity();

        $allowAllCustomer
            ->setName('testing customer')
            ->setCurrentAgreement('testing agreement')
            ->setAllowBookerDepartment(true)
            ->setAllowItDepartment(true);

        // контрагент с доступом к IT-департаменту
        $allowItCustomer = new CustomerEntity();

        $allowItCustomer
            ->setName('testing customer')
            ->setCurrentAgreement('testing agreement')
            ->setAllowItDepartment(true);

        // контрагент с доступом к SMART-бухгалтеру
        $allowBookerCustomer = new CustomerEntity();

        $allowBookerCustomer
            ->setName('testing customer')
            ->setCurrentAgreement('testing agreement')
            ->setAllowBookerDepartment(true);

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
