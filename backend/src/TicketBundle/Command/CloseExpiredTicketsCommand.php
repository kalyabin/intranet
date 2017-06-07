<?php

namespace TicketBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Utils\TicketManager;

/**
 * Закрытие заявок с просроченной датой последнего вопроса (у которых voided_at меньше текущей даты)
 *
 * @package TicketBundle\Command
 */
class CloseExpiredTicketsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ticket:close-expired-tickets')
            ->setDescription('Закрытие заявок с просроченной датой последнего вопроса (у которых voided_at меньше текущей даты)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var TicketManager $ticketManager */
        $ticketManager = $this->getContainer()->get('ticket.manager');
        /** @var ObjectManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var TicketRepository $repository */
        $repository = $entityManager->getRepository(TicketEntity::class);

        $output->writeln([
            'Закрытие заявок, у которых дата voidedAt < ' . (new \DateTime())->format('d.m.Y H:i:s'),
            '==========================================='
        ]);

        // получение всех тикетов, которые требуется закрыть
        $res = $repository->findNeedToClose();
        $success = 0;
        foreach ($res as $items) {
            foreach ($items as $item) {
                /** @var TicketEntity $item */
                $output->writeln(['Закрытие заявки ' . $item->getId()]);
                $ticketManager->closeTicket($item);
                $success++;
            }
        }
        $output->writeln(['Общее количество закрытых заявок: ' . $success]);
    }
}
