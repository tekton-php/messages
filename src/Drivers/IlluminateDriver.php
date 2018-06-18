<?php namespace Tekton\Messages\Drivers;

use Illuminate\Session\SessionManager;
use Tekton\Messages\Drivers\DriverInterface;

class IlluminateDriver implements DriverInterface
{
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    public function set($key, $val)
    {
        $this->session->now($key, $val);
    }

    public function get($key, $default)
    {
        return $this->session->get($key, $default);
    }

    public function clear($key)
    {
        $this->session->forget($key);
    }
}
