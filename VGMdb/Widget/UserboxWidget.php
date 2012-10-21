<?php

namespace VGMdb\Widget;

use VGMdb\Application;
use VGMdb\ControllerWidget;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;


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
        $view = $app['view']('userbox');

        parent::__construct($app, $view);
    }

    public function onKernelView(GetResponseForControllerResultEvent $event) {
        $request = $event->getRequest();

        if ($this->lazy) {
            $this->with(array(
                'widget' => array(
                    'url' => $app['url']('user', array('username' => $this->username)),
                    'view' => 'userbox'
                )
            ));
        } else {
            try {
                $data = $this->app['data.user']($this->username);
                $this->nest($this->app['view']('user', $data));
            } catch (InsufficientAuthenticationException $e) {
                $data = $this->app['data.login']();
                $this->nest($this->app['view']('login', $data));
            } catch (\Exception $e) {
                $data = array('fatal' => $e->getMessage());
                $this->with($data);
            }
        }
    }
}