<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\Search\Search;
use Aternos\Model\Search\SearchResult;

/**
 * Class SearchableInterface
 * @package Aternos\Model\Driver\Features
 */
interface SearchableInterface
{
    /**
     * Execute a search query
     *
     * @param Search $search
     * @return SearchResult
     */
    public function search(Search $search): SearchResult;
}