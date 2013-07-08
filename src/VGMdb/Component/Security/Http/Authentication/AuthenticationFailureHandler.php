<?php

namespace VGMdb\Component\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

/**
 * Class with custom authentication failure handling logic.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ('html' !== $request->getRequestFormat()) {
            throw $exception;
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
