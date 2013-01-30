<?php

/*
 * This code was originally part of CraueFormFlowBundle.
 *
 * (c) 2011-2013 Christian Raue
 */

namespace VGMdb\Component\Form;

/**
 * Events thrown by form flow handling.
 *
 * @author Marcus Stöhr <dafish@soundtrack-board.de>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class FormFlowEvents
{
    const PRE_BIND = 'flow.pre_bind';

    const POST_BIND_REQUEST = 'flow.post_bind_request';

    const POST_BIND_SAVED_DATA = 'flow.post_bind_saved_data';

    const POST_VALIDATE = 'flow.post_validate';
}
