<?php

namespace CustomerBundle\Tests\Entity;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceHistoryEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;

/**
 * Тестирование элемента истории в активациях услуг
 *
 * @package CustomerBundle\Tests\Entity
 */
class ServiceHistoryEntityTest extends WebTestCase
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
            ServiceTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * @covers ServiceHistoryEntity::getId()
     * @covers ServiceHistoryEntity::getCustomer()
     * @covers ServiceHistoryEntity::getService()
     * @covers ServiceHistoryEntity::getTariff()
     * @covers ServiceHistoryEntity::getCreatedAt()
     * @covers ServiceHistoryEntity::getVoidedAt()
     *
     * @covers ServiceHistoryEntity::setCustomer()
     * @covers ServiceHistoryEntity::setService()
     * @covers ServiceHistoryEntity::setTariff()
     * @covers ServiceHistoryEntity::setCreatedAt()
     * @covers ServiceHistoryEntity::setVoidedAt()
     */
    public function testMe()
    {
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-it');
        /** @var ServiceTariffEntity $tariff */
        $tariff = $this->fixtures->getReference('service-it-tariff');

        $entity = new ServiceHistoryEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getCustomer());
        $this->assertNull($entity->getService());
        $this->assertNull($entity->getTariff());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getVoidedAt());

        $entity
            ->setCustomer($customer)
            ->setService($service)
            ->setTariff($tariff)
            ->setCreatedAt(new \DateTime())
            ->setVoidedAt(new \DateTime());

        $this->assertInstanceOf(CustomerEntity::class, $entity->getCustomer());
        $this->assertInstanceOf(ServiceEntity::class, $entity->getService());
        $this->assertInstanceOf(ServiceTariffEntity::class, $entity->getTariff());
        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $entity->getVoidedAt());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertGreaterThan(0, $entity->getId());

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
