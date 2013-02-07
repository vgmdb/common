<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Silex\ExceptionListenerWrapper as BaseExceptionListenerWrapper;

/**
 * Wraps exception listeners.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ExceptionListenerWrapper extends BaseExceptionListenerWrapper
{
    public function __invoke(GetResponseForExceptionEvent $event)
    {
        if (false === $argument = $this->getArgument($event)) {
            return;
        }

        $exception = $event->getException();
        $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $response = call_user_func($this->callback, $argument, $code);

        $this->ensureResponse($response, $event);
    }

    protected function getArgument(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (is_array($this->callback)) {
            $callbackReflection = new \ReflectionMethod($this->callback[0], $this->callback[1]);
        } elseif (is_object($this->callback) && !$this->callback instanceof \Closure) {
            $callbackReflection = new \ReflectionObject($this->callback);
            $callbackReflection = $callbackReflection->getMethod('__invoke');
        } else {
            $callbackReflection = new \ReflectionFunction($this->callback);
        }

        if ($callbackReflection->getNumberOfParameters() > 0) {
            $parameters = $callbackReflection->getParameters();
            $expectedParameter = $parameters[0];
            if (!$expectedParameter->getClass()) {
                return false;
            }
            if ($expectedParameter->getClass()->isInstance($event)) {
                return $event;
            }
            if ($expectedParameter->getClass()->isInstance($exception)) {
                return $exception;
            }
        }

        return false;
    }
}
