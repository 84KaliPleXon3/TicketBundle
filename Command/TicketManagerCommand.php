<?php

namespace Hackzilla\Bundle\TicketBundle\Command;

use FOS\UserBundle\Model\UserManagerInterface;
use Hackzilla\Bundle\TicketBundle\Entity\TicketMessage;
use Hackzilla\Bundle\TicketBundle\Manager\TicketManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TicketManagerCommand extends Command
{
    protected static $defaultName = 'ticket:create';

    /**
     * @var TicketManagerInterface
     */
    private $ticketManager;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(TicketManagerInterface $ticketManager, UserManagerInterface $userManager = null)
    {
        if (!$userManager) {
            throw new \InvalidArgumentException(sprintf('%s requires a user manager in order to set the user for the ticket message. Is "friendsofsymfony/user-bundle" installed and enabled in your project?', self::class));
        }
        parent::__construct();

        $this->ticketManager = $ticketManager;
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(static::$defaultName) // BC for symfony/console < 3.4.0
            ->setDescription('Create a new Ticket')
            ->addArgument(
                'subject',
                InputArgument::REQUIRED,
                'Enter a subject'
            )
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Enter the message'
            )
            ->addOption(
                'priority',
                'p',
                InputOption::VALUE_OPTIONAL,
                'What priority would it be?',
                '21'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ticket = $this->ticketManager->createTicket()
            ->setSubject($input->getArgument('subject'));

        $message = $this->ticketManager->createMessage()
            ->setMessage($input->getArgument('message'))
            ->setStatus(TicketMessage::STATUS_OPEN)
            ->setPriority($input->getOption('priority'))
            ->setUser($this->userManager->findUserByUsername('system'));

        $this->ticketManager->updateTicket($ticket, $message);

        $output->writeln(
            "Ticket with subject '".$ticket->getSubject()."' has been created with ticketnumber #".$ticket->getId().''
        );
    }
}
