<?php

namespace Model\PommTest\PommTestSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\ModelTrait\ReadQueries;
use Model\PommTest\PommTestSchema\AutoStructure\Dingo as DingoStructure;

/**
 * Model class for view dingo.
 *
 * @see Model
 * @extends Model<Dingo>
 */
class DingoModel extends Model
{
    use ReadQueries;

    public function __construct()
    {
        $this->structure = new DingoStructure;
        $this->flexibleEntityClass = Dingo::class;
    }
}
