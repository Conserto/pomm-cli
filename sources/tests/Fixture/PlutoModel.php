<?php

namespace Model\PommTest\PommTestSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\ModelTrait\ReadQueries;
use Model\PommTest\PommTestSchema\AutoStructure\Pluto as PlutoStructure;

/**
 * Model class for materialized view pluto.
 *
 * @see Model
 * @extends Model<Pluto>
 */
class PlutoModel extends Model
{
    /** @use ReadQueries<Pluto> */
    use ReadQueries;

    public function __construct()
    {
        $this->structure = new PlutoStructure();
        $this->flexibleEntityClass = Pluto::class;
    }
}
