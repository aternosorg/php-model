<?php

namespace Aternos\Model\Search;

use Aternos\Model\ModelInterface;

/**
 * Class Search
 * @package Aternos\Model\Search
 */
class Search
{
    /**
     * The class name of the model to search
     * @var class-string<ModelInterface>|null
     */
    protected ?string $modelClassName = null;

    /**
     * The search query
     * @var array
     */
    protected array $searchQuery = [];

    /**
     * Additional parameters for the search
     * @var string[]
     */
    protected array $parameters = [];

    /**
     * Search constructor.
     * @param array $searchQuery
     * @param array $parameters
     */
    public function __construct(
        array $searchQuery = [],
        array $parameters = [],
    )
    {
        $this->searchQuery = $searchQuery;
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getSearchQuery(): array
    {
        return $this->searchQuery;
    }

    /**
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param class-string<ModelInterface>|null $modelClassName
     * @return Search
     */
    public function setModelClassName(?string $modelClassName): Search
    {
        $this->modelClassName = $modelClassName;
        return $this;
    }

    /**
     * @return class-string<ModelInterface>|null
     */
    public function getModelClassName(): ?string
    {
        return $this->modelClassName;
    }
}
