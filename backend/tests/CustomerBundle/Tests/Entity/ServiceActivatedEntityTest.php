<?php

namespace CustomerBundle\Tests\Entity;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\ServiceActivatedEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;

/**
 * Тестирование модели активированной услуги
 *
 * @package CustomerBundle\Tests\Entity
 */
class ServiceActivatedEntityTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->fixtures = $this->loadFixtures([
            CustomerTestFixture::class,
            ServiceTestFixture::class
        ])->getReferenceRepository();
    }

    /**
     * @covers ServiceActivatedEntity::getCustomer()
     * @covers ServiceActivatedEntity::getService()
     * @covers ServiceActivatedEntity::getTariff()
     * @covers ServiceActivatedEntity::getCreatedAt()
     *
     * @covers ServiceActivatedEntity::setCustomer()
     * @covers ServiceActivatedEntity::setService()
     * @covers ServiceActivatedEntity::setTariff()
     * @covers ServiceActivatedEntity::setCreatedAt()
     */
    public function testMe()
    {
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-it');
        /** @var ServiceTariffEntity $tariff */
        $tariff = $this->fixtures->getReference('service-it-tariff');

        $entity = new ServiceActivatedEntity();

        $this->assertNull($entity->getCustomer());
        $this->assertNull($entity->getService());
        $this->assertNull($entity->getTariff());
        $this->assertNull($entity->getCreatedAt());

        $entity
            ->setCustomer($customer)
            ->setService($service)
            ->setTariff($tariff)
            ->setCreatedAt(new \DateTime());

        $this->assertInstanceOf(CustomerEntity::class, $entity->getCustomer());
        $this->assertInstanceOf(ServiceEntity::class, $entity->getService());
        $this->assertInstanceOf(ServiceTariffEntity::class, $entity->getTariff());
        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
