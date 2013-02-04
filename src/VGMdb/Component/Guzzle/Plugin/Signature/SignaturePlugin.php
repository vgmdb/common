<?php

namespace VGMdb\Component\Guzzle\Plugin\Signature;

use VGMdb\Component\OAuthServer\Credentials\CredentialsInterface;
use Guzzle\Common\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Request signing plugin using HMAC-SHA256.
 *
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 */
class SignaturePlugin implements EventSubscriberInterface
{
    /**
     * @var CredentialsInterface
     */
    protected $credentials;

    /**
     * @var SignatureInterface
     */
    protected $signature;

    /**
     * Construct a new request signing plugin
     *
     * @param CredentialsInterface $credentials Credentials used to sign requests
     * @param SignatureInterface   $signature   Signature implementation
     */
    public function __construct(CredentialsInterface $credentials, SignatureInterface $signature)
    {
        $this->credentials = $credentials;
        $this->signature = $signature;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => array('onRequestBeforeSend', -255)
        );
    }

    /**
     * Signs requests before they are sent
     *
     * @param Event $event Event emitted
     */
    public function onRequestBeforeSend(Event $event)
    {
        $this->signature->signRequest($event['request'], $this->credentials);
    }
}
