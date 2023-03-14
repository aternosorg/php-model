<?php

namespace Aternos\Model\Query;

/**
 * Class AverageField
 *
 * @package Aternos\Model\Query
 */
class AverageField extends SelectField
{
    public ?int $function = self::AVERAGE;
}