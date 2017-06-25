<?php

namespace CustomerBundle\Form\Type;


use CustomerBundle\Entity\ServiceTariffEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма редактирования или создания тарифа
 *
 * @package CustomerBundle\Form\Type
 */
class ServiceTariffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive', CheckboxType::class, [
                'label' => 'Активный'
            ])
            ->add('title', TextType::class, [
                'label' => 'Заголовок'
            ])
            ->add('monthlyCost', TextType::class, [
                'label' => 'Ежемесячный платёж'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServiceTariffEntity::class,
            'allow_extra_fields' => true
        ]);
    }
}
