<?php

namespace VGMdb\Component\Translation;

use VGMdb\Component\Silex\AbstractResourceProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;

/**
 * Translation component Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationServiceProvider extends AbstractResourceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['translator.domains'] = array();
        $app['translator.locale_fallback'] = 'en';
        $app['translator.base_dir'] = __DIR__;

        $app['translator.message_selector'] = $app->share(function () {
            return new MessageSelector();
        });

        $app['translator.extractor.classes'] = array();
        $app['translator.formats'] = array(
            'mofile' => 'mo',
            'pofile' => 'po',
            'xliff' => 'xlf',
            'yaml' => 'yml',
            'json' => 'js'
        );

        $app['translator.doc_parser'] = $app->share(function ($app) {
            $parser = new DocParser();
            $parser->setImports(array(
                'desc'    => 'VGMdb\\Component\\Translation\\Annotation\\Desc',
                'meaning' => 'VGMdb\\Component\\Translation\\Annotation\\Meaning',
                'ignore'  => 'VGMdb\\Component\\Translation\\Annotation\\Ignore',
            ));
            $parser->setIgnoreNotImportedAnnotations(true);

            $r = new \ReflectionClass('VGMdb\\Component\\Translation\\Annotation\\Desc');
            $dir = dirname($r->getFilename());
            AnnotationRegistry::registerFile($dir . '/Desc.php');
            AnnotationRegistry::registerFile($dir . '/Meaning.php');
            AnnotationRegistry::registerFile($dir . '/Ignore.php');

            return $parser;
        });

        $app['translator.php_parser'] = $app->share(function ($app) {
            return new \PHPParser_Parser(new \PHPParser_Lexer());
        });

        $app['translator.php_traverser'] = $app->share(function ($app) {
            return new \PHPParser_NodeTraverser();
        });

        $app['translator.extractor'] = $app->share(function ($app) {
            $extractor = new ChainExtractor();
            foreach ($app['translator.extractor.classes'] as $format => $class) {
                $extractor->addExtractor($format, new $class($app));
            }

            return $extractor;
        });

        $app['translator.writer'] = $app->share(function ($app) {
            $writer = new TranslationWriter();
            foreach ($app['translator.writer.classes'] as $format => $class) {
                $writer->addDumper($format, new $class());
            }

            return $writer;
        });

        $app['translator.loader'] = $app->share(function ($app) {
            $loader = new TranslationLoader();
            $loader->setExtensions($app['translator.formats']);
            foreach ($app['translator.loader.classes'] as $format => $class) {
                $loader->addLoader($format, new $class());
            }

            return $loader;
        });

        $app['translator'] = $app->share(function ($app) {
            if ($app['cache']) {
                $translator = new CachedTranslator(
                    $app['request_context'],
                    $app['translator.message_selector'],
                    $app['translator.loader.classes'],
                    array(
                        'cache_dir' => $app['translator.cache_dir'],
                        'debug' => $app['debug']
                    )
                );
            } else {
                $translator = new Translator($app['locale'], $app['translator.message_selector']);
            }
            $translator->setFallbackLocale($app['translator.locale_fallback']);

            foreach ($app['translator.loader.classes'] as $format => $class) {
                if (!$translator instanceof CachedTranslator) {
                    $translator->addLoader($format, new $class());
                }

                $extension = isset($app['translator.formats'][$format])
                    ? $app['translator.formats'][$format]
                    : strtolower($format);

                foreach (glob($app['translator.base_dir'] . '/*.' . $extension) as $file) {
                    $name = basename($file, '.' . $extension);
                    list($domain, $locale) = explode('.', $name);
                    $translator->addResource($format, $file, $locale, $domain);
                }
            }

            return $translator;
        });
    }

    public function boot(Application $app)
    {
    }
}
