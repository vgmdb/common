<?php

namespace VGMdb\Component\User\Form\Handler;

use VGMdb\Component\User\Model\UserManagerInterface;
use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Mailer\MailerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @brief       Handles password reset form submissions.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ResetPasswordFormHandler
{
    protected $request;
    protected $userManager;
    protected $form;

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer)
    {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
    }

    /**
     * @param UserInterface $user
     *
     * @return boolean
     */
    public function process(UserInterface $user)
    {
        $this->form->setData($user);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                $user->setConfirmationToken(null);
                $user->setLastLogin(new \DateTime());
                $this->userManager->updateUser($user);

                $this->mailer->sendNewPasswordEmail($user);

                return true;
            }
        }

        return false;
    }
}
