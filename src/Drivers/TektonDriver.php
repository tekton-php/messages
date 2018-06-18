<?php namespace Tekton\Messages\Drivers;

use Tekton\Session\SessionManager;
use Tekton\Messages\Drivers\DriverInterface;

class TektonDriver implements DriverInterface
{
    public function __construct(SessionManager $session)
    {
        $this->session = $session->segment(self::class);
    }

    public function set($key, $val)
    {
        $this->session->setFlashNow($key, $val);
    }

    public function get($key, $default)
    {
        return $this->session->getFlash($key, $default);
    }

    public function clear($key)
    {
        $this->session->setFlashNow($key, []);
    }
}
