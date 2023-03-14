<?php

namespace Aternos\Model\Query;

/**
 * Class SumField
 *
 * @package Aternos\Model\Query
 */
class SumField extends SelectField
{
    public ?int $function = self::SUM;
}