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
}
