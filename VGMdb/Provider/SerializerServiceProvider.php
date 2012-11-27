<?php

namespace VGMdb\Provider;

use VGMdb\Component\Serializer\LazyLoadingSerializer;
use VGMdb\Component\Serializer\Construction\DoctrineObjectConstructor;
use VGMdb\Component\Serializer\EventDispatcher\LazyEventDispatcher;
use VGMdb\Component\Serializer\Handler\LazyHandlerRegistry;
use VGMdb\Component\Serializer\Metadata\Driver\LazyLoadingDriver;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\SerializerBundle\Serializer\Serializer;
use JMS\SerializerBundle\Serializer\GraphNavigator;
//use JMS\SerializerBundle\Serializer\LazyLoadingSerializer;
use JMS\SerializerBundle\Serializer\JsonSerializationVisitor;
use JMS\SerializerBundle\Serializer\JsonDeserializationVisitor;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\XmlDeserializationVisitor;
use JMS\SerializerBundle\Serializer\YamlSerializationVisitor;
//use JMS\SerializerBundle\Serializer\Construction\DoctrineObjectConstructor;
use JMS\SerializerBundle\Serializer\Construction\UnserializeObjectConstructor;
use JMS\SerializerBundle\Serializer\EventDispatcher\EventDispatcher;
//use JMS\SerializerBundle\Serializer\EventDispatcher\LazyEventDispatcher;
use JMS\SerializerBundle\Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber;
use JMS\SerializerBundle\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\SerializerBundle\Serializer\Handler\HandlerRegistry;
//use JMS\SerializerBundle\Serializer\Handler\LazyHandlerRegistry;
use JMS\SerializerBundle\Serializer\Handler\ArrayCollectionHandler;
use JMS\SerializerBundle\Serializer\Handler\ConstraintViolationHandler;
use JMS\SerializerBundle\Serializer\Handler\DateTimeHandler;
use JMS\SerializerBundle\Serializer\Handler\FormErrorHandler;
use JMS\SerializerBundle\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\SerializerBundle\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\SerializerBundle\Serializer\Naming\CacheNamingStrategy;
use JMS\SerializerBundle\Metadata\Driver\AnnotationDriver;
use JMS\SerializerBundle\Metadata\Driver\YamlDriver;
use JMS\SerializerBundle\Metadata\Driver\XmlDriver;
use JMS\SerializerBundle\Metadata\Driver\PhpDriver;
use Metadata\MetadataFactory;
use Metadata\Cache\FileCache;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
//use Metadata\Driver\LazyLoadingDriver;

