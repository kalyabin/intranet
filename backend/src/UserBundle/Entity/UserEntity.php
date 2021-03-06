<?php

namespace UserBundle\Entity;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use UserBundle\Validator\Constraints\UserEmail;

/**
 * Модель авторизованного пользователя
 *
 * @package UserBundle\Entity
 *
 * @ORM\Entity(repositoryClass="UserBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 */
class UserEntity implements AdvancedUserInterface, \JsonSerializable
{
    /**
     * Статус пользователя - активен
     */
    const STATUS_ACTIVE = 1;

    /**
     * Статус пользователя - требуется активация
     */
    const STATUS_NEED_ACTIVATION = 0;

    /**
     * Статус пользователя - заблокирован
     */
    const STATUS_LOCKED = -1;

    /**
     * Тип пользователя - сотрудник
     */
    const TYPE_MANAGER = 'manager';

    /**
     * Тип пользователя - арендатор
     */
    const TYPE_CUSTOMER = 'customer';

    /**
     * @ORM\Column(type="bigint", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer Идентификатор пользователя
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", name="created_at", nullable=false)
     *
     * @var \DateTime Дата создания пользователя
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", name="last_login_at", nullable=true)
     *
     * @var \DateTime Дата последней авторизации
     */
    private $lastLoginAt;

