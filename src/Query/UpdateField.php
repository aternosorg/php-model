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
    public $value;

    /**
     * UpdateField constructor.
     *
     * @param null $key
     * @param null $value
     */
    public function __construct($key = null, $value = null)
    {
        parent::__construct($key);
        $this->value = $value;
    }

    /**
     * @param mixed|null $value
     * @return UpdateField
     */
    public function setValue($value): UpdateField
    {
        $this->value = $value;
        return $this;
    }
}