<?php

namespace VGMdb\Component\Templating\Helper;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Templating\Helper\HelperInterface;

/**
 * Provides translation functions.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslatorHelper implements HelperInterface
{
    protected $translator;
    protected $charset = 'UTF-8';

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator A TranslatorInterface instance
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @see TranslatorInterface::trans()
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @see TranslatorInterface::transChoice()
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * Sets the default charset.
     *
     * @param string $charset The charset
     *
     * @api
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Gets the default charset.
     *
     * @return string The default charset
     *
     * @api
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translator';
    }

}
