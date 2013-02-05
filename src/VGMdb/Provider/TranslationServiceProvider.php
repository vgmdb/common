<?php

namespace VGMdb\Provider;

use VGMdb\Component\Translation\Translator;
use VGMdb\Component\Translation\TranslationLoader;
use Silex\Application;
use Silex\Provider\TranslationServiceProvider as BaseTranslationServiceProvider;
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
                'id'      => 'VGMdb\\Component\\Translation\\Annotation\\Id',
                'meaning' => 'VGMdb\\Component\\Translation\\Annotation\\Meaning',
                'ignore'  => 'VGMdb\\Component\\Translation\\Annotation\\Ignore',
            ));
            $parser->setIgnoreNotImportedAnnotations(true);

            $r = new \ReflectionClass('VGMdb\\Component\\Translation\\Annotation\\Id');
            $dir = dirname($r->getFilename());
            AnnotationRegistry::registerFile($dir . '/Id.php');
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
            $translator = new Translator($app['locale'], $app['translator.message_selector']);
            $translator->setFallbackLocale($app['locale_fallback']);

            foreach ($app['translator.loader.classes'] as $format => $class) {
                $translator->addLoader($format, new $class());

                if (array_key_exists($format, $app['translator.formats'])) {
                    $extension = $app['translator.formats'][$format];
                } else {
                    $extension = strtolower($format);
                }

                foreach (glob($app['translator.base_dir'] . '/*.' . $extension) as $file) {
                    $name = basename($file, '.' . $extension);
                    list($domain, $locale) = explode('.', $name);
                    $translator->addResource($format, $file, $locale, $domain);
                }
            }

            return $translator;
        });

        $app['translator.base_dir'] = __DIR__;
    }
}
