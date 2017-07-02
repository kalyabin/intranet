<?php

namespace RentBundle\Form\Type;

use RentBundle\Entity\RoomRequestEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма отказа или подтверждения заявки
 *
 * @package RentBundle\Form\Type
 */
class RoomRequestManagerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', TextType::class, [
                'label' => 'Статус',
            ])
            ->add('managerComment', TextareaType::class, [
                'label' => 'Комментарий'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RoomRequestEntity::class,
            'allow_extra_fields' => true
        ]);
    }
}
