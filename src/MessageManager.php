<?php namespace Tekton\Messages;

use InvalidArgumentException;
use Tekton\Messages\Drivers\DriverInterface;

class MessageManager
{
    protected $session;
    protected $types = [];

    public function __construct(DriverInterface $session)
    {
        $this->session = $session;
    }

    protected function getTypes()
    {
        return $this->session->get('_types', []);
    }

    protected function setTypes($types)
    {
        $this->session->set('_types', $types);
    }

    protected function getTags()
    {
        return $this->session->get('_tags', []);
    }

    protected function setTags($tags)
    {
        $this->session->set('_tags', $tags);
    }

    protected function add($type, $key = null, $content = null, $tags = null)
    {
        $types = $this->getTypes();

        // Add type if it's not defined
        if (! in_array($type, $types)) {
            $types[] = $type;
            $this->setTypes($types);
        }

        // Get selected message bag
        $messages = $this->session->get($type, []);
        $key = (! is_null($key)) ? $key : count($messages);

        // Add to bag
        $messages[$key] = $content;
        $this->session->set($type, $messages);

        // Create message instance (we don't store this in the session Since
        // it can't be serialized)
        $message = new Message($this, $type, $key, $content);

        // Add reference to tag (they are stored separately in the session)
        if (! is_null($tags)) {
            $message->tag($tags);
        }

        // Return Message so that it can be chained
        return $message;
    }

    public function remove($type, $key = null)
    {
        if ($type instanceof Message) {
            $key = $type->getKey();
            $type = $type->getType();
        }

        $messages = $this->get($type, null, true);

        // Remove from message bag
        if (! isset($messages[$key])) {
            return null;
        }
        else {
            unset($messages[$key]);
            $this->session->set($type, $messages);
        }

        // If this was the last message of type we must remove it from the types list
        if (empty($messages)) {
            $types = $this->getTypes();
            unset($types[$type]);
            $this->setTypes($types);
        }

        // Remove from tags as well
        $tags = $this->getTags();

        if (! empty($tags)) {
            foreach ($tags as $tag => $messages) {
                foreach ($messages as $tagKey => $message) {
                    list($refType, $refKey) = $message;

                    if ($refType == $type && $refKey == $key) {
                        unset($tags[$tag][$tagKey]);

                        if (empty($tags[$tag])) {
                            unset($tags[$tag]);
                        }
                    }
                }
            }

            $tags = $this->setTags($tags);
        }

        return true;
    }

    public function get($type, $key = null, $contentOnly = false)
    {
        $messages = $this->session->get($type, []);

        // If key is set we return only that message
        if (! is_null($key)) {
            if (isset($messages[$key])) {
                return ($contentOnly)
                    ? $messages[$key]
                    : (new Message($this, $type, $key, $messages[$key]));
           }
           else {
               return null;
           }
        }

        // Create Message instances and return
        if (! $contentOnly) {
            foreach ($messages as $key => $content) {
                $messages[$key] = new Message($this, $type, $key, $content);
            }
        }

        return $messages;
    }

    public function all($flatten = false, $includeData = false, $contentOnly = false)
    {
        // Create an associative array listing all types
        $all = array_flip($this->getTypes());

        if (! $includeData && isset($all['data'])) {
            unset($all['data']);
        }

        foreach ($all as $type => $index) {
            $all[$type] = $this->get($type, null, $contentOnly);
        }

        return ($flatten) ? $this->flatten($all) : $all;
    }

    public function has($type, $key = null)
    {
        return ! empty($this->get($type, $key));
    }

    public function hasAny($includeData = false)
    {
        return ! empty($this->all($includeData));
    }

    public function getTagged($type, $tag, $contentOnly = false)
    {
        // Retrieve all tags
        $tags = $this->getTags();
        $messages = [];

        // Check specific tag
        if (isset($tags[$tag])) {
            // Find messages with same type
            foreach ($tags as $references) {
                foreach ($references as $reference) {
                    list($refType, $refKey) = $reference;

                    if ($type == $refType) {
                        $messages[] = $this->get($refType, $refKey, $contentOnly);
                    }
                }
            }
        }

        return $messages;
    }

    public function allTagged($tag, $flatten = false, $contentOnly = false)
    {
        // Retrieve all tags
        $tags = $this->getTags();
        $messages = [];

        // Check specific tag
        if (isset($tags[$tag])) {
            // Find messages by their type and key
            foreach ($tags as $references) {
                foreach ($references as $reference) {
                    list($refType, $refKey) = $reference;

                    if (! isset($messages[$refType])) {
                        $messages[$refType] = [];
                    }

                    $messages[$refType][] = $this->get($refType, $refKey, $contentOnly);
                }
            }
        }

        return ($flatten) ? $this->flatten($messages) : $messages;
    }

    public function hasTagged($type, $tag)
    {
        return ! empty($this->getTagged($type, $tag));
    }

    public function hasAnyTagged($tag)
    {
        return ! empty($this->allTagged($tag));
    }

    public function data($key, $data, $tags = [])
    {
        return $this->add('data', $key, $data, $tags);
    }

    public function clear($type = null)
    {
        // Either clear all messages or just the specified type
        if (is_null($type)) {
            foreach ($this->getTypes() as $type) {
                $this->session->clear($type);
            }
        }
        elseif ($this->has($type)) {
            $this->session->clear($type);
        }
    }

    protected function flatten($messages)
    {
        $flat = [];

        foreach ($messages as $type => $messages) {
            $flat = array_merge($flat, $messages);
        }

        return $flat;
    }

    /*
     * Alias for add but only accepting message and tags (no key - only data allows for key)
     */
    public function __call($method, $param)
    {
        // Add to bag
        $type = $method;

        if (count($param) >= 1) {
            $message = $param[0];
            $tags = (count($param) > 1) ? $param[0] : null;

            return $this->add($type, null, $message, $tags);
        }
        else {
            throw new InvalidArgumentException('You must provide a message.');
        }
    }
}
