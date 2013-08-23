<?php

namespace VGMdb\Component\Propel\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;
use Propel\Generator\Command\SqlBuildCommand;

/**
 * Task for generating SQL from Propel schema.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelSqlCommand extends SqlBuildCommand
{
    private $schema;

    protected function configure()
    {
        parent::configure();

        $this->setName('propel:sql');
        $this->getDefinition()->getOption('input-dir')->setDefault(getcwd() . '/data/db/xml');
        $this->getDefinition()->getOption('output-dir')->setDefault(getcwd() . '/build/db/sql');
        $this->getDefinition()->addArgument(new InputArgument('schema', InputArgument::OPTIONAL, 'The schema', '*'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true) * 1000;

        $this->schema = $input->getArgument('schema');
        parent::execute($input, $output);

        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }

    protected function getSchemas($directory)
    {
        $finder = new Finder();

        return iterator_to_array($finder
            ->name($this->schema . 'Schema.xml')
            ->in($directory)
            ->depth(0)
            ->files()
        );
    }
}
