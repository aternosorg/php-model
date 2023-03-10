<?php

namespace Aternos\Model\Query;

/**
 * Class UpdateField
 *
 * @package Aternos\Model\Query
 */
class UpdateField extends Field
{
    /**
     * Field key
     *
     * @var mixed|null
     */
    public mixed $value;

    /**
     * UpdateField constructor.
     *
     * @param string|null $key
     * @param mixed|null $value
     */
    public function __construct(?string $key = null, mixed $value = null)
    {
        parent::__construct($key);
        $this->value = $value;
    }

    /**
     * @param mixed|null $value
     * @return $this
     */
    public function setValue(mixed $value): UpdateField
    {
        $this->value = $value;
        return $this;
    }
}