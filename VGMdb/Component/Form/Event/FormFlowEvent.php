<?php

/*
 * This code was originally part of CraueFormFlowBundle.
 *
 * (c) 2011-2013 Christian Raue
 */

namespace VGMdb\Component\Form\Event;

use VGMdb\Component\Form\FormFlow;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base event class for form steps.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class FormFlowEvent extends Event
{
    /**
     * @var FormFlow
     */
    protected $flow;

    /**
     * @return FormFlow
     */
    public function getFlow()
    {
        return $this->flow;
    }
}
