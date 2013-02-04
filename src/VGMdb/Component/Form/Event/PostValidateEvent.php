<?php

/*
 * This code was originally part of CraueFormFlowBundle.
 *
 * (c) 2011-2013 Christian Raue
 */

namespace VGMdb\Component\Form\Event;

use VGMdb\Component\Form\FormFlow;

/**
 * Event called once for the current step after validating the form data.
 *
 * @author Marcus Stöhr <dafish@soundtrack-board.de>
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PostValidateEvent extends FormFlowEvent
{
    /**
     * @var mixed
     */
    protected $formData;

    /**
     * @param FormFlow $flow
     * @param mixed $formData
     */
    public function __construct(FormFlow $flow, $formData)
    {
        $this->flow = $flow;
        $this->formData = $formData;
    }

    /**
     * @return mixed
     */
    public function getFormData()
    {
        return $this->formData;
    }
}
