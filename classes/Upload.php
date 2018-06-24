<?php

namespace Neat\Http;

use RuntimeException;

class Upload
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $error;

    /**
     * @var callable
     */
    protected $move;

    /**
     * @var bool
     */
    protected $moved = false;

    /**
     * File constructor
     *
     * @param string   $path
     * @param string   $name
     * @param string   $type
     * @param int      $error
     * @param callable $move
     */
    public function __construct($path, $name = null, $type = null, $error = UPLOAD_ERR_OK, $move = null)
    {
        if ($path && file_exists($path)) {
            $this->path = $path;
            $this->size = filesize($path);
        } else {
            $error = UPLOAD_ERR_NO_FILE;
        }

        $this->name  = $name;
        $this->type  = $type;
        $this->error = $error;
        $this->move  = $move ?? (PHP_SAPI === 'cli' ? 'rename' : 'move_uploaded_file');
    }

    /**
     * Move file to destination
     *
     * @param string $destination Full destination path including filename
     */
    public function moveTo($destination)
    {
        if (!$this->ok()) {
            throw new RuntimeException('Cannot move invalid file upload');
        }
        if ($this->moved) {
            throw new RuntimeException('Uploaded file already moved');
        }
        if (!($this->move)($this->path, $destination)) {
            throw new RuntimeException('Failed moving uploaded file');
        }

        $this->path  = $destination;
        $this->moved = true;
    }

    /**
     * Is this upload moved already?
     *
     * @return bool
     */
    public function moved()
    {
        return $this->moved;
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function size()
    {
        return $this->size;
    }

    /**
     * Get file name according to the client (unsafe!)
     *
     * @return string
     */
    public function clientName()
    {
        return $this->name;
    }

    /**
     * Get file type according to the client (unsafe!)
     *
     * @return string
     */
    public function clientType()
    {
        return $this->type;
    }

    /**
     * Get upload error code (one of the UPLOAD_ERR_* constants)
     *
     * @return int
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Upload ok?
     *
     * @return bool
     */
    public function ok()
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * Capture uploaded files
     *
     * @param array $files
     * @return null|Upload|Upload[]|Upload[][]|...
     */
    public static function capture($files = null)
    {
        $files = $files ?? $_FILES;
        if (!is_array($files)) {
            return null;
        }

        $keys = array_keys($files);
        sort($keys);
        $multi = $keys !== ['error', 'name', 'size', 'tmp_name', 'type'];
        if (!$multi && is_array($files['name'])) {
            $multi = true;
            $files = array_map(function ($index) use ($files) {
                return array_combine(array_keys($files), array_column($files, $index));
            }, array_keys($files['name']));
        }

        if ($multi) {
            return array_filter(array_map([static::class, 'capture'], $files));
        }

        return new static($files['tmp_name'], $files['name'], $files['type'], $files['error']);
    }
}
