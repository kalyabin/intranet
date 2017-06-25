<?php

namespace CustomerBundle\Tests\Entity;


use CustomerBundle\Entity\ServiceEntity;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\ServiceTestFixture;

/**
 * Тестирование дополнительных услуг
 *
 * @package CustomerBundle\Tests\Entity
 */
class ServiceEntityTest extends WebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures([ServiceTestFixture::class]);

        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @covers ServiceEntity::getId()
     * @covers ServiceEntity::getIsActive()
     * @covers ServiceEntity::getTitle()
     * @covers ServiceEntity::getDescription()
     * @covers ServiceEntity::getCustomerRole()
     *
     * @covers ServiceEntity::setId()
     * @covers ServiceEntity::setIsActive()
     * @covers ServiceEntity::setTitle()
     * @covers ServiceEntity::setDescription()
     * @covers ServiceEntity::setCustomerRole()
     */
    public function testMe()
    {
        $entity = new ServiceEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getIsActive());
        $this->assertNull($entity->getTitle());
        $this->assertNull($entity->getDescription());
        $this->assertNull($entity->getCustomerRole());

        $id = 'test-department';
        $title = 'TEST-аутсорсинг';
        $description = 'testing description';
        $role = 'ROLE_IT_CUSTOMER';

        $entity
            ->setId($id)
            ->setIsActive(true)
            ->setTitle($title)
            ->setDescription($description)
            ->setCustomerRole($role);

        $this->assertEquals($id, $entity->getId());
        $this->assertTrue($entity->getIsActive());
        $this->assertEquals($title, $entity->getTitle());
        $this->assertEquals($description, $entity->getDescription());
        $this->assertEquals($role, $entity->getCustomerRole());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
