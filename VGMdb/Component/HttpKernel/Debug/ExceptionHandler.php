<?php

namespace VGMdb\Component\HttpKernel\Debug;

use VGMdb\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler as BaseExceptionHandler;

/**
 * Handles exceptions.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ExceptionHandler extends BaseExceptionHandler
{
    private $debug;
    private $charset;

    public function __construct($debug = true, $charset = 'UTF-8')
    {
        $this->debug = $debug;
        $this->charset = $charset;

        parent::__construct($debug, $charset);
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
        switch ($exception->getStatusCode()) {
            case 404:
                $title = 'Sorry, the page you are looking for could not be found.';
                break;
            default:
                $title = 'Whoops, looks like something went wrong.';
        }

        $content = $this->getDebugHelp($exception);
        try {
            $count = count($exception->getAllPrevious());
            $total = $count + 1;
            foreach ($exception->toArray() as $position => $e) {
                $ind = $count - $position + 1;
                $class = $this->abbrClass($e['class']);
                $message = nl2br($e['message']);
                $content .= sprintf(<<<EOF
                    <div class="block_exception clear_fix">
                        <h2><span>%d/%d</span> %s: %s</h2>
                    </div>
                    <div class="block">
                        <ol class="traces list_exception">

EOF
                    , $ind, $total, $class, $message);
                foreach ($e['trace'] as $trace) {
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
        } catch (\Exception $e) {
            // something nasty happened and we cannot throw an exception anymore
            if ($this->debug) {
                $title = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($exception), $exception->getMessage());
            } else {
                $title = 'Whoops, looks like something went wrong.';
            }
        }

        return <<<EOF
            <header class="header">
                <div class="container">
                    <h1>$title</h1>
                </div>
            </header>
            <div id="container" class="container">
                <p>$content</p>
            </div>
EOF;
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

        $content = 'Our engineers are working on a fix. You might want to check back later.';

        return <<<EOF
            <header class="header">
                <div class="container">
                    <h1>$title</h1>
                </div>
            </header>
            <div id="container" class="container">
                <p>$content</p>
            </div>
EOF;
    }

    public function getStylesheet(FlattenException $exception)
    {
        return <<<EOF
          html {
            margin: 0;
            padding: 0;
            background-color: white;
          }
          body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 14px;
            text-rendering: optimizeLegibility;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: white;
          }
          .container {
            margin-right: auto;
            margin-left: auto;
            width: 940px;
          }
          pre {
            display: block;
            padding: 9.5px;
            margin: 10px 0;
            font-size: 13px;
            line-height: 20px;
            white-space: pre;
            white-space: pre-wrap;
            background-color: whiteSmoke;
            border: 1px solid #CCC;
            border: 1px solid rgba(0, 0, 0, 0.15);
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
          }
          code {
            padding: 2px 4px;
            color: #D14;
            white-space: nowrap;
            background-color: #F7F7F9;
            border: 1px solid #E1E1E8;
            font-size: 15px;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
          }
          blockquote {
            padding: 0 0 0 15px;
            margin: 0 0 20px;
            border-left: 5px solid #EEE;
            font-size: 16px;
            font-weight: 300;
            line-height: 22px;
          }
          blockquote.alert {
            border-left: 5px solid #B94A48;
            color: #B94A48;
          }
          .header {
            padding: 20px 0;
            margin-bottom: 20px;
            background-color: whiteSmoke;
            border-bottom: 1px solid #E5E5E5;
          }
          .center {
            margin: 40px auto;
            display: block;
          }
          p {
            line-height: 24px;
          }
          h1 {
            font-size: 30px;
            color: black;
            line-height: 32px;
            text-shadow: 1px 1px 0 white;
            letter-spacing: -1px;
            font-weight: bold;
          }
          h2 {
            font-size: 16px;
            line-height: 20px;
            font-weight: bold;
            margin: 20px 0;
          }
          ol {
            list-style-type: decimal;
          }
          li {
            display: list-item;
            text-align: -webkit-match-parent;
            list-style-type: decimal;
            line-height: 20px;
          }
          abbr {
            border-bottom: 1px dotted #333;
          }
          .xdebug-error {
            display: none;
          }
          br {
            display: none;
          }
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
    private function formatArgs(array $args)
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

    /**
     * Tries to generate solutions or instructions based on the exception raised.
     */
    protected function getDebugHelp(FlattenException $exception)
    {
        static $help = array(
            'ConfigServiceProvider' => array(
                'FileException' => "The application tried to create a local configuration file, but was unsuccessful. You can either make the Config directory writable or copy the file yourself. All personalized settings should go to the .yml file, not the original .yml.dist files. <pre>chmod 0777 app/Guru/Resources/Config</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'ConfigCache' => array(
                'RuntimeException' => "The configuration cache could not be created. Please ensure that your cache directory exists and is writable: <pre>mkdir data/cache\nchmod -R 0777 data/cache</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'FileCacheReader' => array(
                'InvalidArgumentException' => "The annotation cache could not be created. Please ensure that your cache directory exists and is writable: <pre>mkdir data/cache\nchmod -R 0777 data/cache</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'SessionHandlerProxy' => array(
                'ErrorException' => "The session handler failed. If you're storing sessions on disk, please ensure that the directory is writable: <pre>mkdir data/sessions\nchmod -R 0777 data/sessions</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'FilesystemCache' => array(
                'RuntimeException' => "The asset cache could not be created. Please ensure that your cache directory exists and is writable: <pre>mkdir data/cache\nchmod -R 0777 data/cache</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'AssetWriter' => array(
                'RuntimeException' => "The minified assets could not be written. Please ensure that /css/lib.css is writable: <pre>touch public/css/lib.css\nchmod 0777 public/css/lib.css</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'SqliteProfilerStorage' => array(
                'Exception' => "The profiler cache could not be created. Please ensure that your cache directory exists and is writable: <pre>mkdir data/cache\nchmod -R 0777 data/cache</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'AbstractView' => array(
                'RuntimeException' => "The template files could not be generated. Either there is an error in your template, or the template cache could not be created. Please ensure that your cache directory exists and is writable: <pre>mkdir data/cache\nchmod -R 0777 data/cache</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            ),
            'SerializerBuilder' => array(
                'InvalidArgumentException' => "The metadata cache could not be created. Please ensure that your cache directory exists and is writable: <pre>mkdir data/cache\nchmod -R 0777 data/cache</pre> Alternatively, run the workspace setup command: <pre>sudo app/cli guru:setup</pre>"
            )
        );

        if (!$exception->getFile()) {
            return '';
        }

        $file = basename($exception->getFile(), '.php');
        $class = explode('\\', $exception->getClass());
        $class = end($class);

        if (isset($help[$file][$class])) {
            return '<blockquote class="alert">' . $help[$file][$class] . '</blockquote>';
        }

        return '';
    }
}
