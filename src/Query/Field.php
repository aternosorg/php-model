<?php

namespace Aternos\Model\Query;

/**
 * Class Field
 *
 * @package Aternos\Model\Query
 */
abstract class Field
{
    /**
     * Field key
     *
     * @var string|null
     */
    public ?string $key;

    /**
     * Field constructor.
     *
     * @param string|null $key
     */
    public function __construct(?string $key = null)
    {
        $this->key = $key;
    }

    /**
     * @param string|null $key
     * @return Field
     */
    public function setKey(?string $key): Field
    {
        $this->key = $key;
        return $this;
    }
}