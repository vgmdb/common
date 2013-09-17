<?php

namespace VGMdb\Component\Debug;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as BaseExceptionHandler;

/**
 * Handles JSON or XML exceptions.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ApiExceptionHandler extends BaseExceptionHandler
{
    private $debug;
    private $charset;

    public function __construct($debug = true, $charset = 'UTF-8')
    {
        $this->debug = $debug;
        $this->charset = $charset;

        parent::__construct($debug, $charset);
    }

    /**
     * Returns an array instead of a Response.
     *
     * @param \Exception|FlattenException $exception An \Exception instance
     *
     * @return array
     */
    public function createResponse($exception)
    {
        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }

        return $this->getContent($exception);
    }

    public function getContent(FlattenException $exception)
    {
        if (!$this->debug) {
            return $this->getPublicContent($exception);
        }

        return $this->getDebugContent($exception);
    }

    public function getDebugContent(FlattenException $exception)
    {
        $code = $exception->getCode();
        $title = $exception->getMessage();
        $errors = array();

        try {
            $count = count($exception->getAllPrevious());
            foreach ($exception->toArray() as $position => $e) {
                $content = array(
                    'code' => isset($e['code']) ? $e['code'] : 0,
                    'message' => $e['message'],
                    'class' => $e['class'],
                    'trace' => array()
                );
                foreach ($e['trace'] as $trace) {
                    $content['trace'][] = array(
                        'func' => $trace['function'] ? sprintf('%s%s%s', $trace['class'], $trace['type'], $trace['function']) : null,
                        'args' => $trace['function'] ? $this->formatArgs($trace['args']) : null,
                        'file' => isset($trace['file']) ? $trace['file'] : null,
                        'line' => isset($trace['line']) ? $trace['line'] : null
                    );
                }
                $errors[] = $content;
            }
        } catch (\Exception $e) {
            // something nasty happened and we cannot throw an exception anymore
            $title = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($exception), $exception->getMessage());
        }

        return array(
            'code' => $code,
            'message' => $title,
            'errors' => $errors
        );
    }

    protected function getPublicContent(FlattenException $exception)
    {
        switch ($exception->getStatusCode()) {
            case 404:
                $title = 'Sorry, the page you are looking for could not be found.';
                break;
            default:
                $title = 'Whoops, looks like something went wrong.';
        }

        return array(
            'code' => $exception->getCode(),
            'message' => $title
        );
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     *
     * @return string
     */
    private function formatArgs(array $args)
    {
        $result = array();
        foreach ($args as $key => $item) {
            if ('object' === $item[0]) {
                $formattedValue = sprintf("object(%s)", $item[1]);
            } elseif ('array' === $item[0]) {
                $formattedValue = sprintf("array(%s)", is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
            } elseif ('string'  === $item[0]) {
                $formattedValue = sprintf("'%s'", htmlspecialchars($item[1], ENT_QUOTES | ENT_SUBSTITUTE, $this->charset));
            } elseif ('null' === $item[0]) {
                $formattedValue = 'null';
            } elseif ('boolean' === $item[0]) {
                $formattedValue = strtolower(var_export($item[1], true));
            } elseif ('resource' === $item[0]) {
                $formattedValue = 'resource';
            } else {
                $formattedValue = str_replace("\n", '', var_export(htmlspecialchars((string) $item[1], ENT_QUOTES | ENT_SUBSTITUTE, $this->charset), true));
            }

            $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", $key, $formattedValue);
        }

        return implode(', ', $result);
    }
}
