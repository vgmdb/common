<?php

namespace VGMdb\Component\User\Security;

use VGMdb\Component\User\Model\UserManagerInterface;
use VGMdb\Component\User\Model\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Updates user last login time upon authentication.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
class InteractiveLoginListener
{
    protected $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof UserInterface) {
            $user->setLastLogin(new \DateTime());
            $this->userManager->updateUser($user);
        }
    }
}
