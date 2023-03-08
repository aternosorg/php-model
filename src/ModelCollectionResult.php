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
     * Success state of the result
     *
     * @var bool
     */
    protected bool $success;

    /**
     * ModelCollectionResult constructor.
     *
     * @param bool $success
     * @param TModel[] $result
     */
    public function __construct(bool $success, array $result = [])
    {
        $this->success = $success;
        if (is_array($result)) {
            $this->models = $result;
        }
    }

    /**
     * Check if the query was successful
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return (bool)$this->success;
    }
}