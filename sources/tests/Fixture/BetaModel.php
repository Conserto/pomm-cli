<?php

namespace Model\PommTest\PommTestSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\Projection;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;

use PommProject\Foundation\Where;

use Model\PommTest\PommTestSchema\AutoStructure\Beta as BetaStructure;
use Model\PommTest\PommTestSchema\Beta;

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
        $this->structure = new BetaStructure();
        $this->flexible_entity_class = Beta::class;
    }
}
