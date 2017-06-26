<?php

namespace CustomerBundle\Tests\Entity\Repository;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\ServiceHistoryRepository;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceHistoryEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;

/**
 * Тестирование репозитория получения истории активированных услуг для арендатора
 *
 * @package CustomerBundle\Tests\Entity\Repository
 */
class ServiceHistoryRepositoryTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ServiceHistoryRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class
        ])->getReferenceRepository();
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $this->entityManager->getRepository(ServiceHistoryEntity::class);
        $this->assertInstanceOf(ServiceHistoryRepository::class, $this->repository);
    }

    /**
     * @covers ServiceHistoryRepository::findOpenedByCustomer()
     */
    public function testFindOpenedByCustomer()
    {
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-it');
        /** @var ServiceTariffEntity $tariff */
        $tariff = $this->fixtures->getReference('service-it-tariff');
        /** @var CustomerEntity $expectedCustomer */
        $expectedCustomer = $this->fixtures->getReference('all-customer');
        /** @var CustomerEntity $unexpectedCustomer */
        $unexpectedCustomer = $this->fixtures->getReference('none-customer');

        // зафиксировать элемент истории у контрагента
        $history = new ServiceHistoryEntity();
        $history
            ->setService($service)
            ->setTariff($tariff)
            ->setCustomer($expectedCustomer)
            ->setCreatedAt(new \DateTime());
        $this->entityManager->persist($history);
        $this->entityManager->flush();

        // проверить что по услуге из репозитория будет получен именно этот элемент
        $expectedList = $this->repository->findOpenedByCustomer($expectedCustomer, $service);
        $this->assertNotEmpty($expectedList);
        $this->assertCount(1, $expectedList);
        $this->assertContainsOnlyInstancesOf(ServiceHistoryEntity::class, $expectedList);
        $this->assertEquals($history->getId(), $expectedList[0]->getId());

        // закрыть услугу, убедиться что она не будет возвращена в открытых
        $history->setVoidedAt(new \DateTime());
        $this->entityManager->persist($history);
        $this->entityManager->flush();
        $this->assertEmpty($this->repository->findOpenedByCustomer($expectedCustomer, $service));

        // у контрагента без доп. услуг не должно быть истории вообще
        $this->assertEmpty($this->repository->findOpenedByCustomer($unexpectedCustomer, $service));
    }
}
