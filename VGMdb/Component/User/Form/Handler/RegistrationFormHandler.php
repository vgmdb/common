<?php

namespace VGMdb\Component\User\Form\Handler;

use VGMdb\Component\User\Model\UserManagerInterface;
use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Mailer\MailerInterface;
use VGMdb\Component\User\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @brief       Handles registration form submissions.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class RegistrationFormHandler
{
    protected $request;
    protected $userManager;
    protected $form;
    protected $mailer;
    protected $tokenGenerator;

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @param boolean $requireConfirmation
     *
     * @return boolean
     */
    public function process($requireConfirmation = false)
    {
        $user = $this->createUser();
        $this->form->setData($user);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $this->activateUser($user, $requireConfirmation);

                return true;
            }
        }

        return false;
    }

    /**
     * @param UserInterface $user
     * @param boolean       $requireConfirmation
     */
    public function activateUser(UserInterface $user, $requireConfirmation)
    {
        if ($requireConfirmation) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }
            $this->mailer->sendConfirmationEmail($user);
        } else {
            $user->setEnabled(true);
        }

        //$this->userManager->updateUser($user);
    }

    /**
     * @return UserInterface
     */
    protected function createUser()
    {
        return $this->userManager->createUser();
    }
}
