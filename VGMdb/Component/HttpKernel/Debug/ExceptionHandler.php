<?php

namespace VGMdb\Component\HttpKernel\Debug;

use VGMdb\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;

if (!defined('ENT_SUBSTITUTE')) {
    define('ENT_SUBSTITUTE', 8);
}

/**
 * @brief       Handles exceptions and fatal errors. Mostly copied from Symfony's HttpKernel.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ExceptionHandler
{
    private $debug;
    private $charset;

    public function __construct($debug = true, $charset = 'UTF-8')
    {
        $this->debug = $debug;
        $this->charset = $charset;
    }

    /**
     * Register the exception handler.
     *
     * @param Boolean $debug
     *
     * @return ExceptionHandler The registered exception handler
     */
    public static function register($debug = true)
    {
        $handler = new static($debug);

        set_exception_handler(array($handler, 'handle'));

        if ($debug) {
            register_shutdown_function(array($handler, 'shutdown'));
        }

        return $handler;
    }

    /**
     * Sends a Response for a fatal error.
     */
    public function shutdown()
    {
        $error = error_get_last();

        if (isset($error)) {
            $this->handle(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
    }

    /**
     * Sends a Response for the given Exception.
     *
     * @param \Exception $exception An \Exception instance
     */
    public function handle(\Exception $exception)
    {
        list($title, $html, $code, $headers) = $this->createResponse($exception);

        $response = new Response($html, $code, $headers);

        $response->send();
    }

    /**
     * Creates the error Response associated with the given Exception.
     *
     * @param \Exception|FlattenException $exception An \Exception instance
     *
     * @return Response A Response instance
     */
    public function createResponse($exception)
    {
        $content = '';
        $title = '';
        try {
            if (!$exception instanceof FlattenException) {
                $exception = FlattenException::create($exception);
            }

            switch ($exception->getStatusCode()) {
                case 404:
                    $title = 'Sorry, the page you are looking for could not be found.';
                    break;
                default:
                    $title = /*$exception->getMessage() ?: */'Whoops, looks like something went wrong.';
            }

            if ($this->debug) {
                $content = $this->getContent($exception);
            } else {
                $content = $this->getPublicContent($exception);
            }
        } catch (\Exception $e) {
            // something nasty happened and we cannot throw an exception here anymore
            if ($this->debug) {
                $title = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($exception), $exception->getMessage());
            } else {
                $title = 'Whoops, looks like something went wrong.';
            }
        }

        return array(
            $title,
            $this->decorate($content, $title),
            $exception->getStatusCode(),
            $exception->getHeaders()
        );
    }

    private function getContent($exception)
    {
        $message = nl2br($exception->getMessage());
        $class = $this->abbrClass($exception->getClass());
        $count = count($exception->getAllPrevious());
        $content = '';
        foreach ($exception->toArray() as $position => $e) {
            $ind = $count - $position + 1;
            $total = $count + 1;
            $class = $this->abbrClass($e['class']);
            $message = nl2br($e['message']);
            $content .= sprintf(<<<EOF
<div class="block_exception clear_fix">
    <h3><span>%d/%d</span> %s: %s</h3>
</div>
<div class="block">
    <ol class="traces list_exception">

EOF
                , $ind, $total, $class, $message);
            foreach ($e['trace'] as $i => $trace) {
                $content .= '       <li>';
                if ($trace['function']) {
                    $content .= sprintf('at %s%s%s(%s)', $this->abbrClass($trace['class']), $trace['type'], $trace['function'], $this->formatArgs($trace['args']));
                }
                if (isset($trace['file']) && isset($trace['line'])) {
                    if ($linkFormat = ini_get('xdebug.file_link_format')) {
                        $link = str_replace(array('%f', '%l'), array($trace['file'], $trace['line']), $linkFormat);
                        $content .= sprintf(' in <a href="%s" title="Go to source">%s line %s</a>', $link, $trace['file'], $trace['line']);
                    } else {
                        $content .= sprintf(' in %s line %s', $trace['file'], $trace['line']);
                    }
                }
                $content .= "</li>\n";
            }

            $content .= "    </ol>\n</div>\n";
        }

        return $content;
    }

    private function getPublicContent($exception)
    {
        $content = '<h3>There was an error processing your request.</h3>' . PHP_EOL;
        $content .= '<p>Our engineers are working on a fix. You might want to check back later.</p>' . PHP_EOL;
        return $content;
    }

    private function decorate($content, $title)
    {
        return <<<EOF
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width">
  <title>{$title}</title>
  <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,400,700">
  <link rel="stylesheet" href="/css/libs/bootstrap.css">
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="header">
  <div class="container">
    <h1>{$title}</h1>
  </div>
</header>
  <div id="container" class="container">
    <div class="row">
      <div class="span12">
        {$content}
      </div>
    </div>
  </div>
  <footer class="footer">
    <div class="container">
      <p>Assembled using Silex, Opauth, Mustache.php, RequireJS, Hogan.js, Modernizr, jQuery, Backbone Boilerplate and Bootstrap 2.1</p>
    </div>
  </footer>
</body>
</html>
EOF;
    }

    private function abbrClass($class)
    {
        $parts = explode('\\', $class);

        return sprintf("<abbr title=\"%s\">%s</abbr>", $class, array_pop($parts));
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     *
     * @return string
     */
    public function formatArgs(array $args)
    {
        $result = array();
        foreach ($args as $key => $item) {
            if ('object' === $item[0]) {
                $formattedValue = sprintf("<em>object</em>(%s)", $this->abbrClass($item[1]));
            } elseif ('array' === $item[0]) {
                $formattedValue = sprintf("<em>array</em>(%s)", is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
            } elseif ('string'  === $item[0]) {
                $formattedValue = sprintf("'%s'", htmlspecialchars($item[1], ENT_QUOTES | ENT_SUBSTITUTE, $this->charset));
            } elseif ('null' === $item[0]) {
                $formattedValue = '<em>null</em>';
            } elseif ('boolean' === $item[0]) {
                $formattedValue = '<em>'.strtolower(var_export($item[1], true)).'</em>';
            } elseif ('resource' === $item[0]) {
                $formattedValue = '<em>resource</em>';
            } else {
                $formattedValue = str_replace("\n", '', var_export(htmlspecialchars((string) $item[1], ENT_QUOTES | ENT_SUBSTITUTE, $this->charset), true));
            }

            $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", $key, $formattedValue);
        }

        return implode(', ', $result);
    }
}
