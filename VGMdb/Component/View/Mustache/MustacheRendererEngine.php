<?php

namespace VGMdb\Component\View\Mustache;

use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Symfony\Component\Form\FormView;

/**
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MustacheRendererEngine extends TemplatingRendererEngine
{
    /**
     * @var \Mustache_Engine
     */
    private $engine;

    public function __construct(\Mustache_Engine $engine, array $defaultThemes = array())
    {
        $this->defaultThemes = $defaultThemes;
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function renderBlock(FormView $view, $resource, $blockName, array $variables = array())
    {
        return trim($this->engine->render($resource, $variables));
    }

    /**
     * Tries to load the resource for a block from a theme.
     *
     * @param string $cacheKey  The cache key for storing the resource.
     * @param string $blockName The name of the block to load a resource for.
     * @param mixed  $theme     The theme to load the block from.
     *
     * @return Boolean True if the resource could be loaded, false otherwise.
     */
    protected function loadResourceFromTheme($cacheKey, $blockName, $theme)
    {
        try {
            $this->engine->loadTemplate($templateName = '@MustacheForm/' . $theme . '/' . $blockName . '.ms');
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        $this->resources[$cacheKey][$blockName] = $templateName;

        return true;
    }
}
