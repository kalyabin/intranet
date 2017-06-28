<?php

namespace CustomerBundle\Command;

use CustomerBundle\Entity\ServiceEntity;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Команда для создания новой услуги
 *
 * @package CustomerBundle\Command
 */
class CreateServiceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('service:create')
            ->setDescription('Создание услуги');
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
            'Создание услуги',
            '==============='
        ]);

        $idQuestion = new Question('Код услуги: ');
        $idQuestion->setValidator($notEmptyValidator);

        $id = $helper->ask($input, $output, $idQuestion);

        $titleQuestion = new Question('Название услуги: ');
        $titleQuestion->setValidator($notEmptyValidator);

        $title = $helper->ask($input, $output, $titleQuestion);

        $customerRoleQuestion = new Question('Код роли арендатора: ');
        $customerRoleQuestion->setValidator($notEmptyValidator);

        $customerRole = $helper->ask($input, $output, $customerRoleQuestion);

        $entity = new ServiceEntity();

        $entity
            ->setId($id)
            ->setTitle($title)
            ->setIsActive(true)
            ->setCustomerRole($customerRole);

        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $em->persist($entity);
        $em->flush();

        $output->writeln([
            'Услуга успешно создана: ',
            print_r(json_decode(json_encode($entity), true), true)
        ]);
    }
}
