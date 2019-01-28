<?php

namespace Aternos\Model\Query\Generator;

use Aternos\Model\Query\Query;

/**
 * Interface QueryGeneratorInterface
 *
 * @package Aternos\Model\Query\Generator
 */
interface QueryGeneratorInterface
{
    /**
     * Generate a query string from a Query object
     *
     * @param Query $query
     * @return string
     */
    public function generate(Query $query): string;
}