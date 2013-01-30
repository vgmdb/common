<?php

namespace VGMdb\Component\User\Form\Handler;

use VGMdb\Component\User\Model\UserManagerInterface;
use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Mailer\MailerInterface;
use VGMdb\Component\User\Util\TokenGeneratorInterface;
use VGMdb\Component\Form\FormFlow;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles registration form submissions.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RegistrationFormHandler
{
    protected $request;
    protected $userManager;
    protected $flow;
    protected $form;
    protected $mailer;
    protected $tokenGenerator;

    public function __construct(FormFlow $flow, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $this->flow = $flow;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;

        $user = $this->createUser();
        $this->flow->bind($user);
        $this->form = $this->flow->createForm($user);
    }

    /**
     * @param boolean $requireConfirmation
     *
     * @return boolean
     */
    public function process($requireConfirmation = false)
    {
        if ($this->flow->isValid($this->form)) {
            $this->flow->saveCurrentStepData();

            $user = $this->form->getData();
            if (!$this->flow->nextStep()) {
                $this->activateUser($user, $requireConfirmation);
                $this->flow->reset();

                return $user;
            }

            $this->form = $this->flow->createForm($user);
        }

        return false;
    }

    /**
     * @return FormFlow
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
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

        $this->userManager->updateUser($user);
    }

    /**
     * @return UserInterface
     */
    protected function createUser()
    {
        return $this->userManager->createUser();
    }
}
