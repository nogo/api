<?php
namespace Nogo\Api\Database;

use Aura\SqlQuery\Common\SelectInterface;

/**
 * Scope will restrict the output of a repository
 * find, findBy, findByData, findAll, remove function.
 */
interface Scope
{

    /**
     *
     * @param SelectInterface $query
     * @param array $bind
     */
    public function execute(SelectInterface $query, array $bind);

}
