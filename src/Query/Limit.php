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
    public $start = 0;

    /**
     * Length of the limit
     *
     * @var int
     */
    public $length;

    /**
     * Limit constructor.
     *
     * @param int $length
     * @param int $start
     */
    public function __construct($length, $start = 0)
    {
        $this->length = $length;
        $this->start = $start;
    }
}