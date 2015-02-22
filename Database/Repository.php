<?php
namespace Nogo\Api\Database;

use Aura\Filter\Filter;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;

class Repository
{
    /**
     * @var ExtendedPdo
     */
    protected $connection;

    /**
     * @var QueryFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $scopes = [];

    /**
     * @var array
     */
    protected $relations = [];

    public function __construct($name, array $columns, ExtendedPdo $connection, QueryFactory $factory, Filter $filter)
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->connection = $connection;
        $this->factory = $factory;
        $this->filter = $filter;
        
        foreach ($this->columns as $key => $column) {
            // find primary key
            if ($column->primary && empty($this->identifier)) {
                $this->identifier = $key;
            }
            // build validator
        }
    }

    public function setConnection(ExtendedPdo $connection)
    {
        $this->connection = $connection;
    }

    public function addScope(Scope $scope)
    {
        $this->scopes[] = $scope;
    }

    public function addRelation(Relation $relation)
    {
        $this->relations[] = $relation;
    }

    public function tableName()
    {
        return $this->name;
    }

    public function identifier()
    {
        return $this->identifier;
    }

    public function find($id)
    {
        return $this->findBy($this->identifier(), $id);
    }

    public function findByData(array $data)
    {
        $result = [];
        if (isset($data[$this->identifier()])) {
            $result = $this->find($data[$this->identifier()]);
        }
        return $result;
    }

    /**
     * Find one entity by name and value.
     *
     * @param $name
     * @param $value
     * @return array | boolean
     */
    public function findBy($name, $value)
    {
        $select = $this->factory->newSelect();
        $select->cols(['*'])
                ->from($this->tableName())
                ->where($name . ' = :' . $name)
                ->orderBy([$this->identifier() . ' ASC']);
        $result = $this->connection->fetchOne($select->__toString(), [ $name => $value ]);
        return $result;
    }

    public function findAll()
    {
        $select = $this->factory->newSelect();
        $select->cols(['*'])
                ->from($this->tableName())
                ->orderBy([$this->identifier() . ' ASC']);

        return $this->connection->fetchAll($select->__toString());
    }

    /**
     * Persist entity, do insert if entity has now identifier and update with identifier.
     *
     * @param array $data
     * @return int identifier
     */
    public function persist(array $data)
    {
        // TODO Validate data
        if (isset($data[$this->identifier()])) {
            $update = $this->factory->newUpdate();
            $update->table($this->tableName());
            $update->cols(array_keys($data));
            $update->where($this->identifier() . ' = :' . $this->identifier());
            
            $this->connection->perform($update, $data);
            $result = $data[$this->identifier()];
        } else {
            $insert = $this->factory->newInsert();
            $insert->into($this->tableName());
            $insert->cols($data);
            $this->connection->perform($insert, $data);
            $result = $this->connection->lastInsertId();
        }
        return $result;
    }

    /**
     * Delete entity.
     *
     * @param $id
     * @return int deleted rows
     */
    public function remove($id)
    {
        $delete = $this->factory->newDelete();
        $delete->from($this->tableName())
                ->where($this->identifier() . ' = :' . $this->identifier());
        
        return $this->connection->perform($delete, array($this->identifier() => $id));
    }
}