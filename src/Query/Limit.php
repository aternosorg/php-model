<?php

namespace Aternos\Model\Query;

/**
 * Class Limit
 *
 * @package Aternos\Model\Query
 */
class Limit
{
    /**
     * Index to start counting the limit
     *
     * @var int
     */
    public int $start = 0;

    /**
     * Length of the limit
     *
     * @var int|null
     */
    public ?int $length = null;

    /**
     * Limit constructor.
     *
     * @param int $length
     * @param int $start
     */
    public function __construct(int $length, int $start = 0)
    {
        $this->length = $length;
        $this->start = $start;
    }
}