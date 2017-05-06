<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 02.05.17
 * Time: 23:00
 */

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Validator\Constraints\UserRole;

/**
 * Роли пользователя
 *
 * @ORM\Entity()
 * @ORM\Table(name="user_role")
 *
 * @package UserBundle\Entity
 */
class UserRoleEntity
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity", inversedBy="role")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var UserEntity Привязка к пользователю
     */
    private $user;

    /**
     * @ORM\Id()
     * @ORM\Column(type="text", name="code", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="50")
     * @UserRole(message="Несуществующая роль для данного типа пользователей", userTypeCallback="getUserType")
     *
     * @var string Код роли
     */
    private $code;

    /**
     * Set code
     *
     * @param string $code
     *
     * @return UserRoleEntity
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\UserEntity $user
     *
     * @return UserRoleEntity
     */
    public function setUser(\UserBundle\Entity\UserEntity $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\UserEntity
     */
    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    /**
     * Получить тип пользователя для валидации роли
     *
     * @return string
     */
    public function getUserType(): ?string
    {
        return $this->getUser() ? $this->getUser()->getUserType() : '';
    }
}
