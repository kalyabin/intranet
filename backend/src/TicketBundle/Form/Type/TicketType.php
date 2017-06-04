<?php

namespace TicketBundle\Form\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use TicketBundle\Entity\TicketCategoryEntity;


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
     * @Assert\NotBlank()
     *
     * @var TicketCategoryEntity Категория тикета
     */
    protected $category;

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
     * Установить категорию
     *
     * @param null|TicketCategoryEntity $category
     *
     * @return TicketType
     */
    public function setCategory($category): self
    {
        $this->category = $category instanceof TicketCategoryEntity ?
            $category : '';

        return $this;
    }

    /**
     * Получить категорию
     *
     * @return null|TicketCategoryEntity
     */
    public function getCategory(): ?TicketCategoryEntity
    {
        return $this->category;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок заявки'
            ])
            ->add('category', EntityType::class, [
                'label' => 'Категория',
                'class' => TicketCategoryEntity::class,
                'choice_label' => 'name',
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
