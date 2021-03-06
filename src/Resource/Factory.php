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
     * Create new model object
     * @param string $resource
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($resource, array $data)
    {
        $modelClass = $this->getClass($resource);

        $model = new $modelClass();
        $model->fill($data);

        return $model;
    }

    /**
     * Create new model object with its relations
     *
     * @param string $resource
     * @param array $data
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createWith($resource, array $data)
    {
        $model = $this->create($resource, $data);

        // Bind related models
        if (!empty($this->config[$resource]['with'])) {
            foreach ($this->config[$resource]['with'] as $relationName) {
                // has related model
                if (array_key_exists($relationName, $data)) {
                    $relatedData = $data[$relationName];
                    $foreignKey = $relationName . '_id';

                    if (is_null($relatedData) || $relatedData === 0) {
                        $model->$foreignKey = null;
                    } else if (is_int($relatedData)) {
                        $model->$foreignKey = $relatedData;
                    } else {
                        if (isset($relatedData['id'])) {
                            $relatedModelClass = $this->getClass($relationName);
                            $relatedModel = $relatedModelClass::where('user_id', $userId)->find($relatedData['id']);
                            $model->$relationName()->associate($relatedModel);
                        } else if (isset($relatedData['alias'])) {
                            $relatedModelClass = $this->getClass($relationName);
                            $relatedModel = $relatedModelClass::where('user_id', $userId)->where('alias', $relatedData['alias'])->first();
                            if ($relatedModel == NULL) {
                                $relatedModel = $this->create($relationName, $relatedData);
                                $relatedModel->user_id = $userId;
                            }
                            $model->$relationName()->associate($relatedModel);
                        }
                    }
                }
            }
        }

        return $model;
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
