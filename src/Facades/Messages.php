<?php namespace Tekton\Messages\Facades;

class Messages extends \Dynamis\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'messages';
    }
}
