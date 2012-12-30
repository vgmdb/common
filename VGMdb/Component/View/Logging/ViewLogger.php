<?php

namespace VGMdb\Component\View\Logging;

use VGMdb\Component\View\ViewInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * A view logger that supports log collection and time profiling.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ViewLogger implements ViewLoggerInterface
{
    protected $logger;
    protected $stopwatch;
    protected $views;
    protected $events;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger    A LoggerInterface instance
     * @param Stopwatch       $stopwatch A Stopwatch instance
     */
    public function __construct(LoggerInterface $logger, Stopwatch $stopwatch = null)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
        $this->views = array();
        $this->events = array();
    }

    /**
     * {@inheritdoc}
     */
    public function startRender(ViewInterface $view)
    {
        if (null !== $this->stopwatch) {
            $id = spl_object_hash($view);
            $this->events[$id] = $this->stopwatch->start($view->getEngineType(), 'template');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopRender(ViewInterface $view)
    {
        $time = null;

        if (null !== $this->stopwatch) {
            $id = spl_object_hash($view);
            if (isset($this->events[$id])) {
                $this->events[$id]->stop($view->getEngineType());
                $time = $this->events[$id]->getDuration();
                unset($this->events[$id]);
            }
        }

        $this->views[] = array(
            'view' => $view,
            'time' => $time
        );

        $this->logger->info(
            sprintf(
                'Template "%s" rendered with %s%s',
                $view->template,
                $view->getEngineType(),
                (null !== $time) ? sprintf(' in %sms', $time) : ''
            )
        );
    }

    /**
     * Returns collected view data.
     *
     * @return array
     */
    public function getViews()
    {
        return $this->views;
    }
}
