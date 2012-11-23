<?php

namespace VGMdb\Component\User\Security;

use VGMdb\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login manager interface definition.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
interface LoginManagerInterface
{
    public function loginUser($firewallName, UserInterface $user, Response $response = null);
}
