<?php

namespace Aternos\Model\Driver\Features;

use Aternos\Model\Search\Search;

/**
 * Class SearchCountableInterface
 * @package Aternos\Model\Driver\Features
 */
interface SearchCountableInterface
{
    /**
     * Execute a count for a search query
     *
     * @param Search $search
     * @return int
     */
    public function searchCount(Search $search): int;
}
