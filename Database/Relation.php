<?php
namespace Nogo\Api\Database;

use Aura\SqlQuery\Common\SelectInterface;

interface Relation
{

    public function execute(SelectInterface $query);
    
}
