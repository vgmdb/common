<?php

namespace VGMdb\Component\HttpKernel\Controller\Traits;

use VGMdb\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * ResponseFactory trait.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
trait ResponseFactoryTrait
{
    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Aborts with an error message.
     *
     * @param string     $message  The error message
     * @param integer    $status   The status code to use for the Response
     * @param \Exception $previous The exception that caused the Response
     *
     * @return HttpExceptionInterface
     */
    public function abort($message, $status = 500, \Exception $previous = null)
    {
        switch ($status) {
            case 404:
                throw new NotFoundHttpException($message, $previous);
                break;
            case 403:
                throw new AccessDeniedHttpException($message, $previous);
                break;
            case 401:
                throw new UnauthorizedHttpException($this->app['name'], $message, $previous);
                break;
            case 400:
                throw new BadRequestHttpException($message, $previous);
                break;
            case 500:
            default:
                throw new HttpException($status, $message, $previous);
                break;
        }
    }

    /**
     * Returns a response with a specific status code.
     *
     * @param mixed   $data   The data returned by the controller
     * @param integer $status The status code to use for the Response
     *
     * @return Response
     */
    public function success($data, $status = 200)
    {
        return new Response($this->app['view'](null, $data), $status);
    }
}
