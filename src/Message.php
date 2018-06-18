<?php namespace Tekton\Messages;

use Tekton\Messages\MessageManager;

class Message
{
    protected $type;
    protected $key;
    protected $manager;
    public $content;

    public function __construct(MessageManager &$manager, $type, $key, $content)
    {
        $this->manager = $manager;
        $this->type = $type;
        $this->key = $key;
        $this->content = $content;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getTags()
    {
        // Get current list of tags
        $tags = $this->manager->getTags();
        $matches = [];

        if (! empty($tags)) {
            return [];
        }

        // Check if any of the tags references this message
        foreach ($tags as $tag => $messages) {
            foreach ($messages as $message) {
                list($refType, $refKey) = $message;

                if ($refType == $this->type && $refKey == $this->key) {
                    $matches[] = $tag;
                }
            }
        }

        return $matches;
    }

    public function tag($newTags)
    {
        // Get current list of tags
        $tags = $this->manager->getTags();
        $newTags = (! is_array($newTags)) ? [$newTags] : $newTags;

        foreach ($newTags as $tag) {
            if (! isset($tags[$tag])) {
                $tags[$tag] = [];
            }

            // Add reference
            $tags[$tag][] = [$this->type, $this->key];
        }

        // Save new list of tags to session
        $this->manager->setTags($tags);

        // Make it possible to add tags in chain
        return $this;
    }

    public function hasTag($tag)
    {
        // Get current list of tags
        $tags = $this->manager->getTags();

        if (! isset($tags[$tag])) {
            return false;
        }

        // Check if any of the tags references this message
        foreach ($tags[$tag] as $message) {
            list($refType, $refKey) = $message;

            if ($refType == $this->type && $refKey == $this->key) {
                return true;
            }
        }

        return false;
    }

    public function __call($method, $param)
    {
        return $this->tag($method);
    }

    public function __toString()
    {
        return $this->getContent();
    }
}
