<?php

namespace Nogo\Api\Resource;

/**
 * Identifier
 *
 * @author Danilo Kuehn <dk@nogo-software.de>
 */
class Factory implements \ArrayAccess, \Countable
{
    protected $config = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Model class
     * @param string $resource
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getClass($resource)
    {
        $name = NULL;
        if (isset($this->config[$resource]) && isset($this->config[$resource]['model'])) {
            $name = $this->config[$resource]['model'];
        }

        return $name;
    }

    /**
     * Model with relations
     * @param string $resource
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function with($resource, array $with = array())
    {
        $name = $this->getClass($resource);
        if (isset($this->config[$resource]) && isset($this->config[$resource]['with'])) {
            $with = array_merge($this->config[$resource]['with'], $with);
        }
        return $name::with($with);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public function count()
    {
        return count($this->config);
    }
}
