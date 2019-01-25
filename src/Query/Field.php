<?php

namespace Aternos\Model\Query;

/**
 * Class Field
 *
 * @author Matthias Neid
 * @package Aternos\Model\Query
 */
class Field
{
    /**
     * Field key
     *
     * @var string|null
     */
    public $key;

    /**
     * Field value
     *
     * @var string|null
     */
    public $value;

    /**
     * Field constructor.
     *
     * @param string|null $key
     * @param string|null $value
     */
    public function __construct($key = null, $value = null)
    {
        $this->key = $key;
        $this->value = $value;
    }
}