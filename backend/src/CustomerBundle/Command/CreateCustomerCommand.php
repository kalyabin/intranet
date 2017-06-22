<?php

namespace CustomerBundle\Command;


use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Создание контрагента в консоли
 *
 * @package CustomerBundle\Command
 */
class CreateCustomerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('customer:create-customer')
            ->setDescription('Создание контрагента');
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
            'Создание контрагента',
            '===================='
        ]);

        $nameQuestion = new Question('Название контрагента: ');
        $nameQuestion->setValidator($notEmptyValidator);

        $name = $helper->ask($input, $output, $nameQuestion);

        $agreementQuestion = new Question('Номер договора: ');
        $agreementQuestion->setValidator($notEmptyValidator);

        $agreement = $helper->ask($input, $output, $agreementQuestion);

        $customer = new CustomerEntity();

        $customer
            ->setName($name)
            ->setCurrentAgreement($agreement);

        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $em->persist($customer);

        $em->flush();

        $output->writeln([
            'Контрагент успешно создан: ',
            print_r(json_decode(json_encode($customer), true), true)
        ]);
    }
}
