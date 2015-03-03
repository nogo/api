<?php
namespace Nogo\Api\Database;

use Aura\SqlQuery\Common\SelectInterface;

/**
 * Relation will add a relation to a
 * repository find, findBy, findByData, findAll function.
 * 
 */
interface Relation
{

    /**
     * @param SelectInterface $query
     */
    public function execute(SelectInterface $query);
    
}
