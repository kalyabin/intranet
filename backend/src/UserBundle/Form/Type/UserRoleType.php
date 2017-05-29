<?php

namespace UserBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\UserRoleEntity;

/**
 * Форма ролей пользователя
 *
 * @package UserBundle\Form\Type
 */
class UserRoleType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', TextType::class, [
            'label' => 'Код роли',
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            // к вновь добавляемой роли добавить пользователя
            /** @var UserRoleEntity $roleEntity */
            $roleEntity = $event->getForm()->getData();
            if ($roleEntity) {
                /** @var UserEntity $userEntity */
                $userEntity = $event->getForm()->getParent()->getParent()->getData();
                $roleEntity->setUser($userEntity);
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserRoleEntity::class,
            'cascade_validation' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
