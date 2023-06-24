<?php

namespace Aternos\Model\Query;

use Aternos\Model\ModelCollectionResult;
use Aternos\Model\ModelInterface;

/**
 * Class QueryResult
 *
 * @template TModel of ModelInterface
 * @extends ModelCollectionResult<TModel>
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
     * Number of affected rows in update or delete queries
     *
     * @var int|null
     */
    protected ?int $affectedRows = null;

    /**
     * QueryResult constructor.
     *
     * @param bool $success
     * @param TModel[] $result
     * @param string|null $queryString
     */
    public function __construct(bool $success, array $result = [], ?string $queryString = null)
    {
        parent::__construct($success, $result);
        $this->queryString = $queryString;
    }

    /**
     * Get the executed query string
     *
     * @return string|null
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

    /**
     * @return int|null
     */
    public function getAffectedRows(): ?int
    {
        return $this->affectedRows;
    }

    /**
     * @param int|null $affectedRows
     * @return $this
     */
    public function setAffectedRows(?int $affectedRows): static
    {
        if ($this->affectedRows === null) {
            $this->affectedRows = $affectedRows;
        }
        return $this;
    }
}