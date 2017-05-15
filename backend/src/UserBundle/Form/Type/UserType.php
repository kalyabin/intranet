<?php

namespace UserBundle\Form\Type;


use CustomerBundle\Entity\CustomerEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Активен' => UserEntity::STATUS_ACTIVE,
                    'Заблокирован' => UserEntity::STATUS_LOCKED
                ],
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
            ->add('customer', EntityType::class, [
                'class' => CustomerEntity::class,
                'choice_label' => 'name',
                'multiple' => false
            ])
            ->add('isTemporaryPassword', CheckboxType::class, [
                'label' => 'Сгенерировать временный пароль'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Пароль пользователя',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var UserEntity $entity */
            $entity = $event->getData();

            if (!$entity->getId()) {
                // статус доступен только для старых пользователей
                $event->getForm()->remove('status');
            } else {
                // удалить флаг временного пароля, если пользователь уже существует
                $event->getForm()->remove('isTemporaryPassword');
                $event->getForm()->remove('password');
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            /** @var array $data */
            $data = $event->getData();
            /** @var UserEntity $entity */
            $entity = $event->getForm()->getData();

            if (!$entity->getId() && !empty($data['isTemporaryPassword'])) {
                // удалить поле с паролем, если устанавливается временный пароль для нового пользователя
                $event->getForm()->remove('password');

                // сгенерировать временный пароль
                $generateRandomString = function() {
                    $length = 10;
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < $length; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }
                    return $randomString;
                };
                $entity->setPassword($generateRandomString());
            }

            if ($entity->getUserType() == UserEntity::TYPE_MANAGER) {
                // удалить поле с выбором контрагента, если редактируемый пользователь - менеджер
                $event->getForm()->remove('customer');
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
