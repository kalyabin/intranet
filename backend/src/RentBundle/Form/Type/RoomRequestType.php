<?php

namespace RentBundle\Form\Type;

use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма заполнения заявки на аренду помещения
 *
 * @package RentBundle\Form\Type
 */
class RoomRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('room', EntityType::class, [
                'label' => 'Помещение для аренды',
                'class' => RoomEntity::class,
                'choice_label' => 'title',
                'multiple' => false
            ])
            ->add('from', DateTimeType::class, [
                'label' => 'Время начала аренды',
                'widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd HH:mm',
            ])
            ->add('to', DateTimeType::class, [
                'label' => 'Время окончания аренды',
                'widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd HH:mm',
            ])
            ->add('customerComment', TextareaType::class, [
                'label' => 'Дополнительные комментарии'
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
