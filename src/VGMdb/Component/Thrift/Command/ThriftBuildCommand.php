<?php

namespace VGMdb\Component\Thrift\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Task for generating Apache Thrift classes from thrift definition files.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ThriftBuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('thrift:build')
            ->setDescription('Generate Apache Thrift classes from thrift definition files.')
            ->setDefinition(array(
                new InputArgument(
                    'src', InputArgument::OPTIONAL, 'Thrift definition directory.', getcwd() . '/data/thrift'
                ),
                new InputArgument(
                    'dest', InputArgument::OPTIONAL, 'Temporary output directory.', getcwd() . '/build'
                )
            ))
            ->setHelp(<<<EOT
Generate Apache Thrift classes from thrift definition files.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true) * 1000;

        $srcDir = $input->getArgument('src');
        $outDir = $input->getArgument('dest');

        foreach (glob($srcDir . '/*.thrift') as $filename) {
            exec('thrift -o ' . $outDir . ' --gen php:server,oop ' . $filename);
            exec('thrift -o ' . $outDir . ' --gen java ' . $filename);
            exec('thrift -o ' . $outDir . ' --gen js:jquery ' . $filename);
            exec('thrift -o ' . $outDir . ' --gen cocoa ' . $filename);
            exec('thrift -o ' . $outDir . ' --gen csharp ' . $filename);
        }

        // show the time needed to generate the thrift classes
        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }
}
