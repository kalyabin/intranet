<?php

namespace UserBundle\Command;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\CustomerRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\UserRoleEntity;
use UserBundle\Utils\UserManager;

/**
 * Создание пользователя в консоли
 *
 * @package UserBunde\Command
 */
class CreateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:create-user')
            ->setDescription('Создание пользователя');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notEmptyValidator = function($value) {
            if (trim($value) == '') {
                throw new \Error('Поле не может быть пустым');
            }

            return $value;
        };

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $output->writeln([
            'Создание пользователя',
            '====================='
        ]);

        $nameQuestion = new Question('Имя пользователя: ');
        $nameQuestion->setValidator($notEmptyValidator);

        $name = $helper->ask($input, $output, $nameQuestion);

        $emailQuestion = new Question('E-mail пользователя: ');
        $emailQuestion->setValidator($notEmptyValidator);

        $email = $helper->ask($input, $output, $emailQuestion);

        $typeQuestion = new ChoiceQuestion(
            'Укажите тип пользователя: ',
            [UserEntity::TYPE_CUSTOMER, UserEntity::TYPE_MANAGER],
            UserEntity::TYPE_MANAGER
        );
        $typeQuestion->setErrorMessage('Неверный тип пользователя');

        $type = $helper->ask($input, $output, $typeQuestion);

        /** @var CustomerEntity $customer */
        $customer = null;

        if ($type == UserEntity::TYPE_CUSTOMER) {
            /** @var ObjectManager $em */
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            /** @var CustomerRepository $repository */
            $repository = $em->getRepository(CustomerEntity::class);

            $customerQuestion = new Question('Идентификатор арендатора: ');
            $customerQuestion->setValidator(function($value) use ($repository) {
                $id = (int) $value;

                $customer = $repository->findOneById($id);

                if (!$customer) {
                    throw new \Error('Арендатор не найден');
                }

                return $customer;
            });

            $customer = $helper->ask($input, $output, $customerQuestion);
        }

        $passwordQuestion = new Question('Пароль пользователя: ');
        $passwordQuestion->setValidator($notEmptyValidator);
        $passwordQuestion->setHidden(true);

        $password = $helper->ask($input, $output, $passwordQuestion);

        /** @var UserManager $userManager */
        $userManager = $this->getContainer()->get('user.manager');

        $user = new UserEntity();

        $user
            ->setName($name)
            ->setPassword($password)
            ->setEmail($email)
            ->setUserType($type);

        if ($customer) {
            $user->setCustomer($customer);
        }

        $role = new UserRoleEntity();

        if ($type == UserEntity::TYPE_CUSTOMER) {
            $role->setCode('CUSTOMER_ADMIN');
        } else {
            $role->setCode('SUPERADMIN');
        }

        $user->addRole($role);

        $userManager->createUserByAdmin($user);

        $output->writeln([
            'Пользователь успешно создан: ',
            print_r(json_decode(json_encode($user), true), true)
        ]);
    }
}
