<?php

namespace VGMdb\Component\User\Mailer;

use VGMdb\Component\User\Model\UserInterface;

/**
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
interface MailerInterface
{
    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendConfirmationEmail(UserInterface $user);

    /**
     * Send an email to a user to confirm the password reset
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendResetPasswordEmail(UserInterface $user);

    /**
     * Send an email to a user to notify that the password was changed
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendNewPasswordEmail(UserInterface $user);
}
