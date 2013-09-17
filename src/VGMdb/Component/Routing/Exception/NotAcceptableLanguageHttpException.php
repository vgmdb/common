<?php

namespace VGMdb\Component\Routing\Exception;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class NotAcceptableLanguageHttpException extends NotAcceptableHttpException
{
    private $requestedLanguage;
    private $availableLanguages;

    public function __construct($requestedLanguage, array $availableLanguages)
    {
        parent::__construct(
            sprintf(
                'The requested language "%s" was not available. Available languages: "%s"',
                $requestedLanguage,
                implode(', ', $availableLanguages)
            )
        );

        $this->requestedLanguage = $requestedLanguage;
        $this->availableLanguages = $availableLanguages;
    }

    public function getRequestedLanguage()
    {
        return $this->requestedLanguage;
    }

    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }
}
