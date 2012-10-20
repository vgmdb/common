<?php

namespace VGMdb;

/**
 * @brief       ViewInterface objects must implement render(), nest() and __toString().
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
     * Insert another view as a data element.
     *
     * @param mixed  $view
     * @param string $key
     * @return ViewInterface
     */
    public function nest($view, $key = 'content');

    /**
     * Renders the object output, magically.
     *
     * @return string
     */
    public function __toString();
}