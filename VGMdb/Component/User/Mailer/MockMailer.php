<?php

namespace VGMdb\Component\User\Mailer;

use VGMdb\Component\User\Model\UserInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * This mailer does nothing.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class MockMailer implements MailerInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function sendConfirmationEmail(UserInterface $user)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Sending confirmation email to "%s"', $user->getEmail()));
        }
    }

    public function sendResetPasswordEmail(UserInterface $user)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Sending password reset email to "%s"', $user->getEmail()));
        }
    }
}
