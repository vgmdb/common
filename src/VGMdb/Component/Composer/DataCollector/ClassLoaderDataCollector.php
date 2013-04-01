<?php

namespace VGMdb\Component\Composer\DataCollector;

use VGMdb\Component\Composer\Debug\TraceableClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects class loader data.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ClassLoaderDataCollector extends DataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $classes = TraceableClassLoader::getLoadedClasses();

        $this->data = array(
            'classes' => $classes,
            'classcount' => count($classes),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'classloader';
    }
}
