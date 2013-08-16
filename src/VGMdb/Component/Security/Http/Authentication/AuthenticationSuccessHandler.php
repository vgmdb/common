<?php

namespace VGMdb\Component\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class with custom authentication success handling logic.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->hasSession()) {
            $request->getSession()->getFlashBag()->set('success', 'You are now logged in.');
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
