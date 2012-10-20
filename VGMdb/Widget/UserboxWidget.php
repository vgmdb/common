<?php

namespace VGMdb\Widget;

use VGMdb\Application;
use VGMdb\ControllerWidget;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;


/**
 * @brief       Widget for displaying a user info box.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class UserboxWidget extends ControllerWidget
{
    private $lazy;
    private $app;
    public $username;

    /**
     * Create a new user box widget.
     *
     * @param Application $app
     * @param string      $username
     * @return void
     */
    public function __construct(Application $app, $username = 'Anonymous')
    {
        $this->lazy = false;
        $this->app = $app;
        $this->username = $username;

        if (!$this->lazy) {
            $view = $app['view']('userbox');
        } else {
            $view = $app['view']('lazybox');
        }

        parent::__construct($app, $view);
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) {
        $request = $event->getRequest();
        $app = $event->getKernel();

        if ($this->lazy) {
            $this->with(array(
                'url' => $app['url']('user', array('username' => $this->username)),
                'tpl' => 'userbox'
            ));
        } else {
            $this->with($this->app['userbox']($this->username));
        }
    }
}