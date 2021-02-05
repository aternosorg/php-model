<?php

namespace Aternos\Model\Search;

/**
 * Class Search
 * @package Aternos\Model\Search
 */
class Search
{
    /**
     * @var string|null
     */
    protected ?string $modelClassName = null;

    /**
     * @var array
     */
    protected array $searchQuery = [];

    /**
     * Search constructor.
     * @param array $searchQuery
     */
    public function __construct(array $searchQuery = [])
    {
        $this->searchQuery = $searchQuery;
    }

    /**
     * @return array
     */
    public function getSearchQuery(): array
    {
        return $this->searchQuery;
    }

    /**
     * @param string|null $modelClassName
     * @return Search
     */
    public function setModelClassName(?string $modelClassName): Search
    {
        $this->modelClassName = $modelClassName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModelClassName(): ?string
    {
        return $this->modelClassName;
    }
}