    /**
     * @ORM\Column(type="string", length=50, name="user_type", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     * @Assert\Choice(callback="getUserTypes", strict=true)
     *
     * @var string Тип пользователя
     */
    private $userType;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     *
     * @var string Имя пользователя
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=false, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(max=100)
     * @UserEmail(needExists=false, message="Такой e-mail уже занят")
     *
     * @var string E-mail пользователя
     */
    private $email;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @var boolean Флаг указывающий на временный пароль
     */
    private $isTemporaryPassword = false;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     *
     * @var string Хеш пароля
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=10, nullable=false)
     *
     * @var string Соль для кодирования пароля
     */
    private $salt;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     * @Assert\Choice(callback="getStatusesList", strict=true)
     *
     * @var integer Статус пользователя (на основе констант self::STATUS_*)
     */
    private $status = 0;

    /**
     * @ORM\OneToMany(targetEntity="UserCheckerEntity", mappedBy="user", cascade={"persist", "remove"})
     *
     * @var UserCheckerEntity[] Привязка к модели проверки пользователя
     */
    private $checker;

    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\UserRoleEntity", mappedBy="user", cascade={"persist", "remove"})
     *
     * @var UserRoleEntity[] Привязка к ролям пользователя
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\CustomerEntity", inversedBy="user")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var CustomerEntity Привязка к контрагенту, если тип пользователя - контрагент
     */
    private $customer;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->checker = new ArrayCollection();
        $this->role = new ArrayCollection();
    }

    /**
     * Перечисление статусов
     *
     * @return integer[]
     */
    public static function getStatusesList(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_LOCKED, self::STATUS_NEED_ACTIVATION];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Установка даты создания пользователя
     *
     * @param \DateTime $createdAt
     *
     * @return UserEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Получить дату создания пользователя
     *
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Установка даты последней авторизации
     *
     * @param \DateTime $lastLoginAt
     *
     * @return UserEntity
     */
    public function setLastLoginAt(\DateTime $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    /**
     * Получить дату последней авторизации
     *
     * @return \DateTime|null
     */
    public function getLastLoginAt(): ?\DateTime
    {
        return $this->lastLoginAt;
    }

    /**
     * Установить тип пользователя
     *
     * @param string $type
     *
     * @return UserEntity
     */
    public function setUserType(?string $type): self
    {
        $this->userType = $type;

        return $this;
    }

    /**
     * Получить тип пользователя
     *
     * @return string
     */
    public function getUserType(): ?string
    {
        return $this->userType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return UserEntity
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserEntity
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return boolean
     */
    public function getIsTemporaryPassword(): ?bool
    {
        return $this->isTemporaryPassword;
    }

    /**
     * @param boolean $isTemporaryPassword
     *
     * @return UserEntity
     */
    public function setIsTemporaryPassword(?bool $isTemporaryPassword): UserEntity
    {
        $this->isTemporaryPassword = (bool) $isTemporaryPassword;
        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return UserEntity
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Сгенерировать соль для пароля
     *
     * @return UserEntity
     */
    public function generateSalt(): self
    {
        if (empty($this->salt)) {
            $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $randStringLen = 10;

            $randString = '';
            for ($i = 0; $i < $randStringLen; $i++) {
                $randString .= $charset[mt_rand(0, strlen($charset) - 1)];
            }

            $this->salt = $randString;
        }

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return UserEntity
     */
    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return UserEntity
     */
    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Get checker
     *
     * @return Collection
     */
    public function getChecker(): Collection
    {
        return $this->checker;
    }

    /**
     * Получить имя пользователя для логина
     *
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->getEmail();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * Коды ролей пользователя
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $result = [];

        foreach ($this->role as $roleEntity) {
            $result[] = $roleEntity->getCode();
        }

        return $result;
    }

    /**
     * Проверка активности
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Проверка необходимости активации
     *
     * @return bool
     */
    public function isNeedActivation(): bool
    {
        return $this->status == self::STATUS_NEED_ACTIVATION;
    }

    /**
     * Проверка заблокированности
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->status == self::STATUS_LOCKED;
    }

    /**
     * Не просрочен ли доступ
     *
     * @return bool
     */
    public function isAccountNonExpired(): bool
    {
        return true;
    }

    /**
     * Не заблокирован ли аккаунт
     *
     * @return bool
     */
    public function isAccountNonLocked(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Не был ли пользователь заблокирован во время работы
     *
     * @return bool
     */
    public function isCredentialsNonExpired(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Не требуется ли активация аккаунта
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Add checker
     *
     * @param \UserBundle\Entity\UserCheckerEntity $checker
     *
     * @return UserEntity
     */
    public function addChecker(\UserBundle\Entity\UserCheckerEntity $checker): self
    {
        $this->checker[] = $checker;

        return $this;
    }

    /**
     * Remove checker
     *
     * @param \UserBundle\Entity\UserCheckerEntity $checker
     */
    public function removeChecker(\UserBundle\Entity\UserCheckerEntity $checker)
    {
        $this->checker->removeElement($checker);
    }

    /**
     * Получить код проверки по типу
     *
     * @param string $type
     *
     * @return null|UserCheckerEntity
     */
    public function getCheckerByType(string $type): ?UserCheckerEntity
    {
        foreach ($this->checker as $checker) {
            if ($checker->getType() == $type) {
                return $checker;
            }
        }
        return null;
    }

    /**
     * Удалить код проверки по типу
     *
     * @param string $type Тип кода проверки
     */
    public function removeCheckerByType(string $type)
    {
        foreach ($this->checker as $checker) {
            if ($checker->getType() == $type) {
                $this->removeChecker($checker);
            }
        }
    }

    /**
     * Очистка всех ролей
     *
     * @return UserEntity
     */
    public function clearRoles(): self
    {
        $this->role->clear();

        return $this;
    }

    /**
     * Add role
     *
     * @param \UserBundle\Entity\UserRoleEntity $role
     *
     * @return UserEntity
     */
    public function addRole(\UserBundle\Entity\UserRoleEntity $role): self
    {
        $role->setUser($this);
        $this->role[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \UserBundle\Entity\UserRoleEntity $role
     */
    public function removeRole(\UserBundle\Entity\UserRoleEntity $role)
    {
        $this->role->removeElement($role);
    }

    /**
     * Get role
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRole(): Collection
    {
        return $this->role;
    }

    /**
     * Установить контрагента
     *
     * @param CustomerEntity $customer
     *
     * @return UserEntity
     */
    public function setCustomer(?CustomerEntity $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * Получить контрагента
     *
     * @return CustomerEntity|null
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * Проверка поля "контрагент".
     *
     * Пользователи типа "арендатор" должны быть привязаны к контрагенту.
     * Пользователи типа "сотрудник" не должны быть привязаны к контрагенту.
     *
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     */
    public function checkCustomer(ExecutionContextInterface $context)
    {
        if ($this->userType == self::TYPE_CUSTOMER && !$this->customer instanceof CustomerEntity) {
            $context->addViolation('Привязка к контрагенту обязательна для типа пользователя "Арендатор"');
        } elseif ($this->userType != self::TYPE_CUSTOMER && $this->customer) {
            $context->addViolation('Пользователи с типом "Сотрудник" не должны быть привязаны к контрагенту');
        }
    }

    /**
     * Получить типы пользователей
     *
     * @return string[]
     */
    public function getUserTypes(): array
    {
        return [self::TYPE_CUSTOMER, self::TYPE_MANAGER];
    }

    /**
     * Сериализация объекта для вывода в REST
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'userType' => $this->getUserType(),
            'customer' => $this->getCustomer() ? $this->getCustomer() : null
        ];
    }
}
