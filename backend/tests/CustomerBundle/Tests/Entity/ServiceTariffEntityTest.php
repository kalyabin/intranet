<?php

namespace CustomerBundle\Tests\Entity;


use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\ServiceTestFixture;

/**
 * Тестирование тарифов по доп. услугам
 *
 * @package CustomerBundle\Tests\Entity
 */
class ServiceTariffEntityTest extends WebTestCase
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
        $this->fixtures = $this->loadFixtures([ServiceTestFixture::class])->getReferenceRepository();
    }

    /**
     * @covers ServiceTariffEntity::getId()
     * @covers ServiceTariffEntity::getIsActive()
     * @covers ServiceTariffEntity::getTitle()
     * @covers ServiceTariffEntity::getService()
     * @covers ServiceTariffEntity::getMonthlyCost()
     *
     * @covers ServiceTariffEntity::getIsActive()
     * @covers ServiceTariffEntity::setTitle()
     * @covers ServiceTariffEntity::setService()
     * @covers ServiceTariffEntity::setMonthlyCost()
     */
    public function testMe()
    {
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-it');

        $entity = new ServiceTariffEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getIsActive());
        $this->assertNull($entity->getTitle());
        $this->assertNull($entity->getService());
        $this->assertNull($entity->getMonthlyCost());

        $title = 'testing rate';
        $cost = 100500;

        $entity
            ->setTitle($title)
            ->setIsActive(true)
            ->setService($service)
            ->setMonthlyCost($cost);

        $this->assertEquals($title, $entity->getTitle());
        $this->assertTrue($entity->getIsActive());
        $this->assertInstanceOf(ServiceEntity::class, $entity->getService());
        $this->assertEquals($cost, $entity->getMonthlyCost());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertGreaterThan(0, $entity->getId());

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
