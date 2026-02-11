<?php

namespace Aternos\Model\Search;

use Aternos\Model\ModelCollection;
use Aternos\Model\ModelInterface;

/**
 * Class SearchResult
 * @template TModel of ModelInterface
 * @extends ModelCollection<TModel>
 * @package Aternos\Model\Search
 */
class SearchResult extends ModelCollection
{
    protected ?int $searchTime = null;
    protected ?int $totalCount = null;
    protected ?CountRelation $totalCountRelation = null;
    protected ?object $aggregations = null;

    /**
     * @return int|null
     */
    public function getSearchTime(): ?int
    {
        return $this->searchTime;
    }

    /**
     * @param int|null $searchTime
     * @return $this
     */
    public function setSearchTime(?int $searchTime): static
    {
        $this->searchTime = $searchTime;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    /**
     * @param int|null $totalCount
     * @return $this
     */
    public function setTotalCount(?int $totalCount): static
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    /**
     * @return CountRelation|null
     */
    public function getTotalCountRelation(): ?CountRelation
    {
        return $this->totalCountRelation;
    }

    /**
     * @param CountRelation|null $totalCountRelation
     * @return $this
     */
    public function setTotalCountRelation(?CountRelation $totalCountRelation): static
    {
        $this->totalCountRelation = $totalCountRelation;
        return $this;
    }

    /**
     * Aggregations returned by OpenSearch, if any.
     * The structure of this object depends on the aggregations defined in the search query.
     *
     * @return object|null
     */
    public function getAggregations(): ?object
    {
        return $this->aggregations;
    }

    /**
     * @param object|null $aggregations
     * @return $this
     */
    public function setAggregations(?object $aggregations): static
    {
        $this->aggregations = $aggregations;
        return $this;
    }
}
