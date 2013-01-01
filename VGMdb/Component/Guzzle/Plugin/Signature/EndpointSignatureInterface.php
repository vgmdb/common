<?php

namespace VGMdb\Component\Guzzle\Plugin\Signature;

use VGMdb\Component\OAuthServer\Credentials\CredentialsInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * Interface for signatures that use specific region and service names when signing requests.
 *
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 */
interface EndpointSignatureInterface extends SignatureInterface
{
    /**
     * Set the service name instead of inferring it from a request URL
     *
     * @param string $service Name of the service used when signing
     *
     * @return self
     */
    public function setServiceName($service);

    /**
     * Set the region name instead of inferring it from a request URL
     *
     * @param string $region Name of the region used when signing
     *
     * @return self
     */
    public function setRegionName($region);
}
