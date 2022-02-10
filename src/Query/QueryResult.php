<?php

namespace Aternos\Model\Query;

use Aternos\Model\ModelCollectionResult;

/**
 * Class QueryResult
 *
 * @author Matthias Neid
 * @package Aternos\Model\Query
 */
class QueryResult extends ModelCollectionResult
{
    /**
     * Raw query string that was executed
     *
     * (mainly for debugging/logging reasons)
     *
     * @var string|null
     */
    protected ?string $queryString = null;

    /**
     * QueryResult constructor.
     *
     * @param bool $success
     * @param array $result Containing models (ModelInterface)
     * @param string|null $queryString
     */
    public function __construct(bool $success, array $result = [], ?string $queryString = null)
    {
        parent::__construct($success, $result);
        $this->queryString = $queryString;
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