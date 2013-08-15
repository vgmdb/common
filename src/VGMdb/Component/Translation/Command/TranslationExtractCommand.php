<?php

namespace VGMdb\Component\Translation\Command;

use VGMdb\Component\Translation\MessageCatalogue;
use VGMdb\Component\Translation\Extractor\Model\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * A command that parse templates to extract translation messages and add them into the translation files.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationExtractCommand extends Command
{
    /**
     * Compiled catalogue of messages.
     * @var MessageCatalogue
     */
    protected $catalogue;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('translation:extract')
            ->setDefinition(array(
                new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
                new InputOption(
                    'prefix', null, InputOption::VALUE_OPTIONAL,
                    'Override the default prefix', '__'
                ),
                new InputOption(
                    'output-format', null, InputOption::VALUE_OPTIONAL,
                    'Override the default output format', 'xliff'
                ),
                new InputOption(
                    'dry-run', null, InputOption::VALUE_NONE,
                    'Should the messages be dumped in the console'
                ),
                new InputOption(
                    'write', null, InputOption::VALUE_NONE,
                    'Should the messages be written to the catalogue'
                ),
                new InputOption(
                    'delete', null, InputOption::VALUE_NONE,
                    'Should obsolete or modified messages be deleted from the catalogue'
                ),
                new InputOption(
                    'routes-only', null, InputOption::VALUE_NONE,
                    'Only dump routes'
                )
            ))
            ->setDescription('Updates the translation file')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command extracts translation strings from php/template files
of a given directory. It can display them or merge the new ones into the translation files.

<info>php %command.full_name% --dry-run en</info>
<info>php %command.full_name% --write zh</info>
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
        $app->boot();

        // check presence of write or dry-run
        if ($input->getOption('write') !== true && $input->getOption('dry-run') !== true) {
            $output->writeln('<info>You must choose one of --write or --dry-run</info>');

            return 1;
        }

        // check format
        $writer = $app['translator.writer'];
        $supportedFormats = $writer->getFormats();
        if (!in_array($input->getOption('output-format'), $supportedFormats)) {
            $output->writeln('<error>Wrong output format</error>');
            $output->writeln('Supported formats are '.implode(', ', $supportedFormats).'.');

            return 1;
        }

        /**
         * @todo Get translations from all resource providers
         */
        $transPath = $destPath = $app['translator.base_dir'];
        $output->writeln(sprintf(
            'Extracting <info>%s</info> messages for <info>%s</info> locale, <info>%s</info> environment with debug mode <info>%s</info>',
            $app['name'],
            $input->getArgument('locale'),
            $app['env'],
            $app['debug'] ? 'ON' : 'OFF'
        ));

        // create catalogue
        $catalogue = new MessageCatalogue($input->getArgument('locale'));

        // load any messages from php files and templates
        $extractor = $app['translator.extractor'];
        $extractor->setPrefix($input->getOption('prefix'));

        if ($input->getOption('routes-only') !== true) {
            $output->writeln('Parsing templates...');
            $extractor->extract(getcwd() . '/public/views', $catalogue);

            $output->writeln('Parsing PHP code...');
            $extractor->extract(getcwd() . '/app', $catalogue);
        }

        $output->writeln('Parsing YAML routes...');
        $routeExtractor = $app['translator.routing.extractor'];
        /**
         * @todo Get routing files from all resource providers
         */
        $routeExtractor->extract($app['routing.resource'], $catalogue);

        // load any existing messages from the translation files
        $output->writeln('Loading translation files...');
        $loader = $app['translator.loader'];
        $loader->loadMessages($transPath, $catalogue, true);

        // show compiled list of messages
        if ($input->getOption('dry-run') === true) {
            foreach ($catalogue->getDomains() as $domain) {
                if ($input->getOption('routes-only') === true && $domain !== 'routes') {
                    continue;
                }
                $messages = $catalogue->all($domain);
                foreach ($messages as $id => $message) {
                    $localeString = (string) $message;
                    if ($message instanceof Message && $message->isNew()) {
                        $localeString .= ' (new)';
                    }
                    $messages[$id] = $localeString;
                }
                $output->writeln(sprintf("\nDisplaying messages for domain \"<info>%s</info>\":\n", $domain));
                $output->writeln(Yaml::dump($messages, 10));
            }
            if ($input->getOption('output-format') == 'xlf') {
                $output->writeln('Xliff output version is <info>1.2</info>');
            }
        }

        if ($input->getOption('output-format') === 'json') {
            $destPath = getcwd() . '/public/js/translations';
        }

        // save the files
        if ($input->getOption('write') === true) {
            $output->writeln('Writing files');
            $writer->writeTranslations($catalogue, $input->getOption('output-format'), array('path' => $destPath));
        }
    }
}
