<?php

namespace VGMdb\Provider;

use VGMdb\Component\Translation\TranslationLoader;
use Silex\Application;
use Silex\Provider\TranslationServiceProvider as BaseTranslationServiceProvider;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;

/**
 * Translation component Provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['translator.extractor.classes'] = array();

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
            foreach ($app['translator.loader.classes'] as $format => $class) {
                $loader->addLoader($format, new $class());
            }

            return $loader;
        });

        $app['translator'] = $app->share(function ($app) {
            $translator = new Translator($app['locale'], $app['translator.message_selector']);
            $translator->setFallbackLocale($app['locale_fallback']);

            foreach (glob($app['translator.base_dir'] . '/*', \GLOB_ONLYDIR|\GLOB_NOSORT) as $path) {
                $format = basename($path);
                if (!array_key_exists($format, $app['translator.loader.classes'])) {
                    throw new \RuntimeException(sprintf('Missing loader class for translation format "%s".', $format));
                }

                $translator->addLoader($format, new $app['translator.loader.classes'][$format]());

                foreach (glob($app['translator.base_dir'] . '/' . $format . '/*.' . $format) as $file) {
                    $name = basename($file, '.' . $format);
                    list($domain, $locale) = explode('.', $name);
                    $translator->addResource($format, $file, $locale, $domain);
                }
            }

            return $translator;
        });

        $app['translator.base_dir'] = __DIR__;
    }
}
