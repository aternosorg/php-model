<?php

namespace Aternos\Model\Query;

/**
 * Class CountField
 *
 * @package Aternos\Model\Query
 */
class CountField extends SelectField
{
    public const COUNT_FIELD = "count";

    public ?int $function = self::COUNT;
    public ?string $alias = self::COUNT_FIELD;

    public function __construct()
    {
        parent::__construct("*");
    }
}