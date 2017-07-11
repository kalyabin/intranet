<?php

namespace RentBundle\Form\Type;


use CustomerBundle\Entity\CustomerEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Форма создания заявки на бронирования помещения от менеджера
 *
 * @package RentBundle\Form\Type
 */
class RoomRequestCreateManagerType extends RoomRequestType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('customer', EntityType::class, [
                'label' => 'Арендатор',
                'class' => CustomerEntity::class,
                'choice_label' => 'name',
                'multiple' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('managerComment', TextareaType::class, [
                'label' => 'Комментарий'
            ]);
    }
}
