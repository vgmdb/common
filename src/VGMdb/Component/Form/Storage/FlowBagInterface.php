<?php

namespace VGMdb\Component\Form\Storage;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * FlowBagInterface.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
interface FlowBagInterface extends SessionBagInterface
{
    /**
     * Registers a message for a given type.
     *
     * @param string       $type
     * @param string|array $message
     */
    public function set($type, $message);

    /**
     * Gets flash messages for a given type.
     *
     * @param string $type    Message category type.
     * @param array  $default Default value if $type does not exist.
     *
     * @return array
     */
    public function peek($type, array $default = array());

    /**
     * Gets all flash messages.
     *
     * @return array
     */
    public function peekAll();

    /**
     * Gets and clears flash from the stack.
     *
     * @param string $type
     * @param array  $default Default value if $type does not exist.
     *
     * @return array
     */
    public function get($type, array $default = array());

    /**
     * Gets and clears flashes from the stack.
     *
     * @return array
     */
    public function all();

    /**
     * Sets all flash messages.
     */
    public function setAll(array $messages);

    /**
     * Has flash messages for a given type?
     *
     * @param string $type
     *
     * @return boolean
     */
    public function has($type);

    /**
     * Returns a list of all defined types.
     *
     * @return array
     */
    public function keys();
}
