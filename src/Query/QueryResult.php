<?php

namespace Aternos\Model\Query;

use Aternos\Model\ModelCollection;

/**
 * Class QueryResult
 *
 * @author Matthias Neid
 * @package Aternos\Model\Query
 */
class QueryResult extends ModelCollection
{
    /**
     * Success state of the query
     *
     * @var bool
     */
    protected $success;

    /**
     * Raw query string that was executed
     *
     * (mainly for debugging/logging reasons)
     *
     * @var string|null
     */
    protected $queryString = null;

    /**
     * QueryResult constructor.
     *
     * @param bool $success
     * @param array $result Containing models (ModelInterface)
     * @param string|null $queryString
     */
    public function __construct(bool $success, $result = [], ?string $queryString = null)
    {
        $this->success = $success;
        $this->queryString = $queryString;
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

    /**
     * Get values from the current model
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->valid()) {
            $model = $this->current();
        } else {
            $model = $this->models[0];
        }

        if (isset($model->{$key})) {
            return $model->{$key};
        } else {
            return null;
        }
    }

    /**
     * Get the executed query string
     *
     * @return string
     */
    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    /**
     * @param string|null $queryString
     * @return QueryResult
     */
    public function setQueryString(?string $queryString): QueryResult
    {
        if ($this->queryString === null) {
            $this->queryString = $queryString;
        }
        return $this;
    }
}