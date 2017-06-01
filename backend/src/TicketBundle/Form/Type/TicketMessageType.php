<?php

namespace TicketBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Форма создания сообщения
 *
 * @package TicketBundle\Form\Type
 */
class TicketMessageType extends AbstractType
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max="1000")
     *
     * @var string Текст сообщения
     */
    protected $text;

    /**
     * @Assert\Type("boolean")
     *
     * @var boolean Закрыть тикет после создания сообщения
     */
    protected $closeTicket;

    /**
     * Установить сообщение
     *
     * @param string $text
     *
     * @return TicketMessageType
     */
    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Получить текст сообщения
     *
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Установка флага closeTicket
     *
     * @param bool|null $closeTicket
     *
     * @return TicketMessageType
     */
    public function setCloseTicket(?bool $closeTicket): self
    {
        $this->closeTicket = $closeTicket;

        return $this;
    }

    /**
     * Получить флаг closeTicket
     *
     * @return bool
     */
    public function getCloseTicket(): bool
    {
        return $this->closeTicket === true;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextType::class, [
                'label' => 'Текст сообщения'
            ])
            ->add('closeTicket', CheckboxType::class, [
                'label' => 'Закрыть заявку'
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data' => self::class,
            'allow_extra_fields' => true,
        ]);
    }
}
