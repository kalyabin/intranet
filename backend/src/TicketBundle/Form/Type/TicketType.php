<?php

namespace TicketBundle\Form\Type;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Форма заполнения тикета
 *
 * @package TicketBundle\Form\Type
 */
class TicketType extends TicketMessageType
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=200)
     *
     * @var string Заголовок заявки
     */
    protected $title;

    /**
     * Установить заголовок
     *
     * @param null|string $title
     *
     * @return TicketType
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Получить заголовок
     *
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок заявки'
            ]);

        parent::buildForm($builder, $options);
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
