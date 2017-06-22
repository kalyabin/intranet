<?php

namespace CustomerBundle\Form\Type;

use CustomerBundle\Entity\CustomerEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма редактирования \ создания контрагента
 *
 * @package CustomerBundle\Form\Type
 */
class CustomerType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название арендатора'
            ])
            ->add('currentAgreement', TextType::class, [
                'label' => 'Номер договора'
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerEntity::class,
            'allow_extra_fields' => true,
        ]);
    }
}