/**
 * JMS Serializer Bundle integration for Silex.
 *
 * @link https://github.com/pink-tie/JMSSerializerServiceProvider
 *
 * @author Marijn Huizendveld <marijn@pink-tie.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SerializerServiceProvider implements ServiceProviderInterface
{
    private $version;

    public function __construct($version = '1.0')
    {
        $this->version = $version;
    }

    public function register(Application $app)
    {
        // default settings
        $app['serializer.naming_strategy.separator'] = '_';
        $app['serializer.naming_strategy.lower_case'] = true;
        $app['serializer.datetime_handler.default_format'] = \DateTime::ISO8601;
        $app['serializer.datetime_handler.default_timezone'] = 'UTC';
        $app['serializer.disable_external_entities'] = true;
        $app['serializer.src_dir'] = '';
        $app['serializer.cache_dir'] = '';
        $app['serializer.config_dirs'] = array();
        $app['serializer.json_serialization_visitor.options'] = array();
        $app['serializer.xml_deserialization_visitor.doctype_whitelist'] = array();

        // event dispatcher
        $app['serializer.event_dispatcher'] = $app->share(function () use ($app) {
            $listeners = array();
            $classes = array(
                'serializer.doctrine_proxy_subscriber' => 'JMS\\SerializerBundle\\Serializer\\EventDispatcher\\Subscriber\\DoctrineProxySubscriber'
            );

            $eventDispatcher = new LazyEventDispatcher($app);

            foreach ($classes as $id => $class) {
                foreach (call_user_func(array($class, 'getSubscribedEvents')) as $eventData) {
                    if (!isset($eventData['event'])) {
                        throw new \RuntimeException(
                            sprintf('The service "%s" (class: %s) must return an event for each subscribed event.', $id, $subscriberClass)
                        );
                    }

                    $class = isset($eventData['class']) ? strtolower($eventData['class']) : null;
                    $format = isset($eventData['format']) ? $eventData['format'] : null;
                    $method = isset($eventData['method']) ? $eventData['method'] : EventDispatcher::getDefaultMethodName($eventData['event']);
                    $priority = isset($attributes['priority']) ? (integer) $attributes['priority'] : 0;

                    $listeners[$eventData['event']][$priority][] = array(array($id, $method), $class, $format);
                }
            }

            if (count($listeners)) {
                array_walk($listeners, function (&$value, $key) {
                    ksort($value);
                });

                foreach ($listeners as &$events) {
                    $events = call_user_func_array('array_merge', $events);
                }

                $eventDispatcher->setListeners($listeners);
            }

            return $eventDispatcher;
        });

        $app['serializer.doctrine_proxy_subscriber'] = $app->share(function () use ($app) {
            return new DoctrineProxySubscriber();
        });

        // handlers
        $app['serializer.handler_registry'] = $app->share(function () use ($app) {
            $handlers = array();
            $classes = array(
                'serializer.datetime_handler'             => 'JMS\\SerializerBundle\\Serializer\\Handler\\DateTimeHandler',
                'serializer.array_collection_handler'     => 'JMS\\SerializerBundle\\Serializer\\Handler\\ArrayCollectionHandler',
                'serializer.form_error_handler'           => 'JMS\\SerializerBundle\\Serializer\\Handler\\FormErrorHandler',
                'serializer.constraint_violation_handler' => 'JMS\\SerializerBundle\\Serializer\\Handler\\ConstraintViolationHandler'
            );

            foreach ($classes as $id => $class) {
                if (!isset($app[$id])) {
                    continue;
                }

                foreach (call_user_func(array($class, 'getSubscribingMethods')) as $methodData) {
                    if (!isset($methodData['format'], $methodData['type'])) {
                        throw new \RuntimeException(
                            sprintf('Each method returned from getSubscribingMethods of service "%s" must have a "type", and "format" attribute.', $id)
                        );
                    }

                    $directions = array(GraphNavigator::DIRECTION_DESERIALIZATION, GraphNavigator::DIRECTION_SERIALIZATION);
                    if (isset($methodData['direction'])) {
                        $directions = array($methodData['direction']);
                    }

                    foreach ($directions as $direction) {
                        $method = isset($methodData['method']) ? $methodData['method'] : HandlerRegistry::getDefaultMethod($direction, $methodData['type'], $methodData['format']);
                        $handlers[$direction][$methodData['type']][$methodData['format']] = array($id, $method);
                    }
                }
            }

            return new LazyHandlerRegistry($app, $handlers);
        });

        $app['serializer.datetime_handler'] = $app->share(function () use ($app) {
            $format = $app['serializer.datetime_handler.default_format'];
            $defaultTimezone = $app['serializer.datetime_handler.default_timezone'];

            return new DateTimeHandler($format, $defaultTimezone);
        });

        $app['serializer.array_collection_handler'] = $app->share(function () use ($app) {
            return new ArrayCollectionHandler();
        });

        if (isset($app['translator'])) {
            $app['serializer.form_error_handler'] = $app->share(function () use ($app) {
                return new FormErrorHandler($app['translator']);
            });
        }

        if (class_exists('Symfony\\Component\\Validator\\ConstraintViolation')) {
            $app['serializer.constraint_violation_handler'] = $app->share(function () use ($app) {
                return new ConstraintViolationHandler();
            });
        }

        // metadata drivers
        $app['serializer.metadata.file_locator'] = $app->share(function () use ($app) {
            return new FileLocator($app['serializer.config_dirs']);
        });

        $app['serializer.metadata.yaml_driver'] = $app->share(function () use ($app) {
            return new YamlDriver($app['serializer.metadata.file_locator']);
        });

        $app['serializer.metadata.xml_driver'] = $app->share(function () use ($app) {
            return new XmlDriver($app['serializer.metadata.file_locator']);
        });

        $app['serializer.metadata.php_driver'] = $app->share(function () use ($app) {
            return new PhpDriver($app['serializer.metadata.file_locator']);
        });

        $app['serializer.metadata.annotation_reader'] = $app->share(function () use ($app) {
            return new AnnotationReader();
        });

        $app['serializer.metadata.annotation_driver'] = $app->share(function () use ($app) {
            return new AnnotationDriver($app['serializer.metadata.annotation_reader']);
        });

        $app['serializer.metadata.driver'] = $app->share(function () use ($app) {
            return new DriverChain(
                array(
                    $app['serializer.metadata.yaml_driver'],
                    $app['serializer.metadata.xml_driver'],
                    $app['serializer.metadata.php_driver'],
                    $app['serializer.metadata.annotation_driver']
                )
            );
        });

        $app['serializer.lazy_loading_driver'] = $app->share(function () use ($app) {
            return new LazyLoadingDriver($app, 'serializer.metadata.driver');
        });

        // metadata factories
        $app['serializer.metadata.cache'] = $app->share(function () use ($app) {
            return new FileCache($app['serializer.cache_dir']);
        });

        $app['serializer.metadata_factory'] = $app->share(function () use ($app) {
            $factory = new MetadataFactory(
                $app['serializer.lazy_loading_driver'],
                'Metadata\\ClassHierarchyMetadata',
                isset($app['debug']) ? $app['debug'] : false
            );
            $factory->setCache($app['serializer.metadata.cache']);

            return $factory;
        });

        // exclusion strategies
        $_version = $this->version;
        $app['serializer.version_exclusion_strategy'] = $app->share(function () use ($app, $_version) {
            return new VersionExclusionStrategy($_version);
        });

        // naming strategies
        $app['serializer.camel_case_naming_strategy'] = $app->share(function () use ($app) {
            $separator = $app['serializer.naming_strategy.separator'];
            $lowerCase = $app['serializer.naming_strategy.lower_case'];

            return new CamelCaseNamingStrategy($separator, $lowerCase);
        });

        $app['serializer.naming_strategy'] = $app->share(function () use ($app) {
            return new SerializedNameAnnotationStrategy($app['serializer.camel_case_naming_strategy']);
        });

        $app['serializer.cache_naming_strategy'] = $app->share(function () use ($app) {
            return new CacheNamingStrategy($app['serializer.camel_case_naming_strategy']);
        });

        // object constructors
        $app['serializer.object_constructor'] = $app->share(function () use ($app) {
            return new UnserializeObjectConstructor();
        });

        $app['serializer.doctrine_object_constructor'] = $app->share(function () use ($app) {
            return new DoctrineObjectConstructor($app['entity_manager'], $app['serializer.object_constructor']);
        });

        // visitors
        $app['serializer.json_serialization_visitor'] = $app->share(function () use ($app) {
            $jsonSerializationVisitor = new JsonSerializationVisitor($app['serializer.naming_strategy']);
            $jsonSerializationVisitor->setOptions($app['serializer.json_serialization_visitor.options']);

            return $jsonSerializationVisitor;
        });

        $app['serializer.json_deserialization_visitor'] = $app->share(function () use ($app) {
            return new JsonDeserializationVisitor($app['serializer.naming_strategy']);
        });

        $app['serializer.xml_serialization_visitor'] = $app->share(function () use ($app) {
            return new XmlSerializationVisitor($app['serializer.naming_strategy']);
        });

        $app['serializer.xml_deserialization_visitor'] = $app->share(function () use ($app) {
            $xmlDeserializationVisitor = new XmlDeserializationVisitor($app['serializer.naming_strategy']);
            $xmlDeserializationVisitor->setDoctypeWhitelist($app['serializer.xml_deserialization_visitor.doctype_whitelist']);
            if (false === $app['serializer.disable_external_entities']) {
                $xmlDeserializationVisitor->enableExternalEntities();
            }

            return $xmlDeserializationVisitor;
        });

        $app['serializer.yaml_serialization_visitor'] = $app->share(function () use ($app) {
            return new YamlSerializationVisitor($app['serializer.naming_strategy']);
        });

        $app['serializer.serialization_visitors'] = $app->share(function () use ($app) {
            return array(
                'json' => $app['serializer.json_serialization_visitor'],
                'xml'  => $app['serializer.xml_serialization_visitor'],
                'yml'  => $app['serializer.yaml_serialization_visitor']
            );
        });

        $app['serializer.deserialization_visitors'] = $app->share(function () use ($app) {
            return array(
                'json' => $app['serializer.json_deserialization_visitor'],
                'xml'  => $app['serializer.xml_deserialization_visitor']
            );
        });

        $app['serializer.serialization.custom_handlers'] = $app->share(function () use ($app) {
            $handlers = array();

            if (isset($app['serializer.constraint_violation_handler'])) {
                $handlers[] = $app['serializer.constraint_violation_handler'];
            }

            if (isset($app['serializer.form_error_handler'])) {
                $handlers[] = $app['serializer.form_error_handler'];
            }

            return $handlers;
        });

        $app['serializer.deserialization.custom_handlers'] = $app->share(function () use ($app) {
            return array(
                $app['serializer.array_collection_handler'],
                $app['serializer.datetime_handler']
            );
        });

        // serializer
        $app['serializer'] = $app->share(function () use ($app) {
            $serializer = new LazyLoadingSerializer(
                $app['serializer.metadata_factory'],
                $app['serializer.handler_registry'],
                $app['serializer.object_constructor'],
                $app['serializer.event_dispatcher'],
                null,
                $app['serializer.serialization_visitors'],
                $app['serializer.deserialization_visitors']
            );
            $serializer->setContainer($app);

            return $serializer;
        });
    }

    public function boot(Application $app)
    {
        // Register our annotations upon boot so that Doctrine won't crash and burn
        AnnotationRegistry::registerAutoloadNamespace('JMS\\SerializerBundle\\Annotation', $app['serializer.src_dir']);
    }
}
