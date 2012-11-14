<?php

namespace VGMdb\Component\User\Mailer;

use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Mailer\MailerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MustacheSwiftMailer implements MailerInterface
{
    protected $mailer;
    protected $urlGenerator;
    protected $mustache;
    protected $logger;
    protected $parameters;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $urlGenerator, \Mustache_Engine $mustache, LoggerInterface $logger = null, array $parameters)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->mustache = $mustache;
        $this->logger = $logger;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmail(UserInterface $user)
    {
        $template = $this->parameters['user.mailer.confirmation.template'];
        $url = $this->urlGenerator->generate('user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Sending confirmation email to "%s" with url %s', $user->getEmail(), $url));
        }

        $this->sendMessage($template, $context, $this->parameters['user.mailer.confirmation.from_email'], $user->getEmail());
    }

    public function sendResetPasswordEmail(UserInterface $user)
    {
        $template = $this->parameters['user.mailer.resetpassword.template'];
        $url = $this->urlGenerator->generate('user_profile_resetpassword', array('token' => $user->getConfirmationToken()), true);
        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Sending password reset email to "%s" with url %s', $user->getEmail(), $url));
        }

        $this->sendMessage($template, $context, $this->parameters['user.mailer.resetpassword.from_email'], $user->getEmail());
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templates, $context, $fromEmail, $toEmail)
    {
        $subject = $this->mustache->loadTemplate($templates['subject'])->render($context);
        $textBody = $this->mustache->loadTemplate($templates['text'])->render($context);
        if (isset($templates['html'])) {
            $htmlBody = $this->mustache->loadTemplate($templates['html'])->render($context);
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }
}
