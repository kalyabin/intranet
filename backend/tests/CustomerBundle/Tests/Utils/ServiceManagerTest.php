<?php

namespace CustomerBundle\Tests\Utils;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\ServiceHistoryRepository;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceHistoryEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use CustomerBundle\Event\ServiceActivatedEvent;
use CustomerBundle\Event\ServiceDeactivatedEvent;
use CustomerBundle\Utils\ServiceManager;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование менеджера управления услугами для арендатора
 *
 * @package CustomerBundle\Tests\Utils
 */
class ServiceManagerTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ServiceManager
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            UserTestFixture::class,
            CustomerTestFixture::class,
            ServiceTestFixture::class,
        ])->getReferenceRepository();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->eventDispatcher = $this->getContainer()->get('event_dispatcher');

        $this->manager = new ServiceManager($entityManager, $eventDispatcher);
    }

    /**
     * @covers ServiceManager::serviceIsAssigned()
     */
    public function testServiceIsAssigned()
    {
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-it');
        /** @var CustomerEntity $expectedCustomer */
        $expectedCustomer = $this->fixtures->getReference('all-customer');
        /** @var CustomerEntity $unexpectedCustomer */
        $unexpectedCustomer = $this->fixtures->getReference('none-customer');

        $this->assertTrue($this->manager->serviceIsAssigned($expectedCustomer, $service));
        $this->assertFalse($this->manager->serviceIsAssigned($unexpectedCustomer, $service));
    }

    /**
     * @covers ServiceManager::activateService()
     */
    public function testActivateService()
    {
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-it');
        /** @var ServiceTariffEntity $tariff */
        $tariff = $this->fixtures->getReference('service-it-tariff');
        /** @var CustomerEntity $unexpectedCustomer */
        $unexpectedCustomer = $this->fixtures->getReference('all-customer');
        /** @var CustomerEntity $expectedCustomer */
        $expectedCustomer = $this->fixtures->getReference('none-customer');

        // проверка если услуга уже активирована
        $this->assertFalse($this->manager->activateService($unexpectedCustomer, $service, $tariff));

        // проверка активации услуги
        // перед этим услуги должны отсутствовать
        $this->assertEmpty($expectedCustomer->getService()->getValues());
        // подписка на событие
        $eventDispatched = false;
        $this->eventDispatcher->addListener(ServiceActivatedEvent::NAME, function($event) use (&$eventDispatched, $expectedCustomer, $service, $tariff) {
            /** @var ServiceActivatedEvent $event */
            $this->assertInstanceOf(ServiceActivatedEvent::class, $event);
            $this->assertInstanceOf(CustomerEntity::class, $event->getCustomer());
            $this->assertEquals($event->getCustomer()->getId(), $expectedCustomer->getId());
            $this->assertInstanceOf(ServiceEntity::class, $event->getService());
            $this->assertEquals($event->getService()->getId(), $service->getId());
            $this->assertInstanceOf(ServiceTariffEntity::class, $event->getTariff());
            $this->assertEquals($event->getTariff()->getId(), $tariff->getId());
            $eventDispatched = true;
        });

        $result = $this->manager->activateService($expectedCustomer, $service, $tariff);
        $this->assertTrue($result);
        $this->assertTrue($eventDispatched);

        // проверить историю, она должна создаться
        /** @var ServiceHistoryRepository $repository */
        $repository = $this->entityManager->getRepository(ServiceHistoryEntity::class);
        $list = $repository->findOpenedByCustomer($expectedCustomer, $service);
        $this->assertCount(1, $list);
        $this->assertNull($list[0]->getVoidedAt());
        $this->assertNotEmpty($expectedCustomer->getService()->getValues());
    }

    /**
     * @covers ServiceManager::deactivateService()
     * @depends testActivateService
     */
    public function testDeactivateService()
    {
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-booker');
        /** @var ServiceTariffEntity $tariff */
        $tariff = $this->fixtures->getReference('service-booker-tariff');
        /** @var CustomerEntity $expectedCustomer */
        $expectedCustomer = $this->fixtures->getReference('none-customer');

        // проверка деактивации ранее неактивированной услуги
        $this->assertFalse($this->manager->deactivateService($expectedCustomer, $service));

        // подключить услугу
        $this->assertTrue($this->manager->activateService($expectedCustomer, $service, $tariff));
        // проверка деактивации ранее активированной услуги
        // подписка на событие
        $eventDispatched = false;
        $this->eventDispatcher->addListener(ServiceDeactivatedEvent::NAME, function($event) use (&$eventDispatched, $expectedCustomer, $service) {
            /** @var ServiceDeactivatedEvent $event */
            $this->assertInstanceOf(ServiceDeactivatedEvent::class, $event);
            $this->assertInstanceOf(CustomerEntity::class, $event->getCustomer());
            $this->assertEquals($event->getCustomer()->getId(), $expectedCustomer->getId());
            $this->assertInstanceOf(ServiceEntity::class, $event->getService());
            $this->assertEquals($event->getService()->getId(), $service->getId());
            $eventDispatched = true;
        });

        $result = $this->manager->deactivateService($expectedCustomer, $service);
        $this->assertTrue($result);
        $this->assertTrue($eventDispatched);

        // получить элемент истории, проверить что заполнилась дата высвобождения услуги
        /** @var ServiceHistoryRepository $repository */
        $repository = $this->entityManager->getRepository(ServiceHistoryEntity::class);
        $list = $repository->findAllByCustomer($expectedCustomer, $service);
        $this->assertCount(1, $list);
        $this->assertInstanceOf(\DateTime::class, $list[0]->getVoidedAt());
        $this->assertEmpty($expectedCustomer->getService()->getValues());
    }
}
