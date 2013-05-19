<?php

namespace VGMdb\Component\NewRelic\Command;

use VGMdb\Component\NewRelic\MonitorInterface;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
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
        $app->boot();
        $appName = $app['newrelic.monitor']->getApplicationName();

        $status = $this->performRequest($app['newrelic.api_host'], $app['newrelic.api_key'], $appName, $input);

        switch ($status) {
            case 200:
            case 201:
                $output->writeln(sprintf("Recorded deployment to '%s' (%s)", $appName, ($input->getOption('description') ? $input->getOption('description') : date('r'))));
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

    public function performRequest($apiHost, $apiKey, $appName, InputInterface $input)
    {
        $client = new Client($apiHost);
        $request = $client->post('/deployments.xml');
        $request->addHeader('x-api-key', $apiKey);

        $request->addPostFields(array('deployment[app_name]' => $appName));

        if (($user = $input->getOption('user'))) {
            $request->addPostFields(array('deployment[user]' => $user));
        }
        if (($revision = $input->getOption('revision'))) {
            $request->addPostFields(array('deployment[revision]' => $revision));
        }
        if (($changelog = $input->getOption('changelog'))) {
            $request->addPostFields(array('deployment[changelog]' => $changelog));
        }
        if (($description = $input->getOption('description'))) {
            $request->addPostFields(array('deployment[description]' => $description));
        }
        if (($environment = $input->getOption('env'))) {
            $request->addPostFields(array('deployment[environment]' => $environment));
        }

        try {
            $response = $request->send();
        } catch (BadResponseException $exception) {
            $response = $exception->getResponse();
        }

        return $response->getStatusCode();
    }
}
