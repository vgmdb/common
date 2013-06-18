<?php

/*
 * This file was originally part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */

namespace VGMdb\Component\Routing\Loader;

/**
 * AllowedMethodsLoaderInterface
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
interface AllowedMethodsLoaderInterface
{
    /**
     * Returns the allowed http methods
     *
     * array(
     *  'some_route'    => array('GET', 'POST'),
     *  'another_route' => array('DELETE', 'PUT'),
     * );
     *
     * @return array
     */
    function getAllowedMethods();
}
