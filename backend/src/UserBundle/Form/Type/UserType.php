<?php

namespace UserBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\UserRoleEntity;

/**
 * Форма редактирования пользователя админом
 *
 * @package UserBundle\Form\Type
 */
class UserType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Имя пользователя'
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail пользователя'
            ])
            ->add('userType', ChoiceType::class, [
                'choices' => [
                    'Сотрудник' => UserEntity::TYPE_MANAGER,
                    'Арендатор' => UserEntity::TYPE_CUSTOMER
                ]
            ])
            ->add('role', CollectionType::class, [
                'entry_type' => UserRoleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'allow_extra_fields' => true,
                'constraints' => [new Valid()]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Пароль пользователя'
            ]);

        // добавить статус пользователя в форму, если пользователь уже существует
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var UserEntity $entity */
            $entity = $event->getData();

            if ($entity->getId()) {
                $event->getForm()->add('status', ChoiceType::class, [
                    'choices' => [
                        'Активен' => UserEntity::STATUS_ACTIVE,
                        'Заблокирован' => UserEntity::STATUS_LOCKED
                    ]
                ]);
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserEntity::class,
            'cascade_validation' => true,
        ]);
    }
}
