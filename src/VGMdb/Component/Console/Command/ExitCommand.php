<?php

namespace VGMdb\Component\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Exits the console shell.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ExitCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('exit')
            ->setAliases(array('quit'))
            ->setDescription('Exits the shell')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command exits the shell.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Goodbye.');

        exit(1);
    }
}
