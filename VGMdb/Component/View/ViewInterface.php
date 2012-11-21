<?php

namespace VGMdb\Component\View;

/**
 * @brief       ViewInterface provides the basic signature of all View objects.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
interface ViewInterface
{
    /**
     * Get the evaluated string content.
     *
     * @param array $data
     * @return string
     */
    public function render($data = array());

    /**
     * Initialize view data.
     *
     * @param mixed $data
     * @param mixed $value
     * @return ViewInterface
     */
    public function with($data, $value = null);

    /**
     * Apply global value across all views.
     *
     * @param mixed $data
     * @param mixed $value
     */
    static public function share($data, $value = null);

    /**
     * Dump all global values.
     *
     * @return array
     */
    static public function globals();

    /**
     * Insert another view as a data element.
     *
     * @param mixed  $view
     * @param string $key
     * @return ViewInterface
     */
    public function nest($view, $key = 'content');

    /**
     * Renders the object output as an array.
     *
     * @param boolean $globals
     * @return array
     */
    public function getArrayCopy($globals = false);

    /**
     * Renders the object output, magically.
     *
     * @return string
     */
    public function __toString();
}
