<?php

namespace AppBundle\Form\Type;

use CustomerBundle\Entity\CustomerEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Форма для переотправки входящих звонков от менеджера к контрагенту
 *
 * @package AppBundle\Form\Type
 */
class IncomingCallResendType extends AbstractType
{
    /**
     * @Assert\NotBlank()
     *
     * @var CustomerEntity Контрагент, которому отправить звонок
     */
    public $customer;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=20)
     *
     * @var string Номер звонившего
     */
    public $callerId;

    /**
     * @Assert\Length(max=255)
     *
     * @var string Дополнительный комментарий
     */
    public $comment;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer', EntityType::class, [
                'label' => 'Арендатор',
                'class' => CustomerEntity::class,
                'choice_label' => 'name',
            ])
            ->add('callerId', TextType::class, [
                'label' => 'Номер звонившего',
            ])
            ->add('comment', TextType::class, [
                'label' => 'Комментарий'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data' => self::class,
            'allow_extra_fields' => true
        ]);
    }
}
