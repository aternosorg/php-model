<?php

namespace Aternos\Model;

/**
 * Class ModelCollectionResult
 *
 * @template TModel of ModelInterface
 * @extends ModelCollection<TModel>
 * @package Aternos\Model
 */
class ModelCollectionResult extends ModelCollection
{
    /**
     * ModelCollectionResult constructor.
     *
     * @param TModel[] $result
     */
    public function __construct(array $result = [])
    {
        if (is_array($result)) {
            $this->models = $result;
        }
    }
}
