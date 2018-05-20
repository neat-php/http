<?php

namespace Neat\Http;

class Input
{
    /**
     * Request
     *
     * @var Request
     */
    protected $request;

    /**
     * Data
     *
     * @var array
     */
    protected $data;

    /**
     * Sources
     *
     * @var array
     */
    protected $sources = ['query', 'post', 'files', 'cookie'];

    /**
     * Filters
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->load();
    }

    /**
     * Select sources (query, post, file, cookie)
     *
     * This reloads the input and flushes all previously applied filters and validations
     *
     * @param array $sources
     */
    public function from(...$sources)
    {
        if ($unknown = array_diff($sources, ['query', 'post', 'files', 'cookie'])) {
            throw new \RuntimeException('Unknown source: ' . implode(', ', $unknown));
        }
        if (empty($sources)) {
            throw new \RuntimeException('Sources must not be empty');
        }

        $this->sources = $sources;
        $this->load();
    }

    /**
     * Load input from the request or session
     */
    public function load()
    {
        $get = function ($source) {
            return $this->request->$source();
        };

        $this->data   = array_merge(...array_map($get, $this->sources));
        $this->errors = [];
    }

    /**
     * Get all variables
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Has variable?
     *
     * @param string $var
     * @return bool
     */
    public function has($var)
    {
        return array_key_exists($var, $this->data);
    }

    /**
     * Get variable
     *
     * @param string $var
     * @return mixed
     */
    public function get($var)
    {
        return $this->data[$var] ?? null;
    }

    /**
     * Register custom input filter
     *
     * @param string   $name
     * @param callable $filter
     */
    public function register($name, $filter)
    {
        $this->filters[$name] = $filter;
    }

    /**
     * Filter an input variable
     *
     * @param string       $var
     * @param string|array $filters
     * @param string       $type
     * @return mixed|null
     */
    public function filter($var, $filters, $type = null)
    {
        if (!is_array($filters)) {
            $filters = $filters ? explode('|', $filters) : [];
        }

        $value = &$this->data[$var] ?? null;
        if ($value === null) {
            return null;
        }
        foreach ($filters as $key => $filter) {
            $params = explode(':', $filter);
            $filter = is_string($key) ? $key : array_shift($params);
            $filter = $this->filters[$filter] ?? function (&$data) use ($filter, $params) {
                $data = $filter($data, ...$params);
            };

            $errors = $filter($value, ...$params);
            if ($errors) {
                $this->errors[$var] = $errors;
                break;
            }
            if ($value === null) {
                return null;
            }
        }

        if ($type) {
            settype($value, $type);
        }

        return $value;
    }

    /**
     * Get boolean input
     *
     * @param string       $var
     * @param string|array $filters
     * @return bool|null
     */
    public function bool($var, $filters = null)
    {
        return $this->filter($var, $filters, 'bool');
    }

    /**
     * Get floating point input
     *
     * @param string       $var
     * @param string|array $filters
     * @return float|null
     */
    public function float($var, $filters = null)
    {
        return $this->filter($var, $filters, 'float');
    }

    /**
     * Get integer input
     *
     * @param string       $var
     * @param string|array $filters
     * @return int|null
     */
    public function int($var, $filters = null)
    {
        return $this->filter($var, $filters, 'int');
    }

    /**
     * Get string input
     *
     * @param string       $var
     * @param string|array $filters
     * @return string|null
     */
    public function string($var, $filters = null)
    {
        return $this->filter($var, $filters, 'string');
    }

    /**
     * Get file input
     *
     * @param string       $var
     * @param string|array $filters
     * @return Upload|null
     */
    public function file($var, $filters = null)
    {
        if ($this->get($var) instanceof Upload) {
            return $this->filter($var, $filters);
        }

        return null;
    }

    /**
     * Get errors
     *
     * @param string $field (optional)
     * @return string[]
     */
    public function errors($field = null)
    {
        if ($field) {
            return $this->errors[$field] ?? [];
        }

        return array_merge([], ...array_values($this->errors));
    }

    /**
     * Is valid?
     *
     * @param string $field (optional)
     * @return bool
     */
    public function valid($field = null)
    {
        return empty($this->errors($field));
    }
}
