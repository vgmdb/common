<?php

/*
 * This code was originally part of the Symfony2 FrameworkBundle.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace VGMdb\Component\Translation;

use VGMdb\Component\Routing\RequestContext;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Translation\MessageSelector;

/**
 * Translator with caching. Loaders are only initialized when the cache needs to be refreshed.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CachedTranslator extends Translator
{
    protected $context;
    protected $options;
    protected $loaderIds;

    /**
     * Constructor.
     *
     * Available options:
     *
     *   * cache_dir: The cache directory (or null to disable caching)
     *   * debug:     Whether to enable debugging or not (false by default)
     *
     * @param RequestContext  $context   A RequestContext instance
     * @param MessageSelector $selector  The message selector for pluralization
     * @param array           $loaderIds An array of loader format to class mappings
     * @param array           $options   An array of options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(RequestContext $context, MessageSelector $selector = null, array $loaderIds = array(), array $options = array())
    {
        $this->context = $context;
        $this->loaderIds = $loaderIds;

        $this->options = array(
            'cache_dir' => null,
            'debug'     => false,
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The Translator does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);

        parent::__construct(null, $selector);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = $this->context->getLanguage();
        }

        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadCatalogue($locale)
    {
        if (isset($this->catalogues[$locale])) {
            return;
        }

        if (null === $this->options['cache_dir']) {
            $this->initialize();

            return parent::loadCatalogue($locale);
        }

        $cache = new ConfigCache($this->options['cache_dir'].'/catalogue.'.$locale.'.php', $this->options['debug']);

        if (!$cache->isFresh()) {
            $this->initialize();
            parent::loadCatalogue($locale);

            $fallbackContent = '';
            $current = '';

            foreach ($this->computeFallbackLocales($locale) as $fallback) {
                $fallbackContent .= sprintf(<<<EOF
\$catalogue%s = new MessageCatalogue('%s', %s);
\$catalogue%s->addFallbackCatalogue(\$catalogue%s);


EOF
                    ,
                    ucfirst($fallback),
                    $fallback,
                    var_export($this->catalogues[$fallback]->all(), true),
                    ucfirst($current),
                    ucfirst($fallback)
                );
                $current = $fallback;
            }

            $content = sprintf(<<<EOF
<?php

use VGMdb\Component\Translation\MessageCatalogue;

\$catalogue = new MessageCatalogue('%s', %s);

%s
return \$catalogue;

EOF
                ,
                $locale,
                var_export($this->catalogues[$locale]->all(), true),
                $fallbackContent
            );

            $cache->write($content, $this->catalogues[$locale]->getResources());

            return;
        }

        $this->catalogues[$locale] = include $cache;
    }

    protected function initialize()
    {
        foreach ($this->loaderIds as $format => $class) {
            $this->addLoader($format, new $class());
        }
    }
}
