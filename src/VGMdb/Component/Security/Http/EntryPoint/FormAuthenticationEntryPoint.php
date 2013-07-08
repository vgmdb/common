<?php

namespace VGMdb\Component\Security\Http\EntryPoint;

use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint as BaseFormAuthenticationEntryPoint;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not redirect for non HTML requests.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class FormAuthenticationEntryPoint extends BaseFormAuthenticationEntryPoint
{
    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (null !== $authException && 'html' !== $request->getRequestFormat()) {
            throw $authException;
        }

        return parent::start($request, $authException);
    }
}
