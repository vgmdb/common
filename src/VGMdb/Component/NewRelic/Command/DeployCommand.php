<?php

namespace VGMdb\Component\NewRelic\Command;

use VGMdb\Component\NewRelic\MonitorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class DeployCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('newrelic:deploy')
            ->setDefinition(array(
                new InputOption(
                    'user', null, InputOption::VALUE_OPTIONAL,
                    'The name of the user/process that triggered this deployment', null
                ),
                new InputOption(
                    'revision', null, InputOption::VALUE_OPTIONAL,
                    'A revision number (e.g., git commit SHA)', null
                ),
                new InputOption(
                    'changelog', null, InputOption::VALUE_OPTIONAL,
                    'A list of changes for this deployment', null
                ),
                new InputOption(
                    'description', null, InputOption::VALUE_OPTIONAL,
                    'Text annotation for the deployment - notes for you', null
                ),
            ))
            ->setDescription('Notify deployment to New Relic')
            ->setHelp(<<<EOT
Notifies New Relic that a new deployment has been made.
EOT
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getContainer($input);
        $monitor = $app['newrelic.monitor'];

        $status = $this->performRequest($app['newrelic.api_key'], $this->createPayload($monitor, $input));

        switch ($status) {
            case 200:
            case 201:
                $output->writeln(sprintf("Recorded deployment to '%s' (%s)", $monitor->getApplicationName(), ($input->getOption('description') ? $input->getOption('description') : date('r'))));
                break;
            case 403:
                $output->writeln('<error>Deployment not recorded: API key invalid</error>');
                break;
            case null:
                $output->writeln('<error>Deployment not recorded: Did not understand response</error>');
                break;
            default:
                $output->writeln(sprintf('<error>Deployment not recorded: Received HTTP status %d</error>', $status));
        }
    }

    public function performRequest($apiKey, $payload)
    {
        /**
         * @todo Refactor this to use Guzzle!
         */

        $headers = array(
            sprintf('x-api-key: %s', $apiKey),
            'Content-type: application/x-www-form-urlencoded'
        );

        $context = array(
            'http' => array(
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $payload,
                'ignore_errors' => true,
            )
        );

        $content = file_get_contents('https://api.newrelic.com/deployments.xml', 0, stream_context_create($context));

        return 200;
    }

    protected function createPayload(MonitorInterface $monitor, InputInterface $input)
    {
        $content_array = array(
            'deployment[app_name]' => $monitor->getApplicationName()
        );

        if (($user = $input->getOption('user'))) {
            $content_array['deployment[user]'] = $user;
        }

        if (($revision = $input->getOption('revision'))) {
            $content_array['deployment[revision]'] = $revision;
        }

        if (($changelog = $input->getOption('changelog'))) {
            $content_array['deployment[changelog]'] = $changelog;
        }

        if (($description = $input->getOption('description'))) {
            $content_array['deployment[description]'] = $description;
        }

        return http_build_query($content_array);
    }
}
