<?php

namespace VGMdb\Component\Propel\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MwbExporter\Bootstrap;
use MwbExporter\Formatter\Propel1\Xml\Formatter;

/**
 * Task for generating Propel schema files from a MySQL Workbench file.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelSchemaCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('propel:schema')
            ->setDescription('Generate Propel schema files from MySQL Workbench schema.')
            ->setDefinition(array(
                new InputArgument(
                    'file', InputArgument::OPTIONAL, 'Schema definition file.', getcwd() . '/data/db/db.mwb'
                ),
                new InputArgument(
                    'dest', InputArgument::OPTIONAL, 'Schema output directory.', getcwd() . '/data/db/schema/build'
                ),
                new InputOption(
                    'namespace', null, InputOption::VALUE_REQUIRED, 'Class namespace', null
                )
            ))
            ->setHelp(<<<EOT
Generate Propel schema files from MySQL Workbench.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true) * 1000;

        // formatter setup
        $setup = array(
            Formatter::CFG_INDENTATION            => 4,
            Formatter::CFG_FILENAME               => '%schema%Schema.xml',
            Formatter::CFG_NAMESPACE              => $input->getOption('namespace'),
            Formatter::CFG_ADD_VENDOR             => false
        );
        $filename = $input->getArgument('file');
        $outDir   = $input->getArgument('dest');

        $bootstrap = new Bootstrap();
        $formatter = $bootstrap->getFormatter('propel1-xml');
        $formatter->setup($setup);
        $document  = $bootstrap->export($formatter, $filename, $outDir, 'file');

        // show the time needed to parse the mwb file
        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }
}
