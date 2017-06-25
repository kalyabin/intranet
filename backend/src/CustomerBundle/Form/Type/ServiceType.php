<?php

namespace CustomerBundle\Form\Type;

use CustomerBundle\Entity\ServiceEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма создания и редактирования услуги
 *
 * @package CustomerBundle\Form\Type
 */
class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'Код услуги'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Активная'
            ])
            ->add('title', TextType::class, [
                'label' => 'Заголовок'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание'
            ])
            ->add('enableCustomerRole', TextType::class, [
                'label' => 'Право пользователей, назначаемое арендатору'
            ])
            ->add('tariff', CollectionType::class, [
                'label' => 'Тарифы',
                'allow_add' => true,
                'allow_delete' => true,
                'allow_extra_fields' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServiceEntity::class,
            'allow_extra_fields' => true
        ]);
    }
}
