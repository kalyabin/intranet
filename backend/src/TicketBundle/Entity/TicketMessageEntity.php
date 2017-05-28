<?php

namespace TicketBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\UserEntity;

/**
 * Модель сообщения по тикету
 *
 * @ORM\Entity()
 * @ORM\Table(name="ticket_message")
 *
 * @package TicketBundle\Entity
 */
class TicketMessageEntity implements \JsonSerializable
{
    /**
     * Тип сообщения - ответ
     */
    const TYPE_ANSWER = 'answer';

    /**
     * Тип сообщения - вопрос
     */
    const TYPE_QUESTION = 'question';

    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer Идентификатор
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime Дата создания сообщения
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @var UserEntity Пользователь, создавший сообщение
     */
    protected $createdBy;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     *
     * @Assert\Choice(choices={TicketMessageEntity::TYPE_ANSWER, TicketMessageEntity::TYPE_QUESTION})
     *
     * @var string Тип сообщения: ответ или вопрос
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="TicketBundle\Entity\TicketEntity", inversedBy="message")
     * @ORM\JoinColumn(name="ticket", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank()
     *
     * @var TicketEntity Привязка к тикету
     */
    protected $ticket;

    /**
     * @ORM\Column(type="text", nullable=false, name="`text`")
     *
     * @Assert\NotBlank()
     *
     * @var string Текст сообщения
     */
    protected $text;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TicketMessageEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return TicketMessageEntity
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return TicketMessageEntity
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\UserEntity $createdBy
     *
     * @return TicketMessageEntity
     */
    public function setCreatedBy(\UserBundle\Entity\UserEntity $createdBy = null): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \UserBundle\Entity\UserEntity
     */
    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    /**
     * Set ticket
     *
     * @param \TicketBundle\Entity\TicketEntity $ticket
     *
     * @return TicketMessageEntity
     */
    public function setTicket(\TicketBundle\Entity\TicketEntity $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return \TicketBundle\Entity\TicketEntity
     */
    public function getTicket(): ?TicketEntity
    {
        return $this->ticket;
    }

    /**
     * Сериализация для REST
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'createdBy' => $this->getCreatedBy(),
            'type' => $this->getType(),
            'text' => $this->getText(),
        ];
    }
}
