<?php

namespace CustomerBundle\Tests\Entity\Repository;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\CustomerRepository;
use CustomerBundle\Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Class CustomerRepositoryTest
 * @package CustomerBundle\Tests\Entity\Repository
 */
class CustomerRepositoryTest extends WebTestCase
{
    /**
     * @var CustomerRepository
     */
    protected $repository;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            CustomerTestFixture::class
        ])->getReferenceRepository();

        $container = $this->getContainer();

        /** @var ObjectManager $em */
        $em = $container->get('doctrine')->getManager();

        $this->repository = $em->getRepository(CustomerEntity::class);
    }

    /**
     * @covers CustomerRepository::findOneById()
     */
    public function testFindOneById()
    {
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');

        $expected = $this->repository->findOneById($customer->getId());

        $this->assertInstanceOf(CustomerEntity::class, $expected);
        $this->assertEquals($expected->getId(), $customer->getId());

        $unexpected = $this->repository->findOneById(0);
        $this->assertNull($unexpected);
    }

    /**
     * @covers CustomerRepository::getTotalCount()
     */
    public function testGetTotalCount()
    {
        $expected = count($this->fixtures->getReferences());

        $result = $this->repository->getTotalCount();

        $this->assertEquals($expected, $result);
    }
}
