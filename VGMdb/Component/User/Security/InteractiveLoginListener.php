<?php

namespace VGMdb\Component\User\Security;

use VGMdb\Component\User\Model\UserManagerInterface;
use VGMdb\Component\User\Model\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
