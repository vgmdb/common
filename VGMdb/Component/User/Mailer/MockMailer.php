<?php

namespace VGMdb\Component\User\Mailer;

use VGMdb\Component\User\Model\UserInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * This mailer does nothing.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
class MockMailer implements MailerInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function sendConfirmationEmail(UserInterface $user)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Sending confirmation email to "%s"', $user->getEmail()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sendResetPasswordEmail(UserInterface $user)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Sending password reset email to "%s"', $user->getEmail()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sendNewPasswordEmail(UserInterface $user)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Sending new password notification email to "%s"', $user->getEmail()));
        }
    }
}
