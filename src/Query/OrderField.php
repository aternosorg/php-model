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
     * @var Direction
     */
    public Direction $direction = Direction::ASCENDING;

    /**
     * OrderField constructor.
     *
     * @param string|null $field
     * @param Direction $direction
     */
    public function __construct(?string $field = null, Direction $direction = Direction::ASCENDING)
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