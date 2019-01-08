<?php

namespace Aternos\Model\Query;

/**
 * Class WhereConditionGroup
 *
 * @author Matthias Neid
 * @package Aternos\Model\Query
 */
class WhereGroup
{
    /**
     * Conjunction values
     */
    const AND = 0;
    const OR = 1;

    /**
     * Multiple WhereGroup or WhereCondition objects
     *
     * @var array
     */
    protected $group = [];

    /**
     * @var int
     */
    public $conjunction = self::AND;
}