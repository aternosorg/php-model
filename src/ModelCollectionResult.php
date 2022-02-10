<?php

namespace Aternos\Model;

/**
 * Class ModelCollectionResult
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
     * @param array $result Containing models (ModelInterface)
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