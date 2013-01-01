<?php

namespace VGMdb\Component\Guzzle\Plugin\Signature;

use VGMdb\Component\OAuthServer\Credentials\CredentialsInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * Interface used to provide interchangeable strategies for signing requests.
 *
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 */
interface SignatureInterface
{
    /**
     * Signs the specified request with an AWS signing protocol by using the
     * provided AWS account credentials and adding the required headers to the
     * request.
     *
     * @param RequestInterface     $request     Request to add a signature to
     * @param CredentialsInterface $credentials Signing credentials
     */
    public function signRequest(RequestInterface $request, CredentialsInterface $credentials);
}
