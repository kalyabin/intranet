<?php

namespace RentBundle\Form\Type;

use RentBundle\Entity\RoomEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма добавления или редактирования помещения
 *
 * @package RentBundle\Form\Type
 */
class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Тип помещения',
                'choices' => RoomEntity::getRoomTypes(),
            ])
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание помещения',
            ])
            ->add('address', TextType::class, [
                'label' => 'Местоположение'
            ])
            ->add('hourlyCost', TextType::class, [
                'label' => 'Тариф (руб/час)'
            ])
            ->add('schedule', TextType::class, [
                'label' => 'Расписание'
            ])
            ->add('scheduleBreak', TextType::class, [
                'label' => 'Ежедневный перерыв'
            ])
            ->add('holidays', TextType::class, [
                'label' => 'Праздничные дни'
            ])
            ->add('requestPause', TextType::class, [
                'label' => 'Перерыв между бронированиями'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => RoomEntity::class,
                'allow_extra_fields' => true
            ]);
    }
}
