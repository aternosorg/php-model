<?php

namespace Aternos\Model\Query;

/**
 * Class AverageField
 *
 * @package Aternos\Model\Query
 */
class AverageField extends SelectField
{
    public ?AggregateFunction $function = AggregateFunction::AVERAGE;
}