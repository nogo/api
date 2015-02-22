<?php
namespace Nogo\Api\Database;

use Aura\SqlQuery\Common\SelectInterface;

interface Scope
{

    public function execute(SelectInterface $query, array $bind);

}
