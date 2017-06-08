<?php

namespace UserBunde\Tests\Entity;


use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование класса UserEntity
 */
class UserEntityTest extends WebTestCase
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Тестирование сохранения модели
     *
     * @covers UserEntity::setEmail()
     * @covers UserEntity::setName()
     * @covers UserEntity::setPassword()
     * @covers UserEntity::setStatus()
     * @covers UserEntity::setSalt()
     * @covers UserEntity::setUserType()
     * @covers UserEntity::generateSalt()
     *
     * @covers UserEntity::getEmail()
     * @covers UserEntity::getName()
     * @covers UserEntity::getPassword()
     * @covers UserEntity::getStatus()
     * @covers UserEntity::getSalt()
     * @covers UserEntity::getUserType()
     */
    public function testMe()
    {
        $model = new UserEntity();

        $email = 'test@test.ru';
        $name = 'Test name';
        $password = 'test password';
        $status = UserEntity::STATUS_ACTIVE;
        $type = UserEntity::TYPE_CUSTOMER;

        $this->assertNull($model->getEmail());
        $this->assertNull($model->getName());
        $this->assertNull($model->getPassword());
        $this->assertNull($model->getUserType());
        $this->assertNull($model->getCustomer());
        $this->assertNull($model->getCreatedAt());
        $this->assertNull($model->getLastLoginAt());

        $model
            ->setCreatedAt(new \DateTime())
            ->setLastLoginAt(new \DateTime())
            ->setEmail($email)
            ->setName($name)
            ->setPassword($password)
            ->setStatus($status)
            ->setUserType($type);

        $this->assertInstanceOf(\DateTime::class, $model->getLastLoginAt());
        $this->assertInstanceOf(\DateTime::class, $model->getCreatedAt());
        $this->assertEmpty($model->getRoles());
        $this->assertEquals($email, $model->getEmail());
        $this->assertEquals($name, $model->getName());
        $this->assertEquals($password, $model->getPassword());
        $this->assertEquals($status, $model->getStatus());
        $this->assertEquals($type, $model->getUserType());

        $model->setSalt('test salt');
        $this->assertEquals('test salt', $model->getSalt());

        $model->setSalt('');
        $model->generateSalt();

        $this->assertNotEmpty($model->getSalt());
        $this->assertNotEquals('test salt', $model->getSalt());

        $this->em->persist($model);

        $this->em->flush();

        $this->assertGreaterThan(0, $model->getId());
    }
}
