<?php

namespace VGMdb\Component\User\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Promotes a user to super admin.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class UserPromoteCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('user:promote')
            ->setDefinition(array(
                new InputArgument('user', InputArgument::REQUIRED, 'The canonical username'),
            ))
            ->setDescription('Promotes a user to super admin.')
            ->setHelp(<<<EOF
Promotes a user to super admin.
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getContainer($input);
        $username = $input->getArgument('user');

        $user = $app['user_manager']->findUserByUsername($username);
        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        $app['user_manipulator']->promote($user);

        $output->writeln(sprintf('User %s (#%d) has been promoted to super admin.', $username, $user->getId()));
    }
}
