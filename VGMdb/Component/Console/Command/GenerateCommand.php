<?php

namespace VGMdb\Component\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use MwbExporter\Bootstrap;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;

/**
 * @brief       Task for generating Doctrine 2.0 Annotation classes from a MySQL Workbench file.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
        ->setName('vgmdb:generate')
        ->setDescription('Generate Doctrine model files from MySQL Workbench schema.')
        ->setDefinition(array(
            new InputArgument(
                'file', InputArgument::OPTIONAL, 'Schema definition file.', getcwd() . '/data/db/vgmdb.mwb'
            ),
            new InputArgument(
                'temp', InputArgument::OPTIONAL, 'Temporary output directory.', getcwd() . '/build/entities'
            ),
            new InputArgument(
                'dest', InputArgument::OPTIONAL, 'Destination directory.', getcwd() . '/src/VGMdb/ORM/Entity'
            )
        ))
        ->setHelp(<<<EOT
Generate Doctrine model files from MySQL Workbench.
EOT
        );
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
            Formatter::CFG_BUNDLE_NAMESPACE          => 'VGMdb\\ORM',
            Formatter::CFG_ENTITY_NAMESPACE          => 'Entity',
            Formatter::CFG_REPOSITORY_NAMESPACE      => 'VGMdb\\ORM\\Repository',
            Formatter::CFG_AUTOMATIC_REPOSITORY      => false,
            Formatter::CFG_SKIP_GETTER_SETTER        => false,
        );
        $filename = $input->getArgument('file');
        $outDir   = $input->getArgument('temp');

        $bootstrap = new Bootstrap();
        $formatter = $bootstrap->getFormatter('doctrine2-annotation');
        $formatter->setup($setup);
        $document  = $bootstrap->export($formatter, $filename, $outDir, 'file');

        foreach (glob($outDir . '/*.php') as $filename) {
            $target = $input->getArgument('dest') . '/' . basename($filename);
            if (!@rename($filename, $target)) {
                $error = error_get_last();
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $filename, $target, $error));
            }
        }

        // show the time needed to parse the mwb file
        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }
}
