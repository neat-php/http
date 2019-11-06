<?php

namespace Neat\Http;

use Neat\Http\Exception\MethodNotAllowedException;
use Neat\Http\Exception\RouteNotFoundException;
use RuntimeException;

/**
 * Router
 */
class Router
{
    /**
     * @var string|null
     */
    private $segment;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var callable[]
     */
    private $handlers;

    /**
     * @var Router[]
     */
    private $literals = [];

    /**
     * @var Router[]
     */
    private $variables = [];

    /**
     * @var Router|null
     */
    private $wildcard;

    /**
     * Router constructor
     *
     * @param string $segment
     */
    public function __construct(string $segment = null)
    {
        $this->segment = $segment;

        if ($segment && preg_match('/^\$([^:]+)(?::(.*))?$/', $segment, $match)) {
            $this->name       = $match[1];
            $this->expression = isset($match[2]) ? "/^$match[2]$/" : null;
        }
    }

    /**
     * Get segment
     *
     * @return null|string
     */
    public function segment()
    {
        return $this->segment;
    }

    /**
     * Is root segment?
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->segment === null;
    }

    /**
     * Is literal segment?
     *
     * @return bool
     */
    public function isLiteral()
    {
        return $this->segment[0] != '$' && $this->segment != '*';
    }

    /**
     * Is variable segment?
     *
     * @return bool
     */
    public function isVariable()
    {
        return $this->segment && $this->segment[0] == '$';
    }

    /**
     * Is wildcard segment?
     *
     * @return bool
     */
    public function isWildcard()
    {
        return $this->segment == '*';
    }

    /**
     * Add GET route
     *
     * @param string   $url
     * @param callable $handler
     */
    public function get($url, $handler)
    {
        $this->add('GET', $this->split($url), $handler);
    }

    /**
     * Add POST route
     *
     * @param string   $url
     * @param callable $handler
     */
    public function post($url, $handler)
    {
        $this->add('POST', $this->split($url), $handler);
    }

    /**
     * Add PUT route
     *
     * @param string   $url
     * @param callable $handler
     */
    public function put($url, $handler)
    {
        $this->add('PUT', $this->split($url), $handler);
    }

    /**
     * Add PATCH route
     *
     * @param string   $url
     * @param callable $handler
     */
    public function patch($url, $handler)
    {
        $this->add('PATCH', $this->split($url), $handler);
    }

    /**
     * Add DELETE route
     *
     * @param string   $url
     * @param callable $handler
     */
    public function delete($url, $handler)
    {
        $this->add('DELETE', $this->split($url), $handler);
    }

    /**
     * Add a controller route
     *
     * @param string $url
     * @param string $class
     */
    public function controller($url, $class)
    {
        $this->add('ANY', $this->split($url), $class);
    }

    /**
     * Get a sub-router
     *
     * @param string $url
     * @return Router
     */
    public function in($url): self
    {
        return $this->map($this->split($url));
    }

    /**
     * Split a path into segments
     *
     * @param string $path
     * @return array
     */
    private function split($path)
    {
        return array_filter(explode('/', $path));
    }

    private function map(array $segments)
    {
        if (!$segments) {
            return $this;
        }

        $segment = array_shift($segments);
        $map     = $this->literals[$segment]
            ?? $this->variables[$segment]
            ?? ($segment == '*' ? $this->wildcard : null);

        if (!$map) {
            $map = new Router($segment);
            if ($map->isWildcard()) {
                $this->wildcard = $map;
            } elseif ($map->isVariable()) {
                $this->variables[$segment] = $map;
            } else {
                $this->literals[$segment] = $map;
            }
        }

        return $map->map($segments);
    }

    /**
     * Add route mapping
     *
     * @param string   $method
     * @param array    $segments
     * @param callable $handler
     */
    private function add($method, array $segments, $handler)
    {
        $this->map($segments)->handlers[$method] = $handler;
    }

    /**
     * Matches segment?
     *
     * @param string $segment
     * @return bool
     */
    private function matchesSegment($segment)
    {
        if ($this->isWildcard()) {
            return true;
        } elseif ($this->isVariable() && (!$this->expression || preg_match($this->expression, $segment))) {
            return true;
        } elseif ($this->isLiteral() && $this->segment == $segment) {
            return true;
        }

        return false;
    }

    /**
     * Match path
     *
     * @param array $segments
     * @param array $arguments
     * @return Router
     */
    private function matchPath(array $segments, &$arguments = [])
    {
        if (!$segments) {
            return $this;
        }

        $segment = array_shift($segments);
        if (isset($this->literals[$segment])) {
            $match = $this->literals[$segment]->matchPath($segments, $arguments);
            if ($match) {
                return $match;
            }
        }
        foreach ($this->variables as $variableMap) {
            if (!$variableMap->matchesSegment($segment)) {
                continue;
            }
            $match = $variableMap->matchPath($segments, $arguments);
            if ($match) {
                $arguments[$variableMap->name] = $segment;

                return $match;
            }
        }
        if ($this->wildcard) {
            array_unshift($segments, $segment);
            $arguments = $segments;
            return $this->wildcard;
        }

        return null;
    }

    /**
     * Match method
     *
     * @param string $method
     * @return callable
     */
    private function matchMethod($method)
    {
        if (isset($this->handlers[$method])) {
            return $this->handlers[$method];
        }
        if (isset($this->handlers['ANY'])) {
            return $this->handlers['ANY'];
        }

        return null;
    }

    /**
     * Route a request and return the handler
     *
     * @param string $method
     * @param string $path
     * @param array  $parameters
     * @return callable
     * @throws RuntimeException
     */
    public function match($method, $path, array &$parameters = null)
    {
        $parameters = [];
        $map        = $this->matchPath($this->split($path), $parameters);
        if (!$map || !$map->handlers) {
            throw new RouteNotFoundException();
        }

        $handler = $map->matchMethod(strtoupper($method));
        if (!$handler) {
            throw new MethodNotAllowedException();
        }

        return $handler;
    }
}
