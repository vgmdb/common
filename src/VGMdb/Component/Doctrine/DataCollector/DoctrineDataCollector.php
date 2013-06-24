<?php

namespace VGMdb\Component\Doctrine\DataCollector;

use Silex\Application;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DoctrineDataCollector.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineDataCollector extends DataCollector
{
    private $registry;
    private $connections;
    private $managers;
    private $loggers = array();

    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
        $this->connections = (null !== $registry) ? $registry->getConnectionNames() : null;
        $this->managers = (null !== $registry) ? $registry->getManagerNames() : null;
    }

    /**
     * Adds the stack logger for a connection.
     *
     * @param string     $name
     * @param DebugStack $logger
     */
    public function addLogger($name, DebugStack $logger)
    {
        $this->loggers[$name] = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $queries = array();
        foreach ($this->loggers as $name => $logger) {
            $queries += $this->sanitizeQueries($name, $logger->queries);
        }

        $this->data = array(
            'queries'     => $queries,
            'connections' => $this->connections,
            'managers'    => $this->managers,
        );
    }

    public function getManagers()
    {
        return $this->data['managers'];
    }

    public function getConnections()
    {
        return $this->data['connections'];
    }

    public function getQueryCount()
    {
        return array_sum(array_map('count', (array) $this->data['queries']));
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getTime()
    {
        $time = 0;
        foreach ((array) $this->data['queries'] as $query) {
            $time += $query['time'];
        }

        return $time;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'doctrine';
    }

    private function sanitizeQueries($connectionName, $queries)
    {
        $sanitized = array();
        foreach ($queries as $query) {
            $sanitized[] = $this->sanitizeQuery($connectionName, $query);
        }

        return $sanitized;
    }

    private function sanitizeQuery($connectionName, $query)
    {
        $query['explainable'] = true;
        $query['params'] = (array) $query['params'];
        $query['time'] = sprintf('%.3f', $query['executionMS']);
        unset($query['executionMS']);
        $query['connection'] = $connectionName;
        foreach ($query['params'] as $j => &$param) {
            if (isset($query['types'][$j])) {
                // Transform the param according to the type
                $type = $query['types'][$j];
                if (is_string($type)) {
                    $type = Type::getType($type);
                }
                if ($type instanceof Type) {
                    $query['types'][$j] = $type->getBindingType();
                    $platform = $this->registry->getConnection($connectionName)->getDatabasePlatform();
                    $param = $type->convertToDatabaseValue($param, $platform);
                }
            }

            list($param, $explainable) = $this->sanitizeParam($param);
            if (!$explainable) {
                $query['explainable'] = false;
            }
        }

        return $query;
    }

    /**
     * Sanitizes a param.
     *
     * The return value is an array with the sanitized value and a boolean
     * indicating if the original value was kept (allowing to use the sanitized
     * value to explain the query).
     *
     * @param mixed $var
     *
     * @return array
     */
    private function sanitizeParam($var)
    {
        if (is_object($var)) {
            return array(sprintf('Object(%s)', get_class($var)), false);
        }

        if (is_array($var)) {
            $a = array();
            $original = true;
            foreach ($var as $k => $v) {
                list($value, $orig) = $this->sanitizeParam($v);
                $original = $original && $orig;
                $a[$k] = $value;
            }

            return array($a, $original);
        }

        if (is_resource($var)) {
            return array(sprintf('Resource(%s)', get_resource_type($var)), false);
        }

        return array($var, true);
    }

    /**
     * Return a query with the parameters replaced
     *
     * @param string $query
     * @param array $parameters
     *
     * @return string
     */
    public function replaceQueryParameters($query, $parameters)
    {
        $i = 0;

        $result = preg_replace_callback(
            '/\?|(:[a-z0-9_]+)/i',
            function ($matches) use ($parameters, &$i) {
                $key = substr($matches[0], 1);
                if (!isset($parameters[$i]) && !isset($parameters[$key])) {
                    return $matches[0];
                }

                $value = isset($parameters[$i]) ? $parameters[$i] : $parameters[$key];
                $result = $this->escapeFunction($value);
                $i++;

                return $result;
            },
            $query
        );

        return $result;
    }

    /**
     * Escape parameters of a SQL query
     * NEVER USE THIS FUNCTION OUTSIDE ITS INTENDED SCOPE
     *
     * @internal
     *
     * @param mixed $parameter
     *
     * @return string
     */
    private function escapeFunction($parameter)
    {
        $result = $parameter;

        switch (true) {
            case is_string($result) :
                $result = "'" . addslashes($result) . "'";
                break;

            case is_array($result) :
                foreach ($result as &$value) {
                    $value = static::escapeFunction($value);
                }

                $result = implode(', ', $result);
                break;

            case is_object($result) :
                $result = addslashes((string) $result);
                break;
        }

        return $result;
    }
}
