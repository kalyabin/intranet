<?php

namespace TicketBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TicketBundle\Entity\TicketCategoryEntity;

/**
 * Создание категории (очереди)
 *
 * @package TicketBundle\Command
 */
class CreateCategoryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ticket:category:create')
            ->setDescription('Создание очереди в тикетной системе');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notEmptyValidator = function(string $value) {
            if (trim($value) == '') {
                throw new \Error('Поле не может быть пустым');
            }

            return $value;
        };

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $output->writeln([
            'Создание очереди тикетной системы',
            '================================='
        ]);

        $idQuestion = new Question('Код очереди (уникальный): ');
        $idQuestion->setValidator($notEmptyValidator);

        $id = $helper->ask($input, $output, $idQuestion);

        $nameQuestion = new Question('Название очереди: ');
        $nameQuestion->setValidator($notEmptyValidator);

        $name = $helper->ask($input, $output, $nameQuestion);

        $managerRoleQuestion = new Question('Право доступа для сотрудников (код): ');
        $managerRoleQuestion->setValidator($notEmptyValidator);

        $managerRole = $helper->ask($input, $output, $managerRoleQuestion);

        $customerRoleQuestion = new Question('Право доступа для арендаторов (код): ');
        $customerRoleQuestion->setValidator($notEmptyValidator);

        $customerRole = $helper->ask($input, $output, $customerRoleQuestion);

        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $category = new TicketCategoryEntity();

        $category
            ->setId($id)
            ->setName($name)
            ->setCustomerRole($customerRole)
            ->setManagerRole($managerRole);

        $entityManager->persist($category);

        $entityManager->flush();

        $output->writeln([
            'Категория успешно создана: ',
            print_r(json_decode(json_encode($category), true), true)
        ]);
    }
}
