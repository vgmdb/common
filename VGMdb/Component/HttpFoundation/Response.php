<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\Component\View\ViewInterface;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Representation of a HTTP response.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Response extends BaseResponse
{
    /**
     * Sets the response content. Does NOT convert ViewInterface objects.
     *
     * @param mixed $content
     * @return Response
     */
    public function setContent($content)
    {
        if ($content instanceof ViewInterface) {
            $this->content = $content;

            return $this;
        }

        return parent::setContent($content);
    }
}
