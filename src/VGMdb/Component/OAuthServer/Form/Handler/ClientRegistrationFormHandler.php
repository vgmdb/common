<?php

namespace VGMdb\Component\OAuthServer\Form\Handler;

use VGMdb\Component\OAuthServer\Model\ClientManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Handles client registration form submissions.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ClientRegistrationFormHandler
{
    protected $request;
    protected $clientManager;
    protected $form;

    public function __construct(FormInterface $form, Request $request, ClientManagerInterface $clientManager)
    {
        $this->form = $form;
        $this->request = $request;
        $this->clientManager = $clientManager;
    }

    /**
     * @param UserInterface $user
     *
     * @return boolean
     */
    public function process(UserInterface $user)
    {
        $client = $this->clientManager->createClient();
        $client->setUser($user);

        $this->form->setData($client);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->clientManager->updateClient($client);

                return true;
            }
        }

        return false;
    }
}
