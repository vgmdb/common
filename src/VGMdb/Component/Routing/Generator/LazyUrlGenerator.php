<?php

namespace VGMdb\Component\Routing\Generator;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Implements a lazy UrlGenerator.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LazyUrlGenerator implements UrlGeneratorInterface
{
    private $factory;

    public function __construct(\Closure $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Returns the corresponding UrlGeneratorInterface instance.
     *
     * @return UrlGeneratorInterface
     */
    public function getUrlGenerator()
    {
        $urlGenerator = call_user_func($this->factory);
        if (!$urlGenerator instanceof UrlGeneratorInterface) {
            throw new \LogicException("Factory supplied to LazyUrlGenerator must return implementation of UrlGeneratorInterface.");
        }

        return $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->getUrlGenerator()->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->getUrlGenerator()->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->getUrlGenerator()->getContext();
    }
}
