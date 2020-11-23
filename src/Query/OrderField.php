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
     * @var string|null
     */
    public ?string $field = null;

    /**
     * @var bool
     */
    public bool $raw = false;

    /**
     * Order direction
     *
     * @var int
     */
    public int $direction = self::ASCENDING;

    /**
     * OrderField constructor.
     *
     * @param string|null $field
     * @param int $direction
     */
    public function __construct(?string $field = null, int $direction = self::ASCENDING)
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    /**
     * @param bool $raw
     * @return OrderField
     */
    public function setRaw(bool $raw): OrderField
    {
        $this->raw = $raw;
        return $this;
    }

    /**
     * @param int $direction
     * @return OrderField
     */
    public function setDirection(int $direction): OrderField
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @param string $field
     * @return OrderField
     */
    public function setField(string $field): OrderField
    {
        $this->field = $field;
        return $this;
    }
}