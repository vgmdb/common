<?php

namespace VGMdb\Component\User\Form\Flow;

use VGMdb\Component\Form\FormFlow;

class RegistrationFormFlow extends FormFlow
{
    protected $maxSteps = 2;

    protected function loadStepDescriptions()
    {
        return array(
            'Account',
            'Password',
        );
    }
}
