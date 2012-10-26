<?php

namespace VGMdb\Component\User\Security;

use VGMdb\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;

interface LoginManagerInterface
{
    public function loginUser($firewallName, UserInterface $user, Response $response = null);
}
