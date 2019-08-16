<?php declare(strict_types=1);

namespace Neat\Http;

use RuntimeException;

class Input
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Input constructor
     *
     * @param Request $request
     * @param Session $session
     */
    public function __construct(Request $request, Session $session)
    {
        $this->request = $request;
        $this->session = $session;

        $this->init();
    }

    /**
     * Initialize the input
     */
    public function init()
    {
        $this->load('query', 'post', 'files', 'cookie');
    }

    /**
     * Clear the input
     */
    public function clear()
    {
        $this->data   = [];
        $this->errors = [];
    }

    /**
     * Load input from the requested sources and the session
     *
     * Flushes all previously applied filters and validations
     *
     * @param array $sources
     */
    public function load(...$sources)
    {
        if (!$sources) {
            throw new RuntimeException('Sources must not be empty');
        }

        $this->clear();
        foreach ($sources as $source) {
            switch ($source) {
                case 'query':
                case 'post':
                case 'files':
                case 'cookie':
                    $this->data = array_merge($this->data, $this->request->$source());
                    break;
                default:
                    throw new RuntimeException('Unknown source: ' . $source);
            }
        }

        if ($session = $this->session->get('input')) {
            $this->session->unset('input');
            if (!$this->data) {
                $this->data   = $session['data'] ?? [];
                $this->errors = $session['errors'] ?? [];
            }
        }
    }

    /**
     * Retry the input at the referring URL
     *
     * Retains the input data using the session and returns a redirect response
     * so the user can safely resume entering the input at the referring URL.
     *
     * @param Transmitter $response
     * @return Response
     */
    public function retry(Transmitter $response)
    {
        $this->session->set('input', [
            'data'   => $this->data,
            'errors' => $this->errors,
        ]);

        return $response->redirect()->back($this->request);
    }

    /**
     * Set variable default
     *
     * @param string $var
     * @param mixed  $value
     */
    public function default($var, $value)
    {
        if (!isset($this->data[$var])) {
            $this->data[$var] = $value;
        }
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
     * Set variable
     *
     * @param string $var
     * @param mixed  $value
     */
    public function set($var, $value)
    {
        $this->data[$var] = $value;
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
            $filter = $this->filters[$filter] ??
                function (&$data) use ($filter, $params) {
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
