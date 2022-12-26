<?php
namespace Jankx\PostLayout\Layout\Tabs;

class Tab
{
    protected $title;
    protected $type;
    protected $type_name;
    protected $object_id;
    protected $url;

    public function __construct($title, $object, $url = null)
    {
        $this->title = $title;
        $this->url = $url;

        if (is_array($object)) {
            $this->type = array_get($object, 'type');
            $this->type_name = array_get($object, 'type_name');
            $this->object_id = array_get($object, 'id');
        }
    }

    public function validateObject()
    {
        if (is_null($this->type)) {
            return !empty($this->url);
        }
        return $this->type && $this->type_name && $this->object_id;
    }

    public function isValid()
    {
        return ($this->title && $this->validateObject());
    }

    public function __isset($name)
    {
        return property_exists($this, $name);
    }

    public function __get($name)
    {
        return $this->$name;
    }
}
