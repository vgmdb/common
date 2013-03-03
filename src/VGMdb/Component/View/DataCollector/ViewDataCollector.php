<?php

namespace VGMdb\Component\View\DataCollector;

use VGMdb\Component\View\AbstractView;
use VGMdb\Component\View\Logger\ViewLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ViewDataCollector extends DataCollector
{
    protected $logger;

    public function __construct(ViewLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['views'] = array();

        foreach ($this->logger->getViews() as $viewData) {
            $view = $viewData['view'];
            $time = $viewData['time'];
            $data = array();

            if ($view->getEngineType() !== 'Collection' && $view->getEngineType() !== 'Widget') {
                foreach ($view as $key => $value) {
                    $data[$key] = $this->varToString($value);
                }
                $template = $view->template;
            } else {
                $template = ($view->getEngineType() === 'Collection')
                    ? '(Collection)'
                    : get_class($view);
            }

            $datum = array(
                'template' => $template,
                'engine' => $view->getEngineType(),
                'data' => $data,
                'time' => $time
            );

            $this->data['views'][] = $datum;
        }

        $this->data['views'] = array_reverse($this->data['views']);
        $this->data['globals'] = AbstractView::globals();
    }

    /**
     * Returns the total time spend on rendering.
     *
     * @return float
     */
    public function getTime()
    {
        $time = 0;
        foreach ($this->data['views'] as $view) {
            $time += (float) $view['time'];
        }

        return $time;
    }

    public function getName()
    {
        return 'view';
    }
}
