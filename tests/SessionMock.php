<?php

namespace Neat\Http\Test;

use Neat\Http\Session;

class SessionMock extends Session
{
    private $active = false;

    /**
     * SessionMock constructor
     *
     * @param array $session
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(&$session = null)
    {
        $this->session = &$session ?? [];
    }

    /**
     * Session active?
     *
     * @return bool
     */
    public function active(): bool
    {
        return $this->active;
    }

    /**
     * Start (open) the session
     */
    public function start()
    {
        $this->active = true;
    }
}
