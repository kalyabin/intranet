<?php

namespace CustomerBundle\Tests\Entity;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Тестирование контрагентов
 *
 * @package CustomerBundle\Tests\Entity
 */
class CustomerEntityTest extends WebTestCase
{
    /**
     * @var ObjectManager
     */
    protected $em;

    public function setUp()
    {
        parent::setUp();

        $container = $this->getContainer();
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * @covers CustomerEntity::getId()
     * @covers CustomerEntity::getName()
     * @covers CustomerEntity::getCurrentAgreement()
     * @covers CustomerEntity::getAllowItDepartment()
     * @covers CustomerEntity::getAllowBookerDepartment()
     *
     * @covers CustomerEntity::setName()
     * @covers CustomerEntity::setCurrentAgreement()
     * @covers CustomerEntity::setAllowItDepartment()
     * @covers CustomerEntity::setAllowBookerDepartment()
     */
    public function testMe()
    {
        $name = 'testing name';
        $agreement = 'testing agreement';

        $entity = new CustomerEntity();

        $this->assertFalse($entity->getAllowBookerDepartment());
        $this->assertFalse($entity->getAllowItDepartment());

        $entity
            ->setName($name)
            ->setCurrentAgreement($agreement)
            ->setAllowItDepartment(true)
            ->setAllowBookerDepartment(true);

        $this->assertEquals($name, $entity->getName());
        $this->assertEquals($agreement, $entity->getCurrentAgreement());
        $this->assertTrue($entity->getAllowItDepartment());
        $this->assertTrue($entity->getAllowBookerDepartment());

        $this->em->persist($entity);
        $this->em->flush();

        $this->assertGreaterThan(0, $entity->getId());
    }
}
