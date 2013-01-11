<?php

namespace VGMdb\Component\View;

use VGMdb\Component\View\Logging\ViewLoggerInterface;

/**
 * Abstract view object.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractView extends \ArrayObject implements ViewInterface
{
    public $template;
    protected $logger;
    private static $engine; // must be redeclared in child classes
    protected static $globals = array();
    protected static $exception;

    /**
     * Create a new view instance.
     *
     * @param array               $data
     * @param mixed               $engine
     * @param ViewLoggerInterface $logger
     */
    public function __construct(array $data = array(), $engine = null, ViewLoggerInterface $logger = null)
    {
        if (!$engine) {
            $engine = function ($view) {
                return vsprintf($view->template, $view);
            };
        }

        static::$engine = $engine;
        $this->logger = $logger;

        parent::__construct($data);
    }

    /**
     * View factory for creating new view instances.
     *
     * @param mixed           $template
     * @param array           $data
     * @param mixed           $engine
     * @param LoggerInterface $logger
     * @return ViewInterface
     */
    public static function create($template, array $data = array(), $engine = null, $logger = null)
    {
        if (!$engine) {
            $engine = static::$engine;
        }

        if (!$logger) {
            $logger = $this->logger;
        }

        return new static($template, $data, $engine, $logger);
    }

    /**
     * Return the view engine.
     *
     * @return mixed
     */
    public function getEngine()
    {
        return static::$engine;
    }

    /**
     * Check if the render has thrown exceptions.
     *
     * @return Boolean
     */
    public function hasException()
    {
        return isset(static::$exception);
    }

    /**
     * Return recorded exceptions.
     *
     * @return \Exception
     */
    public function getException()
    {
        return static::$exception;
    }

    /**
     * {@inheritDoc}
     */
    public function with($data, $value = null)
    {
        if (!is_array($data) && !($data instanceof \Traversable)) {
            $data = array($data => $value);
        }

        foreach ($data as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public static function share($data, $value = null)
    {
        if (!(is_array($data) || $data instanceof \Traversable)) {
            $data = array($data => $value);
        }

        foreach ($data as $key => $value) {
            if (strtoupper($key) !== $key) {
                throw new \InvalidArgumentException(sprintf('Global "%s" must be uppercased.', $key));
            }
            self::$globals[$key] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function globals()
    {
        return static::$globals;
    }

    /**
     * Wraps a view object with another view.
     *
     * @param mixed  $view
     * @param string $key
     * @return View
     */
    public function wrap($view, $key = 'content')
    {
        if (!($view instanceof ViewInterface)) {
            $view = new View((string) $view);
        }

        $view[$key] = $this;

        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function render($data = array())
    {
        $this->with($data);

        //$start = microtime(true);

        if (null !== $this->logger) {
            $this->logger->startRender($this);
        }

        $output = $this->renderInternal($data);

        if (null !== $this->logger) {
            $this->logger->stopRender($this);
        }

        return $output;
    }

    /**
     * Engine-specific render function.
     *
     * @param array $data
     * @return string
     */
    abstract protected function renderInternal($data = array());

    /**
     * {@inheritDoc}
     */
    public function getEngineType()
    {
        return 'Closure';
    }

    /**
     * {@inheritDoc}
     */
    public function nest($view, $key = 'content')
    {
        if (!($view instanceof ViewInterface)) {
            $view = new static((string) $view);
        }

        if (isset($this[$key]) && $this[$key] instanceof ViewInterface) {
            if (!($this[$key] instanceof ViewCollection)) {
                $this[$key] = new ViewCollection($this[$key]);
            }
            $this[$key]->nest($view);
        } else {
            $this[$key] = $view;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        try {
            $content = $this->render();
        } catch (\Exception $e) {
            if (isset($this['DEBUG']) && $this['DEBUG'] === true) {
                self::$exception = new \RuntimeException($e->getMessage(), $e->getCode(), self::$exception);
            }
            $content = '';
        }

        return $content;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param  string $id The unique identifier for the parameter or object
     * @return mixed The value of the parameter or an object
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    function offsetGet($id)
    {
        if (array_key_exists($id, self::$globals)) {
            $value = self::$globals[$id];
        } else {
            if (!$this->offsetExists($id)) {
                throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
            }
            $value = parent::offsetGet($id);
        }

        return $value instanceof \Closure ? $value($this) : $value;
    }

    /**
     * Returns whether the requested index exists.
     *
     * @param  string $id The unique identifier for the parameter or object
     * @return Boolean True if the requested index exists, false otherwise
     */
    public function offsetExists($id)
    {
        if (array_key_exists($id, self::$globals)) {
            return true;
        }

        return parent::offsetExists($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy($globals = false)
    {
        $data = parent::getArrayCopy();

        if ($globals) {
            $data = array_merge($data, static::globals());
        }

        foreach ($data as $key => $value) {
            if ($value instanceof ViewInterface) {
                $data[$key] = $value->getArrayCopy();
            }
        }

        return $data;
    }
}
