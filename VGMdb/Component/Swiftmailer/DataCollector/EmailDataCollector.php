<?php

namespace VGMdb\Component\Swiftmailer\DataCollector;

use Silex\Application;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EmailDataCollector.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Clément JOBEILI <clement.jobeili@gmail.com>
 */
class EmailDataCollector extends DataCollector
{
    private $app;
    private $isSpool;

    /**
     * Constructor.
     *
     * We don't inject the message logger and mailer here
     * to avoid the creation of these objects when no emails are sent.
     *
     * @param Application $app     An Application instance
     * @param Boolean     $isSpool
     */
    public function __construct(Application $app, $isSpool = true)
    {
        $this->app = $app;
        $this->isSpool = $isSpool;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        // only collect when Swiftmailer has already been initialized
        if (class_exists('Swift_Mailer', false)) {
            $logger = $this->app['swiftmailer.plugin.messagelogger'];
            $this->data['messages'] = $logger->getMessages();
            $this->data['messageCount'] = $logger->countMessages();
        } else {
            $this->data['messages'] = array();
            $this->data['messageCount'] = 0;
        }

        $this->data['isSpool'] = $this->isSpool;
    }

    public function getMessageCount()
    {
        return $this->data['messageCount'];
    }

    public function getMessages()
    {
        return $this->data['messages'];
    }

    public function isSpool()
    {
        return $this->data['isSpool'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'swiftmailer';
    }
}
