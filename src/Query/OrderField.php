<?php

namespace Aternos\Model\Query;

/**
 * Class OrderField
 *
 * @package Aternos\Model\Query
 */
class OrderField
{
    /** @deprecated  */
    const Direction ASCENDING = Direction::ASCENDING;
    /** @deprecated  */
    const Direction DESCENDING = Direction::DESCENDING;

    /**
     * @var bool
     */
    public bool $raw = false;

    /**
     * OrderField constructor.
     *
     * @param string|null $field
     * @param Direction $direction
     */
    public function __construct(
        public ?string $field = null,
        public Direction $direction = Direction::ASCENDING)
    {
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
     * @param Direction $direction
     * @return OrderField
     */
    public function setDirection(Direction $direction): OrderField
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
