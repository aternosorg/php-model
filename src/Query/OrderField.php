<?php

namespace Aternos\Model\Query;

/**
 * Class OrderField
 *
 * @package Aternos\Model\Query
 */
class OrderField
{
    /**
     * Direction constants
     */
    const ASCENDING = 0;
    const DESCENDING = 1;

    /**
     * Field name to order by
     *
     * @var string
     */
    public $field;

    /**
     * Order direction
     *
     * @var int
     */
    public $direction = self::ASCENDING;

    /**
     * OrderField constructor.
     *
     * @param string|null $field
     * @param int $direction
     */
    public function __construct(string $field = null, int $direction = self::ASCENDING)
    {
        $this->field = $field;
        $this->direction = $direction;
    }
}