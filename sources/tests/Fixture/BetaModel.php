<?php

namespace Model\PommTest\PommTestSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;
use Model\PommTest\PommTestSchema\AutoStructure\Beta as BetaStructure;

/**
 * Model class for table beta.
 *
 * @see Model
 */
class BetaModel extends Model
{
    use WriteQueries;

    public function __construct()
    {
        $this->structure = new BetaStructure;
        $this->flexible_entity_class = Beta::class;
    }
}
