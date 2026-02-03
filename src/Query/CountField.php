<?php

namespace Aternos\Model\Query;

/**
 * Class CountField
 *
 * @package Aternos\Model\Query
 */
class CountField extends SelectField
{
    public const string COUNT_FIELD = "count";

    public ?AggregateFunction $function = AggregateFunction::COUNT;
    public ?string $alias = self::COUNT_FIELD;

    public function __construct()
    {
        parent::__construct("*");
    }
}
