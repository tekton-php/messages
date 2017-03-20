<?php namespace Tekton\Messages;

use Tekton\Session\SessionManager;

class MessageManager {

    protected $session;

    function __construct(SessionManager $session) {
        $this->session = $session->segment(self::class);
    }

    function set($type, $value) {
        $messages = $this->session->getFlash($type);

        if (empty($messages)) {
            $messages = array();
        }

        return $this->session->setFlashNow($type, array_merge($messages, array($value)));
    }

    function get($type) {
        return $this->session->getFlash($type);
    }

    function success($message) {
        return $this->set('success', $message);
    }

    function info($message) {
        return $this->set('info', $message);
    }

    function warning($message) {
        return $this->set('warning', $message);
    }

    function error($message) {
        return $this->set('error', $message);
    }

    function data($key, $data = null) {
        if (is_null($data)) {
            return $this->get('data-'.$key);
        }

        return $this->set('data-'.$key, $data);
    }

    function clear() {
        return $this->session->clearFlash();
    }

    function all() {
        return array_filter(array(
            'success' => self::get('success'),
            'info' => self::get('info'),
            'warning' => self::get('warning'),
            'error' => self::get('error'),
        ));
    }
}
