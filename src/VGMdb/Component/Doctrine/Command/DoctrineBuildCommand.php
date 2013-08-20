<?php

namespace VGMdb\Component\Doctrine\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MwbExporter\Bootstrap;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;

/**
 * Task for generating Doctrine 2.0 Annotation classes from a MySQL Workbench file.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DoctrineBuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('doctrine:build')
            ->setDescription('Generate Doctrine model files from MySQL Workbench schema.')
            ->setDefinition(array(
                new InputArgument(
                    'file', InputArgument::OPTIONAL, 'Schema definition file.', getcwd() . '/data/db/db.mwb'
                ),
                new InputArgument(
                    'dest', InputArgument::OPTIONAL, 'Temporary output directory.', getcwd() . '/build/entities'
                ),
                new InputOption(
                    'namespace', null, InputOption::VALUE_REQUIRED, 'Class namespace', null
                )
            ))
            ->setHelp(<<<EOT
Generate Doctrine model files from MySQL Workbench.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true) * 1000;

        // formatter setup
        $setup = array(
            Formatter::CFG_USE_LOGGED_STORAGE        => true,
            Formatter::CFG_INDENTATION               => 4,
            Formatter::CFG_FILENAME                  => '%entity%.%extension%',
            Formatter::CFG_ANNOTATION_PREFIX         => 'ORM\\',
            Formatter::CFG_BUNDLE_NAMESPACE          => $input->getOption('namespace'),
            Formatter::CFG_ENTITY_NAMESPACE          => 'Entity',
            Formatter::CFG_REPOSITORY_NAMESPACE      => $input->getOption('namespace') . '\\Repository',
            Formatter::CFG_AUTOMATIC_REPOSITORY      => false,
            Formatter::CFG_SKIP_GETTER_SETTER        => false,
            Formatter::CFG_BACKUP_FILE               => false,
            Formatter::CFG_SKIP_RELATIONS            => false,
        );
        $filename = $input->getArgument('file');
        $outDir   = $input->getArgument('dest');

        $bootstrap = new Bootstrap();
        $formatter = $bootstrap->getFormatter('doctrine2-annotation');
        $formatter->setup($setup);
        $document  = $bootstrap->export($formatter, $filename, $outDir, 'file');

        // show the time needed to parse the mwb file
        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }
}
