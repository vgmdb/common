<?php

namespace VGMdb\Component\Propel\Command;

use Propel\Generator\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use MwbExporter\Bootstrap;
use MwbExporter\Formatter\Propel1\Xml\Formatter;

/**
 * Task for generating Propel schema files from a MySQL Workbench file.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelSchemaCommand extends AbstractCommand
{
    private $schema;

    protected function configure()
    {
        $this
            ->setName('propel:schema')
            ->setDescription('Generate Propel schema files from MySQL Workbench schema.')
            ->setDefinition(array(
                new InputOption('input-dir', null, InputOption::VALUE_REQUIRED, 'The input directory.', getcwd() . '/data/db'),
                new InputOption('output-dir', null, InputOption::VALUE_REQUIRED, 'The output directory.', getcwd() . '/build/db/xml'),
                new InputOption('namespace', null, InputOption::VALUE_OPTIONAL, 'The namespace', ''),
                new InputArgument('schema', InputArgument::OPTIONAL, 'The schema', '*')
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

        $this->schema = $input->getArgument('schema');

        // formatter setup
        $setup = array(
            Formatter::CFG_INDENTATION => 4,
            Formatter::CFG_FILENAME    => '%schema%Schema.xml',
            Formatter::CFG_NAMESPACE   => $input->getOption('namespace'),
            Formatter::CFG_ADD_VENDOR  => false
        );

        $bootstrap = new Bootstrap();
        $formatter = $bootstrap->getFormatter('propel1-xml');
        $formatter->setup($setup);

        $this->createDirectory($input->getOption('output-dir'));

        foreach ($this->getWorkbenchFiles($input->getOption('input-dir')) as $filename) {
            $document = $bootstrap->export($formatter, $filename, $input->getOption('output-dir'), 'file');
        }

        // show the time needed to parse the mwb file
        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }

    protected function getWorkbenchFiles($directory)
    {
        $finder = new Finder();

        return iterator_to_array($finder
            ->name($this->schema . '.mwb')
            ->in($directory)
            ->depth(0)
            ->files()
        );
    }
}
