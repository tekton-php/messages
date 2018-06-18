<?php namespace Tekton\Messages\Drivers;

interface DriverInterface
{
    function set($key, $val);
    function get($key, $default);
    function clear($key);
}
